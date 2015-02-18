<?php
require_once("../../../autoload.php");

use noFlash\CherryHttp\HttpRequest;
use noFlash\CherryHttp\HttpRequestHandlerInterface;
use noFlash\CherryHttp\HttpResponse;
use noFlash\CherryHttp\Server;
use noFlash\CherryHttp\StreamServerClientInterface;
use noFlash\Shout\Shout;

class HelloServer implements HttpRequestHandlerInterface
{
    public function onRequest(StreamServerClientInterface &$client, HttpRequest &$request)
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