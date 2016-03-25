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

use noFlash\CherryHttp\IO\Network\NetworkNodeInterface;
use noFlash\CherryHttp\IO\Network\NetworkNodeTrait;
use noFlash\CherryHttp\IO\Network\TcpListenerNodeTrait;
use ReflectionClass;

class TcpListenerNodeTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TcpListenerNodeTrait
     */
    private $subjectUnderTest;

    /**
     * @var ReflectionClass
     */
    private $subjectUnderTestObjectReflection;

    public function setUp()
    {
        $this->subjectUnderTest = $this->getMockBuilder(TcpListenerNodeTrait::class)->getMockForTrait();
        $this->subjectUnderTestObjectReflection = new \ReflectionObject($this->subjectUnderTest);
    }

    public function testTestedSubjectIsATrait()
    {
        $traitReflection = new ReflectionClass(TcpListenerNodeTrait::class);

        $this->assertTrue($traitReflection->isTrait());
    }

    /**
     * @testdox Tested subject extends NetworkNodeTrait
     */
    public function testTestedSubjectExtendsNetworkNodeTrait()
    {
        $usedTraits = class_uses(TcpListenerNodeTrait::class);
        $this->assertContains(NetworkNodeTrait::class, $usedTraits);
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

        $this->assertTrue(true); //Dummy assertion - test will fail in set_error_handler() or due to exception
    }

    public function testStreamIsAlwaysConsideredNotReadyToWrite()
    {
        $this->assertFalse($this->subjectUnderTest->isWriteReady());

        $this->subjectUnderTest->stream = fopen('php://memory', 'w');
        $this->assertFalse($this->subjectUnderTest->isWriteReady());
    }

    public function testAttemptingToWriteOnListenerStreamResultsInLogicException()
    {
        $this->expectException(\LogicException::class);
        $this->subjectUnderTest->doWrite();
    }

    public function testAttemptingToAddDataToListenerWriteBufferResultsInLogicException()
    {
        $this->expectException(\LogicException::class);
        $this->subjectUnderTest->writeBufferAppend('grumpgrump');
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
     * @dataProvider ipsDataProvider
     */
    public function testLocalIpCanBeSet($ip)
    {
        $this->subjectUnderTest->setLocalIpAddress($ip);
        $this->assertSame($ip, $this->getRestrictedPropertyValue('networkLocalIp'));
    }

    private function getRestrictedPropertyValue($name)
    {
        if (!$this->subjectUnderTestObjectReflection->hasProperty($name)) {
            throw new \RuntimeException('There is no property named ' . $name);
        }

        $property = $this->subjectUnderTestObjectReflection->getProperty($name);
        $property->setAccessible(true);

        return $property->getValue($this->subjectUnderTest);
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
}
