<?php
namespace noFlash\CherryHttp;


class ClientUpgradeExceptionTest extends \PHPUnit_Framework_TestCase
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


    public function testProvidesNonEmptyExplanationAsMessage()
    {
        $clientUpgradeException = new ClientUpgradeException($this->oldNodeMock, $this->newNodeMock);
        $message = $clientUpgradeException->getMessage();

        $this->assertNotEmpty($message);
        $this->assertInternalType('string', $message);
    }

    public function testAlwaysReturnZeroCode()
    {
        $clientUpgradeException = new ClientUpgradeException($this->oldNodeMock, $this->newNodeMock);
        $this->assertSame(0, $clientUpgradeException->getCode());
    }

    public function testReturnPassedNodes()
    {
        $clientUpgradeException = new ClientUpgradeException($this->oldNodeMock, $this->newNodeMock);
        $this->assertSame($this->oldNodeMock, $clientUpgradeException->getOldClient(), 'Invalid old node provided');
        $this->assertSame($this->newNodeMock, $clientUpgradeException->getNewClient(), 'Invalid new node provided');
    }
}
