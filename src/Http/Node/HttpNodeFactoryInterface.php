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

use noFlash\CherryHttp\EventHandler\Request\RequestHandlerInterface;
use noFlash\CherryHttp\Server\Node\NodeFactoryInterface;

/**
 * Objects implementing this interface are creating HTTP-specific nodes (but still backward complaint with standard
 * loop nodes)
 */
interface HttpNodeFactoryInterface extends NodeFactoryInterface
{
    /**
     * Most of HTTP server specific nodes are able to handle request. They however need to know where to pass
     * the new request - here comes request handler.
     * Using this method you can set a default one for particular nodes factory.
     *
     * @param RequestHandlerInterface $requestHandler
     *
     * @return void
     */
    public function setDefaultRequestHandler(RequestHandlerInterface $requestHandler);
}
