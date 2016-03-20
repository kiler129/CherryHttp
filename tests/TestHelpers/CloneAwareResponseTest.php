<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Tests\TestHelpers;

use noFlash\CherryHttp\Http\Response\ResponseInterface;

class CloneAwareResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CloneAwareResponse
     */
    private $subjectUnderTest;

    public function setUp()
    {
        $this->subjectUnderTest = $this->getMockForAbstractClass(CloneAwareResponse::class);
    }

    public function testObjectImplementsResponseInterface()
    {
        $this->assertInstanceOf(ResponseInterface::class, $this->subjectUnderTest);
    }

    public function testFreshObjectReturnsZeroNumberOfClones()
    {
        $this->assertSame(0, $this->subjectUnderTest->_getCloneNumber());
    }

    public function testCloneNumberIsIncrementedOnEveryClone()
    {
        $clone1 = clone $this->subjectUnderTest;
        $this->assertSame(1, $clone1->_getCloneNumber());

        $clone2 = clone $clone1;
        $this->assertSame(2, $clone2->_getCloneNumber());

        $clone3 = clone $clone2;
        $this->assertSame(3, $clone3->_getCloneNumber());
    }

    public function testObjectContainsPublicFieldForMarking()
    {
        $reflection = new \ReflectionObject($this->subjectUnderTest);
        $this->assertTrue($reflection->hasProperty('_publicField'));
        $this->assertTrue($reflection->getProperty('_publicField')->isPublic());
        $this->assertTrue($reflection->hasProperty('_status'));
        $this->assertTrue($reflection->getProperty('_status')->isPublic());
        $this->assertTrue($reflection->hasProperty('_body'));
        $this->assertTrue($reflection->getProperty('_body')->isPublic());
    }

    public function testBodyCanBeSet()
    {
        $this->subjectUnderTest->setBody('foo');
        $this->assertSame('foo', $this->subjectUnderTest->_body);
    }

    public function testCodeCanBeSet()
    {
        $this->subjectUnderTest->setStatus('Code', 'Reason');
        $this->assertSame(['Code', 'Reason'], $this->subjectUnderTest->_status);
    }
}
