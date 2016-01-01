<?php
/*
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible
 *  for everything that code will do.
 *
 * (c) Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace noFlash\CherryHttp\Tests\Server\Exception;

use noFlash\CherryHttp\Application\Lifecycle\LoopInterface;
use noFlash\CherryHttp\Server\Exception\ServerException;
use noFlash\CherryHttp\Tests\TestHelpers\TypehintTestHelperTrait;

class ServerExceptionTest extends \PHPUnit_Framework_TestCase
{
    use TypehintTestHelperTrait;

    /**
     * @var ServerException
     */
    private $subjectUnderTest;

    public function setUp()
    {
        $this->subjectUnderTest = new ServerException();
    }

    public function testClassExtendsCorrectExceptionClasses()
    {
        $serverExceptionReflection = new \ReflectionClass(ServerException::class);
        $this->assertTrue($serverExceptionReflection->isSubclassOf('\RuntimeException'));
        $this->assertTrue($serverExceptionReflection->isSubclassOf('\Exception'));
    }

    public function testLoopIsNullByDefault()
    {
        $this->assertNull($this->subjectUnderTest->getLoop());
    }

    public function testLoopIsHoldByObject()
    {
        /** @var LoopInterface $loop */
        $loop = $this->getMock(LoopInterface::class);

        $this->subjectUnderTest->setLoop($loop);
        $this->assertSame($loop, $this->subjectUnderTest->getLoop());
    }

    public function testLoopSetterPerformsTypechecking()
    {
        $this->setExpectedTypehintError();

        $this->subjectUnderTest->setLoop(new \stdClass());
    }
}
