<?php
namespace noFlash\CherryHttp;


class HttpRouterTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var HttpRouter
     */
    private $httpRouter;

    public function setUp()
    {
        $logger = $this->getMockForAbstractClass('\Psr\Log\LoggerInterface');
        $this->httpRouter = new HttpRouter($logger);
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
}
