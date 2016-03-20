<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Server\Exception;

use noFlash\CherryHttp\Application\Lifecycle\LoopInterface;

/**
 * General server error where no better exception is available.
 * That exception should be used with care, since it indicates mostly unrecoverable situation where everything
 * just crashed and there's no world ahead.
 */
class ServerException extends \RuntimeException
{
    /**
     * @var LoopInterface|null
     */
    private $loop;

    /**
     * Returns server loop where crash originates.
     *
     * @return LoopInterface|null May return null if loop is unknown.
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * Sets server loop where crash originates.
     *
     * @param LoopInterface $loop
     */
    public function setLoop(LoopInterface $loop)
    {
        $this->loop = $loop;
    }
}
