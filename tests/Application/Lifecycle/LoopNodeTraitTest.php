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
use noFlash\CherryHttp\Application\Lifecycle\LoopInterface;
use noFlash\CherryHttp\Application\Lifecycle\LoopNodeTrait;
use noFlash\CherryHttp\Tests\TestHelpers\TestCase;

class LoopNodeTraitTest extends TestCase
{

    public function setUp()
    {
        $this->subjectUnderTest = $this->getMockForTrait(LoopNodeTrait::class);

        parent::setUp();
    }

    public function testTraitDefinesProtectedPropertyForLoop()
    {
        $this->assertTrue($this->subjectUnderTestObjectReflection->hasProperty('loop'));
        $this->assertTrue($this->subjectUnderTestObjectReflection->getProperty('loop')->isProtected());
    }

    public function testTraitContainsPublicMethodLoopGetter()
    {
        $this->assertTrue($this->isMethodImplementedByClass(LoopNodeTrait::class, 'getLoop'));
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
     * @testdox Trait implements onAttach() method
     */
    public function testTraitImplementsOnAttachMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(LoopNodeTrait::class, 'onAttach'));
    }

    /**
     * @testdox Caling onAttach() method assigns given loop to "loop" property
     */
    public function testCallingOnAttachAssignsGivenLoopToLoopProperty()
    {
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
        $testLoop1 = $this->getMockForAbstractClass(LoopInterface::class);
        $testLoop2 = $this->getMockForAbstractClass(LoopInterface::class);
        $this->setRestrictedPropertyValue('loop', $testLoop1);

        $this->expectException(NodeConflictException::class);
        $this->subjectUnderTest->onAttach($testLoop2);
    }

    /**
     * @testdox Trait implements onDetach() method
     */
    public function testTraitImplementsOnDetachMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(LoopNodeTrait::class, 'onDetach'));
    }

    /**
     * @testdox Caling onDetach() method unassigns node from loop
     */
    public function testCallingOnDetachMethodUnassignsNodeFromLoop()
    {
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
