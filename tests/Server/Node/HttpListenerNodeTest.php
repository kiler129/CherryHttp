<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Tests\Server\Node;

use noFlash\CherryHttp\Http\Node\HttpNodeFactoryInterface;
use noFlash\CherryHttp\IO\Network\AbstractNetworkListenerNode;
use noFlash\CherryHttp\Server\Node\HttpListenerNode;
use noFlash\CherryHttp\Server\Node\NodeFactoryInterface;
use noFlash\CherryHttp\Tests\TestHelpers\TestCase;

/**
 * @property HttpListenerNode subjectUnderTest
 */
class HttpListenerNodeTest extends TestCase
{
    public function setUp()
    {
        $this->subjectUnderTest = new HttpListenerNode();

        parent::setUp();
    }

    /**
     * @testdox Class extends AbstractNetworkListenerNode
     */
    public function testClassExtendsAbstractNetworkListenerNode()
    {
        $this->assertInstanceOf(AbstractNetworkListenerNode::class, $this->subjectUnderTest);
    }

    /**
     * @testdox Class implements TcpListenerNodeInterface
     */
    public function testClassImplementsTcpListenerNodeInterface()
    {
        $this->assertInstanceOf(
            \noFlash\CherryHttp\IO\Network\NetworkListenerNodeInterface::class,
            $this->subjectUnderTest
        );
    }

    /**
     * @testdox Class implements getStreamObject() method
     */
    public function testClassImplementsGetStreamObjectMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(HttpListenerNode::class, 'getStreamObject'));
    }

    /**
     * See docblock for that method to read why.
     *
     * @testdox getStreamObject() throws LogicException on call
     */
    public function testGetStreamObjectThrowsLogicExceptionCall()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Listeners do not provide stream objects');

        $this->subjectUnderTest->getStreamObject();
    }

    /**
     * @testdox Class implements doRead() method
     */
    public function testClassImplementsDoReadMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(HttpListenerNode::class, 'doRead'));
    }

    public function streamsProvider()
    {
        return [
            [0, false],
            [null, false],
            [false, false],
            [STDOUT, true],
        ];
    }

    /**
     * @testdox      doRead calls onStreamError if no valid stream is present
     * @dataProvider streamsProvider
     */
    public function testDoReadCallsOnStreamErrorIfNoValidStreamIsPresent($stream, $result)
    {
        /** @var HttpListenerNode|\PHPUnit_Framework_MockObject_MockObject $sut */
        $sut = $this->getMockBuilder(HttpListenerNode::class)->setMethods(['onStreamError'])->getMock();
        $sut->expects(
            ($result ? $this->never() : $this->once())
        )->method('onStreamError');

        $property = (new \ReflectionObject($sut))->getProperty('stream');
        $property->setAccessible(true);
        $property->setValue($sut, $stream);

        $this->assertTrue(true);
        $sut->doRead();
    }

    public function testFreshObjectContainsDefaultNodesFactory()
    {
        $this->assertInstanceOf(NodeFactoryInterface::class, $this->subjectUnderTest->getNodeFactory());
    }

    /**
     * @testdox Class implements getNodeFactory() method
     */
    public function testClassImplementsGetNodeFactoryMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(HttpListenerNode::class, 'getNodeFactory'));
    }

    /**
     * How does this test work? It's actually pretty easy.
     * Since we want to simulate child class it's good to create instance without calling constructor, set nodeFactory
     * and than call the constructor. It will be equivalent of creating stub extending HttpListenerNode with constructor
     * built like that:
     *
     *  public function __construct()
     *  {
     *      $this->setNodeFactory(new MyCustomFactory());
     *      parent::__construct();
     *  }
     */
    public function testClassConstructorWillNotOverwriteCustomNodesFactoryIfSetByChildClass()
    {
        $sutClassReflection = new \ReflectionClass(HttpListenerNode::class);
        /** @var HttpListenerNode $sutInstance */
        $sutInstance = $sutClassReflection->newInstanceWithoutConstructor();

        $this->assertNull($sutInstance->getNodeFactory(), 'nodeFactory is not NULL even without calling constructor');

        $httpNodeFactoryMock = $this->getMockForAbstractClass(HttpNodeFactoryInterface::class);
        $sutInstance->setNodeFactory($httpNodeFactoryMock);
        $sutInstance->__construct(); //parent::__construct();

        $this->assertSame($httpNodeFactoryMock, $sutInstance->getNodeFactory(), 'Constructor changed factory!');
    }

    /**
     * @testdox Class defines public setNodeFactory() method
     */
    public function testClassDefinesPublicSetNodeFactoryMethod()
    {
        $this->assertTrue($this->isMethodImplementedByClass(HttpListenerNode::class, 'setNodeFactory'));
        $this->assertTrue($this->subjectUnderTestObjectReflection->getMethod('setNodeFactory')->isPublic());
    }

    /**
     * @testdox setNodeFactory() will raise typehint-exception when object not implementing HttpNodeFactoryInterface is
     *          passed
     */
    public function testSetNodeFactoryWillRaiseTypehintExceptionWhenObjectNotImplementingHttpNodeFactoryInterfaceIsPassed()
    {
        $nodesFactoryMock = $this->getMockForAbstractClass(NodeFactoryInterface::class); //It's an easy mistake...

        $this->expectTypehintError();
        $this->subjectUnderTest->setNodeFactory($nodesFactoryMock);
    }
}
