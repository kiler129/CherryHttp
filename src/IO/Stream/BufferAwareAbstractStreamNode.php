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

use noFlash\CherryHttp\IO\Exception\BufferOverflowException;

/**
 * Class BufferAwareAbstractStreamNode
 */
abstract class BufferAwareAbstractStreamNode extends AbstractStreamNode
{
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
     * Provides information whatever underlying stream should be checked for free buffer space.
     *
     * @return bool
     */
    public function isWriteReady()
    {
        return !($this->writeBuffer === '' || $this->isDegenerated);
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
        // TODO: Implement doRead() method.
    }

    /**
     * Method gets called if stream has some buffer space and data can be populated.
     *
     * @return void
     */
    public function doWrite()
    {
        // TODO: Implement doWrite() method.
    }

    /**
     * Appends data to stream write buffer.
     *
     * @param string $data
     *
     * @return int Number of bytes added to buffer.
     * @throws BufferOverflowException Exception is thrown if buffer overflown or it's not even available for current
     *                                 StreamNode. Since method may return 0, raising exception suggest permanent and
     *                                 unrecoverable situation. Example use-case will be one-way socket which is
     *                                 capable of only receiving data (e.g. GPS).
     */
    public function writeBufferAppend($data)
    {
        // TODO: Implement writeBufferAppend() method.
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


}
