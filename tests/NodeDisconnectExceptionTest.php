<?php
namespace noFlash\CherryHttp;


class NodeDisconnectExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StreamServerNodeInterface
     */
    private $nodeMock;

    public function setUp()
    {
        $this->nodeMock = $this->getMockBuilder('\noFlash\CherryHttp\StreamServerNodeInterface')->getMock();
    }

    public function testClassExtendsException()
    {
        $serverExceptionReflection = new \ReflectionClass('\noFlash\CherryHttp\NodeDisconnectException');
        $this->assertTrue($serverExceptionReflection->isSubclassOf('\Exception'));
    }

}
