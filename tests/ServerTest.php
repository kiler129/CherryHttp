<?php
namespace noFlash\CherryHttp;


class ServerTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var Server
     */
    private $server;

    public function setUp()
    {
        $this->server = new Server();
    }

    public function testIfNoRouterWasGivenDefaultIsUsed()
    {
        $this->assertInstanceOf('\noFlash\CherryHttp\HttpRouter', $this->server->router);
    }
}
