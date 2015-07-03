<?php
namespace noFlash\CherryHttp;


class HttpRequestTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $loggerMock;


    public function setUp()
    {
        $this->loggerMock = $this->getMockBuilder('\Psr\Log\LoggerInterface')->getMock();
    }

    public function testClassExtendsHttpMessage()
    {
        $clientUpgradeExceptionReflection = new \ReflectionClass('\noFlash\CherryHttp\HttpRequest');
        $this->assertTrue($clientUpgradeExceptionReflection->isSubclassOf('\noFlash\CherryHttp\HttpMessage'));
    }

    public function testEmptyRequestIsRejected()
    {
        $this->setExpectedException('\noFlash\CherryHttp\HttpException');
        new HttpRequest('', $this->loggerMock);
    }
}
