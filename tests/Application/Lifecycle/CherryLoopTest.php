<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Tests\Application\Lifecycle;

use noFlash\CherryHttp\Application\Exception\NodeConflictException;
use noFlash\CherryHttp\Application\Exception\NodeNotFoundException;
use noFlash\CherryHttp\Application\Lifecycle\CherryLoop;
use noFlash\CherryHttp\Application\Lifecycle\LoopInterface;
use noFlash\CherryHttp\Application\Lifecycle\LoopNodeInterface;
use noFlash\CherryHttp\Tests\TestHelpers\TestCase;

/**
 * @property CherryLoop subjectUnderTest
 */
class CherryLoopTest extends TestCase
{
    public function setUp()
    {
        $this->subjectUnderTest = new CherryLoop();

        parent::setUp();
    }

    /**
     * @inheritdoc Class implements LoopInterface
     */
    public function testClassImplementsLoopInterface()
    {
        $this->assertInstanceOf(LoopInterface::class, $this->subjectUnderTest);
    }

    public function testNodeCanBeAttachedToLoop()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|LoopNodeInterface $nodeMock */
        $nodeMock = $this->getMockForAbstractClass(LoopNodeInterface::class);
        $nodeMock->expects($this->once())->method('onAttach')->with($this->subjectUnderTest);

        $this->assertTrue($this->subjectUnderTest->attachNode($nodeMock));
    }

    public function testMultipleNodesCanBeAttachedToOneLoop()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|LoopNodeInterface $nodeMock1 */
        $nodeMock1 = $this->getMockForAbstractClass(LoopNodeInterface::class);
        $nodeMock1->expects($this->once())->method('onAttach')->with($this->subjectUnderTest);

        /** @var \PHPUnit_Framework_MockObject_MockObject|LoopNodeInterface $nodeMock2 */
        $nodeMock2 = $this->getMockForAbstractClass(LoopNodeInterface::class);
        $nodeMock2->expects($this->once())->method('onAttach')->with($this->subjectUnderTest);

        /** @var \PHPUnit_Framework_MockObject_MockObject|LoopNodeInterface $nodeMock3 */
        $nodeMock3 = $this->getMockForAbstractClass(LoopNodeInterface::class);
        $nodeMock3->expects($this->once())->method('onAttach')->with($this->subjectUnderTest);

        $this->assertTrue($this->subjectUnderTest->attachNode($nodeMock1));
        $this->assertTrue($this->subjectUnderTest->attachNode($nodeMock2));
        $this->assertTrue($this->subjectUnderTest->attachNode($nodeMock3));
    }

    public function testTheSameNodeCanBeAttachedToLoopOnlyOnce()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|LoopNodeInterface $nodeMock */
        $nodeMock = $this->getMockForAbstractClass(LoopNodeInterface::class);
        $nodeMock->expects($this->once())->method('onAttach')->with($this->subjectUnderTest);

        $this->subjectUnderTest->attachNode($nodeMock);

        $this->expectException(NodeConflictException::class);
        $this->subjectUnderTest->attachNode($nodeMock);
    }

    /**
     * @testdox attachNode() method accepts only LoopNodeInterface objects
     */
    public function testAttachNodeMethodAcceptsOnlyLoopNodeInterfaceObjects()
    {
        $this->expectTypehintError();
        $this->subjectUnderTest->attachNode(new \stdClass());
    }

    public function testNodeCanBeDetachedFromLoop()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|LoopNodeInterface $nodeMock */
        $nodeMock = $this->getMockForAbstractClass(LoopNodeInterface::class);
        $nodeMock->expects($this->once())->method('onDetach');

        $this->subjectUnderTest->attachNode($nodeMock);
        $this->assertTrue($this->subjectUnderTest->detachNode($nodeMock));
    }

    public function testMultipleNodesCanBeDetachedFromOneLoop()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|LoopNodeInterface $nodeMock1 */
        $nodeMock1 = $this->getMockForAbstractClass(LoopNodeInterface::class);
        $nodeMock1->expects($this->once())->method('onDetach');

        /** @var \PHPUnit_Framework_MockObject_MockObject|LoopNodeInterface $nodeMock2 */
        $nodeMock2 = $this->getMockForAbstractClass(LoopNodeInterface::class);
        $nodeMock2->expects($this->once())->method('onDetach');

        /** @var \PHPUnit_Framework_MockObject_MockObject|LoopNodeInterface $nodeMock3 */
        $nodeMock3 = $this->getMockForAbstractClass(LoopNodeInterface::class);
        $nodeMock3->expects($this->once())->method('onDetach');


        $this->subjectUnderTest->attachNode($nodeMock1);
        $this->subjectUnderTest->attachNode($nodeMock2);
        $this->subjectUnderTest->attachNode($nodeMock3);

        $this->assertTrue($this->subjectUnderTest->detachNode($nodeMock1));
        $this->assertTrue($this->subjectUnderTest->detachNode($nodeMock2));
        $this->assertTrue($this->subjectUnderTest->detachNode($nodeMock3));
    }

    public function testExceptionIsThrownWhenTryingToAttachNotAttached()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|LoopNodeInterface $nodeMock1 */
        $nodeMock1 = $this->getMockForAbstractClass(LoopNodeInterface::class);

        /** @var \PHPUnit_Framework_MockObject_MockObject|LoopNodeInterface $nodeMock2 */
        $nodeMock2 = $this->getMockForAbstractClass(LoopNodeInterface::class);

        $this->subjectUnderTest->attachNode($nodeMock1);

        $this->expectException(NodeNotFoundException::class);
        $this->subjectUnderTest->detachNode($nodeMock2);
    }
}
