<?php
namespace noFlash\CherryHttp;

use Exception;

/**
 * Raised every time when clients disconnects from server by itself or by code exception.
 *
 * @package noFlash\CherryHttp
 */
class ClientDisconnectException extends Exception
{
    /** @var StreamServerClientInterface */
    private $client;

    /**
     * @param StreamServerClientInterface $client Client which should be disconnected
     */
    public function __construct(StreamServerClientInterface &$client)
    {
        $this->client = $client;
        parent::__construct("Client is no longer valid. All remaining resources bounded to it should be removed.");
    }

    /**
     * @return StreamServerClientInterface Client which should be disconnected
     */
    public function getClient()
    {
        return $this->client;
    }
}