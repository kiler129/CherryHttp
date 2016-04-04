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

    public function testBothWriteAndReadBuffersAreSetToEmptyStringOnStreamError()
    {
        $this->setRestrictedPropertyValue('writeBuffer', 'foo');
        $this->setRestrictedPropertyValue('readBuffer', 'bar');

        $this->subjectUnderTest->onStreamError();

        $this->assertSame('', $this->getRestrictedPropertyValue('writeBuffer'));
        $this->assertSame('', $this->getRestrictedPropertyValue('readBuffer'));
    }

    /**
     * @testdox writeBufferAppend() adds given data to writeBuffer returning added length
     */
    public function testWriteBufferAppendAddsGivenDataToWriteBufferReturningAddedLength()
    {
        $testStrings = [
            ['le foo', 6],
            ["another\nfoo", 11],
            ["cr\rfoo", 6],
            ["null\0foo", 8],
            ["backspace\x08foo", 13],
            ["tab\tfoo", 7],
            ["utf☃foo", 9],
            [0b101010, 2],
            ['', 0]
        ];

        $expectedBufferContents = '';
        foreach ($testStrings as $datasetIndex => $testString) {
            $expectedBufferContents .= $testString[0];

            $returnedLength = $this->subjectUnderTest->writeBufferAppend($testString[0]);
            $actualBufferContents = $this->getRestrictedPropertyValue('writeBuffer');

            $this->assertSame(
                $expectedBufferContents,
                $actualBufferContents,
                'Buffer contents mismatch after test string #' . ($datasetIndex + 1)
            );
            $this->assertSame(
                $testString[1],
                $returnedLength,
                'Returned appended data length for test string #' . ($datasetIndex + 1) . ' is not valid'
            );
        }
    }

    /**
     * @testdox shutdownRead() switches socket into write-only mode
     * @short
     */
    public function testShutdownReadSwitchesSocketIntoWriteOnlyMode()
    {
        $server = stream_socket_server('tcp://127.0.0.1:9999');
        $this->assertInternalType('resource', $server, 'Failed to start test server');

        $clientOnClient = stream_socket_client('tcp://127.0.0.1:9999');
        $this->assertInternalType('resource', $clientOnClient, 'Failed to create client socket');

        $clientOnServer = stream_socket_accept($server, 0.5);
        $this->assertInternalType('resource', $clientOnServer, 'Failed to accept client');

        $this->assertNotFalse(stream_set_chunk_size($clientOnServer, 1), 'Failed to set chunk size');
        $this->assertNotFalse(stream_set_read_buffer($clientOnServer, 1), 'Failed to set read buffer size');

        fwrite($clientOnClient, '1234'); //Sends 4 bytes of data from real client

        //It will read 2 bytes and php buffer will should stay empty after this since read buffer was set to 1 byte
        $this->assertSame('12', fread($clientOnServer, 2), 'Test read failed');

        $this->subjectUnderTest->stream = $clientOnServer;
        $this->assertTrue($this->subjectUnderTest->shutdownRead(), 'Failed to shutdown read');

        //Since socket was closed for reading it will return empty string
        $this->assertSame('', fread($clientOnServer, 2), 'Stream was not closed for reading!');
    }

    /**
     * @testdox shutdownRead() will return false if stream was already read-closed
     */
    public function testShutdownReadWillReturnFalseIfStreamWasAlreadyReadClosed()
    {
        $testStream = stream_socket_client('udp://127.0.0.1:9999');
        $this->assertNotFalse($testStream, 'Failed to open test stream');

        $this->assertTrue(stream_socket_shutdown($testStream, STREAM_SHUT_RD), 'Failed to shutdown test stream');

        $this->subjectUnderTest->stream = $testStream;
        $this->assertFalse($this->subjectUnderTest->shutdownRead());
    }

    /**
     * @testdox shutdownRead() will return false if there is no stream set
     */
    public function testShutdownReadWillReturnFalseIfThereIsNoStreamSet()
    {
        $this->subjectUnderTest->stream = null; //It should be in this state anyway
        $this->assertFalse($this->subjectUnderTest->shutdownRead());
    }

    /**
     * @testdox shutdownRead() marks node as degenerated
     */
    public function testShutdownReadMarksNodeAsDegenerated()
    {
        $testStream = stream_socket_client('udp://127.0.0.1:9999');
        $this->assertNotFalse($testStream, 'Failed to open test stream');

        $this->subjectUnderTest->stream = $testStream;
        $this->subjectUnderTest->shutdownRead();

        $this->assertTrue($this->getRestrictedPropertyValue('isDegenerated'));
    }
}
