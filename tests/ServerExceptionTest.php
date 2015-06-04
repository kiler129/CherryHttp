<?php
namespace noFlash\CherryHttp;


class ServerExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testClassExtendsException()
    {
        $serverExceptionReflection = new \ReflectionClass('\noFlash\CherryHttp\ServerException');
        $this->assertTrue($serverExceptionReflection->isSubclassOf('\Exception'));
    }
}
