<?php
namespace noFlash\CherryHttp;


class HttpClientTest extends \PHPUnit_Framework_TestCase {
    public function testImplementsStreamServerNodeInterface()
    {
        $streamServerNodeReflection = new \ReflectionClass('\noFlash\CherryHttp\HttpClient');
        $this->assertTrue($streamServerNodeReflection->isSubclassOf('\noFlash\CherryHttp\StreamServerNodeInterface'));
    }
}
