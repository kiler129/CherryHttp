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
    /** @var StreamServerNodeInterface */
    protected $oldClient;
    /** @var StreamServerNodeInterface */
    protected $newClient;

    /**
     * @param StreamServerNodeInterface $oldNode
     * @param StreamServerNodeInterface $newClient
     */
    public function __construct(StreamServerNodeInterface &$oldNode, StreamServerNodeInterface &$newClient)
    {
        $this->oldClient = &$oldNode;
        $this->newClient = &$newClient;
        parent::__construct("Upgrading client to new one.");
    }

    /**
     * Provides old client object. It's provided for identification purposes.
     *
     * @return StreamServerNodeInterface
     */
    public function getOldClient()
    {
        return $this->oldClient;
    }

    /**
     * Provides new ready to use (already prepared by code raising this exception) object of client.
     *
     * @return StreamServerNodeInterface
     */
    public function getNewClient()
    {
        return $this->newClient;
    }
}
