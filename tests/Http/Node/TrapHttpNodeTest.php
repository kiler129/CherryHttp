<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Tests\Http\Node;

use noFlash\CherryHttp\Http\Node\TrapHttpNode;
use noFlash\CherryHttp\IO\Stream\BufferAwareAbstractStreamNode;
use noFlash\CherryHttp\Tests\TestHelpers\TestCase;

/**
 * @property TrapHttpNode subjectUnderTest
 */
class TrapHttpNodeTest extends TestCase
{
    /**
     * @testdox Class extends BufferAwareAbstractStreamNode
     */
    public function testClassExtendsBufferAwareAbstractStreamNode()
    {
        $this->assertInstanceOf(BufferAwareAbstractStreamNode::class, $this->subjectUnderTest);
    }

    /**
     * We should not add response right away - replying to just opened connections is silly
     */
    public function testNodeIsInitiallyNotWriteReady()
    {
        $this->assertFalse($this->subjectUnderTest->isWriteReady());
    }

    /**
     * @see testNodeIsNotInitiallyWriteReady()
     */
    public function testNodeWriteBufferIsEmptyByDefault()
    {
        $this->assertEmpty($this->getRestrictedPropertyValue('writeBuffer'));
    }

    public function testCorrectErrorResponseIsGeneratedAfterRequest()
    {
        $testServer = $this->createDummyServerWithClient();

        $this->safeWrite($testServer['clientOnClient'], "GET / HTTP/1.0\r\n\r\n");

        $this->subjectUnderTest->stream = $testServer['clientOnServer'];
        $this->subjectUnderTest->doRead();

        $this->assertTrue($this->subjectUnderTest->isWriteReady(), 'Node is not write ready after reading request');
        $this->subjectUnderTest->doWrite();

        $responseContents = stream_get_contents($testServer['clientOnClient']);

        $this->assertRegExp(
            '/' . "HTTP\\/\\d\\.\\d 501 Not Implemented\r\n" . '.*?' . //Any headers
            "\r\n\r\n" . '.*?This is a trap.<br\/>Configure me..*?' . //Contents wrapped into some other HTML
            '/s',
            $responseContents
        );
    }

    protected function setUp()
    {
        $this->subjectUnderTest = new TrapHttpNode();

        parent::setUp();
    }
}
