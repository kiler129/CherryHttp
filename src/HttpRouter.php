<?php
namespace noFlash\CherryHttp;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Default HttpRouter implementation
 *
 * @package noFlash\CherryHttp
 */
class HttpRouter implements HttpRouterInterface
{
    /** @var LoggerInterface PSR-3 logger */
    protected $logger;

    /** @var HttpRequestHandlerInterface[] Contains path handler which are used to route incoming requests. */
    protected $pathHandlers = array();

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = ($logger === null) ? new NullLogger() : $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function addPathHandler(HttpRequestHandlerInterface $requestHandler)
    {
        $paths = $requestHandler->getHandledPaths();
        foreach ($paths as $path) {
            if (isset($this->pathHandlers[$path])) {
                $this->logger->warning("Replacing path handler " . get_class($this->pathHandlers[$path]) . " with " . get_class($requestHandler) . " for path $path");
            }

            $this->pathHandlers[$path] = $requestHandler;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removePathHandler(HttpRequestHandlerInterface $requestHandler)
    {
        foreach ($this->pathHandlers as $path => $handler) {
            if ($handler === $requestHandler) {
                unset($this->pathHandlers[$path]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleClientRequest(StreamServerNodeInterface $client)
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
}