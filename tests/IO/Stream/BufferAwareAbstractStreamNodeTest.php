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
     * @testdox Class contains protercted writeBuffer field
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

    public function testDataFromStreamIsReadAndDiscardedIfNodeIsInDegeneratedState()
    {
        $dummyServer = $this->createDummyServerWithClient();

        $this->subjectUnderTest->stream = $dummyServer['clientOnServer'];
        stream_set_blocking($dummyServer['clientOnServer'], 0);

        fwrite($dummyServer['clientOnClient'], 'test');
        $this->subjectUnderTest->doRead();
        $this->assertSame('test', $this->getRestrictedPropertyValue('readBuffer'), 'Data not arrived via dummy server');

        $this->setRestrictedPropertyValue('isDegenerated', true);
        fwrite($dummyServer['clientOnClient'], 'foo');
        $this->subjectUnderTest->doRead();

        $this->assertSame(
            'test',
            $this->getRestrictedPropertyValue('readBuffer'),
            'Buffer contents is different after second read'
        );
        $this->assertSame('', fread($dummyServer['clientOnServer'], 3), 'Data was not read from stream');
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
     * @testdox Class contains protected abstract processInputBuffer() method
     */
    public function testClassContainsProtectedAbstractProcessInputBufferMethod()
    {
        $this->assertTrue(
            $this->isMethodImplementedByClass(BufferAwareAbstractStreamNode::class, 'processInputBuffer')
        );

        $classReflection = new \ReflectionClass(BufferAwareAbstractStreamNode::class);
        $pibMethod = $classReflection->getMethod('processInputBuffer');

        $this->assertTrue($pibMethod->isAbstract(), 'Method is not abstract');
        $this->assertTrue($pibMethod->isProtected(), 'Method is not protected');
    }

    /**
     * @testdox processInputBuffer() method is called after data read
     */
    public function testProcessInputBufferMethodIsCalledAfterDataRead()
    {
        $this->subjectUnderTest->expects($this->atLeastOnce())->method('processInputBuffer')->willReturnCallback(
            function () {
                $this->assertSame('test', $this->getRestrictedPropertyValue('readBuffer'));

                return true;
            }
        );

        $dummyServer = $this->createDummyServerWithClient();
        $this->subjectUnderTest->stream = $dummyServer['clientOnServer'];

        fwrite($dummyServer['clientOnClient'], 'test');
        $this->subjectUnderTest->doRead();
    }

    /**
     * @testdox processInputBuffer() method is not called if nothing was read
     */
    public function testProcessInputBufferMethodIsNotCalledIfNothingWasRead()
    {
        $this->subjectUnderTest->expects($this->never())->method('processInputBuffer');

        $dummyServer = $this->createDummyServerWithClient();

        $this->subjectUnderTest->stream = $dummyServer['clientOnServer'];
        fclose($dummyServer['clientOnClient']);

        $this->subjectUnderTest->doRead();
    }

    /**
     * @testdox processInputBuffer() method is not called if node is degenerated
     */
    public function testProcessInputBufferMethodIsNotCalledIfNodeIsDegenerated()
    {
        $this->subjectUnderTest->expects($this->once())->method('processInputBuffer');

        $dummyServer = $this->createDummyServerWithClient();

        $this->subjectUnderTest->stream = $dummyServer['clientOnServer'];
        stream_set_blocking($dummyServer['clientOnServer'], 0);

        fwrite($dummyServer['clientOnClient'], 'test');
        $this->subjectUnderTest->doRead();

        $this->setRestrictedPropertyValue('isDegenerated', true);
        fwrite($dummyServer['clientOnClient'], 'foo');
        $this->subjectUnderTest->doRead();
    }

    public function processInputBufferExitProviders()
    {
        return [
            [[true], 1],
            [[null], 1],
            [[0], 1],
            [[''], 1],
            [[1], 1],
            [[false, false, false, false, false, true], 6],
            [[false, false, false, false, false, null], 6],
            [[false, false, false, false, false, 0], 6],
            [[false, false, false, false, false, ''], 6],
            [[false, false, false, false, false, 1], 6],
        ];
    }

    /**
     * @testdox      processInputBuffer() method is called again if returned false
     * @dataProvider processInputBufferExitProviders
     */
    public function testProcessInputBufferMethodIsCalledAgainIfReturnedFalse($returnValues, $expectedCallsNum)
    {
        $sutStub = new BufferAwareAbstractStreamNodeStub();
        $sutStub->pibOutputMap = $returnValues;

        $dummyServer = $this->createDummyServerWithClient();
        $sutStub->stream = $dummyServer['clientOnServer'];

        fwrite($dummyServer['clientOnClient'], 'test');
        $sutStub->doRead();

        $this->assertSame($expectedCallsNum, $sutStub->pibCallsCount);
    }

    /**
     * @testdox Class implements doWrite() method
     */
    public function testClassImplementsDoWriteMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(BufferAwareAbstractStreamNode::class, 'doWrite'));
    }

    public function testContentsOfWriteBufferIsWrittenToStream()
    {
        $dummyServer = $this->createDummyServerWithClient();
        $this->subjectUnderTest->stream = $dummyServer['clientOnServer'];
        $this->setRestrictedPropertyValue('writeBuffer', 'test');

        $this->subjectUnderTest->doWrite();
        $this->assertSame('', $this->getRestrictedPropertyValue('writeBuffer'), 'Buffer is not empty');

        stream_set_blocking($dummyServer['clientOnServer'], 0);
        $this->assertSame(fread($dummyServer['clientOnClient'], 4), 'test', 'Invalid data sent');
    }

    /**
     * This test looks a little bit complicated, but I failed to find simple way to test this behavior.
     * First it creates large (bigger than expected to be transferred by doWrite()) chunk of data, next it's stacked on
     * write buffer and doWrite() is called. Next step (while() loop) is crucial - it reads the other side of connection
     * and counts all bytes received. Based on this knowledge buffer in SUT is compared to expected contents.
     *
     * It's written in a way to not suggest any implementation, so I think it serves the purpose.
     */
    public function testWriteBufferIsCorrectlySplitIfOnlyPartOfTheDataWereWritten()
    {
        $dataSize = (8 * 1024 * 1024);
        $data = str_repeat('a', $dataSize);

        $dummyServer = $this->createDummyServerWithClient();
        stream_set_blocking($dummyServer['clientOnServer'], 0);
        stream_set_blocking($dummyServer['clientOnClient'], 0);
        $this->subjectUnderTest->stream = $dummyServer['clientOnServer'];

        $this->setRestrictedPropertyValue('writeBuffer', $data);
        $bytesWritten = 0;
        while (true) {
            $readChunk = fread($dummyServer['clientOnClient'], 8192);
            $bytesWritten += strlen($readChunk);

            if (empty($readChunk)) {
                break;

            } elseif ($bytesWritten > $dataSize) {
                $this->fail('Failed to determine written data length (data read > data written ?!)');
            }
        }
        $expectedBufferLengthLeft = $dataSize - $bytesWritten;

        $bufferAfterWrite = $this->getRestrictedPropertyValue('writeBuffer');
        $this->assertSame($expectedBufferLengthLeft, strlen($bufferAfterWrite), 'Invalid data length left on buffer');
        $this->assertSame(
            str_repeat('a', $expectedBufferLengthLeft),
            $bufferAfterWrite,
            'Invalid data contents on buffer'
        );
    }


    public function testStreamIsDestroyedAfterWritingAllDataIfNodeIsDegenerated()
    {
        $dummyServer = $this->createDummyServerWithClient();
        stream_set_blocking($dummyServer['clientOnServer'], 0);
        stream_set_blocking($dummyServer['clientOnClient'], 0);

        $this->subjectUnderTest->stream = $dummyServer['clientOnServer'];
        $this->setRestrictedPropertyValue('isDegenerated', true);

        $dataSize = (3 * 1024 * 1024);
        $data = str_repeat('a', $dataSize);
        $this->setRestrictedPropertyValue('writeBuffer', $data);
        $bytesWritten = 0;
        while (!empty($this->getRestrictedPropertyValue('writeBuffer'))) {
            $this->subjectUnderTest->doWrite();

            while (true) {
                $chunkSize = strlen(fread($dummyServer['clientOnClient'], 8192));
                $bytesWritten += $chunkSize;

                if ($chunkSize === 0) {
                    break;
                }
            }
        }

        $this->assertNull($this->subjectUnderTest->stream, 'Stream not destroyed');
        $this->assertSame($dataSize, $bytesWritten, 'Only part of the data transferred');
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
        $this->skipTestOnHHVM('See HVVM bug https://github.com/facebook/hhvm/issues/6573'); //HHVM on all OSs affected
        $this->skipTestOnLinux(
            'See PHP bug https://bugs.php.net/bug.php?id=71951'
        ); //PHP on Linux affected (no need to check if HHVM since all HHVMs are skipped above)

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
        //In fact all intepreters will be affected due to bug nature (shutdown() POSIX call behaves strange on Linux)
        $this->skipTestOnLinux('See PHP bug https://bugs.php.net/bug.php?id=71951');

        $testStream = stream_socket_client('udp://127.0.0.1:9999');
        $this->assertNotFalse($testStream, 'Failed to open test stream');
        $this->streamsToDestroy[] = $testStream;

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
        $this->streamsToDestroy[] = $testStream;

        $this->subjectUnderTest->stream = $testStream;
        $this->subjectUnderTest->shutdownRead();

        $this->assertTrue($this->getRestrictedPropertyValue('isDegenerated'));
    }
}
