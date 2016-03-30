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

use noFlash\CherryHttp\IO\Stream\StreamNodeTrait;

class StreamNodeTraitTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var StreamNodeTrait
     */
    private $subjectUnderTest;

    /**
     * @var \ReflectionClass
     */
    private $subjectUnderTestObjectReflection;

    public function setUp()
    {
        $this->subjectUnderTest = $this->getMockForTrait(StreamNodeTrait::class);
        $this->subjectUnderTestObjectReflection = new \ReflectionObject($this->subjectUnderTest);
    }


    public function testTraitDefinesPublicPropertyForStream()
    {
        $this->assertTrue($this->subjectUnderTestObjectReflection->hasProperty('stream'));
        $this->assertTrue($this->subjectUnderTestObjectReflection->getProperty('stream')->isPublic());
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
}
