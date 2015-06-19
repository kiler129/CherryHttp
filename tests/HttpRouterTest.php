<?php
namespace noFlash\CherryHttp;


class HttpRouterTest extends \PHPUnit_Framework_TestCase {
    public function testClassImplementsHttpRouterInterface()
    {
        $serverExceptionReflection = new \ReflectionClass('\noFlash\CherryHttp\HttpRouter');
        $this->assertTrue($serverExceptionReflection->isSubclassOf('\noFlash\CherryHttp\HttpRouterInterface'));
    }
}
