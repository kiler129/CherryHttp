<?php
namespace noFlash\CherryHttp;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class StreamServerNodeTest extends \PHPUnit_Framework_TestCase {

    const CLASS_NAME = '\noFlash\CherryHttp\StreamServerNode';

    /**
     * @var StreamServerNode.php:38
     */
    private $loggerMock;

    /**
     * @var vfsStreamDirectory
     */
    private $vfsRoot;

    public function setUp()
    {
        $this->loggerMock = $this->getMockForAbstractClass('\Psr\Log\LoggerInterface');
        $this->vfsRoot = vfsStream::setup();
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

    public function testDisconnectedNodeIsDroppedAfterWholeBufferIsWritten()
    {
        $streamMock = vfsStream::newFile('test')->withContent(' ')->at($this->vfsRoot); //Using vfsStream for mocking network sockets has one drawbacks - it's a file, so feof() will return true, some content prevents that
        $stream = fopen($streamMock->url(), 'r+');
        rewind($stream);

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, null, $this->loggerMock)
        );

        $streamServerNode->pushData('123456789');
        $streamServerNode->disconnect();

        $this->setExpectedException('\noFlash\CherryHttp\NodeDisconnectException');
        $streamServerNode->isWriteReady();
        $streamServerNode->onWriteReady();
        $streamServerNode->isWriteReady();

        $this->assertSame('123456789', $streamMock->getContent());
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

