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
use noFlash\CherryHttp\Http\HttpNodeInterface;

/**
 * Produces HTTP nodes
 *
 * @see HttpNodeInterface
 */
class HttpNodeFactory implements HttpNodeFactoryInterface
{
    /**
     * @var HttpNodeInterface
     */
    private $baseNode;

    /**
     * Creates the factory. Since factory itself works by cloning base node (to overcome initialization times)
     * constructor accepts
     *
     * @param HttpNodeInterface $baseNode Optional base node to use during production.
     */
    public function __construct(HttpNodeInterface $baseNode = null)
    {
        if ($baseNode !== null) {
            $this->baseNode = clone $baseNode;
        }
    }

    /**
     * Returns configured HTTP node.
     *
     * @return HttpNodeInterface
     */
    public function getNode()
    {
        return clone $this->baseNode;
    }

    /**
     * Most of HTTP server specific nodes are able to handle request. They however need to know where to pass
     * the new request - here comes request handler.
     * Using this method you can set a default one for particular nodes factory, which will be than automatically
     * set on every new node.
     *
     * @param RequestHandlerInterface $requestHandler
     *
     * @return void
     * @see HttpNodeInterface::setRequestHandler()
     */
    public function setDefaultRequestHandler(RequestHandlerInterface $requestHandler)
    {
        $this->baseNode->setRequestHandler($requestHandler);
    }
}
