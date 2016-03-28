<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\IO;

use noFlash\CherryHttp\Application\Lifecycle\LoopNodeInterface;
use noFlash\CherryHttp\IO\Exception\BufferOverflowException;
use Psr\Http\Message\StreamInterface;

/**
 * Represents any stream-aware node.
 * Please note that StreamNode can carry local socket connection, it's has nothing to do with network.
 *
 * @property resource|null $stream
 */
interface StreamNodeInterface extends LoopNodeInterface
{
    /**
     * Provides PHP stream resource.
     * Output of that method MUST BE consistent with $this->stream contents.
     *
     * @return resource|null
     */
    public function getStreamResource();

    /**
     * Provides object implementing StreamInterface containing stream from current instance.
     *
     * @return StreamInterface|null Null may be returned if there's no valid stream in current instance.
     */
    public function getStreamObject();

    /**
     * There's NO isReadReady method and that was intended.
     * In order to detect stream errors stream must be always checked on read queue.
     * To prevent from checking stream just don't return it in getStream() nor hold in $this->stream
     */
    //public function isReadReady();

    /**
     * Provides information whatever underlying stream should be checked for free buffer space.
     *
     * @return bool
     */
    public function isWriteReady();

    /**
     * Method called everytime stream is considered "read ready".
     * Please note that method can be also called for stream errors (e.g. remote disconnection) - it's how streams are
     * handled by PHP itself.
     *
     * @return void
     */
    public function doRead();

    /**
     * Method gets called if stream has some buffer space and data can be populated.
     *
     * @return void
     */
    public function doWrite();

    /**
     * In rare situations stream may become invalid on such a low level that even PHP is unable to given any clue
     * besides "there's an error on one of your streams, I don't know on which and what happened". Even Linux kernel
     * will not tell you directly which stream failed during select() call.
     * This method will be called if such error was detected and in some magical way pinpointed to given stream.
     *
     * You should definitely trash it without any attempts to recover anything from it - it's gone on the kernel level.
     * For internal details see following links:
     *   http://news.php.net/php.internals/91974
     *   https://github.com/facebook/hhvm/issues/6942
     *
     * @return void
     */
    public function onStreamError();

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
    public function writeBufferAppend($data);
}
