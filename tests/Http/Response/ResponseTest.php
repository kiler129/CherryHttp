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

    public function testResponseIsCreatedWithNoContentCodeAndRespectivePhraseByDefault()
    {
        $defaultCode = ResponseCode::NO_CONTENT;
        $defaultReasonPhrase = ResponseCode::getReasonPhraseByCode($defaultCode);

        $this->assertSame(ResponseCode::NO_CONTENT, $this->subjectUnderTest->getStatusCode());
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
}
