<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Tests\Http\Request;

use noFlash\CherryHttp\Http\Request\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    private $subjectUnderTest;

    public function setUp()
    {
        $this->subjectUnderTest = new Request();
    }

    public function testHeadIsDefaultMethod()
    {
        $this->assertSame('HEAD', $this->subjectUnderTest->getMethod());
    }

    public function validMethodsProvider()
    {
        return [
            ['GET'], //RFC7231
            ['HEAD'], //RFC7231
            ['POST'], //RFC7231
            ['PUT'], //RFC7231
            ['DELETE'], //RFC7231
            ['CONNECT'], //RFC7231
            ['OPTIONS'], //RFC7231
            ['TRACE'], //RFC7231
            ['DERP'] //Custom method
        ];
    }

    /**
     * @dataProvider validMethodsProvider
     */
    public function testValidMethodsAreAccepted($method)
    {
        $this->subjectUnderTest->setMethod($method);
        $this->assertSame($method, $this->subjectUnderTest->getMethod());
    }

    public function testMethodNameIsAlwaysReturnedUppercased()
    {
        $this->subjectUnderTest->setMethod('get');
        $this->assertSame('GET', $this->subjectUnderTest->getMethod());

        $this->subjectUnderTest->setMethod('PoSt');
        $this->assertSame('POST', $this->subjectUnderTest->getMethod());

        $this->subjectUnderTest->setMethod('cUSTOm-MeTHoD');
        $this->assertSame('CUSTOM-METHOD', $this->subjectUnderTest->getMethod());

        $this->subjectUnderTest->setMethod(123); //It's NOT defined behaviour and may change
        $this->assertSame('123', $this->subjectUnderTest->getMethod());
    }

    /*
     * @testdox Default RequestTarget points to "/"
     */
    public function testDefaultRequestTargetPointsToSlash()
    {
        $this->assertSame('/', $this->subjectUnderTest->getRequestTarget());
    }

    public function testRequestTargetCanBeSetToPlainPath()
    {
        $this->subjectUnderTest->setRequestTarget('/');
        $this->assertSame('/', $this->subjectUnderTest->getRequestTarget());

        $this->subjectUnderTest->setRequestTarget('/test');
        $this->assertSame('/test', $this->subjectUnderTest->getRequestTarget());

        $this->subjectUnderTest->setRequestTarget('/test/foo/');
        $this->assertSame('/test/foo/', $this->subjectUnderTest->getRequestTarget());

        $this->subjectUnderTest->setRequestTarget('/test/foo/bar/');
        $this->assertSame('/test/foo/bar/', $this->subjectUnderTest->getRequestTarget());
    }

    public function testRequestTargetCanBeSetToPathWithQueryString()
    {
        $this->subjectUnderTest->setRequestTarget('/?x=1');
        $this->assertSame('/?x=1', $this->subjectUnderTest->getRequestTarget());

        $this->subjectUnderTest->setRequestTarget('/test?y=123&z=13');
        $this->assertSame('/test?y=123&z=13', $this->subjectUnderTest->getRequestTarget());

        $this->subjectUnderTest->setRequestTarget('/test/foo/?x=%22x');
        $this->assertSame('/test/foo/?x=%22x', $this->subjectUnderTest->getRequestTarget());

        $this->subjectUnderTest->setRequestTarget('/test/foo/bar/?x=foo&bar=xx&xx=qq&derp');
        $this->assertSame('/test/foo/bar/?x=foo&bar=xx&xx=qq&derp', $this->subjectUnderTest->getRequestTarget());
    }

    public function testRequestTargerWithoutSlashPrefixCanBeSet()
    {
        $this->subjectUnderTest->setRequestTarget('@foo=1');
        $this->assertSame('@foo=1', $this->subjectUnderTest->getRequestTarget());
    }

    public function testEmptyRequestTargetCannotBeSet()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->subjectUnderTest->setRequestTarget('');
    }

    public function testRequestTargetRejectsValueContainingSpaces()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->subjectUnderTest->setRequestTarget('/foo/b ar/');
    }

    public function testRequestTargetRejectsValuesContainingHash()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->subjectUnderTest->setRequestTarget('/foo/bar.html#derp');
    }

    /*
     * @testdox Default path points to "/"
     */
    public function testDefaultPathPointsToSlash()
    {
        $this->assertSame('/', $this->subjectUnderTest->getPath());
    }

    public function testEmptyPathCannotBeSet()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->subjectUnderTest->setPath('');
    }

    public function testPlainPathCanBeSet()
    {
        $this->subjectUnderTest->setPath('/');
        $this->assertSame('/', $this->subjectUnderTest->getPath());

        $this->subjectUnderTest->setPath('/test');
        $this->assertSame('/test', $this->subjectUnderTest->getPath());

        $this->subjectUnderTest->setPath('/test/foo/');
        $this->assertSame('/test/foo/', $this->subjectUnderTest->getPath());

        $this->subjectUnderTest->setPath('/test/foo/bar/');
        $this->assertSame('/test/foo/bar/', $this->subjectUnderTest->getPath());
    }

    public function testPathWithoutSlashPrefixCanBeSet()
    {
        $this->subjectUnderTest->setPath('*');
        $this->assertSame('*', $this->subjectUnderTest->getPath());
    }

    public function testPathRejectsValueContainingSpaces()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->subjectUnderTest->setPath('/foo/b ar/');
    }

    public function testPathRejectsValuesContainingQuestionMark()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->subjectUnderTest->setPath('/foo/bar.html?derp');
    }

    public function testPathRejectsValuesContainingHash()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->subjectUnderTest->setPath('/foo/bar.html#derp');
    }

    public function testQueryStringIsEmptyByDefault()
    {
        $this->assertSame('', $this->subjectUnderTest->getQueryString());
    }

    public function testEmptyQueryStringCanBeSet()
    {
        $this->subjectUnderTest->setQueryString('bazz=bar');
        $this->subjectUnderTest->setQueryString('');
        $this->assertSame('', $this->subjectUnderTest->getQueryString());
    }

    public function testQueryStringCanBeSet()
    {
        $this->subjectUnderTest->setQueryString('foo=1');
        $this->assertSame('foo=1', $this->subjectUnderTest->getQueryString());

        $this->subjectUnderTest->setQueryString('bar=bazz&aaa=b');
        $this->assertSame('bar=bazz&aaa=b', $this->subjectUnderTest->getQueryString());
    }

    public function testQueryStringRejectsValueContainingSpaces()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->subjectUnderTest->setQueryString('foo = bar');
    }

    public function testQueryStringRejectsValuesContainingQuestionMarkAtTheBeginning()
    {
        $this->subjectUnderTest->setQueryString('foo?x=1&bar=2');
        $this->assertSame('foo?x=1&bar=2', $this->subjectUnderTest->getQueryString());

        $this->setExpectedException('\InvalidArgumentException');
        $this->subjectUnderTest->setQueryString('?oops=1');
    }

    public function testQueryStringRejectsValuesContainingHash()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->subjectUnderTest->setQueryString('foo=1&bar=2#derp');
    }

    public function testCorrectPathCanBeRetrievedAfterSettingRequestTarget()
    {
        $this->subjectUnderTest->setRequestTarget('/test/foo/');
        $this->assertSame('/test/foo/', $this->subjectUnderTest->getPath());

        $this->subjectUnderTest->setRequestTarget('/');
        $this->assertSame('/', $this->subjectUnderTest->getPath());

        $this->subjectUnderTest->setRequestTarget('/test?y=123&z=13');
        $this->assertSame('/test', $this->subjectUnderTest->getPath());

        $this->subjectUnderTest->setRequestTarget('/foo?');
        $this->assertSame('/foo', $this->subjectUnderTest->getPath());
    }

    public function testSettingNewRequestTargetWithoutQueryStringClearsPreviouslySetQueryString()
    {
        $this->subjectUnderTest->setQueryString('a=b');
        $this->subjectUnderTest->setRequestTarget('/');
        $this->assertSame('', $this->subjectUnderTest->getQueryString());
    }

    public function testRequestTargetProperlyExtractsPathIfTwoQuestionMarksArePresent()
    {
        $this->subjectUnderTest->setRequestTarget('/foo.html?a=1&b=?c=3');

        $this->assertSame('/foo.html', $this->subjectUnderTest->getPath());
        $this->assertSame('a=1&b=?c=3', $this->subjectUnderTest->getQueryString());
        $this->assertSame('/foo.html?a=1&b=?c=3', $this->subjectUnderTest->getRequestTarget());
    }

    public function testRequestTargetIsProperlyGeneratedFromPathAndQueryString()
    {
        $this->subjectUnderTest->setPath('/fooooooooo');
        $this->assertSame('/fooooooooo', $this->subjectUnderTest->getRequestTarget());

        $this->subjectUnderTest->setQueryString('derp=bazz');
        $this->assertSame('/fooooooooo?derp=bazz', $this->subjectUnderTest->getRequestTarget());

        $this->subjectUnderTest->setPath('/x');
        $this->assertSame('/x?derp=bazz', $this->subjectUnderTest->getRequestTarget());
    }
}
