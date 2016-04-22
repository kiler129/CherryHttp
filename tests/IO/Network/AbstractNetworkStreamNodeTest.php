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

use noFlash\CherryHttp\IO\Network\AbstractNetworkStreamNode;
use noFlash\CherryHttp\IO\Network\NetworkNodeInterface;
use noFlash\CherryHttp\IO\Stream\AbstractStreamNode;
use noFlash\CherryHttp\IO\StreamNodeInterface;
use noFlash\CherryHttp\Tests\TestHelpers\TestCase;

/**
 * @property AbstractNetworkStreamNode subjectUnderTest
 */
class AbstractNetworkStreamNodeTest extends TestCase
{
    public function setUp()
    {
        $this->subjectUnderTest = $this->getMockForAbstractClass(AbstractNetworkStreamNode::class);

        parent::setUp();
    }

    public function testClassIsAbstract()
    {
        $this->assertIsAbstractClass(AbstractNetworkStreamNode::class);
    }

    /**
     * @testdox Class extends AbstractStreamNode
     */
    public function testClassExtendsAbstractStreamNode()
    {
        $this->assertInstanceOf(AbstractStreamNode::class, $this->subjectUnderTest);
    }

    /**
     * @testdox Class implements StreamNodeInterface
     */
    public function testClassImplementsStreamNodeInterface()
    {
        $this->assertInstanceOf(StreamNodeInterface::class, $this->subjectUnderTest);
    }

    public function testClassDefinesProtectedPropertyForIpVersion()
    {
        $this->assertTrue($this->subjectUnderTestObjectReflection->hasProperty('networkIpVersion'));
        $this->assertTrue($this->subjectUnderTestObjectReflection->getProperty('networkIpVersion')->isProtected());
    }

    /**
     * @testdox IPv4 is used by default
     */
    public function testIpV4IsUsedByDefault()
    {
        $this->assertSame(NetworkNodeInterface::IP_V4, $this->subjectUnderTest->getIpVersion());

        $versionField = $this->subjectUnderTestObjectReflection->getProperty('networkIpVersion');
        $versionField->setAccessible(true);

        $this->assertSame(NetworkNodeInterface::IP_V4, $versionField->getValue($this->subjectUnderTest));
    }

    /**
     * @testdox IP version can be changed to v6 using protected field
     */
    public function testIpVersionCanBeChangedToV6UsingProtectedField()
    {
        $versionField = $this->subjectUnderTestObjectReflection->getProperty('networkIpVersion');
        $versionField->setAccessible(true);
        $versionField->setValue($this->subjectUnderTest, NetworkNodeInterface::IP_V6);

        $this->assertSame(
            NetworkNodeInterface::IP_V6,
            $versionField->getValue($this->subjectUnderTest)
        ); //It's god to test that in case of magic variables
        $this->assertSame(NetworkNodeInterface::IP_V6, $this->subjectUnderTest->getIpVersion());
    }

    public function testClassDefinesProtectedPropertyForLocalAddress()
    {
        $this->assertTrue($this->subjectUnderTestObjectReflection->hasProperty('networkLocalIp'));
        $this->assertTrue($this->subjectUnderTestObjectReflection->getProperty('networkLocalIp')->isProtected());
    }

    /**
     * @testdox Undetermined IPv4 local address is used by default
     */
    public function testUndeterminedIpV4LocalAddressIsUsedByDefault()
    {
        $this->assertSame(NetworkNodeInterface::UNDETERMINED_IPV4, $this->subjectUnderTest->getLocalIpAddress());

        $localAddrField = $this->subjectUnderTestObjectReflection->getProperty('networkLocalIp');
        $localAddrField->setAccessible(true);

        $this->assertSame(NetworkNodeInterface::UNDETERMINED_IPV4, $localAddrField->getValue($this->subjectUnderTest));
    }

    public function testLocalIpAddressCanBeChangedUsingProtectedField()
    {
        $localAddrField = $this->subjectUnderTestObjectReflection->getProperty('networkLocalIp');
        $localAddrField->setAccessible(true);
        $localAddrField->setValue($this->subjectUnderTest, '127.0.0.1');

        $this->assertSame(
            '127.0.0.1',
            $localAddrField->getValue($this->subjectUnderTest)
        ); //It's god to test that in case of magic variables
        $this->assertSame('127.0.0.1', $this->subjectUnderTest->getLocalIpAddress());
    }

    public function testClassDefinesProtectedPropertyForLocalPort()
    {
        $this->assertTrue($this->subjectUnderTestObjectReflection->hasProperty('networkLocalPort'));
        $this->assertTrue($this->subjectUnderTestObjectReflection->getProperty('networkLocalPort')->isProtected());
    }

