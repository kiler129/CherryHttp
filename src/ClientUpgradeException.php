<?php
namespace noFlash\CherryHttp;

use Exception;

/**
 * It's thrown if request handler decides to switch client object to another one
 *
 * @package noFlash\CherryHttp
 */
class ClientUpgradeException extends Exception
{
    /** @var StreamServerClientInterface */
    protected $oldClient;
    /** @var StreamServerClientInterface */
    protected $newClient;

    /**
     * @param StreamServerClientInterface $oldClient
     * @param StreamServerClientInterface $newClient
     */
    public function __construct(StreamServerClientInterface &$oldClient, StreamServerClientInterface &$newClient)
    {
        $this->oldClient = &$oldClient;
        $this->newClient = &$newClient;
        parent::__construct("Upgrading client to new one.");
    }

    /**
     * Provides old client object. It's provided for identification purposes.
     *
     * @return StreamServerClientInterface
     */
    public function getOldClient()
    {
        return $this->oldClient;
    }

    /**
     * Provides new ready to use (already prepared by code raising this exception) object of client.
     *
     * @return StreamServerClientInterface
     */
    public function getNewClient()
    {
        return $this->newClient;
    }
}