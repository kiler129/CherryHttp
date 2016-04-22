<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\IO\Stream;

/**
 * Class BufferAwareAbstractStreamNode
 */
abstract class BufferAwareAbstractStreamNode extends AbstractStreamNode
{
    const READ_CHUNK_SIZE = 16384;

    /**
     * @var string Data to be pushed to the socket. It should NEVER contain data other than string!
     */
    protected $writeBuffer = '';

    /**
     * @var string Data read from the stream
     */
    protected $readBuffer = '';

    /**
     * @var bool Degenerated stream node means that stream was half-closed (or supposed to be put in this state) where
     *           data can be only pushed from server to client but any arriving data will not be accepted.
     */
    protected $isDegenerated = false;

    /**
     * @inheritdoc
     */
    public function isWriteReady()
    {
        return !($this->writeBuffer === '');
    }

    /**
     * Method called everytime stream is considered "read ready".
     * Please note that method can be also called for stream errors (e.g. remote disconnection) - it's how streams are
     * handled by PHP itself.
     *
     * @return void
     */
    public function doRead()
    {
        $data = fread($this->stream, static::READ_CHUNK_SIZE);

        if ($data === '') {
            fclose($this->stream);
            $this->stream = null; //fclose() will only leave stream resource in unknown state

        } elseif ($this->isDegenerated === false) {
            $this->readBuffer .= $data;

            //For explanation read "important note" for processInputBuffer()
            //@formatter:off
            while ($this->processInputBuffer() === false);
            //@formatter:on
        }
    }

    /**
     * @inheritdoc
     */
    public function doWrite()
    {
        //PHP, despite returning number of bytes written, throws warning if buffer overflown 
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        $bytesWritten = @fwrite($this->stream, $this->writeBuffer);

        /*
         * Fragment below looks weird at first, but it's perfectly logical.
         * Most of the time buffer is completely written by fwrite() into system TCP buffer (which is 8KB by default). In
         * that case using (rather expansive) substr() call can be avoided by checking whatever buffer was completely
         * written. To do so efficiently isset() and direct character access is used. fwrite() returns NUMBER OF BYTES
         * written. Character in strings are indexed from 0 (like arrays), so calling fwrite($socket, "abcde") return
         * 5 if it was fully written, calling isset($x[5]) produce "false" because last character have index of 4.
         */
        if (!isset($this->writeBuffer[$bytesWritten])) {
            $this->writeBuffer = '';

            if ($this->isDegenerated) {
                fclose($this->stream);
                $this->stream = null;
            }

        } else {
            $this->writeBuffer = substr($this->writeBuffer, $bytesWritten);
        }
    }

    /**
     * @inheritDoc
     */
    public function onStreamError()
    {
        $this->readBuffer = '';
        $this->writeBuffer = '';

        parent::onStreamError();
    }

    /**
     * @inheritdoc
     */
    public function writeBufferAppend($data)
    {
        $this->writeBuffer .= $data;

        return strlen($data); //Currently this method doesn't implement any length limit
    }

    /**
     * Method is called everytime some data are collected (look into doRead()).
     *
     * Important note: to prevent infinite loops this function MUST NOT return false unless it's intended to be called
     * again. At first it seems weird, but it's useful dealing with multiple "packets" of data after single buffer
     * read efficiently (using recurrence creates very deep stack eating significant amount of memory & CPU).
     *
     * @return bool|null
     */
    abstract protected function processInputBuffer();

    /**
     * This method physically shutdowns socket receiving channel effectively making it write-only.
     * After calling this method node is switched into "degenerated" state where only data already present in a buffer
     * will be sent to client (which is not guaranteed in any way!).
     * This method is equivalent of UNIX "shutdown(socket, SHUT_RD)" system call.
     *
     * Note: This method NOT GUARANTEE that no more data will arrive on socket - it only suggest that socket should be
     * switched to non-read mode. On some OSs data may still be flowing regardless of shutdownRead() call.
     *
     * @return bool
     */
    public function shutdownRead()
    {
        if (!is_resource($this->stream)) {
            return false;
        }

        $this->isDegenerated = true;

        return stream_socket_shutdown($this->stream, STREAM_SHUT_RD);
    }
}
