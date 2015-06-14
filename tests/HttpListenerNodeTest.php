<?php
namespace noFlash\CherryHttp;


class HttpListenerNodeTest extends \PHPUnit_Framework_TestCase {
    /**
     * @testdox Class extends StreamServerNode
     */
    public function testClassExtendsStreamServerNode()
    {
        $clientUpgradeExceptionReflection = new \ReflectionClass('\noFlash\CherryHttp\HttpListenerNode');
        $this->assertTrue($clientUpgradeExceptionReflection->isSubclassOf('\noFlash\CherryHttp\StreamServerNode'));
    }
}
