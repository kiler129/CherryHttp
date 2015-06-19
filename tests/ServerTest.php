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

    public function testIfNoLoggerWasGivenDefaultIsUsed()
    {
        $serverReflection = new \ReflectionObject($this->server);
        $loggerReflection = $serverReflection->getProperty('logger');
        $loggerReflection->setAccessible(true);

        $this->assertInstanceOf('\Psr\Log\NullLogger', $loggerReflection->getValue($this->server));
    }

    public function testIfNoRouterWasGivenDefaultIsUsed()
    {
        $this->assertInstanceOf('\noFlash\CherryHttp\HttpRouter', $this->server->router);
    }
}
