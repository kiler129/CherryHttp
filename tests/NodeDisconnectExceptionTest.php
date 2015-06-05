<?php
namespace noFlash\CherryHttp;


class NodeDisconnectExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testClassExtendsException()
    {
        $serverExceptionReflection = new \ReflectionClass('\noFlash\CherryHttp\NodeDisconnectException');
        $this->assertTrue($serverExceptionReflection->isSubclassOf('\Exception'));
    }
}
