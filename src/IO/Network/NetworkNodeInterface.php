<?php
/*
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible
 *  for everything that code will do.
 *
 * (c) Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace noFlash\CherryHttp\IO\Network;

use noFlash\CherryHttp\IO\StreamNodeInterface;

/**
 * This interface describes generic network-aware node.
 * It could represent both TCP and UDP connection. The only requirement for NetworkNode is IP layer.
 */
interface NetworkNodeInterface extends StreamNodeInterface
{
    const IP_V4 = 4;
    const IP_V6 = 6;

    /**
     * Returns IP version used by this node.
     *
     * @return int Returns IP version (see IP_V4 and IP_V6 constants).
     */
    public function getIpVersion();

    /**
     * Provides peer name for current node.
     *
     * @return string|null Null will be returned if peer name is not known.
     */
    public function getPeerName();

    /**
     * Provides local IP address.
     *
     * @return string IPv4 or IPv6 address.
     */
    public function getLocalIpAddress();

    /**
     * Returns port name in local system.
     *
     * @return int
     */
    public function getLocalPort();

    /**
     * Provides remote IP address.
     *
     * @return string|null IPv4 or IPv6 address. Null will be returned if IP address is not known or node is not
     *                     connected.
     */
    public function getRemoteIpAddress();

    /**
     * Returns port name of the remote side of connection.
     *
     * @return int|null Null will be returned if port is not known or node is not connected.
     */
    public function getRemotePort();

    /**
     * Returns node connection state.
     * Please note some nodes will never reach connected state (e.g. UDP transports or listener nodes).
     *
     * @return bool
     */
    public function isConnected();

    /**
     * Disconnects current node from remote endpoint.
     * Implementation MAY saliently ignore disconnect() calls if node is already disconnected.
     *
     * @return void
     */
    public function disconnect();
}
