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

    public function testCustomHeadersPassedToConstructorOverwriteExistingOnes()
    {
        $headers = array('Connection' => 'close', 'Server' => 'ExampleServer/1.1');

        $httpResponse = new HttpResponse(null, $headers);

        $this->assertSame($headers['Connection'], $httpResponse->getHeader('Connection'));
        $this->assertSame($headers['Server'], $httpResponse->getHeader('Server'));
    }

    public function testBodyIsRejectedForNonBodyCodesInConstructor()
    {
        $this->setExpectedException('\LogicException');
        new HttpResponse('test', array(), HttpCode::NO_CONTENT);
    }

    public function testBodyCanBeChangedOnRuntime()
    {
        $httpResponse = new HttpResponse('test');
        $httpResponse->setBody('test2');

        $this->assertSame('test2', $httpResponse->getBody());
    }

    public function testBodyIsRejectedForNonBodyCodesInSetBody()
    {
        $httpResponse = new HttpResponse(null, array(), HttpCode::NO_CONTENT);

        $this->setExpectedException('\LogicException');
        $httpResponse->setBody('test');
    }

    public function testCodeCanBeChangedOnRuntime()
    {
        $httpResponse = new HttpResponse(null, array(), HttpCode::NO_CONTENT);
        $httpResponse->setCode(HttpCode::OK);

        $this->assertSame(HttpCode::OK, $httpResponse->getCode());
    }

    public function testNonBodyCodeIsRejectedWhenBodyWasSetInConstructor()
    {
        $httpResponse = new HttpResponse('test');

        $this->setExpectedException('\LogicException');
        $httpResponse->setCode(HttpCode::NO_CONTENT);
    }

    public function testNonBodyCodeIsRejectedWhenBodyWasSetBySetBody()
    {
        $httpResponse = new HttpResponse;
        $httpResponse->setBody('test');

        $this->setExpectedException('\LogicException');
        $httpResponse->setCode(HttpCode::NO_CONTENT);
    }

    public function testContentLengthIsCalculatedForBody()
    {
        $httpResponse = new HttpResponse;
        $this->assertEquals(0, $httpResponse->getHeader('content-length'), 'Invalid content-length with empty object');

        $httpResponse = new HttpResponse('', array(), HttpCode::NO_CONTENT);
        $this->assertEquals(0, $httpResponse->getHeader('content-length'), 'Invalid content-length with empty body & NO_CONTENT code');

        $httpResponse = new HttpResponse('test');
        $this->assertEquals(4, $httpResponse->getHeader('content-length'), 'Invalid content-length for ASCII text');

        $httpResponse = new HttpResponse('☃');
        $this->assertEquals(3, $httpResponse->getHeader('content-length'), 'Invalid content-length for UTF text');
    }

    public function testContentLenRgthIsRecalculateAfterBodyChange()
    {
        $httpResponse = new HttpResponse('test');
        $httpResponse->setBody('derp-derp');
        $this->assertEquals(9, $httpResponse->getHeader('content-length'));
    }

    public function testToStringGeneratesValidHttpResponse()
    {
        $httpResponse = new HttpResponse("Test\ncontent", array('X-test' => 'AbC'), HttpCode::IM_A_TEAPOT);
        $response = (string)$httpResponse;

        $responseLines = explode("\r\n", $response);
        $this->assertCount(7, $responseLines, 'Invalid number of lines');

        $this->assertSame('HTTP/' . $httpResponse->getProtocolVersion() . ' ' . HttpCode::getName($httpResponse->getCode()), $responseLines[0], 'Invalid status line');
        $this->assertEquals('server: '.$httpResponse->getHeader('server'), $responseLines[1], 'Server header missing or misplaced', 0, 10, false, true);
        $this->assertEquals('connection: '.$httpResponse->getHeader('connection'), $responseLines[2], 'Connection header missing or misplaced', 0, 10, false, true);
        $this->assertEquals('content-length: '.$httpResponse->getHeader('content-length'), $responseLines[3], 'Content-Length header missing or misplaced', 0, 10, false, true);
        $this->assertEquals('x-test: '.$httpResponse->getHeader('x-test'), $responseLines[4], 'Custom [X-test] header missing or misplaced', 0, 10, false, true);
        $this->assertEmpty($responseLines[5], 'There are no empty line between headers and content');
        $this->assertSame("Test\ncontent", $responseLines[6], 'Malformed content');
    }
}
