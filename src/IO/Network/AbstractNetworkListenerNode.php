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
 * - Listeners cannot contain remote IP sine they're not connected (so, protected $networkRemoteIp = null)
 * - Listener cannot contain remote port since they're not connected (so, protected $networkRemotePort = null)
 * - Again... listeners cannot be connected ;) (so, protected $networkIsConnected = false)
 */
abstract class AbstractNetworkListenerNode extends AbstractNetworkStreamNode implements NetworkListenerNodeInterface
{
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
     * @throws \LogicException It already listens
     * @throws \RuntimeException Something went bad during SOCKET/BIND/LISTEN
     */
    public function startListening()
    {
        if ($this->stream !== null) {
            throw new \LogicException(
                'Listener is already listening, you cannot listen while listening because listening while listening ' .
                'will break that listening which now listens.... but for real, call disconnect() fist'
            );
        }

        $address = ($this->networkIpVersion ===
                    NetworkNodeInterface::IP_V4) ? $this->networkLocalIp : "[{$this->networkLocalIp}]";

        /** @noinspection PhpUsageOfSilenceOperatorInspection It's checked below, but it will throw warn. anyway */
        $this->stream = @stream_socket_server("tcp://$address:{$this->networkLocalPort}");

        if ($this->stream === false) {
            $error = error_get_last();
            $error = (isset($error['message'])) ? $error['message'] : 'Unknown stream_socket_server() error';
            $this->stream = null;

            throw new \RuntimeException($error);
        }
        stream_set_blocking($this->stream, 0);

        $address = stream_socket_get_name($this->stream, false);
        $dividerPosition = strrpos($address, ':');

        $this->networkLocalIp = substr($address, 0, $dividerPosition);
        $this->networkLocalPort = (int)substr($address, $dividerPosition + 1);
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
     * @param mixed $data It will be ignored
     *
     * @throws \LogicException You cannot append to listener buffer.
     *
     * @todo This method likely shouldn't be here - it needs investigation
     */
    public function writeBufferAppend($data)
    {
        throw new \LogicException('You cannot add data to buffer on listener node (because there\'s no any)');
    }
}
