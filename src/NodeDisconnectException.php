<?php
namespace noFlash\CherryHttp;

use Exception;

/**
 * Raised every time when node disconnects from server by itself or by code exception.
 *
 * @package noFlash\CherryHttp
 */
class NodeDisconnectException extends Exception
{
    /** @var StreamServerNodeInterface */
    private $node;

    /**
     * @param StreamServerNodeInterface $node Client which should be disconnected
     */
    public function __construct(StreamServerNodeInterface &$node)
    {
        $this->node = $node;
        parent::__construct("Node disconnected, it's no longer valid. All remaining resources bounded to it should be removed.");
    }

    /**
     * @return StreamServerNodeInterface Client which should be disconnected
     */
    public function getNode()
    {
        return $this->node;
    }
}