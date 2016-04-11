<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Server\Node;

use noFlash\CherryHttp\Application\Lifecycle\LoopInterface;
use noFlash\CherryHttp\IO\Network\TcpListenerNodeTrait;
use Psr\Http\Message\StreamInterface;

/**
 * Class HttpListenerNode
 */
class HttpListenerNode /**implements TcpListenerNodeInterface**/
{
    use TcpListenerNodeTrait;


    /**
     * Every node can be "married"/attached to a loop.
     * This method returns current loop to which a node is bound.
     *
     * @return LoopInterface|null Corresponding loop object or null if not bound to loop yet.
     */
    public function getLoop()
    {
        // TODO: Implement getLoop() method.
    }

    /**
     * Node can receive periodical signals from main loop.
     * This method informs loop how often node wish to be pinged.
     * Keep in mind this value is only suggestion and should NEVER be used in any time-critical routines.
     *
     * Value is cached and retrieved only on node attachment.
     *
     * @return int Number of seconds or self::PING_INTERVAL_ANY if node can be pinged in any intervals.
     */
    public function getPingInterval()
    {
        // TODO: Implement getPingInterval() method.
    }

    /**
     * Method called by loop according to interval specified by getPingInterval() method.
     *
     * @return void
     */
    public function ping()
    {
        // TODO: Implement ping() method.
    }

    /**
     * Method called by loop after attaching to loop.
     *
     * @param LoopInterface $loop Loop to which loop was added.
     *
     * @return void
     */
    public function onAttach(LoopInterface $loop)
    {
        // TODO: Implement onAttach() method.
    }

    /**
     * Method called by loop after detaching from loop.
     * Implementation should at least remove any internal references to loop object to prevent dangling objects & leaks.
     *
     * @return void
     */
    public function onDetach()
    {
        // TODO: Implement onDetach() method.
    }

    /**
     * Provides object implementing StreamInterface containing stream from current instance.
     *
     * @return StreamInterface|null Null may be returned if there's no valid stream in current instance.
     */
    public function getStreamObject()
    {
        // TODO: Implement getStreamObject() method.
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
    public function onStreamError()
    {
        // TODO: Implement onStreamError() method.
    }

    /**
     * After connection accept node holding that connection is needed.
     * To prevent tight coupling a proper factory is needed - that method returns instance of that factory
     * which is used by current instance.
     *
     * @return NodeFactoryInterface
     */
    public function getNodeFactory()
    {
        // TODO: Implement getNodeFactory() method.
    }
}
