<?php
namespace noFlash\CherryHttp;


use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class HttpClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $loggerMock;

    /**
     * @var vfsStreamDirectory
     */
    private $vfsRoot;

    public function setUp()
    {
        $this->loggerMock = $this->getMockForAbstractClass('\Psr\Log\LoggerInterface');
        $this->vfsRoot = vfsStream::setup();
    }

    public function testImplementsStreamServerNodeInterface()
    {
        $streamServerNodeReflection = new \ReflectionClass('\noFlash\CherryHttp\HttpClient');
        $this->assertTrue($streamServerNodeReflection->isSubclassOf('\noFlash\CherryHttp\StreamServerNodeInterface'));
    }

    public function getStreamMockWithContent($content, $name = 'test')
    {
        $streamMock = vfsStream::newFile($name)->withContent($content)->at($this->vfsRoot);
        $stream = fopen($streamMock->url(), 'r+');

        $this->assertNotFalse($stream, 'Failed to open VFS stream (vfs bug?)');
        $this->assertTrue(rewind($stream), 'Failed to rewind VFS stream (vfs bug?)');

        return $stream;
    }

    public function testProperHttpExceptionIsThrownWhenHeadersAreLargerThanDefined()
    {
        //@formatter:off PHPStorm formatter acts weird on such constructions and reformat it to single looong line
        $request = "GET / HTTP/1.1\r\n".
                   "Connection: close\r\n".
                   "X-Test: " . str_repeat('x', HttpRequest::MAX_HEADER_LENGTH)."\r\n".
                   "\r\n";
        //@formatter:on

        $stream = $this->getStreamMockWithContent($request);

        $httpClient = new HttpClient($stream, null, $this->loggerMock);

        $this->setExpectedException('\noFlash\CherryHttp\HttpException');
        try {
            while (!feof($stream)) {
                $httpClient->onReadReady();
            }

        } catch(HttpException $e) {
            $this->assertSame(HttpCode::REQUEST_HEADER_FIELDS_TOO_LARGE, $e->getCode(), 'Invalid HTTP code');
            throw $e;
        }
    }

    public function testAfterReadingWholeBufferRequestIsPresent()
    {
        $request = "GET / HTTP/1.1\r\nConnection: close\r\n\r\n";
        $stream = $this->getStreamMockWithContent($request);

        $httpClient = new HttpClient($stream, null, $this->loggerMock);

        while (!feof($stream)) {
            $httpClient->onReadReady();
        }

        $this->assertNotEmpty($httpClient->request, 'No request found');
        $this->assertInstanceOf('\noFlash\CherryHttp\HttpRequest', $httpClient->request);
    }

    public function testAfterReadingWholeBufferCompositedFromTwoChunksRequestIsPresent()
    {
        $request = "GET / HTTP/1.1\r\n";

        $stream = $this->getStreamMockWithContent($request);

        $httpClient = new HttpClient($stream, null, $this->loggerMock);

        while (!feof($stream)) {
            $httpClient->onReadReady();
        }

        $this->assertNull($httpClient->request, 'Request created after 1st chunk');

        $restOfRequest = "Connection: close\r\n\r\n";
        fwrite($stream, $restOfRequest);
        fseek($stream, strlen($request), SEEK_SET);

        while (!feof($stream)) {
            $httpClient->onReadReady();
        }

        $this->assertNotEmpty($httpClient->request, 'No request found after 2nd chunk');
        $this->assertInstanceOf('\noFlash\CherryHttp\HttpRequest', $httpClient->request);
    }

    public function testRequestWithoutProperLineEndingsAreIgnored()
    {
        $request = "GET / HTTP/1.1\nConnection: close\n\n";

        $stream = $this->getStreamMockWithContent($request);

        $httpClient = new HttpClient($stream, null, $this->loggerMock);

        while (!feof($stream)) {
            $httpClient->onReadReady();
        }

        $this->assertEmpty($httpClient->request, 'Request was created - it shouldn\'t!');
    }
}