    public function testOnWriteReadyReturnsTrueWhenFullBufferHasBeenWritten()
    {
        $stream = fopen((defined('PHP_WINDOWS_VERSION_MAJOR')) ? 'nul' : '/dev/null', 'w');

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, null, $this->loggerMock)
        );

        $streamServerNode->pushData('123456789');
        $this->assertTrue($streamServerNode->onWriteReady());
    }

    /**
     * @short
     */
    public function testOnWriteReadyProperlyHandlesStreamBlockage()
    {
        vfsStream::setQuota(2);
        $streamMock = vfsStream::newFile('test')->withContent(' ')->at($this->vfsRoot); //Using vfsStream for mocking network sockets has one drawbacks - it's a file, so feof() will return true, some content prevents that
        $stream = fopen($streamMock->url(), 'r+');
        rewind($stream);

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, null, $this->loggerMock)
        );

        $streamServerNode->pushData('123456789');
        $this->assertFalse($streamServerNode->onWriteReady(), 'onWriteReady() didn\'t returned false on 1st try'); //This should write two bytes and return false (== not all data was written)
        $this->assertSame('12', $streamMock->getContent(), 'Stream should get first two bytes');

        vfsStream::setQuota(4); //Allow another two bytes to be written
        $this->assertFalse($streamServerNode->onWriteReady(), 'onWriteReady() didn\'t returned false on 2nd try');
        $this->assertSame('1234', $streamMock->getContent(), 'Stream should get next two bytes');

        vfsStream::setQuota(PHP_INT_MAX); //Allow all bytes to pass
        $this->assertTrue($streamServerNode->onWriteReady(), 'onWriteReady() didn\'t returned true on 3rd try');
        $this->assertSame('123456789', $streamMock->getContent(), 'Stream should get remaining bytes');
    }

    public function testOnReadReadyDropsClientIfNoBytesCanBeRead()
    {
        $stream = $this->getSampleSocketStream();
        stream_set_blocking($stream, 0);

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, null, $this->loggerMock)
        );

        $this->setExpectedException('\noFlash\CherryHttp\NodeDisconnectException');
        $streamServerNode->onReadReady();
    }

    public function testOnReadReadyWillNotCallProcessInputBufferIfNoBytesCanBeRead()
    {
        $stream = $this->getSampleSocketStream();
        stream_set_blocking($stream, 0);

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, null, $this->loggerMock)
        );
        $streamServerNode->expects($this->never())->method('processInputBuffer');

        $this->setExpectedException('\noFlash\CherryHttp\NodeDisconnectException');
        $streamServerNode->onReadReady();
    }

    public function testProcessInputBufferIsCalledAfterOnReadReady()
    {
        $streamMock = vfsStream::newFile('test')->withContent('1234')->at($this->vfsRoot); //Using vfsStream for mocking network sockets has one drawbacks - it's a file, so feof() will return true, some content prevents that
        $stream = fopen($streamMock->url(), 'r+');
        rewind($stream);

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, null, $this->loggerMock)
        );
        $streamServerNode->expects($this->atLeastOnce())->method('processInputBuffer');

        $streamServerNode->onReadReady();
    }

    public function testProcessInputBufferIsCalledAgainIfReturnFalseAfterOnReadReady()
    {
        $streamMock = vfsStream::newFile('test')->withContent('1234')->at($this->vfsRoot); //Using vfsStream for mocking network sockets has one drawbacks - it's a file, so feof() will return true, some content prevents that
        $stream = fopen($streamMock->url(), 'r+');
        rewind($stream);

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, null, $this->loggerMock)
        );
        $streamServerNode->expects($this->at(0))->method('processInputBuffer')->willReturn(false);
        $streamServerNode->expects($this->at(1))->method('processInputBuffer')->willReturn(false);
        $streamServerNode->expects($this->at(2))->method('processInputBuffer')->willReturn(true);
        $streamServerNode->expects($this->exactly(3))->method('processInputBuffer');

        $streamServerNode->onReadReady();
    }

    public function testSubscribingBufferEmptyEventEnabledSubscriptionOnlyForThatEvent()
    {
        $stream = $this->getSampleSocketStream();

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, null, $this->loggerMock)
        );

        $eventsTable = $streamServerNode->subscribedEvents;
        $eventsTable['writeBufferEmpty'] = true;

        $streamServerNode->subscribeEvent('writeBufferEmpty');
        $this->assertEquals($eventsTable, $streamServerNode->subscribedEvents);
    }

    public function testSubscribingHttpExceptionEventEnabledSubscriptionOnlyForThatEvent()
    {
        $stream = $this->getSampleSocketStream();

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, null, $this->loggerMock)
        );

        $eventsTable = $streamServerNode->subscribedEvents;
        $eventsTable['httpException'] = true;

        $streamServerNode->subscribeEvent('httpException');
        $this->assertEquals($eventsTable, $streamServerNode->subscribedEvents);
    }

    public function testSubscribingUnknownEventThrowsInvalidArgumentException()
    {
        $stream = $this->getSampleSocketStream();

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, null, $this->loggerMock)
        );

        $this->setExpectedException('\InvalidArgumentException');
        $streamServerNode->subscribeEvent('unknownEvent');
    }

    public function testUnsubscribingBufferEmptyEventDisablesSubscriptionOnlyForThatEvent()
    {
        $stream = $this->getSampleSocketStream();

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, null, $this->loggerMock)
        );

        $streamServerNode->subscribeEvent('httpException');
        $streamServerNode->subscribeEvent('writeBufferEmpty');

        $eventsTable = $streamServerNode->subscribedEvents;
        $eventsTable['writeBufferEmpty'] = false;

        $streamServerNode->unsubscribeEvent('writeBufferEmpty');
        $this->assertEquals($eventsTable, $streamServerNode->subscribedEvents);
    }

    public function testUnsubscribingHttpExceptionEventDisabledSubscriptionOnlyForThatEvent()
    {
        $stream = $this->getSampleSocketStream();

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, null, $this->loggerMock)
        );

        $streamServerNode->subscribeEvent('httpException');
        $streamServerNode->subscribeEvent('writeBufferEmpty');

        $eventsTable = $streamServerNode->subscribedEvents;
        $eventsTable['httpException'] = false;

        $streamServerNode->unsubscribeEvent('httpException');
        $this->assertEquals($eventsTable, $streamServerNode->subscribedEvents);
    }

    public function testUnsubscribingUnknownEventThrowsInvalidArgumentException()
    {
        $stream = $this->getSampleSocketStream();

        $streamServerNode = $this->getMockForAbstractClass(self::CLASS_NAME,
            array($stream, null, $this->loggerMock)
        );

        $this->setExpectedException('\InvalidArgumentException');
        $streamServerNode->unsubscribeEvent('unknownEvent');
    }
}
