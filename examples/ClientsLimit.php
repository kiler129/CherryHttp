<?php
require_once("../../../autoload.php");

/**
 * This example only allows single user to connect, displaying current date on every URL.
 * To test it visit any URL from one browser, and almost at the same time do it from another one. Trying to visit page
 * from another browser will result in "server unexpectedly dropped connection" error until first browser disconnects.
 */

use noFlash\CherryHttp\HttpRequest;
use noFlash\CherryHttp\HttpRequestHandlerInterface;
use noFlash\CherryHttp\HttpResponse;
use noFlash\CherryHttp\Server;
use noFlash\CherryHttp\StreamServerNodeInterface;
use noFlash\Shout\Shout;

class ClientsLimit implements HttpRequestHandlerInterface
{
    public function onRequest(StreamServerNodeInterface &$client, HttpRequest &$request)
    {
        $response = new HttpResponse("Current time: " . date("c"));
        $client->pushData($response);
    }

    public function getHandledPaths()
    {
        return array("*"); //Wildcard URL
    }
}

$prettyErrors = new ClientsLimit();
$server = new Server(new Shout());
$server->setNodesLimit(1); //Set number of clients limit
$server->bind("127.0.0.1", 8080);
$server->addPathHandler($prettyErrors);
$server->run();