<?php
namespace noFlash\CherryHttp;


class ServerExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testClassExtendsException()
    {
        $serverExceptionReflection = new \ReflectionClass('\noFlash\CherryHttp\ServerException');
        $this->assertTrue($serverExceptionReflection->isSubclassOf('\Exception'));
    }

    public function testGivenMessageCanBeRead()
    {
        static $message = 'test message with utf ☃ snowman';

        $serverException = new ServerException($message);
        $this->assertContains($message, $serverException->getMessage(), '', true);
    }
}
