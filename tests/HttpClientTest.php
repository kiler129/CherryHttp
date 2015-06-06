<?php
namespace noFlash\CherryHttp;


use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class HttpClientTest extends \PHPUnit_Framework_TestCase {
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
        $streamMock = vfsStream::newFile($name)
            ->withContent($content)
            ->at($this->vfsRoot);
        $stream = fopen($streamMock->url(), 'r+');

        $this->assertNotFalse($stream, 'Failed to open VFS stream (vfs bug?)');
        $this->assertTrue(rewind($stream), 'Failed to rewind VFS stream (vfs bug?)');

        return $stream;
    }

    public function testProperHttpExceptionIsThrownWhenHeadersAreLargerThanDefined()
    {
        $request = "GET / HTTP/1.1\r\n".
                   "Connection: close\r\n".
                   "X-Test: ".str_repeat('x', HttpRequest::MAX_HEADER_LENGTH)."\r\n".
                   "\r\n";

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
}
