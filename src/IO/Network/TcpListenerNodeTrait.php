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

use noFlash\CherryHttp\Http\Exception\StreamException;

/**
 * Trait implements common methods defined by TcpListenerNodeInterface
 *
 * - Listeners cannot contain remote IP sine they're not connected (so, protected $networkRemoteIp = null)
 * - Listener cannot contain remote port since they're not connected (so, protected $networkRemotePort = null)
 * - Again... listeners cannot be connected ;) (so, protected $networkIsConnected = false)
 */
trait TcpListenerNodeTrait
{
    use NetworkNodeTrait;

    /**
     * Closes listener.
     * Note: clients connected using this listener will be leaved untouched. If you want to disconnect them get clients
     * list and than call disconnect() on each of them.
     *
     * @return void
     */
    public function disconnect()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream); //Perhaps this still can throw E_WARNING but I rather not use @
        }
    }

    /**
     * In case of listener node this always returns that it's not write ready.
     *
     * @return bool
     */
    public function isWriteReady()
    {
        return false;
    }

    /**
     * Method called everytime stream is considered "read ready".
     * Please note that method can be also called for stream errors (e.g. remote disconnection) - it's how streams are
     * handled by PHP itself.
     *
     * @return void
     */
    public function doRead()
    {
        // TODO: Implement doRead() method.
    }

    /**
     * In case of listener node it's just a dummy method.
     *
     * @throws \LogicException
     */
    public function doWrite()
    {
        throw new \LogicException('You cannot perform write on listener node');
    }

    /**
     * In case of listener node it's just a dummy method.
     *
     * @throws \LogicException
     */
    public function writeBufferAppend($data)
    {
        throw new \LogicException('You cannot add data to buffer on listener node (because there\'s no any)');
    }

    /**
     * Sets listening address.
     *
     * @param string $address Any valid IPv4 or IPv6 address, NetworkNodeInterface::UNDETERMINED_IPV4 or
     *                        NetworkNodeInterface::UNDETERMINED_IPV6.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setLocalIpAddress($address)
    {
        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException('Invalid ip address');
        }

        $this->networkIpVersion = (strpos($address, ':') ===
                                   false) ? NetworkNodeInterface::IP_V4 : NetworkNodeInterface::IP_V6;
        $this->networkLocalIp = $address;
    }

    /**
     * Sets listening port.
     *
     * @param int $port Any valid port number, or self::RANDOM_LISTEN_PORT
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setLocalPort($port)
    {
        if ($port < 0 || $port > 65535 || !is_integer($port)) {
            throw new \InvalidArgumentException('Invalid port');
        }

        $this->networkLocalPort = $port;
    }


    /**
     * Prepares listening stream and starts listening.
     * It's equivalent of using socket(), bind(), and listen() system calls.
     *
     * @return resource Listening socket.
     *
     * @throws StreamException
     */
    public function startListening()
    {
        // TODO: Implement startListening() method.
    }
}
