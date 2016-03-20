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

use noFlash\CherryHttp\Http\Response\AutogeneratedResponseTrait;

class AutogeneratedResponseTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AutogeneratedResponseTrait
     */
    private $subjectUnderTest;

    public function setUp()
    {
        $this->subjectUnderTest = $this->getMockBuilder(AutogeneratedResponseTrait::class)->getMockForTrait();
    }

    /**
     * @testdox HTML content is generated by generateAutomaticResponseContent()
     */
    public function testHtmlContentIsGeneratedByGenerateAutomaticResponseContent()
    {
        $this->assertRegExp(
            '/^\<html.*?\>\<\/html\>/is',
            $this->subjectUnderTest->generateAutomaticResponseContent('', '')
        );
    }

    /**
     * @testdox Generated response content contains specified code inside <h1> tag
     */
    public function testGeneratedResponseContentContainsSpecifiedCodeInsideH1Tag()
    {
        $this->assertRegExp(
            '/\<h1.*?\>\s*?404\s*?\<\/h1\>/is',
            $this->subjectUnderTest->generateAutomaticResponseContent('404', '')
        );

        $this->assertRegExp(
            '/\<h1.*?\>\s*?500\s*?\<\/h1\>/is',
            $this->subjectUnderTest->generateAutomaticResponseContent('500', '')
        );

        $this->assertRegExp(
            '/\<h1.*?\>\s*?0\s*?\<\/h1\>/is',
            $this->subjectUnderTest->generateAutomaticResponseContent('0', '')
        );

        $this->assertRegExp(
            '/\<h1.*?\>\s*?101\s*?\<\/h1\>/is',
            $this->subjectUnderTest->generateAutomaticResponseContent(101, '')
        );


        //It could be also a custom code, not a HTTP one
        $this->assertRegExp(
            '/\<h1.*?\>\s*?#189.21\s*?\<\/h1\>/is',
            $this->subjectUnderTest->generateAutomaticResponseContent('#189.21', '')
        );
    }

    /**
     * @testdox Generated response content contains specified reason phrase inside <h2> tag
     */
    public function testGeneratedResponseContentContainsSpecifiedReasonPhraseInsideH2Tag()
    {
        $this->assertRegExp(
            '/\<h2.*?\>\s*?Not Found\s*?\<\/h2\>/is',
            $this->subjectUnderTest->generateAutomaticResponseContent('', 'Not Found')
        );

        $this->assertRegExp(
            '/\<h2.*?\>\s*?Internal Server Error\s*?\<\/h2\>/is',
            $this->subjectUnderTest->generateAutomaticResponseContent('', 'Internal Server Error')
        );

        $this->assertRegExp(
            '/\<h2.*?\>\s*?Derp Derp\s*?\<\/h2\>/is',
            $this->subjectUnderTest->generateAutomaticResponseContent('', 'Derp Derp')
        );
    }

    public function testGeneratedResponseContentContainsTitleSpecified()
    {
        $this->assertRegExp(
            '/\<title.*?\>\s*?Test Title\s*?\<\/title\>/is',
            $this->subjectUnderTest->generateAutomaticResponseContent('', '', 'Test Title')
        );

        $this->assertRegExp(
            '/\<title.*?\>\s*?This is FooBazz title inside html title tag\s*?\<\/title\>/is',
            $this->subjectUnderTest->generateAutomaticResponseContent(
                '',
                '',
                'This is FooBazz title inside html title tag'
            )
        );

        $this->assertRegExp(
            '/\<title.*?\>\s*?\<\/title\>/is',
            $this->subjectUnderTest->generateAutomaticResponseContent('', '', '')
        );
    }

    public function testGeneratedResponseContentContainsTitleGeneratedFromCodeAndReasonPhrase()
    {
        $this->assertRegExp(
            '/\<title.*?\>\s*?404 Not Found\s*?\<\/title\>/is',
            $this->subjectUnderTest->generateAutomaticResponseContent('404', 'Not Found')
        );

        $this->assertRegExp(
            '/\<title.*?\>\s*?500\s*?\<\/title\>/is',
            $this->subjectUnderTest->generateAutomaticResponseContent('500', '')
        );

        $this->assertRegExp(
            '/\<title.*?\>\s*?Boo hoo\s*?\<\/title\>/is',
            $this->subjectUnderTest->generateAutomaticResponseContent('', 'Boo hoo')
        );
    }
}
