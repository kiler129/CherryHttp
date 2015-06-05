<?php
namespace noFlash\CherryHttp;


class ClientUpgradeExceptionExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testClassExtendsException()
    {
        $clientUpgradeExceptionReflection = new \ReflectionClass('\noFlash\CherryHttp\ClientUpgradeException');
        $this->assertTrue($clientUpgradeExceptionReflection->isSubclassOf('\Exception'));
    }
}
