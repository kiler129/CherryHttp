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

    public function validPortsProvider()
    {
        return array(
            array(8080),
            array(1024),
            array(9999),
            array(65535),
        );
    }

    /**
     * @dataProvider validPortsProvider
     */
    public function testConstructorAcceptsValidPorts($port)
    {
        $serverMock = $this->getMockBuilder('\noFlash\CherryHttp\Server')->getMock();

        $listener = new HttpListenerNode($serverMock, '127.0.0.1', $port);
        $peername = $listener->getPeerName();
        $actualPort = substr($peername, strrpos($peername, ':')+1);

        $this->assertEquals($port, $actualPort);
    }

    public function invalidPortsProvider()
    {
        return array(
            array(0),
            array(-10),
            array(65536),
        );
    }

    /**
     * @dataProvider invalidPortsProvider
     */
    public function testConstructorRejectsInvalidPorts($port)
    {
        $serverMock = $this->getMockBuilder('\noFlash\CherryHttp\Server')->getMock();

        $this->setExpectedException('\Exception'); //Hmm, maybe it should be InvalidArgumentException?
        new HttpListenerNode($serverMock, $port);
    }

    public function testConstructorAllowDisabledSsl()
    {
        $serverMock = $this->getMockBuilder('\noFlash\CherryHttp\Server')->getMock();

        new HttpListenerNode($serverMock, '127.0.0.1', 8080, false);
        //No assertion - if connection is created no exception will be thrown, simple ;)
    }

    /**
     * Since SSL is not implemented this test is required - it will be removed after issue #2 is fixed
     */
    public function testConstructorRejectsNotImplementedSsl()
    {
        $serverMock = $this->getMockBuilder('\noFlash\CherryHttp\Server')->getMock();

        $this->setExpectedException('\noFlash\CherryHttp\ServerException', 'not implemented');
        new HttpListenerNode($serverMock, '127.0.0.1', 8080, true);
    }

    public function testConstructorThrowsExceptionIfSocketCreationFailed()
    {
        $serverMock = $this->getMockBuilder('\noFlash\CherryHttp\Server')->getMock();

        $dummyServer = stream_socket_server('tcp://[127.0.0.1]:8080', $errNo, $errStr);
        $this->assertNotFalse($dummyServer, "Unable to create dummy listener - $errStr (e# $errNo)");

        $this->setExpectedException('\noFlash\CherryHttp\ServerException', 'already in use');
        new HttpListenerNode($serverMock, '127.0.0.1', 8080);
    }
}
