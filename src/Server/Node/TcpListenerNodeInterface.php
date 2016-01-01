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

namespace noFlash\CherryHttp\Server\Node;


use noFlash\CherryHttp\Http\Exception\StreamException;
use noFlash\CherryHttp\IO\Network\NetworkNodeInterface;

/**
 * Listening node which can accept incoming connections.
 */
interface TcpListenerNodeInterface extends NetworkNodeInterface
{
    const LISTEN_ALL_IPV4    = '0.0.0.0';
    const LISTEN_ALL_IPV6    = '0:0:0:0:0:0:0:0'; //Of course :: can also be used for listening on all IPs
    const RANDOM_LISTEN_PORT = 0;

    /**
     * Sets listening address.
     *
     * @param string $address Any valid IPv4 or IPv6 address, self::LISTEN_ALL_IPV4 or LISTEN_ALL_IPV6.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setIpAddress($address);

    /**
     * Sets listening port.
     *
     * @param int $port Any valid port number, or self::RANDOM_LISTEN_PORT
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setLocalPort($port);

    /**
     * Prepares listening stream and starts listening.
     * It's equivalent of using socket(), bind(), and listen() system calls.
     *
     * @return resource Listening socket.
     *
     * @throws StreamException
     */
    public function startListening();

    /**
     * After connection accept node holding that connection is needed.
     * To prevent tight coupling a proper factory is needed - that method returns instance of that factory
     * which is used by current instance.
     *
     * @return NodeFactoryInterface
     */
    public function getNodeFactory();
}
