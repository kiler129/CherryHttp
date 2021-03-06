<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Tests\IO\Network;

use noFlash\CherryHttp\IO\Network\AbstractNetworkListenerNode;
use noFlash\CherryHttp\IO\Network\AbstractNetworkStreamNode;
use noFlash\CherryHttp\IO\Network\NetworkListenerNodeInterface;
use noFlash\CherryHttp\IO\Network\NetworkNodeInterface;
use noFlash\CherryHttp\Tests\TestHelpers\TestCase;
use ReflectionClass;

/**
 * @property AbstractNetworkListenerNode|\PHPUnit_Framework_MockObject_MockObject  subjectUnderTest
 */
class AbstractNetworkListenerNodeTest extends TestCase
{
    public function setUp()
    {
        $this->subjectUnderTest = $this->getMockForAbstractClass(AbstractNetworkListenerNode::class);

        parent::setUp();
    }

    public function tearDown()
    {
        //Prevent dangling open sockets
        if (!empty($this->subjectUnderTest->stream) && is_resource($this->subjectUnderTest->stream)) {
            @fclose($this->subjectUnderTest->stream);
        }
    }

    public function testClassExtendsAbstractNetworkStreamNode()
    {
        $this->assertInstanceOf(AbstractNetworkStreamNode::class, $this->subjectUnderTest);
    }

    public function testClassImplementsNetworkListenerNodeInterface()
    {
        $this->assertInstanceOf(NetworkListenerNodeInterface::class, $this->subjectUnderTest);
    }

