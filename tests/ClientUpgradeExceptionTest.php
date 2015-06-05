<?php
namespace noFlash\CherryHttp;


class ClientUpgradeExceptionExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StreamServerNodeInterface
     */
    private $oldNodeMock;

    /**
     * @var StreamServerNodeInterface
     */
    private $newNodeMock;

    public function setUp()
    {
        $this->oldNodeMock = $this->getMockBuilder('\noFlash\CherryHttp\StreamServerNodeInterface')->getMock();
        $this->newNodeMock = $this->getMockBuilder('\noFlash\CherryHttp\StreamServerNodeInterface')->getMock();
    }

    public function testClassExtendsException()
    {
        $clientUpgradeExceptionReflection = new \ReflectionClass('\noFlash\CherryHttp\ClientUpgradeException');
        $this->assertTrue($clientUpgradeExceptionReflection->isSubclassOf('\Exception'));
    }
}
