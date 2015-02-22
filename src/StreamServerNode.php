<?php
namespace noFlash\CherryHttp;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;

/**
 * Generic TCP stream node representation.
 *
 * @package noFlash\CherryHttp
 */
abstract class StreamServerNode implements StreamServerNodeInterface
{
    /* @var integer Defines default chunk size read from wire. By default most (all?) systems use 8KB */
    const STREAM_CHUNK_SIZE = 8192;
    /** @var resource Client socket */
    public $socket;
    /** @var array Defines which events should be dispatched to eventsHandler by Server class */
    public $subscribedEvents = array("writeBufferEmpty" => false, "httpException" => false);
    /** @var LoggerInterface */
    protected $logger;
    protected $inputBuffer;
    protected $outputBuffer;
    /** @var string Local socket IP:PORT */
    private $peerName;
    private $ip;
    /* @var bool If set to false it will only push remaining buffers and then destruct itself */
    private $isDegenerated = false;

    /**
     * @param resource $socket Client socket returned by stream_socket_accept()
     * @param string $peerName IP:PORT of local socket returned by stream_socket_get_name()/stream_socket_accept()
     *
     * @param LoggerInterface $logger
     *
     * @throws NodeDisconnectException
     */
    public function __construct($socket, $peerName, LoggerInterface &$logger)
    {
        $this->logger = &$logger;

        $this->socket = $socket;
        $this->peerName = empty($peerName) ? stream_socket_get_name($socket, false) : $peerName;

        if (!is_resource($socket) || feof($socket)) {
            $this->logger->error("Node $this is gone before handling (server overloaded?)");
            $this->disconnect(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function getPeerName()
    {
        return $this->peerName;
    }

    /**
     * {@inheritdoc}
     */
    final public function getIp()
    {
        if ($this->ip === null) {
            $this->ip = substr($this->peerName, 0, strrpos($this->peerName, ":"));
        }

        return $this->ip;
    }

    /**
     * {@inheritdoc}
     */
    public function isWriteReady()
    {
        if ($this->outputBuffer === '') { //You CANNOT use empty() - buffer can contain single \0
            if ($this->isDegenerated) {
                $this->disconnect();
            }

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function onReadReady()
    {
        $readContent = fread($this->socket, static::STREAM_CHUNK_SIZE); //Around 13% faster than stream_get_contents()
        if ($readContent === '') { //Client disconnected
            if ($this->isDegenerated) { //It's only way to prevent client from begin dropped after using stream_socket_shutdown($this->socket, STREAM_SHUT_RD)
                return;
            }

            $this->disconnect(true);
            $this->logger->debug("Client $this socket gone (client disconnected/R)");

            return;
        }

        $this->inputBuffer .= $readContent;
        //$this->logger->debug("Got " . strlen($readContent) . " bytes from client $this");
        //$this->logger->debug("Client $this contains " . strlen($this->inputBuffer) . " bytes in read buffer"); //Time consuming

        //For explanation read "important note" for processInputBuffer()
        //@formatter:off
        while ($this->processInputBuffer() === false);
        //@formatter:on
    }

    /**
     * {@inheritdoc}
     *
     * @throws NodeDisconnectException
     */
    public function onWriteReady()
    {
        $bytesWritten = fwrite($this->socket, $this->outputBuffer);
        /*if ($bytesWritten === 0) { //This assumption can be wrong. It was observed but maybe due possible buggy pushData()?
            $this->disconnect(true);
            //$this->logger->debug("Client $this socket gone (client disconnected/W)");

            return;
        }*/

        /*
         * Fragment below looks weird at first, but it's perfectly logical.
         * Most of the time buffer is completely written by fwrite() into system TCP buffer (which is 8KB by default). In
         * that case using (rather expansive) substr() call can be avoided by checking whatever buffer was completely
         * written. To do so efficiently isset() and direct character access is used. fwrite() returns NUMBER OF BYTES
         * written. Character in strings are indexed from 0 (like arrays), so calling fwrite($socket, "abcde") return
         * 5 if it was fully written, calling isset($x[5]) produce "false" because last character have index of 4.
         */
        $this->outputBuffer = (!isset($this->outputBuffer[$bytesWritten])) ? '' : substr($this->outputBuffer,
            $bytesWritten);

        //$this->logger->debug("Sent $bytesWritten to client $this");
        //$this->logger->debug("Client $this contains " . strlen($this->outputBuffer) . " bytes in write buffer"); //Time consuming

        return ($this->outputBuffer === '');
    }

    /**
     * {@inheritdoc}
     *
     * @throws NodeDisconnectException
     */
    public function disconnect($drop = false)
    {
        if ($drop || ($this->isDegenerated && empty($this->outputBuffer))) {
            //$this->logger->debug("Dropping client $this [buff: " . strlen($this->outputBuffer) . "]");

            $this->inputBuffer = "";
            $this->outputBuffer = "";
            /** @noinspection PhpUsageOfSilenceOperatorInspection fclose() generates E_WARNING if socket is invalid, in this case we don't care if it's valid or not */
            @fclose($this->socket);
            throw new NodeDisconnectException($this);

        } else {
            //$this->logger->debug("Disconnecting client $this (waiting for output buffer to be send...)");
            stream_socket_shutdown($this->socket, STREAM_SHUT_RD); //Tell kernel to discard any input data from now
            $this->isDegenerated = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function __toString()
    {
        return $this->peerName . " (" . __CLASS__ . ")";
    }

    /**
     * @{@inheritdoc}
     */
    public function pushData($data)
    {
        if ($this->isDegenerated) {
            return false;
        }

        $this->outputBuffer .= $data;

        //$this->logger->debug("Added data to buffer, now contains " . strlen($this->outputBuffer) . " bytes"); //Time consuming
        return true;
    }

    /**
     * Method is called everytime some data are collected.
     *
     * Important note: to prevent infinite loops this function MUST NOT return false unless it's intended to be called
     * again. At first it seems weird, but it's useful dealing with multiple "packets" of data after single buffer
     * read efficiently (using recurrence creates very deep stack eating significant amount of memory & CPU).
     *
     * @return bool|null
     */
    abstract protected function processInputBuffer();

    /**
     * Subscribe to event (aka enable it).
     *
     * @param string $eventName Any valid client event name
     *
     * @throws InvalidArgumentException Invalid event name specified
     */
    public function subscribeEvent($eventName)
    {
        if (!isset($this->subscribedEvents[$eventName])) {
            throw new InvalidArgumentException("Event $eventName doesn't exists in client context");
        }

        $this->subscribedEvents[$eventName] = true;
    }

    /**
     * Unsubscribe from event (aka disable it).
     *
     * @param string $eventName Any valid event name
     *
     * @throws InvalidArgumentException Invalid event name specified
     */
    public function unsubscribeEvent($eventName)
    {
        if (!isset($this->subscribedEvents[$eventName])) {
            throw new InvalidArgumentException("Event $eventName doesn't exists in client context");
        }

        $this->subscribedEvents[$eventName] = false;
    }
}
