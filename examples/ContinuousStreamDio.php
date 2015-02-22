<?php
require_once("../../../autoload.php");

/**
 * This example serves infinite stream of null bytes at url http://127.0.0.1:8080/zero
 * It uses "Direct I/O" which require DIO php extension. It's generally around 10% faster than standard approach.
 *
 * Note: This example doesn't work on Windows (there's no /dev/zero stream)
 */

if (function_exists("xdebug_get_profiler_filename")) {
    echo xdebug_get_profiler_filename() . "\n"; //Used for profiling - not actual part of example ;)
}

use noFlash\CherryHttp\EventsHandlerInterface;
use noFlash\CherryHttp\HttpRequest;
use noFlash\CherryHttp\HttpRequestHandlerInterface;
use noFlash\CherryHttp\HttpResponse;
use noFlash\CherryHttp\Server;
use noFlash\CherryHttp\StreamServerNodeInterface;

class StreamServerDio implements HttpRequestHandlerInterface, EventsHandlerInterface
{
    private $zeroStream;

    public function __construct()
    {
        /** @noinspection PhpUndefinedConstantInspection O_RDONLY is not part of PHP core, but DIO extension */
        $this->zeroStream = dio_open("/dev/zero", O_RDONLY);
    }

    public function onRequest(StreamServerNodeInterface &$client, HttpRequest &$request)
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

    public function onWriteBufferEmpty(StreamServerNodeInterface &$client)
    {
        $client->pushData(dio_read($this->zeroStream, 131072));
    }

    public function onHttpException(\noFlash\CherryHttp\HttpException &$exception, StreamServerNodeInterface &$client)
    {
    }
}


$streamServer = new StreamServerDio();
$server = new Server(); //Logger omitted to speed it up ;)
$server->bind("127.0.0.1", 8080);
$server->router->addPathHandler($streamServer);
$server->setEventsHandler($streamServer);
$server->subscribeEvent("writeBufferEmpty");
$server->run();
