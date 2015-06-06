<?php
namespace noFlash\CherryHttp;


class HttpExceptionTest extends \PHPUnit_Framework_TestCase {
    public function testClassExtendsException()
    {
        $serverExceptionReflection = new \ReflectionClass('\noFlash\CherryHttp\HttpException');
        $this->assertTrue($serverExceptionReflection->isSubclassOf('\Exception'));
    }
}
