<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Http;

use noFlash\CherryHttp\EventHandler\Request\RequestHandlerInterface;
use noFlash\CherryHttp\Http\Response\ResponseInterface;
use noFlash\CherryHttp\IO\Network\NetworkNodeInterface;

/**
 * Interface HttpNodeInterface represents standard HTTP node, usually client.
 */
interface HttpNodeInterface extends NetworkNodeInterface
{
    /**
     * Higher level method than writeBufferAppend().
     * It appends new HTTP response to given connection.
     * 
     * @param ResponseInterface $response
     *
     * @return void
     */
    public function responseAppend(ResponseInterface $response);

    /**
     * HTTP request is divided into many states. To prevent tight coupling this method allows you to set external
     * request handler which will be notified about various events happening during the process of handling request.
     *
     * @return RequestHandlerInterface
     */
    public function setRequestHandler();
}
