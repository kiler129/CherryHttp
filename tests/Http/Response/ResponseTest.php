<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Tests\Http\Response;

use noFlash\CherryHttp\Http\Response\Response;
use noFlash\CherryHttp\Http\Response\ResponseCode;
use noFlash\CherryHttp\IO\Stream\StreamInterface;
use noFlash\CherryHttp\Tests\TestHelpers\TestCase;

/**
 * @property Response subjectUnderTest
 */
class ResponseTest extends TestCase
{
    public function setUp()
    {
        $this->subjectUnderTest = new Response();
        parent::setUp();
    }

    public function testResponseContainsServerHeaderByDefault()
    {
        $this->assertTrue($this->subjectUnderTest->hasHeader('Server'));

        $serverHeader = $this->subjectUnderTest->getHeader('Server');
        $this->assertSame(1, count($serverHeader));

        $this->assertStringStartsWith('CherryHttp/', reset($serverHeader));
    }

    public function testResponseIsCreatedWithOKCodeAndRespectivePhraseByDefault()
    {
        $defaultCode = ResponseCode::OK;
        $defaultReasonPhrase = ResponseCode::getReasonPhraseByCode($defaultCode);

        $this->assertSame(ResponseCode::OK, $this->subjectUnderTest->getStatusCode());
        $this->assertSame($defaultReasonPhrase, $this->subjectUnderTest->getReasonPhrase());
    }

    public function ianaCodesProvider()
    {
        $ianaCodes = [
            ResponseCode::CONTINUE_INFORMATION,
            ResponseCode::SWITCHING_PROTOCOLS,
            ResponseCode::PROCESSING,
            ResponseCode::OK,
            ResponseCode::CREATED,
            ResponseCode::ACCEPTED,
            ResponseCode::NON_AUTHORITATIVE_INFORMATION,
            ResponseCode::NO_CONTENT,
            ResponseCode::RESET_CONTENT,
            ResponseCode::PARTIAL_CONTENT,
            ResponseCode::MULTI_STATUS,
            ResponseCode::ALREADY_REPORTED,
            ResponseCode::IM_USED,
            ResponseCode::MULTIPLE_CHOICES,
            ResponseCode::MOVED_PERMANENTLY,
            ResponseCode::FOUND,
            ResponseCode::SEE_OTHER,
            ResponseCode::NOT_MODIFIED,
            ResponseCode::USE_PROXY,
            ResponseCode::SWITCH_PROXY,
            ResponseCode::TEMPORARY_REDIRECT,
            ResponseCode::PERMANENT_REDIRECT,
            ResponseCode::BAD_REQUEST,
            ResponseCode::UNAUTHORIZED,
            ResponseCode::PAYMENT_REQUIRED,
            ResponseCode::FORBIDDEN,
            ResponseCode::NOT_FOUND,
            ResponseCode::METHOD_NOT_ALLOWED,
            ResponseCode::NOT_ACCEPTABLE,
            ResponseCode::PROXY_AUTHENTICATION_REQUIRED,
            ResponseCode::REQUEST_TIMEOUT,
            ResponseCode::CONFLICT,
            ResponseCode::GONE,
            ResponseCode::LENGTH_REQUIRED,
            ResponseCode::PRECONDITION_FAILED,
            ResponseCode::PAYLOAD_TOO_LARGE,
            ResponseCode::URI_TOO_LONG,
            ResponseCode::UNSUPPORTED_MEDIA_TYPE,
            ResponseCode::RANGE_NOT_SATISFIABLE,
            ResponseCode::EXPECTATION_FAILED,
            ResponseCode::MISDIRECTED_REQUEST,
            ResponseCode::UNPROCESSABLE_ENTITY,
            ResponseCode::LOCKED,
            ResponseCode::FAILED_DEPENDENCY,
            ResponseCode::UPGRADE_REQUIRED,
            ResponseCode::PRECONDITION_REQUIRED,
            ResponseCode::TOO_MANY_REQUESTS,
            ResponseCode::REQUEST_HEADER_FIELDS_TOO_LARGE,
            ResponseCode::UNAVAILABLE_FOR_LEGAL_REASONS,
            ResponseCode::INTERNAL_SERVER_ERROR,
            ResponseCode::NOT_IMPLEMENTED,
            ResponseCode::BAD_GATEWAY,
            ResponseCode::SERVICE_UNAVAILABLE,
            ResponseCode::GATEWAY_TIMEOUT,
            ResponseCode::HTTP_VERSION_NOT_SUPPORTED,
            ResponseCode::VARIANT_ALSO_NEGOTIATES,
            ResponseCode::INSUFFICIENT_STORAGE,
            ResponseCode::LOOP_DETECTED,
            ResponseCode::NOT_EXTENDED,
            ResponseCode::NETWORK_AUTHENTICATION_REQUIRED
        ];

        foreach ($ianaCodes as $code) {
            yield [$code, ResponseCode::getReasonPhraseByCode($code)];
        }
    }

