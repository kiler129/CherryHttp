<?php
namespace noFlash\CherryHttp;


class HttpResponseTest extends \PHPUnit_Framework_TestCase {
    public function testResponseUsesHttpOkCodeByDefault()
    {
        $httpResponse = new HttpResponse;

        $this->assertSame(HttpCode::OK, $httpResponse->getCode());
    }

    public function testResponseHasNoBodyByDefault()
    {
        $httpResponse = new HttpResponse;

        $this->assertEmpty($httpResponse->getBody());
    }
}
