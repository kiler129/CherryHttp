<?php
require_once('../../../autoload.php');

/**
 * It works the same as HelloServer.php example, but binds to IPv6 or both IPv4 and IPv6 (depends on operating system)
 */

use noFlash\CherryHttp\HttpRequest;
use noFlash\CherryHttp\HttpRequestHandlerInterface;
use noFlash\CherryHttp\HttpResponse;
use noFlash\CherryHttp\Server;
use noFlash\CherryHttp\StreamServerNodeInterface;
use noFlash\Shout\Shout;

class HelloServer implements HttpRequestHandlerInterface
{
    public function onRequest(StreamServerNodeInterface $client, HttpRequest $request)
    {
        $response = new HttpResponse("I'm everywhere ;)\nIt's " . date('c'));
        $client->pushData($response);
    }

    public function getHandledPaths()
    {
        return array('*');
    }
}

$helloServer = new HelloServer();
$server = new Server(new Shout());
$server->bind('::', 8080);
$server->router->addPathHandler($helloServer);
$server->run();
