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
     * @var LoopInterface|null A loop to which stream node is attached or null if it's not attached to any
     */
    protected $loop;

    /**
     * @inheritdoc
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * @inheritdoc
     */
    public function getPingInterval()
    {
        return self::PING_INTERVAL_ANY;
    }

    /**
     * @inheritdoc
     */
    public function ping()
    {
    }

    /**
     * @inheritdoc
     */
    public function onStreamError()
    {
        @fclose($this->stream); //Well... I don't think it's gonna work really
        $this->stream = null;
    }
}
