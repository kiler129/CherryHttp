<?php
namespace noFlash\CherryHttp;

use Exception;
use InvalidArgumentException;
use LogicException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Main CherryHttp server class.
 * Don't worry - it's not that big, it just carry a lot of comments ;)
 *
 * @package noFlash\CherryHttp
 * @todo SSL support
 */
class Server
{
    /** @var HttpRouterInterface Routes request to path handlers */
    public $router;
    /** @var LoggerInterface PSR-3 logger */
    protected $logger;
    /** @var EventsHandlerInterface|null */
    protected $eventsHandler = null;
    /** @var array Defines which events should be dispatched to eventsHandler - global array */
    protected $subscribedEvents = array("heartbeat" => false, "writeBufferEmpty" => false, "httpException" => false);
    /** @var StreamServerNodeInterface[] Contains all connected nodes (clients+listeners) objects */
    private $nodes = array();
    /** @var integer Current nodes counter (it's better than calling count($this->nodes) many times) */
    private $nodesCount = 0;
    /** @var integer Hard nodes limit. Every connection above that limit will be dropped. {@see setNodesLimit()} */
    private $nodesLimit = 1024;
    /** @var integer|null Interval in seconds to call callback */
    private $heartbeatInterval = null;
    /** @var integer Holds unix timestamp of next heartbeat call */
    private $nextHeartbeatTime = 0;

    /**
     * @param LoggerInterface $logger
     * @param HttpRouterInterface $router
     */
    public function __construct(LoggerInterface $logger = null, HttpRouterInterface $router = null)
    {
        $this->logger = ($logger === null) ? new NullLogger() : $logger;
        $this->router = ($router === null) ? new HttpRouter($this->logger) : $router;
    }

    /**
     * Creates listener using default HttpListenerNode class
     *
     * @param string $ip
     * @param int $port
     * @param bool $ssl
     *
     * @throws ServerException
     * @see ListenerNode
     */
    public function bind($ip = "0.0.0.0", $port = 8080, $ssl = false)
    {
        $this->addNode(new HttpListenerNode($this, $ip, $port, $ssl, $this->logger));
    }

    /**
     * Sets nodes (clients+listeners) number limit
     * By default linux kernels sets FD limit to 1024/process (see ulimit -n), so 1024 is mostly maximum.
     *
     * @param integer $limit Value cannot be negative (for obvious reasons), but it's allowed to be 0 (no nodes are
     *     accepted)
     *
     * @throws InvalidArgumentException Thrown if $limit < 0 or non-integer
     */
    public function setNodesLimit($limit)
    {
        if (!is_integer($limit) || $limit < 0) {
            throw new InvalidArgumentException("Nodes limit must be integer >= 0");
        }

        $this->nodesLimit = $limit;
        $this->logger->info("Changed nodes limit to $limit");
    }

    /**
     * Disconnects all nodes and properly closes server
     */
    public function __destruct()
    {
        $this->logger->info("Server is going down");
        foreach ($this->nodes as $node) {
            try {
                $node->disconnect(true);
            } catch(NodeDisconnectException $e) {
                $this->removeNode($e->getNode());
            }
        }
    }

    /**
     * Adds new node (client/listener) to current server instance
     *
     * @param StreamServerNodeInterface $node
     *
     * @throws ServerException
     */
    public function addNode(StreamServerNodeInterface $node)
    {
        try {
            if ($this->nodesCount >= $this->nodesLimit) {
                $node->disconnect();
                $this->logger->warning("Node $node dropped - limit of " . $this->nodesLimit . " connections exceeded");

                return;
            }

            $this->nodes[(int)$node->socket] = $node;
            $this->nodes[(int)$node->socket]->subscribedEvents = $this->subscribedEvents; //TODO this should be moved to HttpListenerNode
            $this->nodesCount++;
            $this->logger->info("New node added to server: " . $node->getPeerName());

        } catch(Exception $e) {
            $this->logger->error("Exception occurred during adding node - " . $e->getMessage() . "[" . $e->getFile() . "@" . $e->getLine() . "]");

            $this->removeNode($node);
        }
    }

