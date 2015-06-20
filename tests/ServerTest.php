<?php
namespace noFlash\CherryHttp;

/**
 * @todo Testing (un)subscribe with changeAll=true/false
 */
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

    public function testEventsHandlerCanBeSet()
    {
        $handler = $this->getMockBuilder('\noFlash\CherryHttp\EventsHandlerInterface')->getMock();
        $this->server->setEventsHandler($handler);

        $serverReflection = new \ReflectionObject($this->server);
        $eventsHandlerReflection = $serverReflection->getProperty('eventsHandler');
        $eventsHandlerReflection->setAccessible(true);

        $this->assertSame($handler, $eventsHandlerReflection->getValue($this->server));
    }

    public function eventsProvider()
    {
        return array(
            array('writeBufferEmpty'),
            array('httpException'),
            array('heartbeat')
        );
    }

    /**
     * @dataProvider eventsProvider
     */
    public function testSubscribingEventEnablesSubscriptionOnlyForThatEvent($eventName)
    {
        $handler = $this->getMockBuilder('\noFlash\CherryHttp\EventsHandlerInterface')->getMock();
        $this->server->setEventsHandler($handler);

        $serverReflection = new \ReflectionObject($this->server);
        $subscribedEventsReflection = $serverReflection->getProperty('subscribedEvents');
        $subscribedEventsReflection->setAccessible(true);

        $eventsTable = $subscribedEventsReflection->getValue($this->server);
        $eventsTable[$eventName] = true;

        $this->server->subscribeEvent($eventName);
        $this->assertEquals($eventsTable, $subscribedEventsReflection->getValue($this->server));
    }

    public function testSubscribingUnknownEventThrowsInvalidArgumentException()
    {
        $handler = $this->getMockBuilder('\noFlash\CherryHttp\EventsHandlerInterface')->getMock();
        $this->server->setEventsHandler($handler);

        $this->setExpectedException('\InvalidArgumentException');
        $this->server->subscribeEvent('unknownEvent');
    }

    public function testSubscribingEventWithoutSettingEventsHandlerThrowsServerException()
    {
        $this->setExpectedException('\noFlash\CherryHttp\ServerException');
        $this->server->subscribeEvent('httpException');
    }

    /**
     * @dataProvider eventsProvider
     */
    public function testUnsubscribingEventEnablesSubscriptionOnlyForThatEvent($eventName)
    {
        $handler = $this->getMockBuilder('\noFlash\CherryHttp\EventsHandlerInterface')->getMock();
        $this->server->setEventsHandler($handler);

        $serverReflection = new \ReflectionObject($this->server);
        $subscribedEventsReflection = $serverReflection->getProperty('subscribedEvents');
        $subscribedEventsReflection->setAccessible(true);

        $eventsTable = $subscribedEventsReflection->getValue($this->server);
        $eventsTable[$eventName] = false;

        $this->server->unsubscribeEvent($eventName);
        $this->assertEquals($eventsTable, $subscribedEventsReflection->getValue($this->server));
    }

    public function testHearbeatIntervalCanBeSet()
    {
        $serverReflection = new \ReflectionObject($this->server);
        $hbIntervalReflection = $serverReflection->getProperty('heartbeatInterval');
        $hbIntervalReflection->setAccessible(true);

        $this->server->setHearbeatInterval(5);

        $this->assertSame(5, $hbIntervalReflection->getValue($this->server));
    }

    public function testHearbeatIntervalRejectsNonInteger()
    {
        $serverReflection = new \ReflectionObject($this->server);
        $hbIntervalReflection = $serverReflection->getProperty('heartbeatInterval');
        $hbIntervalReflection->setAccessible(true);

        $this->setExpectedException('\InvalidArgumentException', 'integer');
        $this->server->setHearbeatInterval(M_PI);
    }

    public function testHearbeatIntervalRejectsNegativeValues()
    {
        $serverReflection = new \ReflectionObject($this->server);
        $hbIntervalReflection = $serverReflection->getProperty('heartbeatInterval');
        $hbIntervalReflection->setAccessible(true);

        $this->setExpectedException('\InvalidArgumentException');
        $this->server->setHearbeatInterval(-3);
    }
}
