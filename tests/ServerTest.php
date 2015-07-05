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

    private function getSampleSocketStream()
    {
        $stream = stream_socket_server('tcp://0.0.0.0:0');
        $this->assertNotFalse($stream, 'Failed to create socket for test (environment problem?)');

        return $stream;
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

    public function testUnubscribingUnknownEventThrowsInvalidArgumentException()
    {
        $handler = $this->getMockBuilder('\noFlash\CherryHttp\EventsHandlerInterface')->getMock();
        $this->server->setEventsHandler($handler);

        $this->setExpectedException('\InvalidArgumentException');
        $this->server->unsubscribeEvent('unknownEvent');
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

    public function testHandlingHttpExceptionPushesResponseToClient()
    {
        $serverReflection = new \ReflectionObject($this->server);
        $httpExceptionHandlerReflection = $serverReflection->getMethod('handleHttpException');
        $httpExceptionHandlerReflection->setAccessible(true);

        $response = $this->getMock('\noFlash\CherryHttp\HttpResponse');

        $httpException = $this
            ->getMockBuilder('\noFlash\CherryHttp\HttpException')
            ->disableOriginalConstructor()
            ->getMock();
        $httpException
            ->expects($this->atLeastOnce())
            ->method('getResponse')
            ->willReturn($response);

        $client = $this->getMockBuilder('\noFlash\CherryHttp\StreamServerNodeInterface')->getMock();
        $client->subscribedEvents = array('httpException' => false);
        $client->expects($this->atLeastOnce())->method('pushData')->with($response);

        $httpExceptionHandlerReflection->invoke($this->server, $httpException, $client);
    }

    public function testClientIsDisconnectedAccordingToTheRequestDuringHttpExceptionHandling()
    {
        $serverReflection = new \ReflectionObject($this->server);
        $httpExceptionHandlerReflection = $serverReflection->getMethod('handleHttpException');
        $httpExceptionHandlerReflection->setAccessible(true);

        $response = $this
            ->getMock('\noFlash\CherryHttp\HttpResponse');
        $response
            ->expects($this->atLeastOnce())
            ->method('isConnectionClose')
            ->willReturn(true);

        $httpException = $this
            ->getMockBuilder('\noFlash\CherryHttp\HttpException')
            ->disableOriginalConstructor()
            ->getMock();
        $httpException
            ->expects($this->atLeastOnce())
            ->method('getResponse')
            ->willReturn($response);

        $client = $this->getMockBuilder('\noFlash\CherryHttp\StreamServerNodeInterface')->getMock();
        $client->subscribedEvents = array('httpException' => false);
        $client->expects($this->atLeastOnce())->method('disconnect');

        $httpExceptionHandlerReflection->invoke($this->server, $httpException, $client);
    }

    public function testEventHandlerIsExecutedOnHttpException()
    {
        $serverReflection = new \ReflectionObject($this->server);
        $httpExceptionHandlerReflection = $serverReflection->getMethod('handleHttpException');
        $httpExceptionHandlerReflection->setAccessible(true);

        $httpException = $this
            ->getMockBuilder('\noFlash\CherryHttp\HttpException')
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getMockBuilder('\noFlash\CherryHttp\StreamServerNodeInterface')->getMock();
        $client->subscribedEvents = array('httpException' => true);

        $eventsHandler = $this->getMockBuilder('\noFlash\CherryHttp\EventsHandlerInterface')->getMock();
        $eventsHandler
            ->expects($this->atLeastOnce())
            ->method('onHttpException')
            ->with($httpException, $client)
            ->willReturn($this->getMock('\noFlash\CherryHttp\HttpResponse'));
        $this->server->setEventsHandler($eventsHandler);
        $this->server->subscribeEvent('httpException');

        $httpExceptionHandlerReflection->invoke($this->server, $httpException, $client);
    }

    public function testNodeCanBeAdded()
    {
        $node = $this->getMockBuilder('\noFlash\CherryHttp\StreamServerNodeInterface')->getMock();
        $node->socket = $this->getSampleSocketStream();

        $serverReflection = new \ReflectionObject($this->server);
        $nodes = $serverReflection->getProperty('nodes');
        $nodes->setAccessible(true);

        $this->server->addNode($node);
        $this->assertContains($node, $nodes->getValue($this->server));
    }

    public function testNodeCanBeRemoved()
    {
        $node = $this->getMockBuilder('\noFlash\CherryHttp\StreamServerNodeInterface')->getMock();
        $node->socket = $this->getSampleSocketStream();

        $serverReflection = new \ReflectionObject($this->server);
        $nodes = $serverReflection->getProperty('nodes');
        $nodes->setAccessible(true);

        $this->server->addNode($node);
        $this->server->removeNode($node);
        $this->assertNotContains($node, $nodes->getValue($this->server));
    }

    public function testRemovingNotExistingNodeProducesError()
    {
        $node = $this->getMockBuilder('\noFlash\CherryHttp\StreamServerNodeInterface')->getMock();
        $node->socket = $this->getSampleSocketStream();

        $logger = $this->getMockBuilder('\Psr\Log\LoggerInterface')->getMock();
        $logger->expects($this->atLeastOnce())->method('error');

        $server = new Server($logger);
        $server->removeNode($node);
    }
}
