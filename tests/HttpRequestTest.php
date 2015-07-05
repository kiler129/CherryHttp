<?php
namespace noFlash\CherryHttp;


class HttpRequestTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $loggerMock;


    public function setUp()
    {
        $this->loggerMock = $this->getMockBuilder('\Psr\Log\LoggerInterface')->getMock();
    }

    public function testClassExtendsHttpMessage()
    {
        $clientUpgradeExceptionReflection = new \ReflectionClass('\noFlash\CherryHttp\HttpRequest');
        $this->assertTrue($clientUpgradeExceptionReflection->isSubclassOf('\noFlash\CherryHttp\HttpMessage'));
    }

    public function testEmptyRequestIsRejected()
    {
        $this->setExpectedException('\noFlash\CherryHttp\HttpException', 'not HTTP', HttpCode::BAD_REQUEST);
        new HttpRequest('', $this->loggerMock);
    }

    public function testStatusLineWithoutProperProtocolIsRejected()
    {
        $this->setExpectedException('\noFlash\CherryHttp\HttpException', 'not HTTP', HttpCode::BAD_REQUEST);
        new HttpRequest('GET / FTP/1.1', $this->loggerMock);
    }

    public function commonHttpMethodsProvider()
    {
        return array(
            array('GET'),
            array('HEAD'),
            array('POST'),
            array('PUT'),
            array('DELETE')
        );
    }

    /**
     * @dataProvider commonHttpMethodsProvider()
     */
    public function testMethodIsRetrievedFromStatusLine($method)
    {
        $request = new HttpRequest("$method /test HTTP/1.1", $this->loggerMock);
        $this->assertEquals($method, $request->getMethod(), '', 0.0, 10, false, true);
    }

    public function testUriIsRetrievedFromStatusLine()
    {
        $request = new HttpRequest("GET /test HTTP/1.0", $this->loggerMock);
        $this->assertSame('/test', $request->getUri());
    }

    public function testTooLongUrisAreRejected()
    {
        $this->setExpectedException('\noFlash\CherryHttp\HttpException', '', HttpCode::REQUEST_URI_TOO_LONG);

        $uri = '/'.str_repeat('a', HttpRequest::MAX_URI_LENGTH);
        new HttpRequest("GET $uri HTTP/1.0", $this->loggerMock);
    }

    public function testQueryStringIsRetrievedFromStatusLine()
    {
        $request = new HttpRequest("GET /test?a=b&c=d HTTP/1.0", $this->loggerMock);
        $this->assertSame('a=b&c=d', $request->getQueryString());
    }

    public function testValidHttpVersionsAreRetrievedFromStatusLine()
    {
        $request = new HttpRequest("GET / HTTP/1.0", $this->loggerMock);
        $this->assertEquals('1.0', $request->getProtocolVersion());

        $request = new HttpRequest("GET / HTTP/1.1", $this->loggerMock);
        $this->assertEquals('1.1', $request->getProtocolVersion());
    }

    public function testInvalidHttpVersionIsRejected()
    {
        $this->setExpectedException('\noFlash\CherryHttp\HttpException', 'HTTP version', HttpCode::VERSION_NOT_SUPPORTED);
        new HttpRequest('GET / HTTP/69.0', $this->loggerMock);
    }

    public function testSimpleHeaderIsParsed()
    {
        $requestText = "GET / HTTP/1.1\r\n".
                       "X-Test: TeSt\r\n".
                       "X-Test2:tEsT\r\n\r\n";

        $request = new HttpRequest($requestText, $this->loggerMock);
        $this->assertSame('TeSt', $request->getHeader('X-Test'));
        $this->assertSame('tEsT', $request->getHeader('X-Test2'));
    }

    public function testHeaderWithoutValueIsAvailable()
    {
        $requestText = "GET / HTTP/1.1\r\n".
                       "X-Test:\r\n".
                       "X-Test2: test\r\n\r\n";

        $request = new HttpRequest($requestText, $this->loggerMock);
        $this->assertNotNull($request->getHeader('X-Test'), 'Header missing');
        $this->assertEmpty($request->getHeader('X-Test'), 'Header has invalid value');
        $this->assertNotEmpty($request->getHeader('X-Test2'), 'Header following empty header missing');
    }

    public function testMultipleHeadersWithTheSameNameAreParsed()
    {
        $requestText = "GET / HTTP/1.1\r\n".
                       "X-Test: TeSt\r\n".
                       "X-Test:TeSt2\r\n".
                       "X-Test: TeSt3\r\n\r\n";

        $request = new HttpRequest($requestText, $this->loggerMock);
        $this->assertSame('TeSt,TeSt2,TeSt3', $request->getHeader('X-Test'));
    }

    public function testMultipleHeadersWithTheSameNameWithDifferentCasesAreParsed()
    {
        $requestText = "GET / HTTP/1.1\r\n".
            "X-Test: TeSt\r\n".
            "x-Test:TeSt2\r\n".
            "X-TESt: TeSt3\r\n\r\n";

        $request = new HttpRequest($requestText, $this->loggerMock);
        $this->assertSame('TeSt,TeSt2,TeSt3', $request->getHeader('X-Test'));
    }

    public function testConvertingRequestToStringGivesValidHttpRequestRepresentation()
    {
        $requestText = "GET /test?a=b&c=d HTTP/1.1\r\n".
            "X-Test: TeSt\r\n".
            "X-Test: TeSt2\r\n".
            "X-Test: TeSt3\r\n\r\n";

        $request = new HttpRequest($requestText, $this->loggerMock);
        $this->assertSame($requestText, (string)$request);
    }
}
