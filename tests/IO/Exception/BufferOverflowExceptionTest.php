<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Tests\IO\Exception;

use noFlash\CherryHttp\IO\Exception\BufferOverflowException;

class BufferOverflowExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BufferOverflowException
     */
    private $subjectUnderTest;

    public function setUp()
    {
        $this->subjectUnderTest = new BufferOverflowException();
    }

    public function testClassExtendsCorrectExceptionClasses()
    {
        $serverExceptionReflection = new \ReflectionClass(BufferOverflowException::class);
        $this->assertTrue($serverExceptionReflection->isSubclassOf('\OverflowException'));
        $this->assertTrue($serverExceptionReflection->isSubclassOf('\Exception'));
    }

    public function testOverflowMagnitudeIsNullByDefault()
    {
        $this->assertNull($this->subjectUnderTest->getOverflowMagnitude());
    }

    public function testOverflowMagnitudeIsHeldByObject()
    {
        $this->subjectUnderTest->setOverflowMagnitude(123);
        $this->assertSame(123, $this->subjectUnderTest->getOverflowMagnitude());

        $this->subjectUnderTest->setOverflowMagnitude(333);
        $this->assertSame(333, $this->subjectUnderTest->getOverflowMagnitude());

        $this->subjectUnderTest->setOverflowMagnitude(999999);
        $this->assertSame(999999, $this->subjectUnderTest->getOverflowMagnitude());

        $this->subjectUnderTest->setOverflowMagnitude(PHP_INT_MAX);
        $this->assertSame(PHP_INT_MAX, $this->subjectUnderTest->getOverflowMagnitude());
    }

    public function testOverflowMagnitudeRejectsNonNumericValues()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Overflow magnitude should be a number.');

        $this->subjectUnderTest->setOverflowMagnitude([42]);
    }

    public function testOverflowMagnitudeRejectsNegativeValues()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Overflow magnitude cannot be negative.');

        $this->subjectUnderTest->setOverflowMagnitude(-1);
    }
}
