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

    public function testResponseContainsConnectionHeaderByDefault()
    {
        $httpResponse = new HttpResponse;

        $this->assertNotNull($httpResponse->getHeader('connection'));
    }

    public function testResponseContainsServerHeaderByDefault()
    {
        $httpResponse = new HttpResponse;

        $this->assertContains('CherryHttp', $httpResponse->getHeader('server'), '', true);
    }

    public function testBodyCanBeSetWithDefaultParameters()
    {
        static $body = '🍒CherryHttp';
        $httpResponse = new HttpResponse($body);

        $this->assertSame($body, $httpResponse->getBody());
    }

    public function testCustomHeadersPassedToConstructorAreSet()
    {
        $headers = array('X-Test' => 'blah_blah', 'X-Something' => 'aaa');

        $httpResponse = new HttpResponse(null, $headers);

        $this->assertSame($headers['X-Test'], $httpResponse->getHeader('X-Test'));
        $this->assertSame($headers['X-Something'], $httpResponse->getHeader('X-Something'));

    }
}
