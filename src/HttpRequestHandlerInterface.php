<?php
namespace noFlash\CherryHttp;

/**
 * Every HTTP request need to be handled. This interface describes how server announces new request.
 * Most of the time your classes will implement this interface along with EventsHandlerInterface.
 * HttpRequestHandlerInterface was separated from EHI to add ability to specify multiple http request handlers per
 * server.
 *
 * @package noFlash\CherryHttp
 */
interface HttpRequestHandlerInterface
{
    /**
     * Called everytime new request matching paths specified by getHandledPaths() completely arrives.
     *
     * @param StreamServerClientInterface $client
     * @param HttpRequest $request
     *
     * @return void
     * @throws ClientDisconnectException
     * @throws ClientUpgradeException
     *
     * @see getHandledPaths()
     */
    public function onRequest(StreamServerClientInterface &$client, HttpRequest &$request);

    /**
     * Returns paths which events handler wish to handle by receiving requests.
     *
     * @return string[] Array of RFC-complaint URIs
     */
    public function getHandledPaths();
}