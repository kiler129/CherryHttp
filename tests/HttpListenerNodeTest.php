<?php
namespace noFlash\CherryHttp;


class HttpListenerNodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @testdox Class extends StreamServerNode
     */
    public function testClassExtendsStreamServerNode()
    {
        $clientUpgradeExceptionReflection = new \ReflectionClass('\noFlash\CherryHttp\HttpListenerNode');
        $this->assertTrue($clientUpgradeExceptionReflection->isSubclassOf('\noFlash\CherryHttp\StreamServerNode'));
    }

    public function validIpsProvider()
    {
        return array(
            array('127.0.0.1'),
            array('::1'),
            array('::ffff:127.0.0.1'),
            array('0.0.0.0')
        );
    }

    /**
     * @dataProvider validIpsProvider
     */
    public function testConstructorAcceptsValidIps($ip)
    {
        $serverMock = $this->getMockBuilder('\noFlash\CherryHttp\Server')->getMock();

        $listener = new HttpListenerNode($serverMock, $ip);
        $this->assertSame($ip, $listener->getIp());
    }

    public function invalidIpsProvider()
    {
        return array(
            array('localhost'),
            array('127.0.0.1:8080'),
            array('127,0,0,1'),
            array('999.999.999.999')
        );
    }

    /**
     * @dataProvider invalidIpsProvider
     */
    public function testConstructorRejectsInvalidIps($ip)
    {
        $serverMock = $this->getMockBuilder('\noFlash\CherryHttp\Server')->getMock();

        $this->setExpectedException('\InvalidArgumentException');
        new HttpListenerNode($serverMock, $ip);
    }
}
