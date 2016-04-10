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
use noFlash\CherryHttp\Tests\TestHelpers\TestCase;

/**
 * @property BufferAwareAbstractStreamNode|\PHPUnit_Framework_MockObject_MockObject subjectUnderTest
 */
class BufferAwareAbstractStreamNodeTest extends TestCase
{
    public function setUp()
    {
        $this->foo = '';
        /** @var BufferAwareAbstractStreamNode|\PHPUnit_Framework_MockObject_MockObject $subjectUnderTest */
        $this->subjectUnderTest = $this->getMockForAbstractClass(BufferAwareAbstractStreamNode::class);

        parent::setUp();
    }

    public function testClassIsDefinedAsAbstract()
    {
        $sutClassReflection = new \ReflectionClass(BufferAwareAbstractStreamNode::class);
        $this->assertTrue($sutClassReflection->isAbstract());
    }

    /**
     * @testdox Class contains READ_CHUNK_SIZE constant containing integer greater than zero
     */
    public function testClassContainsReadChunkSizeConstantContainingIntegerGreaterThanZero()
    {
        $this->assertTrue(defined(BufferAwareAbstractStreamNode::class . '::READ_CHUNK_SIZE'));
        $this->assertInternalType('integer', BufferAwareAbstractStreamNode::READ_CHUNK_SIZE);
        $this->assertGreaterThan(0, BufferAwareAbstractStreamNode::READ_CHUNK_SIZE);
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

    /**
     * @testdox Class implements isWriteReady() method
     */
    public function testClassImplementsIsWriteReadyMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(BufferAwareAbstractStreamNode::class, 'isWriteReady'));
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

