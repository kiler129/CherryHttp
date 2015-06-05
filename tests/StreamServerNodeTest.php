<?php
namespace noFlash\CherryHttp;

class StreamServerNodeTest extends \PHPUnit_Framework_TestCase {

    const CLASS_NAME = '\noFlash\CherryHttp\StreamServerNode';

    /**
     * @var StreamServerNode.php:38
     */
    private $loggerMock;

    public function setUp()
    {
        $this->loggerMock = $this->getMockForAbstractClass('\Psr\Log\LoggerInterface');
    }

    public function testImplementsStreamServerNodeInterface()
    {
        $streamServerNodeReflection = new \ReflectionClass(self::CLASS_NAME);
        $this->assertTrue($streamServerNodeReflection->isSubclassOf('\noFlash\CherryHttp\StreamServerNodeInterface'));
    }

    public function testConstructorAssignsPassedSocketToSocketProperty()
    {
        $stream = stream_socket_server('tcp://0.0.0.0:0');
        $this->assertNotFalse($stream, 'Failed to create socket for test (environment problem?)');

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, '', $this->loggerMock)
        );

        $this->assertSame($stream, $streamServerNode->socket);
    }

}
