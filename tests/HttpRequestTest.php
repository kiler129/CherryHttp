<?php
namespace noFlash\CherryHttp;


class HttpRequestTest extends \PHPUnit_Framework_TestCase {
    public function testClassExtendsHttpMessage()
    {
        $clientUpgradeExceptionReflection = new \ReflectionClass('\noFlash\CherryHttp\HttpRequest');
        $this->assertTrue($clientUpgradeExceptionReflection->isSubclassOf('\noFlash\CherryHttp\HttpMessage'));
    }
}
