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
}
