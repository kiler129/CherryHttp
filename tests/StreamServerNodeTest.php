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

    public function testConstructorAcceptsBothIpV4AndIpV6PeerNames()
    {
        $stream = $this->getSampleSocketStream();
        static $customNameIpV4 = "127.0.0.1:8080";
        static $customNameIpV6 = "::ffff:127.0.0.1:8080";

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, $customNameIpV4, $this->loggerMock)
        );

        $this->assertSame($customNameIpV4, $streamServerNode->getPeerName(), 'IP v4 name not accepted');


        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, $customNameIpV6, $this->loggerMock)
        );

        $this->assertSame($customNameIpV6, $streamServerNode->getPeerName(), 'IP v6 name not accepted');
    }

    public function testConstructorFailsOnPeerNameWithoutPort()
    {
        $stream = $this->getSampleSocketStream();
        static $customName = "127.0.0.1";

        $this->setExpectedException('\InvalidArgumentException');
        $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, $customName, $this->loggerMock)
        );
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

    public function testFetchingIpV4ReturnsValidIpAddress()
    {
        $stream = $this->getSampleSocketStream();
        $customName = "127.0.0.1:8080";

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, $customName, $this->loggerMock)
        );

        $this->assertSame('127.0.0.1', $streamServerNode->getIp());
    }

    public function testFetchingIpV6ReturnsValidIpAddress()
    {
        $stream = $this->getSampleSocketStream();
        $customName = "::ffff:127.0.0.1:8080";

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, $customName, $this->loggerMock)
        );

        $this->assertSame('::ffff:127.0.0.1', $streamServerNode->getIp());
    }

    public function testStringRepresentationContainsPeerName()
    {
        $stream = $this->getSampleSocketStream();
        $customName = "127.0.0.1:8080";

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, $customName, $this->loggerMock)
        );

        $this->assertContains($customName, (string)$streamServerNode);
    }

    public function testFreshObjectWithValidSocketAllowsAddingDataToOutputBuffer()
    {
        $stream = $this->getSampleSocketStream();

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, null, $this->loggerMock)
        );

        $this->assertTrue($streamServerNode->pushData('test'));
    }

    public function testAddingDataAfterDisconnectFails()
    {
        $stream = $this->getSampleSocketStream();

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, null, $this->loggerMock)
        );

        $streamServerNode->disconnect();

        $this->assertFalse($streamServerNode->pushData('test'));
    }

    public function testCheckIfNodeIsWriteReadyWithoutPopulatingAnyData()
    {
        $stream = $this->getSampleSocketStream();

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, null, $this->loggerMock)
        );

        $this->assertFalse($streamServerNode->isWriteReady());
    }

    public function testCheckIfNodeIsWriteReadyAfterSendingWholeBuffer()
    {
        $stream = fopen((defined('PHP_WINDOWS_VERSION_MAJOR')) ? 'nul' : '/dev/null', 'w');

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, null, $this->loggerMock)
        );

        $streamServerNode->pushData('test');
        $streamServerNode->onWriteReady();

        $this->assertFalse($streamServerNode->isWriteReady());
    }


    public function testCheckIfNodeIsWriteReadyWithSingleNullCharacterInBuffer()
    {
        $stream = $this->getSampleSocketStream();

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, null, $this->loggerMock)
        );

        $streamServerNode->pushData("\0");

        $this->assertTrue($streamServerNode->isWriteReady());
    }

}
