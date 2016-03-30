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

use noFlash\CherryHttp\Application\Lifecycle\LoopInterface;
use noFlash\CherryHttp\Application\Lifecycle\LoopNodeInterface;
use noFlash\CherryHttp\IO\Stream\AbstractStreamNode;
use noFlash\CherryHttp\IO\Stream\StreamNodeTrait;

class AbstractStreamNodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractStreamNode
     */
    private $subjectUnderTest;

    /**
     * @var \ReflectionObject
     */
    private $subjectUnderTestObjectReflection;

    public function setUp()
    {
        $this->subjectUnderTest = $this->getMockForAbstractClass(AbstractStreamNode::class);
        $this->subjectUnderTestObjectReflection = new \ReflectionObject($this->subjectUnderTest);
    }

    /**
     * @testdox Tested subject uses StreamNodeTrait
     */
    public function testTestedSubjectUsesStreamNodeTrait()
    {
        $this->assertContains(StreamNodeTrait::class, class_uses(AbstractStreamNode::class));
    }

    /**
     * @testdox Class implements getLoop() method
     */
    public function testClassImplementsGetLoopMethod()
    {
        $this->assertTrue($this->isMethodImplementedBySUTClass('getLoop'));
    }

    private function isMethodImplementedBySUTClass($name)
    {
        //This is, I believe, the only method to really check if abstract class implementing interface has a method

        $subjectUnderTestClassReflection = new \ReflectionClass(AbstractStreamNode::class);

        if (!$subjectUnderTestClassReflection->hasMethod($name)) {
            return false;
        }

        $methodReflection = $subjectUnderTestClassReflection->getMethod($name);

        return ($methodReflection->getDeclaringClass()->name === AbstractStreamNode::class);
    }

    public function testClassContainsProtectedLoopProperty()
    {
        $this->assertTrue($this->subjectUnderTestObjectReflection->hasProperty('loop'));

        $loopProperty = $this->subjectUnderTestObjectReflection->getProperty('loop');
        $this->assertTrue($loopProperty->isProtected());
    }

    public function testByDefaultThereIsNoLoopDefined()
    {
        $this->assertNull($this->getRestrictedPropertyValue('loop'));
        $this->assertNull($this->subjectUnderTest->getLoop());
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

    public function testLoopGetterReturnsValueOfProtectedLoopProperty()
    {
        $loopMock = $this->getMockForAbstractClass(LoopInterface::class);

        $this->setRestrictedPropertyValue('loop', $loopMock);
        $this->assertSame($loopMock, $this->subjectUnderTest->getLoop());
    }

    private function setRestrictedPropertyValue($name, $value)
    {
        if (!$this->subjectUnderTestObjectReflection->hasProperty($name)) {
            throw new \RuntimeException('There is no property named ' . $name);
        }

        $property = $this->subjectUnderTestObjectReflection->getProperty($name);
        $property->setAccessible(true);
        $property->setValue($this->subjectUnderTest, $value);
    }

    /**
     * @testdox Class implements getPingInterval() method
     */
    public function testClassImplementsGetPingIntervalMethod()
    {
        $this->assertTrue($this->isMethodImplementedBySUTClass('getPingInterval'));
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
        $this->assertTrue($this->isMethodImplementedBySUTClass('ping'));
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
        $this->assertTrue($this->isMethodImplementedBySUTClass('onStreamError'));
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
