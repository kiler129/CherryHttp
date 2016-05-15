<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Http\Node;

use noFlash\CherryHttp\Http\Response\ErrorResponse;
use noFlash\CherryHttp\Http\Response\ResponseCode;
use noFlash\CherryHttp\IO\Stream\BufferAwareAbstractStreamNode;

/**
 * TrapHttpNode is an standard HTTP node for not configured framework.
 * It's marked as final since you should not modify it.
 */
final class TrapHttpNode extends BufferAwareAbstractStreamNode
{
    /**
     * Prevents response regeneration on multiple processInputBuffer() calls
     *
     * @var bool
     */
    private $responseGenerated = false;


    /**
     * @inheritdoc
     */
    protected function processInputBuffer()
    {
        if (!$this->responseGenerated) { //I have no idea how to test this if
            $this->shutdownRead();

            $errorResponse = new ErrorResponse();
            $errorResponse->setStatus(ResponseCode::NOT_IMPLEMENTED);
            $errorResponse->setExplanation('This is a trap.<br/>Configure me.');
            $errorResponse->setHeader('Connection', 'Close');

            $this->writeBufferAppend((string)$errorResponse);
            $this->responseGenerated = true;
        }

        return null;
    }
}