    /**
     * Removes node (client/listener) based on given socket node object.
     *
     * @param StreamServerNodeInterface $node Node to remove from server
     *
     * @throws ServerException
     */
    public function removeNode(StreamServerNodeInterface $node)
    {
        if (!isset($this->nodes[(int)$node->socket])) {
            throw new ServerException("Tried to remove nonexistent node [bug?]");
        }

        unset($this->nodes[(int)$node->socket]);
        $this->nodesCount--;
        $this->logger->info("Node $node removed from server");
    }

    /**
     * CherryHttp uses I/O multiplexing to manage connections. Due that fact it freezes whole application if there's
     * no data flow on any socket. Some application however need to perform some tasks independently from network
     * communication. By setting heartbeat-callback (or other events described in EventsHandlerInterface) it's possible
     * to do so.
     *
     * @param EventsHandlerInterface $handler
     *
     * @see http://en.wikipedia.org/wiki/Select_(Unix)
     */
    public function setEventsHandler(EventsHandlerInterface $handler)
    {
        $this->eventsHandler = $handler;
    }

    /**
     * Sets how ofter heartbeat callback will be called counting real time (not CPU time).
     * Code SHOULD NOT relay on that timing - in some cases callback can be called later than interval specifies. Treat
     * this value like suggestion: "call me no more than once every 60s".
     * This method resets last callback call time, so expect callback execution in less than newly set interval.
     *
     * @param integer $interval Real seconds. Using "0" (callback every loop) is not recommended due high CPU usage.
     *
     * @throws InvalidArgumentException Specified interval value is not integer >= 0
     */
    public function setHearbeatInterval($interval)
    {
        if (!is_integer($interval) || $interval < 0) {
            throw new InvalidArgumentException("Hearbeat interval MUST be integer >= 0");
        }

        $this->nextHeartbeatTime = 0;
        $this->heartbeatInterval = $interval;
    }

    /**
     * Subscribe to event (aka enable it).
     *
     * @param string $eventName Any valid event name
     * @param bool $changeAll When true [default] subscription status will be changed for currently connected nodes,
     *      when false only new nodes gonna be affected
     *
     * @throws InvalidArgumentException Invalid event name specified
     * @throws ServerException
     * @see setEventHandler()
     */
    public function subscribeEvent($eventName, $changeAll = true)
    {
        if (!isset($this->subscribedEvents[$eventName])) {
            throw new InvalidArgumentException("Event $eventName doesn't exists");
        }

        if ($this->eventsHandler === null) {
            throw new ServerException("You have to specify events handler, using setEventHandler(), before subscribing");
        }

        $this->subscribedEvents[$eventName] = true;

        if (!$changeAll) {
            return;
        }

        foreach ($this->nodes as $node) {
            if (isset($node->subscribedEvents[$eventName])) {
                continue; //Event doesn't exist at client level (eg. heartbeat)
            }
            $node->subscribedEvents[$eventName] = false;
        }
    }

    /**
     * Unsubscribe from event (aka disable it).
     *
     * @param string $eventName Any valid event name
     * @param bool $changeAll When true [default] subscription status will be changed for currently connected nodes,
     *      when false only new nodes gonna be affected
     *
     * @throws InvalidArgumentException Invalid event name specified
     * @internal param bool $overwriteAll When true [default] subscription status will be changed for currently
     *     connected nodes, when false only new nodes gonna be affected
     *
     */
    public function unsubscribeEvent($eventName, $changeAll = true)
    {
        if (!isset($this->subscribedEvents[$eventName])) {
            throw new InvalidArgumentException("Event $eventName doesn't exists");
        }

        $this->subscribedEvents[$eventName] = false;

        if (!$changeAll) {
            return;
        }

        foreach ($this->nodes as $node) {
            if (isset($node->subscribedEvents[$eventName])) {
                continue; //Event doesn't exist at node level (eg. heartbeat)
            }
            $node->subscribedEvents[$eventName] = false;
        }
    }

