<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Tests\Http\Message;

use noFlash\CherryHttp\Http\Message\Message;
use noFlash\CherryHttp\Http\Message\MessageInterface;
use noFlash\CherryHttp\IO\Stream\StreamInterface;
use noFlash\CherryHttp\Tests\TestHelpers\TestCase;

/**
 * @property Message subjectUnderTest
 */
class MessageTest extends TestCase
{
    public function setUp()
    {
        $this->subjectUnderTest = new Message();
        
        parent::setUp();
    }

    public function testClassDefinedProtectedArrayFieldForHeaders()
    {
        $this->assertTrue($this->subjectUnderTestObjectReflection->hasProperty('headers'));
        $this->assertTrue($this->subjectUnderTestObjectReflection->getProperty('headers')->isProtected());
        $this->assertInternalType('array', $this->getRestrictedPropertyValue('headers'));
    }

    public function testClassDefinedProtectedFieldForBody()
    {
        $this->assertTrue($this->subjectUnderTestObjectReflection->hasProperty('body'));
        $this->assertTrue($this->subjectUnderTestObjectReflection->getProperty('body')->isProtected());
    }

    public function testClassDefinedProtectedFieldForProtocolVersion()
    {
        $this->assertTrue($this->subjectUnderTestObjectReflection->hasProperty('protocolVersion'));
        $this->assertTrue($this->subjectUnderTestObjectReflection->getProperty('protocolVersion')->isProtected());
    }

    /**
     * @testdox Newly created message uses HTTP/1.1
     */
    public function testNewlyCreatedMessageUsesHttp11()
    {
        $this->assertEquals(MessageInterface::HTTP_11, $this->subjectUnderTest->getProtocolVersion());
    }

    public function testObjectAcceptsValidHttpVersions()
    {
        $this->subjectUnderTest->setProtocolVersion(MessageInterface::HTTP_10);
        $this->assertEquals(MessageInterface::HTTP_10, $this->subjectUnderTest->getProtocolVersion());

        $this->subjectUnderTest->setProtocolVersion(MessageInterface::HTTP_11);
        $this->assertEquals(MessageInterface::HTTP_11, $this->subjectUnderTest->getProtocolVersion());

        $this->subjectUnderTest->setProtocolVersion('0.9');
        $this->assertEquals('0.9', $this->subjectUnderTest->getProtocolVersion());

        $this->subjectUnderTest->setProtocolVersion('2.0');
        $this->assertEquals('2.0', $this->subjectUnderTest->getProtocolVersion());

        //Not existing BUT still semantically valid
        $this->subjectUnderTest->setProtocolVersion('6.9');
        $this->assertEquals('6.9', $this->subjectUnderTest->getProtocolVersion());

        //Not existing BUT still semantically valid
        $this->subjectUnderTest->setProtocolVersion('0.3');
        $this->assertEquals('0.3', $this->subjectUnderTest->getProtocolVersion());
    }

    public function invalidHttpVersionsProvider()
    {
        return [
            ['XYZ'], //Completely invalid
            ['x.1'], //First digit invalid
            ['1.x'], //Second digit invalid
            ['1.1-dev'], //Additional postifx
            ['1.33'],
            ['1,1'], //Invalid separator
            [null],
            [false],
            [true],
            ["11\x8.1"], //Backspace injection
            ["1.1\x0nullbyte"], //Null-byte injection
            ["\x1.1"], //Converting first digit to decimal will result in 1, but it's still not valid!
            ["1.\x1"] //Same as above but for 2nd digit
        ];
    }

    /**
     * @dataProvider invalidHttpVersionsProvider
     */
    public function testSettingUnknownProtocolVersionThrowsException($version)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid HTTP version - valid version should be in DIGIT.DIGIT format.');

