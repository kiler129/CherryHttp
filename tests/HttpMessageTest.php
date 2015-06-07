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
}
