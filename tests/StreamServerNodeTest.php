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

    private function getSampleSocketStream()
    {
        $stream = stream_socket_server('tcp://0.0.0.0:0');
        $this->assertNotFalse($stream, 'Failed to create socket for test (environment problem?)');

        return $stream;
    }

    public function testImplementsStreamServerNodeInterface()
    {
        $streamServerNodeReflection = new \ReflectionClass(self::CLASS_NAME);
        $this->assertTrue($streamServerNodeReflection->isSubclassOf('\noFlash\CherryHttp\StreamServerNodeInterface'));
    }

    public function testConstructorAssignsPassedSocketToSocketProperty()
    {
        $stream = $this->getSampleSocketStream();

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, '', $this->loggerMock)
        );

        $this->assertSame($stream, $streamServerNode->socket);
    }

    public function testConstructorHonorsCustomPeerName()
    {
        $stream = $this->getSampleSocketStream();
        static $customName = "customName:8080";

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, $customName, $this->loggerMock)
        );

        $this->assertSame($customName, $streamServerNode->getPeerName());
    }

    public function testConstructorFetchesLocalPeerNameIfNotSpecified()
    {
        $stream = $this->getSampleSocketStream();
        $localName = stream_socket_get_name($stream, false);

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, null, $this->loggerMock)
        );

        $this->assertSame($localName, $streamServerNode->getPeerName());
    }

    public function testConstructorDisconnectsNodeWithClosedSocket()
    {
        $stream = $this->getSampleSocketStream();
        fclose($stream);

        $this->setExpectedException('\noFlash\CherryHttp\NodeDisconnectException');
        $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, null, $this->loggerMock)
        );
    }

    public function testConstructorDisconnectsNodeWithInvalidSocketValue()
    {
        $stream = 'boooo!';

        $this->setExpectedException('\noFlash\CherryHttp\NodeDisconnectException');
        $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, null, $this->loggerMock)
        );
    }
}
