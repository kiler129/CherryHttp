<?php
namespace noFlash\CherryHttp;

/**
 * Interface for stream server nodes (nodes & listeners)
 *
 * @package noFlash\CherryHttp
 *
 * @property resource $socket Client socket returned by stream_socket_accept()
 * @property string[] $subscribedEvents Contains subscription status for events of client
 * @property HttpRequest|null $request Current request sent by client, null if no request present
 *
 */
interface StreamServerNodeInterface
{
    /**
     * Return remote peername
     *
     * @return string Peername in IP:PORT format
     */
    public function getPeerName();

    /**
     * In opposite to getPeerName() it returns only IP address (without port number)
     *
     * @see getPeerName()
     * @return string Client IP
     */
    public function getIp();

    ///**
    // * Returns valid PHP stream socket returned by stream_socket_accept().
    // *
    // * @return resource
    // */
    //public function getSocket();

    /**
     * Specifies if client contain something to write.
     * Note: HttpException thrown from this method will NOT be handled by server. If you need to (which isn't logical)
     * produce HTTP error from this method call use pushData() with HttpResponse.
     *
     * @return bool
     */
    public function isWriteReady();

    /**
     * Informs client about data availability on TCP socket.
     * Note: due to PHP's internal streams implementation it's also called in case of socket exception (eg.
     * disconnection)
     *
     * @return void
     * @throws NodeDisconnectException On client disconnection
     */
    public function onReadReady();

    /**
     * Informs client about TCP socket begin ready to write to.
     * Note: due to PHP's internal streams implementation it's also called in case of socket exception (eg.
     * disconnection)
     *
     * @return bool Returns true if write was completed (write buffer is empty), false otherwise
     */
    public function onWriteReady();

    /**
     * Disconnects client from server.
     *
     * @param bool $drop By default client is disconnected after delivering output buffer contents. Set to true to drop
     *     it immediately.
     *
     * @return void
     */
    public function disconnect($drop = false);

    /**
     * Send some data to client.
     *
     * @param string $data DO NOT try to pass other data types (like NULL, bool or array), it can result in unexpected
     *     & destructive behaviours. The only exception is object implementing __toString() method.
     *
     * @return bool
     */
    public function pushData($data);

    /**
     * Provides human readable client identification.
     * It's recommended to use peer name along with class name.
     *
     * @see getPeerName()
     * @return string
     */
    public function __toString();
}