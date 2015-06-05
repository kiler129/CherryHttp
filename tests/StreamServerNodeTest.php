<?php
namespace noFlash\CherryHttp;

class StreamServerNodeTest extends \PHPUnit_Framework_TestCase {

    public function testImplementsStreamServerNodeInterface()
    {
        $streamServerNodeReflection = new \ReflectionClass('\noFlash\CherryHttp\StreamServerNode');
        $this->assertTrue($streamServerNodeReflection->isSubclassOf('\noFlash\CherryHttp\StreamServerNodeInterface'));
    }

}
