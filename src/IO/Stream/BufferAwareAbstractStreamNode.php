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
     * Method gets called if stream has some buffer space and data can be populated.
     *
     * @return void
     */
    public function doWrite()
    {
        // TODO: Implement doWrite() method.
        // Check if degenerated & buffer empty (if so disconnect)
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
     * @inheritdoc
     */
    public function shutdownRead()
    {
        if (!is_resource($this->stream)) {
            return false;
        }

        $this->isDegenerated = true;
        
        return stream_socket_shutdown($this->stream, STREAM_SHUT_RD);
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
}
