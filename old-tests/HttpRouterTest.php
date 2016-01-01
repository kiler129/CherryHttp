<?php
namespace noFlash\CherryHttp;


class HttpRouterTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Psr\Log\LoggerInterface
     */
    private $loggerMock;

    /**
     * @var HttpRouter
     */
    private $httpRouter;

    public function setUp()
    {
        $this->loggerMock = $this->getMock('\Psr\Log\LoggerInterface');
        $this->httpRouter = new HttpRouter($this->loggerMock);
    }

    public function testClassImplementsHttpRouterInterface()
    {
        $serverExceptionReflection = new \ReflectionClass('\noFlash\CherryHttp\HttpRouter');
        $this->assertTrue($serverExceptionReflection->isSubclassOf('\noFlash\CherryHttp\HttpRouterInterface'));
    }

    public function testPathHandlerCanBeAdded()
    {
        $requestHandler = $this->getMockBuilder('\noFlash\CherryHttp\HttpRequestHandlerInterface')->getMock();
        $requestHandler
            ->expects($this->atLeastOnce())
            ->method('getHandledPaths')
            ->willReturn(array('/test'));

        $this->httpRouter->addPathHandler($requestHandler);

        $routerReflection = new \ReflectionObject($this->httpRouter);
        $pathHandlersReflection = $routerReflection->getProperty('pathHandlers');
        $pathHandlersReflection->setAccessible(true);
        $handledPaths = $pathHandlersReflection->getValue($this->httpRouter);
        $this->assertSame(array('/test' => $requestHandler), $handledPaths);
    }

    public function testAddingPathHandlerWithAlreadyExistingRouterEmitsWarning()
    {
        $requestHandler1 = $this->getMockBuilder('\noFlash\CherryHttp\HttpRequestHandlerInterface')->getMock();
        $requestHandler1
            ->expects($this->atLeastOnce())
            ->method('getHandledPaths')
            ->willReturn(array('/test', '/test2'));

        $requestHandler2 = $this->getMockBuilder('\noFlash\CherryHttp\HttpRequestHandlerInterface')->getMock();
        $requestHandler2
            ->expects($this->atLeastOnce())
            ->method('getHandledPaths')
            ->willReturn(array('/test', '/test3'));

        $this->httpRouter->addPathHandler($requestHandler1);

        $this->loggerMock
            ->expects($this->atLeastOnce())
            ->method('warning')
            ->with($this->stringContains('replacing'));

        $this->httpRouter->addPathHandler($requestHandler2);
    }

    public function testAddingPathHandlerWithAlreadyExistingRouterOverwritesPreviousOne()
    {
        $requestHandler1 = $this->getMockBuilder('\noFlash\CherryHttp\HttpRequestHandlerInterface')->getMock();
        $requestHandler1
            ->expects($this->atLeastOnce())
            ->method('getHandledPaths')
            ->willReturn(array('/test', '/test2'));

        $requestHandler2 = $this->getMockBuilder('\noFlash\CherryHttp\HttpRequestHandlerInterface')->getMock();
        $requestHandler2
            ->expects($this->atLeastOnce())
            ->method('getHandledPaths')
            ->willReturn(array('/test', '/test3'));


        $this->httpRouter->addPathHandler($requestHandler1);
        $this->httpRouter->addPathHandler($requestHandler2);

        $routerReflection = new \ReflectionObject($this->httpRouter);
        $pathHandlersReflection = $routerReflection->getProperty('pathHandlers');
        $pathHandlersReflection->setAccessible(true);
        $handledPaths = $pathHandlersReflection->getValue($this->httpRouter);

        $this->assertArrayHasKey('/test', $handledPaths, 'Replaced route missing completely');
        $this->assertSame($handledPaths['/test'], $requestHandler2, 'Invalid handler for replaced route');
        $this->assertArrayHasKey('/test2', $handledPaths, 'Unique route from 1st handler missing');
        $this->assertSame($handledPaths['/test2'], $requestHandler1, 'Unique router from 1st handler refers to invalid handler');
        $this->assertArrayHasKey('/test3', $handledPaths, 'Unique route from 2nd handler missing');
        $this->assertSame($handledPaths['/test3'], $requestHandler2, 'Unique router from 2nd handler refers to invalid handler');
    }

    public function testPathHandlerCanBeRemoved()
    {
        $requestHandler1 = $this->getMockBuilder('\noFlash\CherryHttp\HttpRequestHandlerInterface')->getMock();
        $requestHandler1
            ->expects($this->atLeastOnce())
            ->method('getHandledPaths')
            ->willReturn(array('/test', '/test2'));

        $requestHandler2 = $this->getMockBuilder('\noFlash\CherryHttp\HttpRequestHandlerInterface')->getMock();
        $requestHandler2
            ->expects($this->atLeastOnce())
            ->method('getHandledPaths')
            ->willReturn(array('/test3', '/test4'));

        $this->httpRouter->addPathHandler($requestHandler1);
        $this->httpRouter->addPathHandler($requestHandler2);
        $this->httpRouter->removePathHandler($requestHandler1);

        $routerReflection = new \ReflectionObject($this->httpRouter);
        $pathHandlersReflection = $routerReflection->getProperty('pathHandlers');
        $pathHandlersReflection->setAccessible(true);
        $handledPaths = $pathHandlersReflection->getValue($this->httpRouter);
        $this->assertSame(array('/test3' => $requestHandler2, '/test4' => $requestHandler2), $handledPaths);
    }

    public function testHandlingClientRequestRemovesItsRequest()
    {
        $requestHandler = $this->getMockBuilder('\noFlash\CherryHttp\HttpRequestHandlerInterface')->getMock();
        $requestHandler
            ->expects($this->atLeastOnce())
            ->method('getHandledPaths')
            ->willReturn(array('/test'));
        $this->httpRouter->addPathHandler($requestHandler);


        $client = $this->getMockBuilder('\noFlash\CherryHttp\StreamServerNodeInterface')->getMock();
        $request = $this->getMockBuilder('\noFlash\CherryHttp\HttpRequest')
            ->disableOriginalConstructor()
            ->getMock();
        $request
            ->expects($this->any())
            ->method('getUri')
            ->willReturn('/test');
        $client->request = $request;

        $this->httpRouter->handleClientRequest($client);
        $this->assertNull($client->request);
    }

    public function testHandlingClientRequestCallsCorrectRequestHandler()
    {
        $requestHandler1 = $this->getMockBuilder('\noFlash\CherryHttp\HttpRequestHandlerInterface')->getMock();
        $requestHandler1
            ->expects($this->atLeastOnce())
            ->method('getHandledPaths')
            ->willReturn(array('/test', '/test2'));

        $requestHandler2 = $this->getMockBuilder('\noFlash\CherryHttp\HttpRequestHandlerInterface')->getMock();
        $requestHandler2
            ->expects($this->atLeastOnce())
            ->method('getHandledPaths')
            ->willReturn(array('/test3', '/test4'));


        $this->httpRouter->addPathHandler($requestHandler1);
        $this->httpRouter->addPathHandler($requestHandler2);

        $client = $this->getMockBuilder('\noFlash\CherryHttp\StreamServerNodeInterface')->getMock();
        $request = $this->getMockBuilder('\noFlash\CherryHttp\HttpRequest')
            ->disableOriginalConstructor()
            ->getMock();
        $request
            ->expects($this->any())
            ->method('getUri')
            ->willReturn('/test2');
        $client->request = $request;


        $requestHandler1
            ->expects($this->atLeastOnce())
            ->method('onRequest')
            ->with($this->equalTo($client), $this->equalTo($request));

        $this->httpRouter->handleClientRequest($client);
    }

    public function testHandlingClientRequestWithUnknownUriCallsDefaultRequestHandler()
    {
        $requestHandler1 = $this->getMockBuilder('\noFlash\CherryHttp\HttpRequestHandlerInterface')->getMock();
        $requestHandler1
            ->expects($this->atLeastOnce())
            ->method('getHandledPaths')
            ->willReturn(array('*', '/test2'));

        $requestHandler2 = $this->getMockBuilder('\noFlash\CherryHttp\HttpRequestHandlerInterface')->getMock();
        $requestHandler2
            ->expects($this->atLeastOnce())
            ->method('getHandledPaths')
            ->willReturn(array('/test3', '/test4'));


        $this->httpRouter->addPathHandler($requestHandler1);
        $this->httpRouter->addPathHandler($requestHandler2);

        $client = $this->getMockBuilder('\noFlash\CherryHttp\StreamServerNodeInterface')->getMock();
        $request = $this->getMockBuilder('\noFlash\CherryHttp\HttpRequest')
            ->disableOriginalConstructor()
            ->getMock();
        $request
            ->expects($this->any())
            ->method('getUri')
            ->willReturn('/test5');
        $client->request = $request;

        $requestHandler1
            ->expects($this->atLeastOnce())
            ->method('onRequest')
            ->with($this->equalTo($client), $this->equalTo($request));

        $this->httpRouter->handleClientRequest($client);
    }

    public function testHandlingClientRequestWithUnknownUriThrowsExceptionIfNoDefaultHandlerDefined()
    {
        $requestHandler1 = $this->getMockBuilder('\noFlash\CherryHttp\HttpRequestHandlerInterface')->getMock();
        $requestHandler1
            ->expects($this->atLeastOnce())
            ->method('getHandledPaths')
            ->willReturn(array('/test', '/test2'));

        $requestHandler2 = $this->getMockBuilder('\noFlash\CherryHttp\HttpRequestHandlerInterface')->getMock();
        $requestHandler2
            ->expects($this->atLeastOnce())
            ->method('getHandledPaths')
            ->willReturn(array('/test3', '/test4'));

        $this->httpRouter->addPathHandler($requestHandler1);
        $this->httpRouter->addPathHandler($requestHandler2);

        $client = $this->getMockBuilder('\noFlash\CherryHttp\StreamServerNodeInterface')->getMock();
        $request = $this->getMockBuilder('\noFlash\CherryHttp\HttpRequest')
            ->disableOriginalConstructor()
            ->getMock();
        $request
            ->expects($this->any())
            ->method('getUri')
            ->willReturn('/test5');
        $client->request = $request;

        try {
            $this->httpRouter->handleClientRequest($client);

        } catch (\noFlash\CherryHttp\HttpException $e) {
            $this->assertSame(HttpCode::NOT_FOUND, $e->getCode(), 'Invalid exception code');
        }
    }
}
