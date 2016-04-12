<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Tests\IO\Stream;

use noFlash\CherryHttp\Application\Lifecycle\LoopNodeInterface;
use noFlash\CherryHttp\Application\Lifecycle\LoopNodeTrait;
use noFlash\CherryHttp\IO\Stream\AbstractStreamNode;
use noFlash\CherryHttp\IO\Stream\StreamNodeTrait;
use noFlash\CherryHttp\Tests\TestHelpers\TestCase;

class AbstractStreamNodeTest extends TestCase
{
    public function setUp()
    {
        /** @var AbstractStreamNode|\PHPUnit_Framework_MockObject_MockObject subjectUnderTest */
        $this->subjectUnderTest = $this->getMockForAbstractClass(AbstractStreamNode::class);

        parent::setUp();
    }

    public function testClassIsDefinedAsAbstract()
    {
        $sutClassReflection = new \ReflectionClass(AbstractStreamNode::class);
        $this->assertTrue($sutClassReflection->isAbstract());
    }

    /**
     * @testdox Tested subject uses StreamNodeTrait
     */
    public function testTestedSubjectUsesStreamNodeTrait()
    {
        $this->assertContains(StreamNodeTrait::class, class_uses(AbstractStreamNode::class));
    }

    /**
     * @testdox Tested subject uses LoopNodeTrait
     */
    public function testTestedSubjectUsesLoopNodeTrait()
    {
        $this->assertContains(LoopNodeTrait::class, class_uses(AbstractStreamNode::class));
    }

    /**
     * @testdox Class implements getPingInterval() method
     */
    public function testClassImplementsGetPingIntervalMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(AbstractStreamNode::class, 'getPingInterval'));
    }

    /**
     * @testdox Ping interval returns LoopNodeInterface::PING_INTERVAL_ANY
     */
    public function testPingIntervalReturnsNodeInterfacePingIntervalAny()
    {
        $this->assertSame(LoopNodeInterface::PING_INTERVAL_ANY, $this->subjectUnderTest->getPingInterval());
    }

    /**
     * @testdox Class implements ping() method
     */
    public function testClassImplementsPingMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(AbstractStreamNode::class, 'ping'));
    }

    /**
     * @testdox ping() returns null
     */
    public function testPingReturnsNull()
    {
        $this->assertNull($this->subjectUnderTest->ping());
    }

    /**
     * @testdox Class implements onStreamError() method
     */
    public function testClassImplementsOnStreamErrorMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(AbstractStreamNode::class, 'onStreamError'));
    }

    /**
     * @testdox Sockets is destroyed after calling onStreamError() method
     */
    public function testSocketIsDestroyedAfterCallingOnStreamErrorMethod()
    {
        $socket = fopen('php://memory', 'r');

        $this->setRestrictedPropertyValue('stream', $socket);
        $this->subjectUnderTest->onStreamError();
        $this->assertNull($this->subjectUnderTest->getStreamResource());
        $this->assertNull($this->getRestrictedPropertyValue('stream'));
    }
}
