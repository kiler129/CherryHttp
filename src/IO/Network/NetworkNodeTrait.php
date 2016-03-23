<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\IO\Network;

/**
 * Trait implements common methods defined by NetworkNodeInterface
 * Keep in mind this trait represents just generic NetworkNode - nor listener nor stream client.
 */
trait NetworkNodeTrait
{
    protected $networkIpVersion   = NetworkNodeInterface::IP_V4;
    protected $networkLocalIp     = NetworkNodeInterface::UNDETERMINED_IPV4;
    protected $networkLocalPort   = 0;
    protected $networkRemoteIp;
    protected $networkRemotePort;
    protected $networkIsConnected = false;

    /**
     * Returns IP version used by this node.
     *
     * @return int Returns IP version (see IP_V4 and IP_V6 constants).
     */
    public function getIpVersion()
    {
        return $this->networkIpVersion;
    }

    /**
     * Provides local IP address.
     *
     * @return string IPv4 or IPv6 address.
     */
    public function getLocalIpAddress()
    {
        return $this->networkLocalIp;
    }

    /**
     * Returns port name in local system.
     *
     * @return int
     */
    public function getLocalPort()
    {
        return $this->networkLocalPort;
    }

    /**
     * Provides remote IP address.
     *
     * @return string|null IPv4 or IPv6 address. Null will be returned if IP address is not known or node is not
     *                     connected.
     */
    public function getRemoteIpAddress()
    {
        return $this->networkRemoteIp;
    }

    /**
     * Returns port name of the remote side of connection.
     *
     * @return int|null Null will be returned if port is not known or node is not connected.
     */
    public function getRemotePort()
    {
        return $this->networkRemotePort;
    }

    /**
     * Returns node connection state.
     * Please note some nodes will never reach connected state (e.g. UDP transports or listener nodes).
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->networkIsConnected;
    }
}
