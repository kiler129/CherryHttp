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

use noFlash\CherryHttp\Application\Lifecycle\LoopNodeInterface;
use noFlash\CherryHttp\Http\HttpNodeInterface;
use noFlash\CherryHttp\Http\Node\HttpNodeFactory;
use noFlash\CherryHttp\Server\Node\NodeFactoryInterface;
use noFlash\CherryHttp\Tests\TestHelpers\CloneAwareHttpNode;
use noFlash\CherryHttp\Tests\TestHelpers\TestCase;

/**
 * @property HttpNodeFactory subjectUnderTest
 */
class HttpNodeFactoryTest extends TestCase
{
    /**
     * @testdox Class implements NodeFactoryInterface
     */
    public function testClassImplementsNodeFactoryInterface()
    {
        $this->assertInstanceOf(NodeFactoryInterface::class, $this->subjectUnderTest);
    }

    /**
     * @testdox Class constructor rejects base nodes not implementing HttpNodeInterface
     */
    public function testClassConstructorRejectsBaseNodesNotImplementingHttpNodeInterface()
    {
        $baseObjectMock = $this->getMockForAbstractClass(LoopNodeInterface::class);

        $this->expectTypehintError();
        $this->subjectUnderTest = new HttpNodeFactory($baseObjectMock);
    }

    /**
     * @testdox getNode() method produces objects implementing HttpNodeInterface
     */
    public function testGetNodeMethodProducesObjectsImplementingHttpNodeInterface()
    {
        $this->markTestIncomplete('There\'s no implementation for HttpNodeInterface');

        $this->assertInstanceOf(HttpNodeInterface::class, $this->subjectUnderTest->getNode());
    }

    public function testBaseNodeIsClonedByConstructor()
    {
        /** @var CloneAwareHttpNode $baseNode */
        $baseNode = $this->getMockBuilder(CloneAwareHttpNode::class)->getMockForAbstractClass();
        $baseNode->_publicField = 'I am the original mock object';

        $this->subjectUnderTest = new HttpNodeFactory($baseNode); //This method should clone the object
        $baseNode->_publicField = 'I was changed after set'; //...so this change will not reflect it

        /** @var CloneAwareHttpNode $sutBaseNode */
        $sutBaseNode = $this->subjectUnderTest->getNode(); //This method also should clone object internally
        $this->assertSame('I am the original mock object', $sutBaseNode->_publicField); //Check if cloned on set
        $this->assertSame(2, $sutBaseNode->_getCloneNumber()); //1 for __construct, 1 for get
    }

    public function testFactoredNodeIsAlwaysNewObject()
    {
        //TODO: remove these two lines after fixing testGetNodeMethodProducesObjectsImplementingHttpNodeInterface()
        $baseNode = $this->getMockForAbstractClass(HttpNodeInterface::class);
        $this->subjectUnderTest = new HttpNodeFactory($baseNode);

        $instance1 = $this->subjectUnderTest->getNode();
        $instance2 = $this->subjectUnderTest->getNode();

        $this->assertEquals($instance1, $instance2);
        $this->assertNotSame($instance1, $instance2);
    }

    protected function setUp()
    {
        $this->subjectUnderTest = new HttpNodeFactory();

        parent::setUp();
    }
}
