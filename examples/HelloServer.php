<?php
require_once("../../../autoload.php");

/**
 * This example provides outputs "I'm everywhere" along with current time (ISO 8601 format) on all URLs
 */

use noFlash\CherryHttp\HttpRequest;
use noFlash\CherryHttp\HttpRequestHandlerInterface;
use noFlash\CherryHttp\HttpResponse;
use noFlash\CherryHttp\Server;
use noFlash\CherryHttp\StreamServerNodeInterface;
use noFlash\Shout\Shout;

class HelloServer implements HttpRequestHandlerInterface
{
    public function onRequest(StreamServerNodeInterface &$client, HttpRequest &$request)
    {
        $response = new HttpResponse("I'm everywhere ;)\nIt's " . date("c"));
        $client->pushData($response);
    }

    public function getHandledPaths()
    {
        return array("*");
    }
}

$helloServer = new HelloServer();
$server = new Server(new Shout());
$server->bind("127.0.0.1", 8080);
$server->addPathHandler($helloServer);
$server->run();