    public function testZeroIsUsedAsLocalPortByDefault()
    {
        $this->assertSame(0, $this->subjectUnderTest->getLocalPort());

        $localPortField = $this->subjectUnderTestObjectReflection->getProperty('networkLocalPort');
        $localPortField->setAccessible(true);

        $this->assertSame(0, $localPortField->getValue($this->subjectUnderTest));
    }

    public function testLocalPortCanBeChangedUsingProtectedField()
    {
        $localPortField = $this->subjectUnderTestObjectReflection->getProperty('networkLocalPort');
        $localPortField->setAccessible(true);
        $localPortField->setValue($this->subjectUnderTest, 80);

        $this->assertSame(
            80,
            $localPortField->getValue($this->subjectUnderTest)
        ); //It's god to test that in case of magic variables
        $this->assertSame(80, $this->subjectUnderTest->getLocalPort());
    }

    public function testClassDefinesProtectedPropertyForRemoteAddress()
    {
        $this->assertTrue($this->subjectUnderTestObjectReflection->hasProperty('networkRemoteIp'));
        $this->assertTrue($this->subjectUnderTestObjectReflection->getProperty('networkRemoteIp')->isProtected());
    }

    public function testNullIsUsedAsRemoteAddressByDefault()
    {
        $this->assertNull($this->subjectUnderTest->getRemoteIpAddress());

        $remoteAddrField = $this->subjectUnderTestObjectReflection->getProperty('networkRemoteIp');
        $remoteAddrField->setAccessible(true);

        $this->assertNull($remoteAddrField->getValue($this->subjectUnderTest));
    }

    public function testRemoteIpAddressCanBeChangedUsingProtectedField()
    {
        $remoteAddrField = $this->subjectUnderTestObjectReflection->getProperty('networkRemoteIp');
        $remoteAddrField->setAccessible(true);
        $remoteAddrField->setValue($this->subjectUnderTest, '10.0.0.1');

        $this->assertSame(
            '10.0.0.1',
            $remoteAddrField->getValue($this->subjectUnderTest)
        ); //It's god to test that in case of magic variables
        $this->assertSame('10.0.0.1', $this->subjectUnderTest->getRemoteIpAddress());
    }

    public function testClassDefinesProtectedPropertyForRemotePort()
    {
        $this->assertTrue($this->subjectUnderTestObjectReflection->hasProperty('networkRemotePort'));
        $this->assertTrue($this->subjectUnderTestObjectReflection->getProperty('networkRemotePort')->isProtected());
    }

    public function testNullIsUsedAsRemotePortByDefault()
    {
        $this->assertNull($this->subjectUnderTest->getRemotePort());

        $remotePortField = $this->subjectUnderTestObjectReflection->getProperty('networkRemotePort');
        $remotePortField->setAccessible(true);

        $this->assertNull($remotePortField->getValue($this->subjectUnderTest));
    }

    public function testRemotePortCanBeChangedUsingProtectedField()
    {
        $remotePortField = $this->subjectUnderTestObjectReflection->getProperty('networkRemotePort');
        $remotePortField->setAccessible(true);
        $remotePortField->setValue($this->subjectUnderTest, 13981);

        $this->assertSame(
            13981,
            $remotePortField->getValue($this->subjectUnderTest)
        ); //It's god to test that in case of magic variables
        $this->assertSame(13981, $this->subjectUnderTest->getRemotePort());
    }

    public function testClassDefinesProtectedPropertyConnectionStatus()
    {
        $this->assertTrue($this->subjectUnderTestObjectReflection->hasProperty('networkIsConnected'));
        $this->assertTrue($this->subjectUnderTestObjectReflection->getProperty('networkIsConnected')->isProtected());
    }

    public function testFalseIsUsedByDefaultAsNetworkConnectionStatus()
    {
        $this->assertFalse($this->subjectUnderTest->isConnected());

        $isConnectedField = $this->subjectUnderTestObjectReflection->getProperty('networkIsConnected');
        $isConnectedField->setAccessible(true);

        $this->assertFalse($isConnectedField->getValue($this->subjectUnderTest));
    }

    public function testConnectionStatusCanBeChangedUsingProtectedField()
    {
        $isConnectedField = $this->subjectUnderTestObjectReflection->getProperty('networkIsConnected');
        $isConnectedField->setAccessible(true);
        $isConnectedField->setValue($this->subjectUnderTest, true);

        $this->assertTrue(
            $isConnectedField->getValue($this->subjectUnderTest)
        ); //It's god to test that in case of magic variables
        $this->assertTrue($this->subjectUnderTest->isConnected());
    }
}
