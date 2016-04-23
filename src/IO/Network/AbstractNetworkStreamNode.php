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

use noFlash\CherryHttp\IO\Stream\AbstractStreamNode;
use noFlash\CherryHttp\IO\StreamNodeInterface;

/**
 * Represents stub for StreamNodeInterface. It contains most used method for that interface.
 * You only need to implement actual writing & reading (since this class is universal to ANY streams - local, TCP,
 * UDP, raw IP.....)
 */
abstract class AbstractNetworkStreamNode extends AbstractStreamNode implements StreamNodeInterface
{
    /**
     * @var int Determines IP protocol version. This can contain value of 4 or 6 (NetworkNodeInterface::IP_V4/6)
     */
    protected $networkIpVersion = NetworkNodeInterface::IP_V4;

    /**
     * @var string Local IP address. You could also use NetworkNodeInterface::UNDETERMINED_IPV4/6
     */
    protected $networkLocalIp = NetworkNodeInterface::UNDETERMINED_IPV4;

    /**
     * @var int Local port. Values between 0 and 65535 are generally expected here (0 is an random port per TCP/IP RFC)
     */
    protected $networkLocalPort = 0;

    /**
     * @var string|null Remote IP address. For some implementations (e.g. listeners) it's NULL for obvious reasons.
     */
    protected $networkRemoteIp;

    /**
     * @var int|null Remote port. Holds value between 0-65535 or null (e.g. for listeners).
     */
    protected $networkRemotePort;

    /**
     * @var bool Specifies if this instance is connected. Meaning of this field depends on implementation.
     */
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
