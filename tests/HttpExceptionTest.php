<?php
namespace noFlash\CherryHttp;


class HttpExceptionTest extends \PHPUnit_Framework_TestCase {
    public function testClassExtendsException()
    {
        $serverExceptionReflection = new \ReflectionClass('\noFlash\CherryHttp\HttpException');
        $this->assertTrue($serverExceptionReflection->isSubclassOf('\Exception'));
    }

    public function testResponseIsGeneratedWithoutAnyParameters()
    {
        $httpException = new HttpException();
        $this->assertInstanceOf('\noFlash\CherryHttp\HttpResponse', $httpException->getResponse());
    }

    public function testProvidesPassedMessageUsingStandardExceptionInterface()
    {
        static $message = 'test message with utf ☃ snowman';

        $httpException = new HttpException($message);
        $this->assertEquals($message, $httpException->getMessage());
    }

    public function testProvidesPassedCodeUsingStandardExceptionInterface()
    {
        $httpException = new HttpException('', HttpCode::BAD_REQUEST);
        $this->assertSame(HttpCode::BAD_REQUEST, $httpException->getCode());
    }

    public function testFailsIfProvidedCodeIsInvalid()
    {
        $this->setExpectedException('\Exception');
        new HttpException('', 9999);
    }

    public function testCreatesHttpResponseWithMessagePassed()
    {
        static $message = 'test message with utf ☃ snowman';

        $httpException = new HttpException($message);
        $this->assertEquals($message, $httpException->getResponse()->getBody());
    }
}
