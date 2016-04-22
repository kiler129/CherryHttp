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

use noFlash\CherryHttp\Application\Lifecycle\LoopNodeTrait;
use noFlash\CherryHttp\Http\Node\HttpNodeFactory;
use noFlash\CherryHttp\Http\Node\HttpNodeFactoryInterface;
use noFlash\CherryHttp\IO\Network\AbstractNetworkListenerNode;
use noFlash\CherryHttp\IO\Network\NetworkListenerNodeInterface;
use noFlash\CherryHttp\IO\Network\TcpListenerNodeTrait;

/**
 * Class HttpListenerNode
 */
class HttpListenerNode extends AbstractNetworkListenerNode implements NetworkListenerNodeInterface
{
    /**
     * @var HttpNodeFactoryInterface
     */
    private $nodeFactory;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        if ($this->nodeFactory === null) {
            $this->nodeFactory = new HttpNodeFactory();
        }
    }

    /**
     * Listener node uses stream to accept connections and no data transfer is made through that stream.
     * Due to that fact creating object is useless.
     *
     * @throws \LogicException
     */
    public function getStreamObject()
    {
        throw new \LogicException('Listeners do not provide stream objects');
    }

    /**
     * @inheritdoc
     */
    public function doRead()
    {
        if (!$this->stream) {
            $this->onStreamError();
        }
    }

    /**
     * @inheritdoc
     *
     * @return HttpNodeFactoryInterface
     */
    public function getNodeFactory()
    {
        return $this->nodeFactory;
    }

    /**
     * Sets node factory on object.
     * Since it's HTTP-specific listener only HttpNodeFactory is allowed.
     *
     * @param HttpNodeFactoryInterface $nodeFactory
     */
    public function setNodeFactory(HttpNodeFactoryInterface $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
}
