<?php
require_once('../../../autoload.php');

/**
 * This example routes users based on their browsers (Chrome+Safari/Firefox/Other).
 * Of course that can be realized on request handler level, but it's just a demonstration of custom router.
 */

use noFlash\CherryHttp\HttpCode;
use noFlash\CherryHttp\HttpRequest;
use noFlash\CherryHttp\HttpRequestHandlerInterface;
use noFlash\CherryHttp\HttpResponse;
use noFlash\CherryHttp\HttpException;
use noFlash\CherryHttp\HttpRouterInterface;
use noFlash\CherryHttp\Server;
use noFlash\CherryHttp\StreamServerNodeInterface;
use noFlash\Shout\Shout;

/**
 * Remember: that's just POC - it SHOULD NOT be considered as proper implementation of routing based on User-Agent
 */
class CustomRouter implements HttpRouterInterface
{
    /** @var HttpRequestHandlerInterface */
    private $browsers;

    public function handleClientRequest(StreamServerNodeInterface $client)
    {
        $userAgent = $client->request->getHeader('user-agent');
        $isHandled = false;

        foreach ($this->browsers as $name => $object) {
            if (strpos($userAgent, $name) !== false) {
                $object->onRequest($client, $client->request);
                $isHandled = true;
            }
        }

        $client->request = null; //Request handling finished
        if (!$isHandled) { //No supported browser found
            throw new HttpException('Unsupported browser', HttpCode::FORBIDDEN);
        }
    }

    public function addPathHandler(HttpRequestHandlerInterface $requestHandler)
    {
        $this->browsers[get_class($requestHandler)] = $requestHandler;
    }

    public function removePathHandler(HttpRequestHandlerInterface $requestHandler)
    {
        unset($this->browsers[get_class($requestHandler)]);
    }
}

class AppleWebKit implements HttpRequestHandlerInterface
{
    public function onRequest(StreamServerNodeInterface $client, HttpRequest $request)
    {
        $client->pushData(new HttpResponse('Hello WebKit-based browser!'));
    }


    public function getHandledPaths()
    {
        return array('*');
    }
}

class Firefox implements HttpRequestHandlerInterface
{
    public function onRequest(StreamServerNodeInterface $client, HttpRequest $request)
    {
        $client->pushData(new HttpResponse('Hello Firefox!'));
    }


    public function getHandledPaths()
    {
        return array('*');
    }
}

class MSIE implements HttpRequestHandlerInterface
{
    public function onRequest(StreamServerNodeInterface $client, HttpRequest $request)
    {
        $client->pushData(new HttpResponse('Internet Exploder, could you stop destroying the Internet, please?'));
    }


    public function getHandledPaths()
    {
        return array('*');
    }
}

$server = new Server(new Shout(), new CustomRouter());
$server->bind('127.0.0.1', 8080);
$server->router->addPathHandler(new AppleWebKit());
$server->router->addPathHandler(new Firefox());
$server->router->addPathHandler(new MSIE());
$server->run();
