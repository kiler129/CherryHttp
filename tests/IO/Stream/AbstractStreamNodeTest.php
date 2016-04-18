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

use noFlash\CherryHttp\Application\Lifecycle\AbstractLoopNode;
use noFlash\CherryHttp\Application\Lifecycle\LoopNodeTrait;
use noFlash\CherryHttp\IO\Stream\AbstractStreamNode;
use noFlash\CherryHttp\Tests\TestHelpers\TestCase;

/**
 * @property AbstractStreamNode|\PHPUnit_Framework_MockObject_MockObject subjectUnderTest
 */
class AbstractStreamNodeTest extends TestCase
{
    public function setUp()
    {
        $this->subjectUnderTest = $this->getMockForAbstractClass(AbstractStreamNode::class);

        parent::setUp();
    }

    public function testClassIsDefinedAsAbstract()
    {
        $this->assertIsAbstractClass(AbstractStreamNode::class);
    }

    /**
     * @testdox Class extends AbstractLoopNode
     */
    public function testClassExtendsAbstractLoopNode()
    {
        $this->assertInstanceOf(AbstractLoopNode::class, $this->subjectUnderTest);
    }

    public function testTraitDefinesPublicPropertyForStream()
    {
        $this->assertTrue($this->subjectUnderTestObjectReflection->hasProperty('stream'));
        $this->assertTrue($this->subjectUnderTestObjectReflection->getProperty('stream')->isPublic());
    }

    /**
     * @testdox Class implements getStreamResource() method
     */
    public function testClassImplementsGetStreamResourceMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(AbstractStreamNode::class, 'getStreamResource'));
    }

    /**
     * @testdox Stream getter reflects public stream field
     */
    public function testStreamGetterReflectsPublicStreamField()
    {
        $testStream = fopen('php://memory', 'r');

        $this->subjectUnderTest->stream = $testStream;
        $this->assertSame($testStream, $this->subjectUnderTest->stream); //Magic setters anyone? ;)
        $this->assertSame($testStream, $this->subjectUnderTest->getStreamResource());
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