    /**
     * @testdox      All IANA status codes can be set
     * @dataProvider ianaCodesProvider
     */
    public function testAllIanaStatusCodesCanBeSet($code)
    {
        $this->subjectUnderTest->setStatus($code);
        $this->assertSame($code, $this->subjectUnderTest->getStatusCode());
    }

    public function semanticallyInvalidCodesProvider()
    {
        return [
            [99],
            [1000],
            [100.1],
            [false],
            [0],
            [null],
            [true]
        ];
    }

    /**
     * @dataProvider semanticallyInvalidCodesProvider
     */
    public function testCodeRejectsSemanticallyInvalidCodes($code)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid code specified. Consult RFC.');
        $this->subjectUnderTest->setStatus($code);
    }

    public function customCodesProvider()
    {
        $customCodes = [
            199,
            215,
            333,
            482,
            576
        ];

        foreach ($customCodes as $code) {
            yield [$code, ResponseCode::getReasonPhraseByCode($code)];
        }
    }

    /**
     * @dataProvider customCodesProvider
     */
    public function testCustomCodesCanBeSet($code, $defaultPhrase)
    {
        $this->subjectUnderTest->setStatus($code);
        $this->assertSame($code, $this->subjectUnderTest->getStatusCode());
        $this->assertSame($defaultPhrase, $this->subjectUnderTest->getReasonPhrase());
    }

    /**
     * @testdox      IANA status codes have default reason phrase assigned if it wasn't set
     * @dataProvider ianaCodesProvider
     */
    public function testIanaStatusCodesHaveDefaultReasonPhraseAssignedIfItWasntSet($code, $defaultPhrase)
    {
        $this->subjectUnderTest->setStatus($code);
        $this->assertSame($defaultPhrase, $this->subjectUnderTest->getReasonPhrase());
    }

    public function customReasonPhrasesCanBeSet()
    {
        //Set, expect. They should be validated using 200 code
        static $defaultPhrase = 'OK';

        return [
            ['test', 'test'],
            ['Test Phrase', 'Test Phrase'],
            ['', $defaultPhrase],
            [null, $defaultPhrase],
            [true, '1'],
            [false, 'OK']
        ];
    }

    /**
     * @dataProvider customReasonPhrasesCanBeSet
     */
    public function testCustomReasonPhraseCanBeUsed($setPhrase, $checkPhrase)
    {
        $this->subjectUnderTest->setStatus(200, $setPhrase);
        $this->assertSame($checkPhrase, $this->subjectUnderTest->getReasonPhrase());
    }

    /**
     * @testdox Setting body is restricted on "No Content" code
     */
    public function testSettingBodyIsRestrictedOnNoContentCode()
    {
        $this->subjectUnderTest->setStatus(ResponseCode::NO_CONTENT);

        $this->subjectUnderTest->setBody('');
        $this->assertSame('', $this->subjectUnderTest->getBody());

        $this->subjectUnderTest->setBody(null);
        $this->assertNull($this->subjectUnderTest->getBody());

        $body = chr(rand(32, 126)); //Random ASCII symbol excluding controls
        $this->expectException(\LogicException::class);
        $this->subjectUnderTest->setBody($body);
    }

    public function informationalCodesProvider()
    {
        for ($i = 100; $i < 200; $i++) {
            yield [$i];
        }
    }

    /**
     * @testdox      Setting body is restricted on informational responses
     * @dataProvider informationalCodesProvider
     */
    public function testSettingBodyIsRestrictedOnInformationalResponses($code)
    {
        $this->subjectUnderTest->setStatus($code);

        $this->subjectUnderTest->setBody('');
        $this->assertSame('', $this->subjectUnderTest->getBody());

        $this->subjectUnderTest->setBody(null);
        $this->assertNull($this->subjectUnderTest->getBody());

        $body = chr(rand(32, 126)); //Random ASCII symbol excluding controls
        $this->expectException(\LogicException::class);
        $this->subjectUnderTest->setBody($body);
    }

    public function testFreshObjectContainsEmptyStringBody()
    {
        $this->assertSame('', $this->subjectUnderTest->getBody());
    }

    public function testBodyCanBeSetToStringValue()
    {
        $testString = 'FooBar';

        $this->subjectUnderTest->setBody($testString);
        $this->assertSame(
            $testString,
            $this->getRestrictedPropertyValue('body'),
            'Protected body field is was not populated by setBody()'
        );
        $this->assertSame($testString, $this->subjectUnderTest->getBody());


        $testString = 'BazzBar';

        $this->subjectUnderTest->setBody($testString);
        $this->assertSame(
            $testString,
            $this->getRestrictedPropertyValue('body'),
            'Protected body field is was not populated by setBody()'
        );
        $this->assertSame($testString, $this->subjectUnderTest->getBody());
    }

    /**
     * @testdox Body can be set to StreamInterface object
     */
    public function testBodyCanBeSetToStreamInterfaceObject()
    {
        $testStream = $this->getMockForAbstractClass(StreamInterface::class);

        $this->subjectUnderTest->setBody($testStream);
        $this->assertSame(
            $testStream,
            $this->getRestrictedPropertyValue('body'),
            'Protected body field is was not populated by setBody()'
        );
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

    public function contentLengthsTestProvider()
    {
        return [
            ['Le foo?', '7'],
            ["a\nb", '3'],
            ['☃', '3'],
            ["aa\0b", '4'],
            [' a b ', '5']
        ];
    }

    /**
     * @testdox      Proper Content-Length header is added for string payload on setBody
     * @dataProvider contentLengthsTestProvider
     */
    public function testProperContentLengthHeaderIsAddedForStringPayloadOnSetBody($body, $length)
    {
        $this->subjectUnderTest->setBody('Le foo?');
        $this->subjectUnderTest->setBody($body);

        $this->assertTrue(
            $this->subjectUnderTest->hasHeader('Content-Length'),
            'No header was added'
        );

        $this->assertSame(
            [$length],
            $this->subjectUnderTest->getHeader('Content-Length'),
            'Invalid header value'
        );
    }

    /**
     * @testdox Proper Content-Length header is added for finite stream
     */
    public function testProperContentLengthHeaderIsAddedForFiniteStream()
    {
        $streamLength = rand(5, 100);

        $stream = $this->getMockForAbstractClass(StreamInterface::class);
        $stream->expects($this->atLeastOnce())->method('getLength')->willReturn($streamLength);

        $this->subjectUnderTest->setBody($stream);
        $this->assertTrue(
            $this->subjectUnderTest->hasHeader('Content-Length'),
            'No header was added'
        );

        $this->assertSame(
            [(string)$streamLength],
            $this->subjectUnderTest->getHeader('Content-Length'),
            'Invalid header value'
        );
    }

    /**
     * @testdox No Content-Length header is added for stream with no known length
     */
    public function testNoContentLengthHeaderIsAddedForStreamWWithNoKnownLength()
    {
        $stream = $this->getMockForAbstractClass(StreamInterface::class);
        $stream->expects($this->atLeastOnce())->method('getLength')->willReturn(null);

        $this->subjectUnderTest->setBody($stream);
        $this->assertFalse(
            $this->subjectUnderTest->hasHeader('Content-Length'),
            'Header was not expected to be present (but it is)'
        );
    }

    /**
     * @inheritdoc Content-Length header for string payload is overwritten if previously set
     */
    public function tesContentLengthHeaderForStringPayloadIsOverwrittenIfPreviouslySet()
    {
        $this->subjectUnderTest->setHeader('Content-Length', '123');
        $this->subjectUnderTest->setBody('abcd');

        $this->assertTrue(
            $this->subjectUnderTest->hasHeader('Content-Length'),
            'Header vanished completely'
        );

        $this->assertSame(
            ['4'],
            $this->subjectUnderTest->getHeader('Content-Length'),
            'Invalid header value'
        );
    }

    /**
     * @inheritdoc Content-Length header for stream **WITH** known length is overwritten if previously set
     */
    public function tesContentLengthHeaderForStreamWithKnownLengthIsOverwrittenIfPreviouslySet()
    {
        $streamLength = rand(5, 100);

        $stream = $this->getMockForAbstractClass(StreamInterface::class);
        $stream->expects($this->atLeastOnce())->method('getLength')->willReturn($streamLength);

        $this->subjectUnderTest->setHeader('Content-Length', '4');
        $this->subjectUnderTest->setBody($stream);

        $this->assertTrue(
            $this->subjectUnderTest->hasHeader('Content-Length'),
            'Header vanished completely'
        );

        $this->assertSame(
            [(string)$streamLength],
            $this->subjectUnderTest->getHeader('Content-Length'),
            'Invalid header value'
        );
    }

    /**
     * @testdox Content-Length header for stream **WITHOUT** known length is not overwritten
     */
    public function testContentLengthHeaderForStreamWithoutKnownLengthIsNotOverwritten()
    {
        $streamLength = (string)rand(5, 100);

        $stream = $this->getMockForAbstractClass(StreamInterface::class);
        $stream->expects($this->atLeastOnce())->method('getLength')->willReturn(null);

        $this->subjectUnderTest->setHeader('Content-Length', $streamLength);
        $this->subjectUnderTest->setBody($stream);

        $this->assertTrue(
            $this->subjectUnderTest->hasHeader('Content-Length'),
            'Header vanished'
        );

        $this->assertSame(
            [$streamLength],
            $this->subjectUnderTest->getHeader('Content-Length'),
            'Value changed'
        );
    }

    public function testObjectCanBeCastedToString()
    {
        $this->assertInternalType('string', (string)$this->subjectUnderTest);
    }

    public function testHeaderSectionContainsProperlyFormattedStatusLine()
    {
        $this->assertRegExp(
            "/^HTTP\/[0-1]\.[0-9] [0-9]{3} [A-Za-z0-9\s]+\r\n/",
            (string)$this->subjectUnderTest->getHeaderSection()
        );
    }

    public function testStatusLineInHeaderSectionContainsProperProtocolVersion()
    {
        $this->subjectUnderTest->setProtocolVersion(Response::HTTP_10);
        $this->assertStringStartsWith('HTTP/1.0', (string)$this->subjectUnderTest->getHeaderSection());

        $this->subjectUnderTest->setProtocolVersion(Response::HTTP_11);
        $this->assertStringStartsWith('HTTP/1.1', (string)$this->subjectUnderTest->getHeaderSection());

        $this->subjectUnderTest->setProtocolVersion('0.9');
        $this->assertStringStartsWith('HTTP/0.9', (string)$this->subjectUnderTest->getHeaderSection());
    }

    public function testStatusLineInHeaderSectionContainsProperCodeAndReasonPhrase()
    {
        $this->subjectUnderTest->setStatus(200, 'Foo');
        $this->assertContains('200 Foo', (string)$this->subjectUnderTest->getHeaderSection());

        $this->subjectUnderTest->setStatus(500, 'Bar');
        $this->assertContains('500 Bar', (string)$this->subjectUnderTest->getHeaderSection());
    }

    public function testHeaderSectionContainsAllHeadersDefined()
    {
        $expectedResult = [
            '/.*?/', //Status line format has it's own separate test
            "/^Server:\s?DerpServ\/0.99/",
            "/^Foo:\s?Bar$/",
            "/^Baz:\s?AaA$/",
            "/^Baz:\s?bBb$/",
            "/^Baz:\s?ccc/",
            "/^$/",
            "/^$/",
        ];

        $this->subjectUnderTest->setHeader('Server', 'DerpServ/0.99');
        $this->subjectUnderTest->setHeader('Foo', 'Bar');
        $this->subjectUnderTest->addHeader('Baz', 'AaA');
        $this->subjectUnderTest->addHeader('Baz', 'bBb');
        $this->subjectUnderTest->addHeader('Baz', 'ccc');


        $headerSection = explode("\r\n", $this->subjectUnderTest->getHeaderSection());
        $this->assertSame(count($expectedResult),
                          count($headerSection),
                          "Different number of lines returned than expected");

        foreach($headerSection as $lineIndex => $lineContents)
        {
            $this->assertRegExp($expectedResult[$lineIndex], $lineContents, "Response line #$lineIndex failed test");
        }
    }

    public function testHeaderSectionEndsWithEmptyLine()
    {
        $this->assertStringEndsWith("\r\n\r\n",
                                    (string)$this->subjectUnderTest->getHeaderSection(),
                                    'Header section need to end with empty line');
    }

    //@todo Add "Content-Length" magic tests
}