    /**
     * @testdox Class implements doRead() method
     */
    public function testClassImplementsDoReadMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(BufferAwareAbstractStreamNode::class, 'doRead'));
    }

    public function testReadingFromRemotelyDisconnectedStreamDestroysTheStream()
    {
        $dummyServer = $this->createDummyServerWithClient();

        $this->subjectUnderTest->stream = $dummyServer['clientOnServer'];

        fwrite($dummyServer['clientOnClient'], 'ping');
        $this->subjectUnderTest->doRead();
        $this->assertInternalType('resource', $this->subjectUnderTest->stream, 'Valid stream was destroyed');


        fclose($dummyServer['clientOnClient']);
        $this->subjectUnderTest->doRead();
        $this->assertNull($this->subjectUnderTest->stream, 'Disconnected stream not destroyed');
    }

    public function testDataFromStreamIsReadToReadBuffer()
    {
        $dummyServer = $this->createDummyServerWithClient();
        $this->subjectUnderTest->stream = $dummyServer['clientOnServer'];

        fwrite($dummyServer['clientOnClient'], 'test');
        $this->subjectUnderTest->doRead();
        $this->assertSame('test', $this->getRestrictedPropertyValue('readBuffer'));

        fwrite($dummyServer['clientOnClient'], 'foo');
        $this->subjectUnderTest->doRead();
        $this->assertSame('testfoo', $this->getRestrictedPropertyValue('readBuffer'));
    }

    /**
     * Since this test may fail on depending on HHVM compile-time configuration and HHVM lacks
     *  stream_set_chunk_size() & stream_set_read_buffer() it's marked skipped.
     *
     * @see https://github.com/facebook/hhvm/issues/6573
     * @see https://github.com/facebook/hhvm/issues/6977
     */
    public function testDataIsReadFromStreamInSpecifiedChunks()
    {
        $this->skipTestOnHHVM();

        $chunkSize = BufferAwareAbstractStreamNode::READ_CHUNK_SIZE;
        $data1 = str_repeat('a', $chunkSize);
        $data2 = str_repeat('b', $chunkSize);

        $dummyServer = $this->createDummyServerWithClient();

        $this->assertNotFalse(
            stream_set_chunk_size($dummyServer['clientOnServer'], $chunkSize * 3),
            'Failed to set chunk size'
        );
        $this->assertNotFalse(
            stream_set_read_buffer($dummyServer['clientOnServer'], $chunkSize * 3),
            'Failed to set read buffer size'
        );

        $this->subjectUnderTest->stream = $dummyServer['clientOnServer'];
        fwrite($dummyServer['clientOnClient'], $data1 . $data2);

        $this->subjectUnderTest->doRead();
        $this->assertSame(
            $data1,
            $this->getRestrictedPropertyValue('readBuffer'),
            'Buffer contains invalid data after first read'
        );

        $this->subjectUnderTest->doRead();
        $this->assertSame(
            $data1 . $data2,
            $this->getRestrictedPropertyValue('readBuffer'),
            'Buffer contains invalid data after second read'
        );
    }

    /**
     * @testdox Class implements doWrite() method
     */
    public function testClassImplementsDoWriteMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(BufferAwareAbstractStreamNode::class, 'doWrite'));
    }

    /**
     * @testdox Class implements onStreamError() method
     */
    public function testClassImplementsOnStreamErrorMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(BufferAwareAbstractStreamNode::class, 'onStreamError'));
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
     * @testdox Class implements writeBufferAppend() method
     */
    public function testClassImplementsWriteBufferAppendMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(BufferAwareAbstractStreamNode::class, 'writeBufferAppend'));
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
     * @testdox Class implements shutdownRead() method
     */
    public function testClassImplementsShutdownReadMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(BufferAwareAbstractStreamNode::class, 'shutdownRead'));
    }
    
    /**
     * @testdox shutdownRead() switches socket into write-only mode
     * @short
     */
    public function testShutdownReadSwitchesSocketIntoWriteOnlyMode()
    {
        $this->skipTestOnHHVM('See HVVM bug https://github.com/facebook/hhvm/issues/6573');
        
        if (PHP_OS === 'Linux') {
            $this->markTestSkipped('Skipping due to possible PHP bug #71951');
        }

        $dummyServer = $this->createDummyServerWithClient();

        $this->assertNotFalse(stream_set_chunk_size($dummyServer['clientOnServer'], 1), 'Failed to set chunk size');
        $this->assertNotFalse(stream_set_read_buffer($dummyServer['clientOnServer'], 1), 'Failed to set read buffer size');

        fwrite($dummyServer['clientOnClient'], '1234'); //Sends 4 bytes of data from real client

        //It will read 2 bytes and php buffer will should stay empty after this since read buffer was set to 1 byte
        $this->assertSame('12', fread($dummyServer['clientOnServer'], 2), 'Test read failed');

        $this->subjectUnderTest->stream = $dummyServer['clientOnServer'];
        $this->assertTrue($this->subjectUnderTest->shutdownRead(), 'Failed to shutdown read');

        //Since socket was closed for reading it will return empty string
        $this->assertSame('', fread($dummyServer['clientOnServer'], 2), 'Stream was not closed for reading!');
    }

    /**
     * @testdox shutdownRead() will return false if stream was already read-closed
     */
    public function testShutdownReadWillReturnFalseIfStreamWasAlreadyReadClosed()
    {
        if (PHP_OS === 'Linux') {
            $this->markTestSkipped('Skipping due to possible PHP bug #71951');
        }

        $testStream = stream_socket_client('udp://127.0.0.1:9999');
        $this->assertNotFalse($testStream, 'Failed to open test stream');

        $this->assertTrue(stream_socket_shutdown($testStream, STREAM_SHUT_RD), 'Failed to shutdown test stream');

        $this->subjectUnderTest->stream = $testStream;

        try {
            $this->assertFalse($this->subjectUnderTest->shutdownRead());

        } catch (\PHPUnit_Framework_Error_Warning $e) {
            if (!$this->isHHVM() && !$this->isOSX()) { //See https://github.com/facebook/hhvm/issues/6978 for details
                throw $e;
            }
        }
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
