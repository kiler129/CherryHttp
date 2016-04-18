<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Tests\Application\Lifecycle;

use noFlash\CherryHttp\Application\Exception\NodeConflictException;
use noFlash\CherryHttp\Application\Lifecycle\AbstractLoopNode;
use noFlash\CherryHttp\Application\Lifecycle\LoopInterface;
use noFlash\CherryHttp\Tests\TestHelpers\TestCase;

/**
 * @property AbstractLoopNode subjectUnderTest
 */
class AbstractLoopNodeTest extends TestCase
{
    public function setUp()
    {
        $this->subjectUnderTest = $this->getMockForAbstractClass(AbstractLoopNode::class);

        parent::setUp();
    }

    public function testClassIsMarkedAsAbstract()
    {
        $this->assertIsAbstractClass(AbstractLoopNode::class);
    }

    public function testClassDefinesProtectedPropertyForLoop()
    {
        $this->assertTrue($this->subjectUnderTestObjectReflection->hasProperty('loop'));
        $this->assertTrue($this->subjectUnderTestObjectReflection->getProperty('loop')->isProtected());
    }

    public function testClassContainsPublicMethodLoopGetter()
    {
        $this->assertTrue($this->isMethodImplementedByClass(AbstractLoopNode::class, 'getLoop'));
    }

    public function testByDefaultThereIsNoLoopDefined()
    {
        $this->assertNull($this->getRestrictedPropertyValue('loop'));
        $this->assertNull($this->subjectUnderTest->getLoop());
    }

    public function testLoopGetterReflectsProtectedLoopField()
    {
        $testLoop = $this->getMockForAbstractClass(LoopInterface::class);

        $this->setRestrictedPropertyValue('loop', $testLoop);
        $this->assertSame($testLoop, $this->getRestrictedPropertyValue('loop')); //Magic setters anyone? ;)
        $this->assertSame($testLoop, $this->subjectUnderTest->getLoop());
    }

    /**
     * @testdox Class implements getPingInterval() method
     */
    public function testClassImplementsGetPingIntervalMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(AbstractLoopNode::class, 'getPingInterval'));
    }

    /**
     * @testdox Ping interval returns LoopNodeInterface::PING_INTERVAL_ANY
     */
    public function testPingIntervalReturnsNodeInterfacePingIntervalAny()
    {
        $this->assertSame(AbstractLoopNode::PING_INTERVAL_ANY, $this->subjectUnderTest->getPingInterval());
    }

    /**
     * @testdox Class implements ping() method
     */
    public function testClassImplementsPingMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(AbstractLoopNode::class, 'ping'));
    }

    /**
     * @testdox ping() returns null
     */
    public function testPingReturnsNull()
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($this->subjectUnderTest->ping());
    }

    /**
     * @testdox Class implements onAttach() method
     */
    public function testClassImplementsOnAttachMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(AbstractLoopNode::class, 'onAttach'));
    }

    /**
     * @testdox Calling onAttach() method assigns given loop to "loop" property
     */
    public function testCallingOnAttachAssignsGivenLoopToLoopProperty()
    {
        /** @var LoopInterface|\PHPUnit_Framework_MockObject_MockObject $loop */
        $loop = $this->getMockForAbstractClass(LoopInterface::class);

        $this->subjectUnderTest->onAttach($loop);
        $this->assertSame($loop, $this->getRestrictedPropertyValue('loop'));
        $this->assertSame($loop, $this->subjectUnderTest->getLoop());
    }

    /**
     * @testdox Calling onAttach() method with the same loop as already assigned throws NodeConflictException
     */
    public function testCallingOnAttachMethodWithTheSameLoopAsAlreadyAssignedNodeThrowsNodeConflictException()
    {
        /** @var LoopInterface|\PHPUnit_Framework_MockObject_MockObject $testLoop */
        $testLoop = $this->getMockForAbstractClass(LoopInterface::class);
        $this->setRestrictedPropertyValue('loop', $testLoop);

        $this->expectException(NodeConflictException::class);
        $this->subjectUnderTest->onAttach($testLoop);
    }

    /**
     * @testdox Calling onAttach() method on already assigned node with loop other than assigned throws
     *          NodeConflictException
     */
    public function testCallingOnAttachMethodOnAlreadyAssignedNodeWithLoopAnotherThanAssignedThrowsNodeThrowsNodeConflictException()
    {
        /** @var LoopInterface|\PHPUnit_Framework_MockObject_MockObject $testLoop1 */
        $testLoop1 = $this->getMockForAbstractClass(LoopInterface::class);
        /** @var LoopInterface|\PHPUnit_Framework_MockObject_MockObject $testLoop2 */
        $testLoop2 = $this->getMockForAbstractClass(LoopInterface::class);
        $this->setRestrictedPropertyValue('loop', $testLoop1);

        $this->expectException(NodeConflictException::class);
        $this->subjectUnderTest->onAttach($testLoop2);
    }

    /**
     * @testdox Class implements onDetach() method
     */
    public function testClassImplementsOnDetachMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(AbstractLoopNode::class, 'onDetach'));
    }

    /**
     * @testdox Calling onDetach() method unassigns node from loop
     */
    public function testCallingOnDetachMethodUnassignsNodeFromLoop()
    {
        /** @var LoopInterface|\PHPUnit_Framework_MockObject_MockObject $testLoop */
        $testLoop = $this->getMockForAbstractClass(LoopInterface::class);

        $this->setRestrictedPropertyValue('loop', $testLoop);
        $this->subjectUnderTest->onDetach();
        
        $this->assertNull($this->getRestrictedPropertyValue('loop'));
        $this->assertNull($this->subjectUnderTest->getLoop());
    }

    public function testCallingOnDetachMethodOnNotAssignedThrowsNodeConflictException()
    {
        $this->expectException(NodeConflictException::class);
        $this->subjectUnderTest->onDetach();
    }
}
