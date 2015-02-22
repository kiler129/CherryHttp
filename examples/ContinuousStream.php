<?php
require_once("../../../autoload.php");

/**
 * This example serves infinite stream of null bytes at url http://127.0.0.1:8080/zero
 *
 * Note: This example doesn't work on Windows (there's no /dev/zero stream)
 */

use noFlash\CherryHttp\EventsHandlerInterface;
use noFlash\CherryHttp\HttpRequest;
use noFlash\CherryHttp\HttpRequestHandlerInterface;
use noFlash\CherryHttp\HttpResponse;
use noFlash\CherryHttp\Server;
use noFlash\CherryHttp\StreamServerNodeInterface;

class StreamServer implements HttpRequestHandlerInterface, EventsHandlerInterface
{
    private $zeroStream;

    public function __construct()
    {
        $this->zeroStream = fopen("/dev/zero", "r");
    }

    public function onRequest(StreamServerNodeInterface $client, HttpRequest $request)
    {
        $response = new HttpResponse();
        $response->setHeader("Content-Disposition", "attachment; filename=zero.bin;");
        $response->setHeader("Connection", "close");
        $client->pushData($response);
    }

    public function getHandledPaths()
    {
        return array("/zero");
    }

    public function onHeartbeat()
    {
    }

    public function onWriteBufferEmpty(StreamServerNodeInterface $client)
    {
        $client->pushData(fread($this->zeroStream, 131072));
    }

    public function onHttpException(\noFlash\CherryHttp\HttpException $exception, StreamServerNodeInterface $client)
    {
    }
}

$streamServer = new StreamServer();
$server = new Server(); //Logger omitted to speed it up ;)
$server->bind("127.0.0.1", 8080);
$server->router->addPathHandler($streamServer);
$server->setEventsHandler($streamServer);
$server->subscribeEvent("writeBufferEmpty");
$server->run();
