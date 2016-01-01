<?php
namespace noFlash\CherryHttp;

/**
 * Routes incoming HTTP requests to correct handlers.
 *
 * @package noFlash\CherryHttp
 */
interface HttpRouterInterface
{
    /**
     * Adds path handler to server
     * Note: If you add many handlers with the same path the "the last wins" rule applies
     *
     * @param HttpRequestHandlerInterface $requestHandler
     */
    public function addPathHandler(HttpRequestHandlerInterface $requestHandler);

    /**
     * Removes previously added path handler
     * Note: method doesn't perform checks whatever handler has been previously added or not
     *
     * @param HttpRequestHandlerInterface $requestHandler Previously added path handler
     */
    public function removePathHandler(HttpRequestHandlerInterface $requestHandler);

    /**
     * Routes request from client
     *
     * @param StreamServerNodeInterface $client
     *
     * @throws HttpException
     */
    public function handleClientRequest(StreamServerNodeInterface $client);
}
