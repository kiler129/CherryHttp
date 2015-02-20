<?php
require_once("../../../autoload.php");

/**
 * This example provides two URLs - http://127.0.0.1:8080/hello (serving "Hello world!") and http://127.0.0.1:8080/time
 */

use noFlash\CherryHttp\HttpRequest;
use noFlash\CherryHttp\HttpRequestHandlerInterface;
use noFlash\CherryHttp\HttpResponse;
use noFlash\CherryHttp\Server;
use noFlash\CherryHttp\StreamServerClientInterface;
use noFlash\Shout\Shout;

class SimpleRouter implements HttpRequestHandlerInterface
{
    public function onRequest(StreamServerClientInterface &$client, HttpRequest &$request)
    {
        switch ($request->getUri()) {
            case "/hello":
                $response = new HttpResponse("Hello world!");
                $client->pushData($response);
                break;

            case "/time":
                $response = new HttpResponse("<h2>It's " . date("r") . "</h2>");
                $response->setHeader("Content-Type", "text/html");

                $client->pushData($response);
                break;
        }
    }

    public function getHandledPaths()
    {
        return array("/time", "/hello");
    }
}

$routingServer = new SimpleRouter();
$server = new Server(new Shout());
$server->bind("127.0.0.1", 8080);
$server->addPathHandler($routingServer);
$server->run();