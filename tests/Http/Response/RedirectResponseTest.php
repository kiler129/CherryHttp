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

use noFlash\CherryHttp\Http\Response\RedirectResponse;
use noFlash\CherryHttp\Http\Response\ResponseCode;
use noFlash\CherryHttp\Http\Response\ResponseInterface;

class RedirectResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RedirectResponse
     */
    private $subjectUnderTest;

    public function setUp()
    {
        $this->subjectUnderTest = new RedirectResponse();
    }

    public function testObjectImplementsResponseInterface()
    {
        $this->assertInstanceOf(ResponseInterface::class, $this->subjectUnderTest);
    }

    public function testFreshInstanceContainsTemporaryRedirectWithIanaReasonPhrase()
    {
        $expectedCode = ResponseCode::TEMPORARY_REDIRECT;
        $expectedReasonPhrase = ResponseCode::getReasonPhraseByCode($expectedCode);

        $this->assertSame($expectedCode, $this->subjectUnderTest->getStatusCode());
        $this->assertSame($expectedReasonPhrase, $this->subjectUnderTest->getReasonPhrase());
    }

    public function testFreshInstanceRedirectsToAboutBlankUsingLocationHeader()
    {
        $this->assertTrue($this->subjectUnderTest->hasHeader('location'));
        $this->assertSame('about:blank', $this->subjectUnderTest->getHeader('location')[0]);
    }

    public function testLocationCanBeSetUsingDedicatedMethod()
    {
        $this->subjectUnderTest->setLocation('http://example.com');
        $this->assertTrue($this->subjectUnderTest->hasHeader('location'));
        $this->assertSame('http://example.com', $this->subjectUnderTest->getHeader('location')[0]);

        $this->subjectUnderTest->setLocation('http://noFlash.pl');
        $this->assertTrue($this->subjectUnderTest->hasHeader('location'));
        $this->assertSame('http://noFlash.pl', $this->subjectUnderTest->getHeader('location')[0]);

        $this->subjectUnderTest->setLocation('/test');
        $this->assertTrue($this->subjectUnderTest->hasHeader('location'));
        $this->assertSame('/test', $this->subjectUnderTest->getHeader('location')[0]);

        $this->subjectUnderTest->setLocation('/derp#foo');
        $this->assertTrue($this->subjectUnderTest->hasHeader('location'));
        $this->assertSame('/derp#foo', $this->subjectUnderTest->getHeader('location')[0]);

        $this->subjectUnderTest->setLocation('https://127.0.0.1/foo/bazz.html');
        $this->assertTrue($this->subjectUnderTest->hasHeader('location'));
        $this->assertSame('https://127.0.0.1/foo/bazz.html', $this->subjectUnderTest->getHeader('location')[0]);
    }

    public function testLocationSetUsingDedicatedMethodCanBeOverwrittenUsingHeaders()
    {
        $this->subjectUnderTest->setLocation('http://example.com');
        $this->subjectUnderTest->setHeader('location', 'https://example.org');
        $this->assertSame('https://example.org', $this->subjectUnderTest->getHeader('location')[0]);
    }

    public function testLocationSetUsingHeadersCanBeOverwrittenUsingDedicatedMethod()
    {
        $this->subjectUnderTest->setHeader('location', 'https://example.tld');
        $this->subjectUnderTest->setLocation('http://example.sh');
        $this->assertSame('http://example.sh', $this->subjectUnderTest->getHeader('location')[0]);
    }

    public function redirectCodesProvider()
    {
        return [
            [ResponseCode::MULTIPLE_CHOICES],
            [ResponseCode::MOVED_PERMANENTLY],
            [ResponseCode::FOUND],
            [ResponseCode::SEE_OTHER],
            [ResponseCode::USE_PROXY],
            [ResponseCode::SWITCH_PROXY],
            [ResponseCode::TEMPORARY_REDIRECT],
            [ResponseCode::PERMANENT_REDIRECT],
            [378], //Possible future codes
            [399]
        ];
    }

    /**
     * @dataProvider redirectCodesProvider
     */
    public function testStatusAcceptsAllRedirectCodes($code)
    {
        $this->subjectUnderTest->setStatus($code);
        $this->assertSame($code, $this->subjectUnderTest->getStatusCode());
    }

    public function nonRedirectCodesProvider()
    {
        return [
            [ResponseCode::CONTINUE_INFORMATION],
            [ResponseCode::OK],
            [ResponseCode::NOT_MODIFIED],
            [ResponseCode::FORBIDDEN],
            [ResponseCode::BAD_GATEWAY],
            [652],
            [781],
            [833],
            [911]
        ];
    }

    /**
     * @dataProvider nonRedirectCodesProvider
     */
    public function testStatusRejectsNonRedirectCodes($code)
    {
        $this->expectException(\LogicException::class);
        $this->subjectUnderTest->setStatus($code);
    }

    public function testFreshInstanceContainsHtmlContentType()
    {
        $this->assertTrue($this->subjectUnderTest->hasHeader('content-type'));
        $this->assertStringStartsWith('text/html', $this->subjectUnderTest->getHeader('content-type')[0]);
    }

    public function testHtmlBodyIsAutogeneratedIfNotSetAndLocationIsPresent()
    {
        $body = $this->subjectUnderTest->getBody();
        $this->assertNotEmpty($body);
        $this->assertRegExp('/^\<html.*?\>\<\/html\>/is', $body);

        $this->subjectUnderTest->setBody('foo');
        $this->assertSame('foo', $this->subjectUnderTest->getBody());

        $this->subjectUnderTest->setBody(null);
        $body = $this->subjectUnderTest->getBody();
        $this->assertNotEmpty($body);
        $this->assertRegExp('/^\<html.*?\>\<\/html\>/is', $body);

        $this->subjectUnderTest->unsetHeader('location');
        $this->assertEmpty($this->subjectUnderTest->getBody());
    }

    public function testAutoGeneratedHtmlContainsLinkToSpecifiedLocation()
    {
        $this->subjectUnderTest->setLocation('ftp://example.org');
        $this->assertRegExp('/\<a.*?href="ftp:\/\/example.org"/is', $this->subjectUnderTest->getBody());
    }
}
