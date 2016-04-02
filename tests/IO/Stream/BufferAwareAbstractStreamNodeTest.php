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

use noFlash\CherryHttp\IO\Stream\BufferAwareAbstractStreamNode;

class BufferAwareAbstractStreamNodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BufferAwareAbstractStreamNode
     */
    private $subjectUnderTest;

    /**
     * @var \ReflectionObject
     */
    private $subjectUnderTestObjectReflection;

    public function setUp()
    {
        $this->subjectUnderTest = $this->getMockForAbstractClass(BufferAwareAbstractStreamNode::class);
        $this->subjectUnderTestObjectReflection = new \ReflectionObject($this->subjectUnderTest);
    }

    public function testClassIsDefinedAsAbstract()
    {
        $sutClassReflection = new \ReflectionClass(BufferAwareAbstractStreamNode::class);
        $this->assertTrue($sutClassReflection->isAbstract());
    }

    /**
     * @testdox Class contains protected writeBuffer field
     */
    public function testClassContainsProtectedWriteBufferField()
    {
        $this->assertTrue($this->subjectUnderTestObjectReflection->hasProperty('writeBuffer'));
        $this->assertTrue($this->subjectUnderTestObjectReflection->getProperty('writeBuffer')->isProtected());
    }

    /**
     * @testdox Protected writeBuffer field contains empty string on fresh object
     */
    public function testProtectedWriteBufferFieldContainsEmptyStringOnFreshObject()
    {
        $writeBufferValue = $this->getRestrictedPropertyValue('writeBuffer');
        $this->assertInternalType('string', $writeBufferValue);
        $this->assertSame('', $writeBufferValue);
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

    /**
     * @testdox Class contains protected redBuffer field
     */
    public function testClassContainsProtectedReadBufferField()
    {
        $this->assertTrue($this->subjectUnderTestObjectReflection->hasProperty('readBuffer'));
        $this->assertTrue($this->subjectUnderTestObjectReflection->getProperty('readBuffer')->isProtected());
    }

    /**
     * @testdox Protected readBuffer field contains empty string on fresh object
     */
    public function testProtectedReadBufferFieldContainsEmptyStringOnFreshObject()
    {
        $readBufferValue = $this->getRestrictedPropertyValue('readBuffer');
        $this->assertInternalType('string', $readBufferValue);
        $this->assertSame('', $readBufferValue);
    }

    /**
     * @testdox Class contains protected isDegenerated field
     */
    public function testClassContainsProtectedIsDegeneratedField()
    {
        $this->assertTrue($this->subjectUnderTestObjectReflection->hasProperty('isDegenerated'));
        $this->assertTrue($this->subjectUnderTestObjectReflection->getProperty('isDegenerated')->isProtected());
    }

    /**
     * @testdox Protected isGenerated field contains bool(false) on fresh object
     */
    public function testProtectedIsDegeneratedFieldContainsBoolFalseOnFreshObject()
    {
        $isDegeneratedValue = $this->getRestrictedPropertyValue('isDegenerated');
        $this->assertInternalType('bool', $isDegeneratedValue);
        $this->assertSame(false, $isDegeneratedValue);
    }

    public function testFreshNodeIsConsideredNotWriteReady()
    {
        $this->assertFalse($this->subjectUnderTest->isWriteReady());
    }

    public function writeBufferValuesFuzzingForIsWriteReady()
    {
        //Data, expected isWriteReady value
        return [
            ['', false],
            ["\0", true],
            ['a', true],
            ['0', true],
            ['-1', true],
            [0, true],
            [-1, true]
        ];
    }

    /**
     * @testdox      isWriteReady() returns correct values for fuzzed data
     * @dataProvider writeBufferValuesFuzzingForIsWriteReady
     */
    public function testIsWriteReadyReturnsCorrectValuesForFuzzedData($value, $expectedResult)
    {
        $this->setRestrictedPropertyValue('writeBuffer', $value);
        $this->assertSame($expectedResult, $this->subjectUnderTest->isWriteReady());
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
     * @testdox      isWriteReady() always returns false if node is degemerated
     * @dataProvider writeBufferValuesFuzzingForIsWriteReady
     */
    public function testIsWriteReadyAlwaysReturnsFalseIfNodeIsDegenerated($value)
    {
        $this->setRestrictedPropertyValue('isDegenerated', true);
        $this->setRestrictedPropertyValue('writeBuffer', $value);
        $this->assertFalse($this->subjectUnderTest->isWriteReady());
    }
    
    public function testBothWriteAndReadBuffersAreSetToEmptyStringOnStreamError()
    {
        $this->setRestrictedPropertyValue('writeBuffer', 'foo');
        $this->setRestrictedPropertyValue('readBuffer', 'bar');

        $this->subjectUnderTest->onStreamError();

        $this->assertSame('', $this->getRestrictedPropertyValue('writeBuffer'));
        $this->assertSame('', $this->getRestrictedPropertyValue('readBuffer'));
    }
}
