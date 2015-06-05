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
        $nodeDisconnectExceptionReflection = new \ReflectionClass('\noFlash\CherryHttp\NodeDisconnectException');
        $this->assertTrue($nodeDisconnectExceptionReflection->isSubclassOf('\Exception'));
    }

    public function testProvidesNonEmptyExplanationAsMessage()
    {
        $nodeDisconnectException = new NodeDisconnectException($this->nodeMock);
        $message = $nodeDisconnectException->getMessage();

        $this->assertNotEmpty($message);
        $this->assertInternalType('string', $message);
    }

    public function testAlwaysReturnZeroCode()
    {
        $nodeDisconnectException = new NodeDisconnectException($this->nodeMock, 123);
        $this->assertSame(0, $nodeDisconnectException->getCode());
    }


}
