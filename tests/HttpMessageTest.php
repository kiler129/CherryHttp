<?php
namespace noFlash\CherryHttp;


class HttpMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpMessage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpMessage;

    public function setUp()
    {
        $this->httpMessage = $this->getMockForAbstractClass('\noFlash\CherryHttp\HttpMessage');
    }

    /**
     * @testdox Class provides abstract __toString() method
     */
    public function testClassProvidesAbstractToStringMethod()
    {
        $this->assertTrue(method_exists($this->httpMessage, '__toString'));
    }

    public function testDefaultMessageIsCreatedWithNewestProtocolVersion()
    {
        $this->assertEquals('1.1', $this->httpMessage->getProtocolVersion());
    }

    /**
     * @testdox Setting 1.0 protocol version is persisted
     */
    public function testSetting10ProtocolVersionIsPersisted()
    {
        $this->httpMessage->setProtocolVersion('1.0');

        $this->assertEquals('1.0', $this->httpMessage->getProtocolVersion());
    }

    /**
     * @testdox Setting 1.1 protocol version is persisted
     */
    public function testSetting11ProtocolVersionIsPersisted()
    {
        $this->httpMessage->setProtocolVersion('1.1');

        $this->assertEquals('1.1', $this->httpMessage->getProtocolVersion());
    }

    public function testSettingUnknownProtocolVersionThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->httpMessage->setProtocolVersion('1.2');
    }

    public function testSingleHeaderIsPersisted()
    {
        $this->httpMessage->setHeader('test', 'value');

        $this->assertSame('value', $this->httpMessage->getHeader('test'));
    }

    public function testSingleHeaderCanBeGetByCaseInsensitiveName()
    {
        $this->httpMessage->setHeader('TeSt', 'value');

        $this->assertSame('value', $this->httpMessage->getHeader('tEsT'));
    }

    public function testSingleHeaderValueCaseIsPreserved()
    {
        $this->httpMessage->setHeader('test', 'vAlUe');

        $this->assertSame('vAlUe', $this->httpMessage->getHeader('test'));
    }

    public function testFetchingUnknownHeaderReturnsNull()
    {
        $this->assertNull($this->httpMessage->getHeader('test'));
    }

    public function testSingleHeaderCanBeRemoved()
    {
        $this->httpMessage->setHeader('test', 'value');
        $this->httpMessage->removeReader('test');

        $this->assertNull($this->httpMessage->getHeader('test'));
    }

    public function testSettingHeaderWithTheSameNameReplacesPreviousValueByDefault()
    {
        $this->httpMessage->setHeader('test', 'value1');
        $this->httpMessage->setHeader('test', 'value2');
        $this->httpMessage->setHeader('test', 'value3');

        $this->assertSame('value3', $this->httpMessage->getHeader('test'));
    }

    public function testMultipleHeadersWithTheSameNameCanBeSet()
    {
        $this->httpMessage->setHeader('test', 'value1', false);
        $this->httpMessage->setHeader('test', 'value2', false);
        $this->httpMessage->setHeader('test', 'value3', false);

        $this->assertSame('value1,value2,value3', $this->httpMessage->getHeader('test'));
    }

    public function testMultipleHeadersWithTheSameNameCanBeGetByCaseInsensitiveName()
    {
        $this->httpMessage->setHeader('tEsT', 'value1', false);
        $this->httpMessage->setHeader('tEsT', 'value2', false);
        $this->httpMessage->setHeader('tEsT', 'value3', false);

        $this->assertSame('value1,value2,value3', $this->httpMessage->getHeader('teSt'));
    }

    public function testSetHeaderReplacesHeaderWithTheSameNameIgnoringNameCase()
    {
        $this->httpMessage->setHeader('Test', 'value1', false);
        $this->httpMessage->setHeader('tEst', 'value2', false);
        $this->httpMessage->setHeader('teSt', 'value3', false);

        $this->assertSame('value1,value2,value3', $this->httpMessage->getHeader('test'));
    }

    public function testMultipleHeadersWithTheSamePreservesValuesCase()
    {
        $this->httpMessage->setHeader('test', 'Value1', false);
        $this->httpMessage->setHeader('test', 'vAlue2', false);
        $this->httpMessage->setHeader('test', 'vaLue3', false);

        $this->assertSame('Value1,vAlue2,vaLue3', $this->httpMessage->getHeader('test'));
    }

    public function testMultipleHeadersCanBeRemovedUsingSingleRemoveCall()
    {
        $this->httpMessage->setHeader('test', 'Value1', false);
        $this->httpMessage->setHeader('test', 'vAlue2', false);
        $this->httpMessage->setHeader('test', 'vaLue3', false);
        $this->httpMessage->removeReader('test');

        $this->assertNull($this->httpMessage->getHeader('test'));
    }

    public function testGetHeadersMethodReturnsWhatItSupposeToReturn()
    {
        $this->httpMessage->setHeader('Test', 'Value1', false);
        $this->httpMessage->setHeader('test', 'vAlue2', false);
        $this->httpMessage->setHeader('test', 'vaLue3', false);
        $this->httpMessage->setHeader('Test2', 'value4');
        $this->httpMessage->setHeader('tEst2', 'value5');

        $validOutput = array(
            'Test' => array('Value1', 'vAlue2', 'vaLue3'), //Header name cases is determined by first call in addition mode
            'tEst2' => array('value5') //Header name cases is determined by last call in replace mode
        );

        $this->assertSame($validOutput, $this->httpMessage->getHeaders());
    }

    public function testGetIndexedHeadersMethodReturnsWhatItSupposeToReturn()
    {
        $this->httpMessage->setHeader('Test', 'Value1', false);
        $this->httpMessage->setHeader('test', 'vAlue2', false);
        $this->httpMessage->setHeader('test', 'vaLue3', false);
        $this->httpMessage->setHeader('Test2', 'value4');
        $this->httpMessage->setHeader('tEst2', 'value5');

        $validOutput = array(
            'test' => array(
                'Test', //Header name cases is determined by first call in addition mode
                array('Value1', 'vAlue2', 'vaLue3')
            ),
            'test2' => array(
                'tEst2', //Header name cases is determined by last call in replace mode
                array('value5')
            )
        );

        $this->assertSame($validOutput, $this->httpMessage->getIndexedHeaders());
    }

    public function testGetHeaderLinesReturnsMultipleValuesAsArray()
    {
        $this->httpMessage->setHeader('Test', 'Value1', false);
        $this->httpMessage->setHeader('test', 'vAlue2', false);
        $this->httpMessage->setHeader('test', 'vaLue3', false);

        $this->assertSame(array('Value1', 'vAlue2', 'vaLue3'), $this->httpMessage->getHeaderLines('TeSt'));
    }

    public function testGetHeaderLinesReturnsEmptyArrayIfHeaderMissing()
    {
        $this->assertSame(array(), $this->httpMessage->getHeaderLines('non-existing-header'));
    }

    /**
     * @testdox Connection is not closed for HTTP/1.1 by default
     */
    public function testConnectionIsNotClosedForHttp11ByDefault()
    {
        $this->httpMessage->setProtocolVersion('1.1');

        $this->assertFalse($this->httpMessage->isConnectionClose());
    }

    /**
     * @testdox Connection is not closed for HTTP/1.1 after removing "Connection" header
     */
    public function testConnectionIsNotClosedForHttp11AfterRemovingConnectionHeader()
    {
        $this->httpMessage->setProtocolVersion('1.1');
        $this->httpMessage->removeReader('connection');

        $this->assertFalse($this->httpMessage->isConnectionClose());
    }

    public function closeHeaderValueProvider()
    {
        return array(
            array('close'),
            array('CLOSE'),
            array('Close'),
            array('ClOsE'),
        );
    }

    /**
     * @testdox Connection is closed for HTTP/1.1 with "Connection: close" header
     * @dataProvider closeHeaderValueProvider
     */
    public function testConnectionIsClosedForHttp11WithConnectionCloseHeader($connectionValue)
    {
        $this->httpMessage->setProtocolVersion('1.1');
        $this->httpMessage->setHeader('connection', $connectionValue);

        $this->assertTrue($this->httpMessage->isConnectionClose(), "Failed for \"$connectionValue\"");
    }

    public function keepAliveHeaderValueProvider()
    {
        return array(
            array('keep-alive'),
            array('KEEP-ALIVE'),
            array('Keep-alive'),
            array('Keep-Alive'),
            array('KeEp-AlIvE'),
        );
    }

    /**
     * @testdox Connection is not closed for HTTP/1.1 with "Connection: keep-alive" header
     * @dataProvider keepAliveHeaderValueProvider
     */
    public function testConnectionIsNotClosedForHttp11WithConnectionKeepAliveHeader($connectionValue)
    {
        $this->httpMessage->setProtocolVersion('1.1');
        $this->httpMessage->setHeader('connection', $connectionValue);

        $this->assertFalse($this->httpMessage->isConnectionClose(), "Failed for \"$connectionValue\"");
    }

    /**
     * @testdox Connection is closed for HTTP/1.0 by default
     */
    public function testConnectionIsClosedForHttp10ByDefault()
    {
        $this->httpMessage->setProtocolVersion('1.0');

        $this->assertTrue($this->httpMessage->isConnectionClose());
    }

    /**
     * @testdox Connection is closed for HTTP/1.0 after removing "Connection" header
     */
    public function testConnectionIsClosedForHttp10AfterRemovingConnectionHeader()
    {
        $this->httpMessage->setProtocolVersion('1.0');
        $this->httpMessage->removeReader('connection');

        $this->assertTrue($this->httpMessage->isConnectionClose());
    }

    /**
     * @testdox Connection is closed for HTTP/1.0 with "Connection: close" header
     * @dataProvider closeHeaderValueProvider
     */
    public function testConnectionIsClosedForHttp10WithConnectionCloseHeader($connectionValue)
    {
        $this->httpMessage->setProtocolVersion('1.0');
        $this->httpMessage->setHeader('connection', $connectionValue);

        $this->assertTrue($this->httpMessage->isConnectionClose(), "Failed for \"$connectionValue\"");
    }

    /**
     * @testdox Connection is not closed for HTTP/1.0 with "Connection: keep-alive" header
     * @dataProvider keepAliveHeaderValueProvider
     */
    public function testConnectionIsNotClosedForHttp10WithConnectionKeepAliveHeader($connectionValue)
    {
        $this->httpMessage->setProtocolVersion('1.0');
        $this->httpMessage->setHeader('connection', $connectionValue);

        $this->assertFalse($this->httpMessage->isConnectionClose(), "Failed for \"$connectionValue\"");
    }
}
