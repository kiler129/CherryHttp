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
}
