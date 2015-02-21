<?php
require_once("../../../autoload.php");

/**
 * This example replaces all server error pages with pretty one. It also provides example HTTP/500
 * http://127.0.0.1:8080/500
 */

use noFlash\CherryHttp\EventsHandlerInterface;
use noFlash\CherryHttp\HttpException;
use noFlash\CherryHttp\HttpRequest;
use noFlash\CherryHttp\HttpRequestHandlerInterface;
use noFlash\CherryHttp\Server;
use noFlash\CherryHttp\StreamServerNodeInterface;
use noFlash\Shout\Shout;

class PrettyErrors implements HttpRequestHandlerInterface, EventsHandlerInterface
{
    const ERROR_PAGE = '<html><head><title>&#127826; Error %1$s</title><style>html,body{margin:0;padding:0;height:100%%;width:100%%;background:gray;font-family:sans-serif;}</style></head><body><div style="width:100%%;height:100%%;background:radial-gradient(ellipse at center,rgba(255,255,255,1) 0%%,rgba(255,255,255,0) 100%%);"><div style="text-align:center;position:relative;top:45%%;transform:translateY(-45%%);"><h1 style="font-size:10em;margin:0;">%1$s</h1><p style="font-family:Monospace;font-size:1.5em;">%2$s</p></div></div></body></html>';

    public function onRequest(StreamServerNodeInterface &$client, HttpRequest &$request)
    {
        throw new HttpException("It's a trap, run away!");
    }

    public function getHandledPaths()
    {
        return array("/500");
    }

    public function onHeartbeat()
    {
    }

    public function onWriteBufferEmpty(StreamServerNodeInterface &$client)
    {
    }

    public function onHttpException(HttpException &$exception, StreamServerNodeInterface &$client)
    {
        $exceptionResponse = $exception->getResponse();

        if ((int)$exceptionResponse->getCode() >= 400) { //Do not catch eg. redirects
            $prettyBody = sprintf(self::ERROR_PAGE, $exceptionResponse->getCode(), $exceptionResponse->getBody());
            $exceptionResponse->setBody($prettyBody);
        }

        return $exceptionResponse;
    }
}

$prettyErrors = new PrettyErrors();
$server = new Server(new Shout());
$server->bind("127.0.0.1", 8080);
$server->addPathHandler($prettyErrors);
$server->setEventsHandler($prettyErrors);
$server->subscribeEvent("httpException");
$server->run();