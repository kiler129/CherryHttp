<?php
namespace noFlash\CherryHttp;


class HttpMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return HttpMessage|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getHttpMessageObject()
    {
        return $this->getMockForAbstractClass('\noFlash\CherryHttp\HttpMessage');
    }

    public function testDefaultMessageIsCreatedWithNewestProtocolVersion()
    {
        $httpMessage = $this->getHttpMessageObject();

        $this->assertEquals('1.1', $httpMessage->getProtocolVersion());
    }

    public function testSettingKnownProtocolVersionsIsPersisted()
    {
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setProtocolVersion('1.0');

        $this->assertEquals('1.0', $httpMessage->getProtocolVersion(), 'Failed to set HTTP/1.0');

        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setProtocolVersion('1.1');

        $this->assertEquals('1.1', $httpMessage->getProtocolVersion(), 'Failed to set HTTP/1.1');
    }

    public function testSettingUnknownProtocolVersionThrowsException()
    {
        $httpMessage = $this->getHttpMessageObject();

        $this->setExpectedException('\InvalidArgumentException');
        $httpMessage->setProtocolVersion('1.2');
    }

    public function testSingleHeaderIsPersisted()
    {
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setHeader('test', 'value');

        $this->assertSame('value', $httpMessage->getHeader('test'));
    }

    public function testSingleHeaderCanBeGetByCaseInsensitiveName()
    {
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setHeader('TeSt', 'value');

        $this->assertSame('value', $httpMessage->getHeader('tEsT'));
    }

    public function testSingleHeaderValueCaseIsPreserved()
    {
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setHeader('test', 'vAlUe');

        $this->assertSame('vAlUe', $httpMessage->getHeader('test'));
    }

    public function testFetchingUnknownHeaderReturnsNull()
    {
        $httpMessage = $this->getHttpMessageObject();

        $this->assertNull($httpMessage->getHeader('test'));
    }

    public function testSingleHeaderCanBeRemoved()
    {
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setHeader('test', 'value');
        $httpMessage->removeReader('test');

        $this->assertNull($httpMessage->getHeader('test'));
    }

    public function testSettingHeaderWithTheSameNameReplacesPreviousValueByDefault()
    {
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setHeader('test', 'value1');
        $httpMessage->setHeader('test', 'value2');
        $httpMessage->setHeader('test', 'value3');

        $this->assertSame('value3', $httpMessage->getHeader('test'));
    }

    public function testMultipleHeadersWithTheSameNameCanBeSet()
    {
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setHeader('test', 'value1', false);
        $httpMessage->setHeader('test', 'value2', false);
        $httpMessage->setHeader('test', 'value3', false);

        $this->assertSame('value1,value2,value3', $httpMessage->getHeader('test'));
    }

    public function testMultipleHeadersWithTheSameNameCanBeGetByCaseInsensitiveName()
    {
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setHeader('tEsT', 'value1', false);
        $httpMessage->setHeader('tEsT', 'value2', false);
        $httpMessage->setHeader('tEsT', 'value3', false);

        $this->assertSame('value1,value2,value3', $httpMessage->getHeader('teSt'));
    }

    public function testSetHeaderReplacesHeaderWithTheSameNameIgnoringNameCase()
    {
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setHeader('Test', 'value1', false);
        $httpMessage->setHeader('tEst', 'value2', false);
        $httpMessage->setHeader('teSt', 'value3', false);

        $this->assertSame('value1,value2,value3', $httpMessage->getHeader('test'));
    }

    public function testMultipleHeadersWithTheSamePreservesValuesCase()
    {
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setHeader('test', 'Value1', false);
        $httpMessage->setHeader('test', 'vAlue2', false);
        $httpMessage->setHeader('test', 'vaLue3', false);

        $this->assertSame('Value1,vAlue2,vaLue3', $httpMessage->getHeader('test'));
    }

    public function testMultipleHeadersCanBeRemovedUsingSingleRemoveCall()
    {
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setHeader('test', 'Value1', false);
        $httpMessage->setHeader('test', 'vAlue2', false);
        $httpMessage->setHeader('test', 'vaLue3', false);
        $httpMessage->removeReader('test');

        $this->assertNull($httpMessage->getHeader('test'));
    }

    public function testGetHeadersMethodReturnsWhatItSupposeToReturn()
    {
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setHeader('Test', 'Value1', false);
        $httpMessage->setHeader('test', 'vAlue2', false);
        $httpMessage->setHeader('test', 'vaLue3', false);
        $httpMessage->setHeader('Test2', 'value4');
        $httpMessage->setHeader('tEst2', 'value5');

        $validOutput = array(
            'Test' => array('Value1', 'vAlue2', 'vaLue3'), //Header name cases is determined by first call in addition mode
            'tEst2' => array('value5') //Header name cases is determined by last call in replace mode
        );

        $this->assertSame($validOutput, $httpMessage->getHeaders());
    }

    public function testGetIndexedHeadersMethodReturnsWhatItSupposeToReturn()
    {
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setHeader('Test', 'Value1', false);
        $httpMessage->setHeader('test', 'vAlue2', false);
        $httpMessage->setHeader('test', 'vaLue3', false);
        $httpMessage->setHeader('Test2', 'value4');
        $httpMessage->setHeader('tEst2', 'value5');

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

        $this->assertSame($validOutput, $httpMessage->getIndexedHeaders());
    }

    public function testGetHeaderLinesReturnsMultipleValuesAsArray()
    {
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setHeader('Test', 'Value1', false);
        $httpMessage->setHeader('test', 'vAlue2', false);
        $httpMessage->setHeader('test', 'vaLue3', false);

        $this->assertSame(array('Value1', 'vAlue2', 'vaLue3'), $httpMessage->getHeaderLines('TeSt'));
    }

    public function testGetHeaderLinesReturnsEmptyArrayIfHeaderMissing()
    {
        $httpMessage = $this->getHttpMessageObject();

        $this->assertSame(array(), $httpMessage->getHeaderLines('non-existing-header'));
    }

    /**
     * @testdox Connection is not closed for HTTP/1.1 by default
     */
    public function testConnectionIsNotClosedForHttp11ByDefault()
    {
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setProtocolVersion('1.1');

        $this->assertFalse($httpMessage->isConnectionClose());
    }

    /**
     * @testdox Connection is not closed for HTTP/1.1 after removing "Connection" header
     */
    public function testConnectionIsNotClosedForHttp11AfterRemovingConnectionHeader()
    {
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setProtocolVersion('1.1');
        $httpMessage->removeReader('connection');

        $this->assertFalse($httpMessage->isConnectionClose());
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
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setProtocolVersion('1.1');
        $httpMessage->setHeader('connection', $connectionValue);

        $this->assertTrue($httpMessage->isConnectionClose(), "Failed for \"$connectionValue\"");
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
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setProtocolVersion('1.1');
        $httpMessage->setHeader('connection', $connectionValue);

        $this->assertFalse($httpMessage->isConnectionClose(), "Failed for \"$connectionValue\"");
    }

    /**
     * @testdox Connection is closed for HTTP/1.0 by default
     */
    public function testConnectionIsClosedForHttp10ByDefault()
    {
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setProtocolVersion('1.0');

        $this->assertTrue($httpMessage->isConnectionClose());
    }

    /**
     * @testdox Connection is closed for HTTP/1.0 after removing "Connection" header
     */
    public function testConnectionIsClosedForHttp10AfterRemovingConnectionHeader()
    {
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setProtocolVersion('1.0');
        $httpMessage->removeReader('connection');

        $this->assertTrue($httpMessage->isConnectionClose());
    }

    /**
     * @testdox Connection is closed for HTTP/1.0 with "Connection: close" header
     * @dataProvider closeHeaderValueProvider
     */
    public function testConnectionIsClosedForHttp10WithConnectionCloseHeader($connectionValue)
    {
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setProtocolVersion('1.0');
        $httpMessage->setHeader('connection', $connectionValue);

        $this->assertTrue($httpMessage->isConnectionClose(), "Failed for \"$connectionValue\"");
    }

    /**
     * @testdox Connection is not closed for HTTP/1.0 with "Connection: keep-alive" header
     * @dataProvider keepAliveHeaderValueProvider
     */
    public function testConnectionIsNotClosedForHttp10WithConnectionKeepAliveHeader($connectionValue)
    {
        $httpMessage = $this->getHttpMessageObject();
        $httpMessage->setProtocolVersion('1.0');
        $httpMessage->setHeader('connection', $connectionValue);

        $this->assertFalse($httpMessage->isConnectionClose(), "Failed for \"$connectionValue\"");
    }
}
