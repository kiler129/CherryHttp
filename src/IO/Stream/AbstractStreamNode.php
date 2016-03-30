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

use noFlash\CherryHttp\Application\Lifecycle\LoopInterface;
use noFlash\CherryHttp\IO\StreamNodeInterface;

/**
 * Class AbstractStreamNode
 */
abstract class AbstractStreamNode implements StreamNodeInterface
{
    use StreamNodeTrait;

    /**
     * @var LoopInterface|null
     */
    protected $loop;

    /**
     * Every node can be "married"/attached to a loop.
     * This method returns current loop to which a node is bound.
     *
     * @return LoopInterface|null Corresponding loop object or null if not bound to loop yet.
     */
    public function getLoop()
    {
        return $this->loop;
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
        return self::PING_INTERVAL_ANY;
    }

    /**
     * Method called by loop according to interval specified by getPingInterval() method.
     *
     * @return void
     */
    public function ping()
    {
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
        @fclose($this->stream); //Well... I don't think it's gonna work really
        $this->stream = null;
    }
}