        $this->subjectUnderTest->setProtocolVersion($version);
    }

    public function headerDataProvider()
    {
        list($names, $values) = $this->getNamesAndValues();

        foreach ($names as $setName => $checkName) {
            foreach ($values as $setValue => $checkValue) {
                yield [$setName, $checkName, $setValue, $checkValue];
            }
        }
    }

    private function getNamesAndValues()
    {
        //Set, check
        $names = [
            'Test' => 'Test', //Exact
            'F2oo' => 'F2oo', //Exact w/number
            'fOo'  => 'FOO', //Uppercase
            'BaR'  => 'bar', //Lowercase
            'bAz'  => 'Baz' //Mixed
        ];

        //Set, check
        $values = [
            'meow' => 'meow', //Standard string
            ''     => '', //Empty string
            null   => '',
            false  => '0',
            true   => '1',
            0      => '0',
            "\x0"  => "\x0" //Null byte
        ];

        return [$names, $values];
    }

    /**
     * @dataProvider headerDataProvider
     */
    public function testSingleHeaderIsPersisted($setName, $checkName, $setValue, $checkValue)
    {
        $this->subjectUnderTest->setHeader($setName, $setValue);
        $this->assertSame([$checkValue], $this->subjectUnderTest->getHeader($checkName));
    }

    public function testFetchingUnknownHeaderReturnsEmptyArray()
    {
        $this->assertSame([], $this->subjectUnderTest->getHeader('test'));
        $this->assertSame([], $this->subjectUnderTest->getHeader(''));
        $this->assertSame([], $this->subjectUnderTest->getHeader(null));
        $this->assertSame([], $this->subjectUnderTest->getHeader(false));
        $this->assertSame([], $this->subjectUnderTest->getHeader(true));
        $this->assertSame([], $this->subjectUnderTest->getHeader(0));
    }

    /**
     * @dataProvider headerDataProvider
     */
    public function testCheckingForHeaderExistanceRetrunsRealResult($setName, $checkName, $setValue)
    {
        $this->assertFalse($this->subjectUnderTest->hasHeader($checkName));
        $this->subjectUnderTest->setHeader($setName, $setValue);
        $this->assertTrue($this->subjectUnderTest->hasHeader($checkName));
    }

    /**
     * @testdox hasHeader() works while multiple headers were set
     */
    public function testHasHeadersWorksWhileMultipleHeadersWereSet()
    {
        $this->assertFalse($this->subjectUnderTest->hasHeader('TEST'));
        $this->assertFalse($this->subjectUnderTest->hasHeader('foo'));
        $this->subjectUnderTest->setHeader('test', 'moew');
        $this->assertTrue($this->subjectUnderTest->hasHeader('TEST'));
        $this->assertFalse($this->subjectUnderTest->hasHeader('foo'));

        $this->subjectUnderTest->setHeader('FOO', 'grrr');
        $this->assertTrue($this->subjectUnderTest->hasHeader('FoO'));

    }

    /**
     * @dataProvider headerDataProvider
     */
    public function testSingleHeaderCanBeRemovedAndDoesntAffectOtherHeadersSet($setName, $removeName, $setValue)
    {
        $pseudoRandomV1 = md5(rand());
        $pseudoRandomV2 = sha1(rand());

        $this->subjectUnderTest->setHeader($pseudoRandomV1, $pseudoRandomV2);
        $this->subjectUnderTest->setHeader($pseudoRandomV2, $pseudoRandomV1);
        $this->assertTrue($this->subjectUnderTest->hasHeader($pseudoRandomV1));
        $this->assertSame([$pseudoRandomV2], $this->subjectUnderTest->getHeader($pseudoRandomV1));
        $this->assertTrue($this->subjectUnderTest->hasHeader($pseudoRandomV2));
        $this->assertSame([$pseudoRandomV1], $this->subjectUnderTest->getHeader($pseudoRandomV2));


        $this->assertFalse($this->subjectUnderTest->hasHeader($removeName));
        $this->subjectUnderTest->setHeader($setName, $setValue);
        $this->assertTrue($this->subjectUnderTest->hasHeader($removeName));
        $this->subjectUnderTest->unsetHeader($removeName);
        $this->assertFalse($this->subjectUnderTest->hasHeader($removeName));

        $this->assertTrue($this->subjectUnderTest->hasHeader($pseudoRandomV1));
        $this->assertSame([$pseudoRandomV2], $this->subjectUnderTest->getHeader($pseudoRandomV1));
        $this->assertTrue($this->subjectUnderTest->hasHeader($pseudoRandomV2));
        $this->assertSame([$pseudoRandomV1], $this->subjectUnderTest->getHeader($pseudoRandomV2));
    }

    /**
     * @dataProvider headerDataProvider
     */
    public function testSettingHeaderWithTheSameNameReplacesPreviousOne($setName, $checkName, $value)
    {
        $processedValue = md5($value);

        $this->subjectUnderTest->setHeader($setName, $value);
        $this->subjectUnderTest->setHeader($setName, $processedValue);

        $this->assertTrue($this->subjectUnderTest->hasHeader($checkName));
        $this->assertSame([$processedValue], $this->subjectUnderTest->getHeader($checkName));
    }

    /**
     * @dataProvider headerDataProvider
     */
    public function testMultipleHeaderValuesCanBeAddedUnderSingleName($setName, $checkName, $setValue, $checkValue)
    {
        $processedValue1 = md5($setValue);
        $processedValue2 = sha1($setValue);
        $expectedResult = [$processedValue1, $checkValue, $processedValue2];

        $this->subjectUnderTest->setHeader($setName, $processedValue1);
        $this->subjectUnderTest->addHeader($setName, $setValue);
        $this->subjectUnderTest->addHeader($setName, $processedValue2);

        $this->assertSame($expectedResult, $this->subjectUnderTest->getHeader($checkName));
    }

    /**
     * @dataProvider headerDataProvider
     */
    public function testHeaderCanBeAddedWithoutSettingItFirst($setName, $checkName, $setValue, $checkValue)
    {
        $this->subjectUnderTest->addHeader($setName, $setValue);
        $this->assertSame([$checkValue], $this->subjectUnderTest->getHeader($checkName));
    }

    /**
     * This test ensures all names from list defined in getNamesAndValues() can be used and correctly returned by
     * getHeaders().
     */
    public function testAllHeadersCanBeRetrievedAtOnceWhileFuzzedNamesAreUsed()
    {

        list($names, $values) = $this->getNamesAndValues();

        $expected = [];
        foreach ($names as $name => $modifiedName) {
            $randomValue = sha1(microtime() . rand());
            $expected[$name][] = $randomValue;

            $this->subjectUnderTest->setHeader($name, $randomValue);
            $this->assertSame(
                $expected,
                $this->subjectUnderTest->getHeaders(),
                'Test failed after adding header with name >>' . $name . '<< and random value of ' . $randomValue
            );
        }
    }

    /**
     * This test ensures all values from list defined in getNamesAndValues() can be used and correctly returned by
     * getHeaders().
     */
    public function testAllHeadersCanBeRetrievedAtOnceWhileFuzzedValuesAreUsed()
    {
        list($names, $values) = $this->getNamesAndValues();

        $expected = [];
        foreach ($values as $setValue => $expectedValue) {
            $randomName = sha1(microtime() . rand());
            $expected[$randomName][] = $expectedValue;

            $this->subjectUnderTest->setHeader($randomName, $setValue);
            $this->assertSame(
                $expected,
                $this->subjectUnderTest->getHeaders(),
                'Test failed after adding header with value >>' . $setValue . '<< and random name of ' . $randomName
            );
        }
    }

    public function testAllHeadersCanBeRetrievedAtOnceWhileSomeHeadersContainsMultipleValues()
    {
        $expectedResult = [
            'TestHeader1' => ['testValue1', 'testValue2', 'testValue3'],
            'TestHeader2' => ['testValue4'],
            'TestHeader3' => ['testValue5'],
            'TestHeader4' => ['testValue6'],
            'TestHeader5' => ['testValue7', 'testValue8'],
        ];

        $this->subjectUnderTest->setHeader('TestHeader1', 'testValue1');
        $this->subjectUnderTest->addHeader('TestHeader1', 'testValue2');
        $this->subjectUnderTest->addHeader('TestHeader1', 'testValue3');

        $this->subjectUnderTest->setHeader('TestHeader2', 'testValue4');
        $this->subjectUnderTest->setHeader('TestHeader3', 'testValue5');
        $this->subjectUnderTest->setHeader('TestHeader4', 'testValue6');

        $this->subjectUnderTest->addHeader('TestHeader5', 'testValue7');
        $this->subjectUnderTest->addHeader('TestHeader5', 'testValue8');

        $this->assertSame($expectedResult, $this->subjectUnderTest->getHeaders());
    }

    public function testTryingToGetAllHeadersWhereNoneWasSetResultsInEmptyArray()
    {
        $this->assertSame([], $this->subjectUnderTest->getHeaders());
    }

    public function testFreshObjectContainsEmptyStringBody()
    {
        $this->assertSame('', $this->subjectUnderTest->getBody());
    }

    public function testBodyCanBeSetToStringValue()
    {
        $testString = 'FooBar';

        $this->subjectUnderTest->setBody($testString);
        $this->assertSame($testString,
                          $this->getRestrictedPropertyValue('body'),
                          'Protected body field is was not populated by setBody()');
        $this->assertSame($testString, $this->subjectUnderTest->getBody());


        $testString = 'BazzBar';

        $this->subjectUnderTest->setBody($testString);
        $this->assertSame($testString,
                          $this->getRestrictedPropertyValue('body'),
                          'Protected body field is was not populated by setBody()');
        $this->assertSame($testString, $this->subjectUnderTest->getBody());
    }

    /**
     * @testdox Body can be set to StreamInterface object
     */
    public function testBodyCanBeSetToStreamInterfaceObject()
    {
        $testStream = $this->getMockForAbstractClass(StreamInterface::class);

        $this->subjectUnderTest->setBody($testStream);
        $this->assertSame($testStream,
                          $this->getRestrictedPropertyValue('body'),
                          'Protected body field is was not populated by setBody()');
        $this->assertSame($testStream, $this->subjectUnderTest->getBody());
    }

    public function testBodyCanBeClearedBySettingNull()
    {
        $this->subjectUnderTest->setBody('foo');
        $this->subjectUnderTest->setBody(null);
        $this->assertNull($this->subjectUnderTest->getBody());
    }

    /**
     * @testdox setBody() throws \InvalidArgumentException object not implementing StreamInterface was passed
     */
    public function testSetBodyThrowsInvalidArgumentExceptionIfIObjectNotImplementingStreamInterfaceWasPassed()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->subjectUnderTest->setBody(new \stdClass());
    }
}