    /**
     * Starts server main loop.
     * Note: this method it's too long to be considered proper OOP - compromise was made due to performance reasons.
     *
     * @wastedHoursCounter 17 Increment after every failure of making this method more OO or reducing indention
     * @throws ServerException
     * @throws LogicException
     */
    public function run()
    {
        //$this->logger->debug("run() called - multiplexer is running");
        while (true) {
            try {
                //Fire callback before building sockets arrays (if callback decides to modify sth it will be catched right away)
                if ($this->subscribedEvents["heartbeat"] && time() >= $this->nextHeartbeatTime) {
                    //$this->logger->debug("Firing heartbeat event");
                    $this->nextHeartbeatTime = time() + $this->heartbeatInterval;
                    $this->eventsHandler->onHeartbeat();
                }

                $read = $write = array();
                foreach ($this->nodes as $node) {
                    $read[] = $node->socket;

                    if ($node->isWriteReady()) {
                        $write[] = $node->socket;
                    }
                }

                //$this->logger->debug("Calling select() for " . (int)$this->heartbeatInterval . "s");
                $changedSocketsNum = stream_select($read, $write, $except = null, $this->heartbeatInterval);
                if ($changedSocketsNum === false) { //It doesn't always mean error - it's normal when application is interrupted by signal
                    throw new ServerException("select() call failed or interrupted");
                }
                //$this->logger->debug("Select returned with $changedSocketsNum changed socket(s)");
                try {
                    foreach ($read as $socket) {
                        /*if (!isset($this->nodes[(int)$socket])) { //TODO debug only, node have to be in this array. Remove it after debugging.
                            throw new ServerException("Internal server error - failed to locate node for socket (?!)");
                        }*/
                        $socketId = (int)$socket;
                        $this->nodes[$socketId]->onReadReady();

                        if (isset($this->nodes[$socketId]->request)) { //Request to handle
                            $this->router->handleClientRequest($this->nodes[$socketId]);
                        }
                    }

                    foreach ($write as $socket) {
                        $socketId = (int)$socket;

                        if ($this->nodes[$socketId]->onWriteReady() && $this->nodes[$socketId]->subscribedEvents["writeBufferEmpty"]) {
                            $this->eventsHandler->onWriteBufferEmpty($this->nodes[$socketId]);
                        }
                    }

                } catch(HttpException $e) {
                    /** @noinspection PhpUndefinedVariableInspection It's defined unless HttpException misused */
                    $this->handleHttpException($e, $this->nodes[$socketId]);
                }

            } catch(ClientUpgradeException $e) {
                $this->upgradeClient($e);

            } catch(NodeDisconnectException $e) {
                $this->removeNode($e->getNode());
            }
        }
    }

    /**
     * @param HttpException $exception HttpException to handle
     * @param StreamServerNodeInterface $client Client which generated that exception
     */
    private function handleHttpException(HttpException $exception, StreamServerNodeInterface $client)
    {
        //$this->logger->debug("Handling HttpException [code: " . $exception->getCode() . ", reason: " . $exception->getMessage() . "]");

        $errorResponse = ($client->subscribedEvents["httpException"]) ? $this->eventsHandler->onHttpException($exception,
            $client) : $exception->getResponse();

        $client->pushData($errorResponse);

        if ($errorResponse->isConnectionClose()) {
            $client->disconnect();
        }
    }

    /**
     * Upgrades client protocol.
     * It's done by replacing whole client class in nodes table.
     *
     * @param ClientUpgradeException $upgrade
     *
     * @throws ServerException Old client cannot be found on server (it's serious error)
     */
    private function upgradeClient(ClientUpgradeException $upgrade)
    {
        $oldClient = $upgrade->getOldClient();

        if (!isset($oldClient->socket, $this->nodes[(int)$oldClient->socket])) {
            $this->logger->emergency("Client $oldClient not found during upgrade");
            throw new ServerException("Tried to upgrade non existing client!");
        }

        $this->nodes[(int)$oldClient->socket] = $upgrade->getNewClient();
    }
}
