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
}
