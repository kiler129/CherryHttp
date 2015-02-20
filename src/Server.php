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
    /** @var LoggerInterface PSR-3 logger */
    protected $logger;
    /** @var HttpRequestHandlerInterface[] Contains path handler which are used to route incoming requests. */
    protected $pathHandlers = array();
    /** @var EventsHandlerInterface|null */
    protected $eventsHandler = null;
    /** @var array Defines which events should be dispatched to eventsHandler - global array */
    protected $subscribedEvents = array("heartbeat" => false, "writeBufferEmpty" => false, "httpException" => false);
    /** @var resource|null Holds listening server socket. */
    private $serverSocket;
    /** @var StreamServerClientInterface[] Contains all connected clients objects */
    private $clients = array();
    /** @var integer Current clients counter (it's better than calling count($this->clients) many times) */
    private $clientsCount = 0;
    /** @var integer Hard clients limit. Every connection above that limit will be dropped. {@see setClientsLimit()} */
    private $clientsLimit = 1023;
    /** @var integer|null Interval in seconds to call callback */
    private $heartbeatInterval = null;
    /** @var integer Holds unix timestamp of last heartbeat call */
    private $lastHeartbeatTime = 0;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface &$logger = null)
    {
        if ($logger === null) {
            $this->logger = new NullLogger();

        } else {
            $this->logger = &$logger;
        }
    }

    /**
     * Sets clients number limit
     * By default linux kernels sets FD limit to 1024/process (see ulimit -n), so 1023 is most of the time max. number
     * of clients (bcs 1 FD is occupied by serverSocket).
     *
     * @param integer $limit Value cannot be negative (for obvious reasons), but it's allowed to be 0 (no clients are
     *     accepted)
     *
     * @throws InvalidArgumentException Thrown if $limit < 0 or non-integer
     */
    public function setClientsLimit($limit)
    {
        if (!is_integer($limit) || $limit < 0) {
            throw new InvalidArgumentException("Clients limit must be integer >= 0");
        }

        $this->clientsLimit = $limit;
        $this->logger->info("Changed clients limit to $limit");
    }

    /**
     * Adds path handler to server
     * Note: If you add many handlers with the same path the "the last wins" rule applies
     *
     * @param HttpRequestHandlerInterface $requestHandler
     */
    public function addPathHandler(HttpRequestHandlerInterface &$requestHandler)
    {
        $paths = $requestHandler->getHandledPaths();
        foreach ($paths as $path) {
            if (isset($this->pathHandlers[$path])) {
                $this->logger->warning("Replacing path handler " . get_class($this->pathHandlers[$path]) . " with " . get_class($requestHandler) . " for path $path");
            }

            $this->pathHandlers[$path] = &$requestHandler;
        }
    }

    /**
     * Removes previously added path handler
     * Note: method doesn't perform checks whatever handler has been previously added or not
     *
     * @param HttpRequestHandlerInterface $requestHandler Previously added path handler
     */
    public function removePathHandler(HttpRequestHandlerInterface &$requestHandler)
    {
        foreach ($this->pathHandlers as $path => $handler) {
            if ($handler === $requestHandler) {
                unset($this->pathHandlers[$path]);
            }
        }
    }

    /**
     * Disconnects all clients and properly closes server
     */
    public function __destruct()
    {
        $this->logger->info("Server is going down");
        foreach ($this->clients as $client) {
            try {
                $client->disconnect(true);
            } catch(ClientDisconnectException $e) {
                $this->removeClient($e->getClient());
            }
        }

        if (fclose($this->serverSocket)) {
            $this->logger->info("Server closed");
        } else {
            $this->logger->warning("Failed to close server socket");
        }
    }

    /**
     * Removes client based on given socket client object.
     *
     * @param StreamServerClientInterface $client Client to remove from server
     */
    private function removeClient($client)
    {
        /*if (!isset($this->clients[(int)$client->socket])) { //TODO debug only, client have to be in this array. Commentout after debugging.
            throw new ServerException("Tried to remove nonexisting client [bug?]");
        }*/

        unset($this->clients[(int)$client->socket]);
        $this->clientsCount--;
        $this->logger->info("Client $client removed from server");
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
    public function setEventsHandler(EventsHandlerInterface &$handler)
    {
        $this->eventsHandler = &$handler;
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

        $this->lastHeartbeatTime = 0;
        $this->heartbeatInterval = $interval;
    }

    /**
     * Subscribe to event (aka enable it).
     *
     * @param string $eventName Any valid event name
     * @param bool $overwriteAll When true [default] subscription status will be changed for currently connected
     *     clients, when false only new clients gonna be affected
     *
     * @throws InvalidArgumentException Invalid event name specified
     * @throws ServerException Trying to subscribe event before specifying events handler
     *
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

        foreach ($this->clients as $client) {
            if (isset($client->subscribedEvents[$eventName])) {
                continue; //Event doesn't exist at client level (eg. heartbeat)
            }
            $client->subscribedEvents[$eventName] = false;
        }
    }

    /**
     * Unsubscribe from event (aka disable it).
     *
     * @param string $eventName Any valid event name
     * @param bool $overwriteAll When true [default] subscription status will be changed for currently connected
     *     clients, when false only new clients gonna be affected
     *
     * @throws InvalidArgumentException Invalid event name specified
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

        foreach ($this->clients as $client) {
            if (isset($client->subscribedEvents[$eventName])) {
                continue; //Event doesn't exist at client level (eg. heartbeat)
            }
            $client->subscribedEvents[$eventName] = false;
        }
    }

    /**
     * Starts server main loop.
     * Note: this method it's too long to be considered proper OOP - compromise was made due to performance reasons.
     *
     * @wastedHoursCounter 19.5 Increment after every failure of making this method more OO or reducing indention
     * @throws ServerException
     * @throws LogicException
     */
    public function run()
    {
        if ($this->serverSocket === null) {
            $this->logger->warning("You should call bind() before run() to ensure correct IP & port");
            $this->bind();
        }

        $this->logger->debug("Server started");
        while (true) {
            try {
                //Fire callback before builidng sockets arrays (if callback decides to modify sth it will be catched right away)
                if ($this->subscribedEvents["heartbeat"] && time() - $this->lastHeartbeatTime >= $this->heartbeatInterval) {
                    //$this->logger->debug("Firing heartbeat event");

                    $this->lastHeartbeatTime = time();
                    $this->eventsHandler->onHeartbeat();
                }

                $read = array($this->serverSocket);
                $write = array();

                //Building sockets arrays for stream_select() - it modifies originally passed arrays
                foreach ($this->clients as $client) {
                    $read[] = $client->socket;

                    if ($client->isWriteReady()) {
                        $write[] = $client->socket;
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
                        if ($socket === $this->serverSocket) {
                            $this->acceptClient();

                        } else {
                            /*if (!isset($this->clients[(int)$socket])) { //TODO debug only, client have to be in this array. Remove it after debugging.
                                throw new ServerException("Internal server error - failed to locate client for socket (?!)");
                            }*/
                            $socketId = (int)$socket;

                            $this->clients[$socketId]->onReadReady();

                            if (isset($this->clients[$socketId]->request)) { //Request to handle
                                $this->handleClientRequest($this->clients[$socketId]);
                            }
                        }
                    }

                    foreach ($write as $socket) {
                        $socketId = (int)$socket;

                        if ($this->clients[$socketId]->onWriteReady() && $this->clients[$socketId]->subscribedEvents["writeBufferEmpty"]) {
                            $this->eventsHandler->onWriteBufferEmpty($this->clients[$socketId]);
                        }
                    }

                } catch(HttpException $e) {
                    /** @noinspection PhpUndefinedVariableInspection It's defined unless HttpException missused */
                    $this->handleHttpException($e, $this->clients[$socketId]);
                }

            } catch(ClientUpgradeException $e) {
                $this->upgradeClient($e);

            } catch(ClientDisconnectException $e) {
                $this->removeClient($e->getClient());
            }
        }
    }

    /**
     * Binds to given IP and port.
     * Note: SSL support is not implemented right now.
     *
     * @param string $ip IP address to listen on
     * @param integer $port Port to listen on. If you pass 0 port random free port will be assigned by OS
     * @param bool $ssl
     *
     * @throws LogicException Thrown if you try to bind twice
     * @throws ServerException Server cannot be latched due to internal error
     */
    public function bind($ip = "0.0.0.0", $port = 8080, $ssl = false)
    {
        if ($ssl) {
            throw new ServerException("SSL support is not implemented");
        }

        if ($this->serverSocket !== null) {
            throw new LogicException("You cannot bind twice");
            //Actually you can but there's no practical usage for that behaviour and it can produce hard to trace bugs
        }

        /** @noinspection PhpUsageOfSilenceOperatorInspection This function raises PHP E_WARNING */
        $socket = @stream_socket_server("tcp://$ip:$port", $errNo, $errStr);
        if (!$socket) {
            throw new ServerException("Failed to launch server at tcp://$ip:$port [SSL: " . (int)$ssl . "] - $errStr (e: $errNo)");
        }

        stream_set_blocking($socket, 0);
        $this->serverSocket = $socket;

        //We have to get address instead of using $port in case of random port selection ($port === 0)
        $this->logger->info("Started server at tcp://" . stream_socket_get_name($this->serverSocket,
                false) . " [SSL: " . (int)$ssl . "]");
    }

    /**
     * Accepts connection to server and bootstraps client.
     * Note: Throwing HttpException from this method will result in unexpected behaviours or/and server crash!
     *
     * @throws ServerException Thrown when server socket get disconnected (it shouldn't) or accept() failed
     */
    private function acceptClient()
    {
        if (!$this->serverSocket) {
            throw new ServerException("Server socked has gone away (external problem?)");
        }

        try {
            $clientSocket = stream_socket_accept($this->serverSocket, null, $peerName);
            if ($clientSocket === false) {
                throw new ServerException("Failed to accept client (didn't you run out of FDs?)");
            }
            stream_set_blocking($clientSocket, 0);

            if ($this->clientsCount >= $this->clientsLimit) {
                fclose($clientSocket);
                $this->logger->warning("Client $peerName dropped - limit of " . $this->clientsLimit . " connections exceeded");

                return;
            }

            $this->clients[(int)$clientSocket] = new HttpClient($clientSocket, $peerName, $this->logger);
            $this->clients[(int)$clientSocket]->subscribedEvents = $this->subscribedEvents;
            $this->clientsCount++;
            $this->logger->info("New client connected $peerName");

            //} catch(HttpException $e) { //It can be thrown by HttpClient() [it's not currently used]
            //    $this->handleHttpException($e, $clientSocket);
        } catch(Exception $e) {
            $this->logger->error("Exception occured during client acceptance - " . $e->getMessage() . "[" . $e->getFile() . "@" . $e->getLine() . "]");

            if (isset($clientSocket) && isset($this->clients[(int)$clientSocket])) { //It's not a bug - exception can occur before addition (eg. stream_socket_accept() fail)
                $this->removeClient($this->clients[(int)$clientSocket]);
            }
        }
    }

    /**
     * Routes request from client
     *
     * @param StreamServerClientInterface $client
     *
     * @throws HttpException
     */
    private function handleClientRequest(StreamServerClientInterface &$client)
    {
        //$this->logger->debug("Request receiving from " . $client . " finished");
        $request = $client->request;
        $client->request = null;

        $uri = $request->getUri();
        if (isset($this->pathHandlers[$uri])) {
            $this->pathHandlers[$uri]->onRequest($client, $request);

        } elseif (isset($this->pathHandlers["*"])) {
            $this->pathHandlers["*"]->onRequest($client, $request);

        } else {
            throw new HttpException("No resource lives here.", HttpCode::NOT_FOUND,
                array("X-Reason" => "no module for path"), $request->closeConnection());
        }
    }

    /**
     * @param HttpException $exception HttpException to handle
     * @param StreamServerClientInterface $client Client which generated that exception
     */
    private function handleHttpException(HttpException &$exception, StreamServerClientInterface &$client)
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
     * It's done by replacing whole client class in clients table.
     *
     * @param ClientUpgradeException $upgrade
     *
     * @throws ServerException Old client cannot be found on server (it's serious error)
     */
    private function upgradeClient(ClientUpgradeException $upgrade)
    {
        $oldClient = $upgrade->getOldClient();

        if (!isset($oldClient->socket, $this->clients[(int)$oldClient->socket])) {
            $this->logger->emergency("Client $oldClient not found during upgrade");
            throw new ServerException("Tried to upgrade non existing client!");
        }

        $this->clients[(int)$oldClient->socket] = &$upgrade->getNewClient();
    }
}