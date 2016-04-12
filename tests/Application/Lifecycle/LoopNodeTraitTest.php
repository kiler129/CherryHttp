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
}