    /**
     * @testdox Class contains disconnect() method
     */
    public function testClassContainsDisconnectMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(AbstractNetworkListenerNode::class, 'disconnect'));
    }

    public function testDisconnectClosesStream()
    {
        $this->subjectUnderTest->stream = fopen('php://memory', 'r');
        $this->assertNotFalse($this->subjectUnderTest->stream, 'Creating test stream failed');
        $this->assertFalse(
            stream_get_meta_data($this->subjectUnderTest->stream)['eof'],
            'Test stream is closed after opening (?!)'
        );

        $this->subjectUnderTest->disconnect();

    }

    public function testDisconnectWillRaiseNoErrorsAndExceptionsIfStreamWasNotSetOrSetToInvalidValue()
    {
        set_error_handler(
            function () {
                var_dump(
                    func_get_args()
                ); //int $errno, string $errstr, string $errfile, int $errline, array $errcontext
                $this->fail('PHP error occurred!');
            }
        );

        $this->subjectUnderTest->disconnect();

        $this->subjectUnderTest->stream = null;
        $this->subjectUnderTest->disconnect();

        $this->subjectUnderTest->stream = false;
        $this->subjectUnderTest->disconnect();

        $this->subjectUnderTest->stream = true;
        $this->subjectUnderTest->disconnect();

        $this->subjectUnderTest->stream = 'derp';
        $this->subjectUnderTest->disconnect();

        $this->subjectUnderTest->stream = new \stdClass();
        $this->subjectUnderTest->disconnect();
        restore_error_handler();

        $this->assertTrue(true); //Dummy assertion - test will fail in set_error_handler() or due to exception
    }

    /**
     * @testdox Class contains isWriteReady() method
     */
    public function testClassContainsIsWriteReadyMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(AbstractNetworkListenerNode::class, 'isWriteReady'));
    }

    public function testStreamIsAlwaysConsideredNotReadyToWrite()
    {
        $this->assertFalse($this->subjectUnderTest->isWriteReady());

        $this->subjectUnderTest->stream = fopen('php://memory', 'w');
        $this->assertFalse($this->subjectUnderTest->isWriteReady());
    }

    /**
     * @testdox Class contains doWrite() method
     */
    public function testClassContainsDoWriteMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(AbstractNetworkListenerNode::class, 'doWrite'));
    }

    public function testAttemptingToWriteOnListenerStreamResultsInLogicException()
    {
        $this->expectException(\LogicException::class);
        $this->subjectUnderTest->doWrite();
    }

    /**
     * @testdox Class contains writeBufferAppend() method
     */
    public function testClassContainsWriteBufferAppendMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(AbstractNetworkListenerNode::class, 'writeBufferAppend'));
    }

    public function testCallingWriteBufferAppendMethodThrowsLogicException()
    {
        $this->expectException(\LogicException::class);
        $this->subjectUnderTest->writeBufferAppend('');
    }

    public function ipsDataProvider()
    {
        foreach ($this->ipv4DataProvider() as $ipArg) {
            yield $ipArg;
        }

        foreach ($this->ipv6DataProvider() as $ipArg) {
            yield $ipArg;
        }
    }

    public function ipv4DataProvider()
    {
        return [
            [NetworkNodeInterface::UNDETERMINED_IPV4],
            ['1.2.3.4'], //Internet
            ['10.255.225.42'], //Local
            ['85.85.85.85'], //Internet
            ['100.127.255.254'], //Local
            ['127.0.0.1'], //Loopback
            ['127.0.0.42'], //Loopback
            ['172.16.0.1'], //Local
            ['192.88.99.200'], //6to4 anycast
            ['192.168.42.24'], //Local
            ['198.19.200.13'], //Testing
            ['239.255.255.255'], //Internet
            ['240.0.0.1'], //Internet
            ['255.255.255.253'] //Internet
        ];
    }

    public function ipv6DataProvider()
    {
        return [
            [NetworkNodeInterface::UNDETERMINED_IPV6],
            ['::1'], //Loopback
            ['::ffff:192.0.2.47'], //IPv4 in IPv6
            ['fdf8:f53b:82e4::53'], //ULA
            ['fe80::200:5aee:feaa:20a2'], //Link local
            ['2001:0000:4136:e378:8000:63bf:3fff:fdd2'], //Teredo
            ['2001:10:240:ab::a'], //Fixed tests
            ['2002:cb0a:3cdd:1::1'], //6to4
            ['fe80:0000:0000:0000:0202:b3ff:fe1e:8329']
        ];
    }

    public function invalidIpsProvider()
    {
        return [
            ['1200::ab00:1234::2222:3333:2222'], //Used :: two times
            ['1200:0000:AB00:1111:O000:1111:2222:1111'], //O letter instead of zero
            ['1.2.4.256']
            //tbc...
        ];
    }

    /**
     * @testdox Class contains setLocalIpAddress() method
     */
    public function testClassContainsSetLocalIpAddressMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(AbstractNetworkListenerNode::class, 'setLocalIpAddress'));
    }

    /**
     * Declaration of the method need to be complaint with the one in interface.
     *
     * @testdox Method setLocalIpAddress() declares one argument without type-hint
     */
    public function testMethodSetLocalIpAddressDeclaresOneArgumentWithoutTypehint()
    {
        $classReflection = new ReflectionClass(AbstractNetworkListenerNode::class);
        $wbaMethodReflection = $classReflection->getMethod('setLocalIpAddress');

        $this->assertSame(1, $wbaMethodReflection->getNumberOfParameters(), 'Method defines more than one parameter');

        $wbaMethodParameters = $wbaMethodReflection->getParameters();

        /** @var \ReflectionParameter $wbaMethodParameter */
        $wbaMethodParameter = reset($wbaMethodParameters); //Gets first parameter
        $this->assertNull($wbaMethodParameter->getClass()); //Unfortunately hasType() is available since 7.0
        $this->assertFalse($wbaMethodParameter->isDefaultValueAvailable());
        $this->assertFalse($wbaMethodParameter->isPassedByReference());
        $this->assertSame('address', $wbaMethodParameter->getName());
    }

    /**
     * @dataProvider ipsDataProvider
     */
    public function testLocalIpCanBeSet($ip)
    {
        $this->subjectUnderTest->setLocalIpAddress($ip);
        $this->assertSame($ip, $this->getRestrictedPropertyValue('networkLocalIp'));
    }

    /**
     * @dataProvider invalidIpsProvider
     */
    public function testLocalIpIsVerified($ip)
    {
        $this->expectException(\InvalidArgumentException::class);        
        $this->subjectUnderTest->setLocalIpAddress($ip);
    }

    public function testIpVersionIsSetToFourByDefault()
    {
        $this->assertSame(NetworkNodeInterface::IP_V4, $this->getRestrictedPropertyValue('networkIpVersion'));
    }

    /**
     * @dataProvider ipv4DataProvider
     */
    public function testIpVersionIsChangedToV4AfterSettingV4Address($ip)
    {
        $this->subjectUnderTest->setLocalIpAddress('2001:10:240:ab::a');
        $this->subjectUnderTest->setLocalIpAddress($ip);

        $this->assertSame(NetworkNodeInterface::IP_V4, $this->getRestrictedPropertyValue('networkIpVersion'));
    }

    /**
     * @dataProvider ipv6DataProvider
     */
    public function testIpVersionIsChangedToV6AfterSettingV6Address($ip)
    {
        $this->subjectUnderTest->setLocalIpAddress(
            rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255)
        );
        $this->subjectUnderTest->setLocalIpAddress($ip);

        $this->assertSame(NetworkNodeInterface::IP_V6, $this->getRestrictedPropertyValue('networkIpVersion'));
    }

    public function validPortsProvider()
    {
        for ($i = 0; $i <= 65535; $i += rand(30, 300)) {
            yield [$i];
        }
    }

    /**
     * @testdox Class contains setLocalPort() method
     */
    public function testClassContainsSetLocalPortMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(AbstractNetworkListenerNode::class, 'setLocalPort'));
    }

    /**
     * Declaration of the method need to be complaint with the one in interface.
     *
     * @testdox Method setLocalPort() declares one argument without type-hint
     */
    public function testMethodSetLocalPortDeclaresOneArgumentWithoutTypehint()
    {
        $classReflection = new ReflectionClass(AbstractNetworkListenerNode::class);
        $wbaMethodReflection = $classReflection->getMethod('setLocalPort');

        $this->assertSame(1, $wbaMethodReflection->getNumberOfParameters(), 'Method defines more than one parameter');

        $wbaMethodParameters = $wbaMethodReflection->getParameters();

        /** @var \ReflectionParameter $wbaMethodParameter */
        $wbaMethodParameter = reset($wbaMethodParameters); //Gets first parameter
        $this->assertNull($wbaMethodParameter->getClass()); //Unfortunately hasType() is available since 7.0
        $this->assertFalse($wbaMethodParameter->isDefaultValueAvailable());
        $this->assertFalse($wbaMethodParameter->isPassedByReference());
        $this->assertSame('port', $wbaMethodParameter->getName());
    }
    
    /**
     * @dataProvider validPortsProvider
     * @long
     */
    public function testLocalPortCanBeSetToValidValues($port)
    {
        $this->subjectUnderTest->setLocalPort($port);
        $this->assertSame($port, $this->getRestrictedPropertyValue('networkLocalPort'));
    }

    public function invalidPortsProvider()
    {
        return [
            [-1],
            [false],
            [null],
            [true],
            ['1'],
            [65536]
        ];
    }

    /**
     * @dataProvider invalidPortsProvider
     */
    public function testLocalPortRejectsInvalidPorts($ip)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->subjectUnderTest->setLocalPort($ip);
    }

    /**
     * @testdox Class contains startListening() method
     */
    public function testClassContainsStartListeningMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(AbstractNetworkListenerNode::class, 'startListening'));
    }

    public function testListeningCanBeStartedWithDefaultSettings()
    {
        $this->assertNull($this->subjectUnderTest->stream, 'Stream field is already populated on fresh object');

        $this->subjectUnderTest->startListening();
        $this->assertInternalType('resource', $this->subjectUnderTest->stream);
    }


    public function testListeningCreatesListeningSocket()
    {
        $this->subjectUnderTest->startListening();

        //@ is needed for HHVM - https://github.com/facebook/hhvm/issues/6937
        $remoteName = @stream_socket_get_name($this->subjectUnderTest->stream, true);

        //See PHP test source file ext/standard/tests/streams/stream_socket_get_name.phpt
        $this->assertFalse($remoteName);
    }

    /**
     * @testdox Listening creates TCP/IP socket
     */
    public function testListeningCreatesTcpIpSocket()
    {
        $this->subjectUnderTest->startListening();

        //See php_stream_generic_socket_ops in PHP source file main/streams/xp_socket.c (look for tcp_socket)
        //However while SSL extension situation changes (see /ext/openssl/xp_ssl.c) -> tcp_socket/ssl
        $streamMetadata = stream_get_meta_data($this->subjectUnderTest->stream);
        $this->assertStringStartsWith('tcp_socket', $streamMetadata['stream_type']);
    }

    /**
     * This test may fail on older HHVMs - see https://github.com/facebook/hhvm/issues/6938
     */
    public function testListeningCreatesNonBlockingSocket()
    {
        $this->verifyStreamBlockingBug();

        $this->subjectUnderTest->startListening();

        $streamMetadata = stream_get_meta_data($this->subjectUnderTest->stream);
        $this->assertFalse($streamMetadata['blocked']);
    }

    /**
     * @testdox Listening is started on proper IPv4 address and correct port
     */
    public function testListeningIsStartedOnProperIpV4AddressAndCorrectPort()
    {
        $randomPort = rand(1024, 65535);
        $this->subjectUnderTest->setLocalIpAddress('127.0.0.1');
        $this->subjectUnderTest->setLocalPort($randomPort);

        $this->subjectUnderTest->startListening();
        $this->assertSame("127.0.0.1:$randomPort", stream_socket_get_name($this->subjectUnderTest->stream, false));
    }

    /**
     * @testdox Listening is started on proper IPv6 address and correct port
     */
    public function testListeningIsStartedOnProperIpV6AddressAndCorrectPort()
    {
        $this->markTestSkippedIfNoIpV6TestEnvironment();

        $randomPort = rand(1024, 65535);
        $this->subjectUnderTest->setLocalIpAddress('::1');
        $this->subjectUnderTest->setLocalPort($randomPort);

        $this->subjectUnderTest->startListening();
        $this->assertSame("::1:$randomPort", stream_socket_get_name($this->subjectUnderTest->stream, false));
    }

    /**
     * @testdox Port is populated after listening starts on IPv4 address
     */
    public function testPortIsPopulatedAfterListeningStartsOnIpV4Address()
    {
        $this->subjectUnderTest->setLocalIpAddress('127.0.0.1');
        $this->subjectUnderTest->setLocalPort(0);
        $this->subjectUnderTest->startListening();

        $address = stream_socket_get_name($this->subjectUnderTest->stream, false);
        $realPort = (int)substr($address, strrpos($address, ':') + 1);

        $this->assertSame($realPort, $this->getRestrictedPropertyValue('networkLocalPort'));
    }

    /**
     * @testdox Port is populated after listening starts on IPv6 address
     */
    public function testPortIsPopulatedAfterListeningStartsOnIpV6Address()
    {
        $this->markTestSkippedIfNoIpV6TestEnvironment();

        $this->subjectUnderTest->setLocalIpAddress('::1');
        $this->subjectUnderTest->setLocalPort(0);
        $this->subjectUnderTest->startListening();

        $address = stream_socket_get_name($this->subjectUnderTest->stream, false);
        $realPort = (int)substr($address, strrpos($address, ':') + 1);

        $this->assertSame($realPort, $this->getRestrictedPropertyValue('networkLocalPort'));
    }

    /**
     * @testdox Address is populated after listening starts on IPv6 address
     */
    public function testAddressIsPopulatedAfterListeningStartsOnIpV6Address()
    {
        $this->markTestSkippedIfNoIpV6TestEnvironment();

        $this->subjectUnderTest->setLocalIpAddress('0:0:0:0:0:0:0:1');
        $this->subjectUnderTest->setLocalPort(0);
        $this->subjectUnderTest->startListening();

        $address = stream_socket_get_name($this->subjectUnderTest->stream, false);
        $realIp = substr($address, 0, strrpos($address, ':'));

        $this->assertSame($realIp, $this->getRestrictedPropertyValue('networkLocalIp'));
    }

    public function testRuntimeExceptionIsThrownIfListeningStartFailed()
    {
        $randomPort = rand(1024, 65535);
        //GC / OPC trick: https://3v4l.org/YCV5E
        $dummyVariable = stream_socket_server("127.0.0.1:$randomPort"); //Let's occupy a port

        $this->subjectUnderTest->setLocalIpAddress('127.0.0.1');
        $this->subjectUnderTest->setLocalPort($randomPort);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageRegExp('/Address already in use/');
        $this->subjectUnderTest->startListening();
    }

    public function testLogicExceptionIsThrownIfStartListeningWasCalledOnAlreadyListeningObject()
    {
        $this->subjectUnderTest->startListening();

        $this->expectException(\LogicException::class);
        $this->subjectUnderTest->startListening();
    }
}
