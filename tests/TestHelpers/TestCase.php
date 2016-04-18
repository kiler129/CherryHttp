<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Tests\TestHelpers;

/**
 * General TestCase class used for test requiring extra helper methods
 *
 * Hmm... maybe I should do tests for this class too even if it wasn't written using TDD?
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    const MAX_SAFE_WRITE_TRIES = 100;

    protected $subjectUnderTest;

    /**
     * @var \ReflectionObject
     */
    protected $subjectUnderTestObjectReflection;

    protected $streamsToDestroy = [];

    /**
     * Allows cataching typehint errors on PHP <7 & >=7
     *
     *
     * For explanation refer to links below:
     * - http://stackoverflow.com/questions/25570786/how-to-unit-test-type-hint-with-phpunit
     * - https://github.com/sebastianbergmann/phpunit/issues/178
     */
    public function expectTypehintError()
    {
        $className = (PHP_MAJOR_VERSION < 7) ? get_class(new \PHPUnit_Framework_Error("", 0, "", 1)) : '\TypeError';
        $this->expectException($className);
    }

    protected function isOSX()
    {
        return (PHP_OS === 'Darwin');
    }

    protected function setUp()
    {
        $this->subjectUnderTestObjectReflection = new \ReflectionObject($this->subjectUnderTest);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        foreach ($this->streamsToDestroy as $stream) {
            @stream_socket_shutdown($stream, STREAM_SHUT_RDWR);
            @fclose($stream);
        }

        gc_collect_cycles();
        parent::tearDown();
    }

    protected function getRestrictedPropertyValue($name)
    {
        if (!$this->subjectUnderTestObjectReflection->hasProperty($name)) {
            throw new \RuntimeException('There is no property named ' . $name);
        }

        $property = $this->subjectUnderTestObjectReflection->getProperty($name);
        $property->setAccessible(true);

        return $property->getValue($this->subjectUnderTest);
    }

    protected function setRestrictedPropertyValue($name, $value)
    {
        if (!$this->subjectUnderTestObjectReflection->hasProperty($name)) {
            throw new \RuntimeException('There is no property named ' . $name);
        }

        $property = $this->subjectUnderTestObjectReflection->getProperty($name);
        $property->setAccessible(true);
        $property->setValue($this->subjectUnderTest, $value);
    }

    protected function markTestSkippedIfNoIpV6TestEnvironment()
    {
        //Method found at https://github.com/symfony/http-foundation/blob/master/IpUtils.php#L100
        if (!((extension_loaded('sockets') && defined('AF_INET6')) || @inet_pton('::1'))) {
            $this->markTestSkipped('Unable to check IPv6. Check that PHP was not compiled with option "disable-ipv6".');
        }
    }

    protected function assertClassImplementsMethod($expectedMethodName, $className)
    {
        $this->assertTrue(
            $this->isMethodImplementedByClass($className, $expectedMethodName),
            "Class does not implement \"$expectedMethodName\" method"
        );
    }

    protected function isMethodImplementedByClass($className, $methodName)
    {
        //This is, I believe, the only method to really check if abstract class implementing interface has a method

        $subjectUnderTestClassReflection = new \ReflectionClass(
            $className
        ); //Existing object reflection cannot be used due to possible mocking

        if (!$subjectUnderTestClassReflection->hasMethod($methodName)) {
            return false;
        }

        $methodReflection = $subjectUnderTestClassReflection->getMethod($methodName);

        return ($methodReflection->getDeclaringClass()->name === $className);
    }

    protected function assertIsAbstractClass($className)
    {
        $this->assertTrue(class_exists($className), 'Given class does not exists');

        $classReflection = new \ReflectionClass($className);
        $this->assertTrue($classReflection->isAbstract(), 'Class is not abstract');
    }
    
    protected function createDummyServerWithClient()
    {
        $server = stream_socket_server('tcp://127.0.0.1:0');
        $this->assertInternalType('resource', $server, 'Failed to start test server');

        $address = stream_socket_get_name($server, false);
        $this->assertNotFalse($address, 'Failed to obtain test server address');

        $clientOnClient = stream_socket_client('tcp://' . $address);
        $this->assertInternalType('resource', $clientOnClient, 'Failed to create client socket');

        $clientOnServer = stream_socket_accept($server, 5);
        $this->assertInternalType('resource', $clientOnServer, 'Failed to accept client');

        $this->streamsToDestroy[] = $server;
        $this->streamsToDestroy[] = $clientOnServer;
        $this->streamsToDestroy[] = $clientOnClient;

        return ['server' => $server, 'clientOnServer' => $clientOnServer, 'clientOnClient' => $clientOnClient];
    }

    protected function skipTestOnHHVM($info = 'See test comments for details')
    {
        if ($this->isHHVM()) {
            $this->markTestSkipped($info);
        }
    }

    public function isHHVM()
    {
        return defined('HHVM_VERSION');
    }

    protected function skipTestOnLinux($info = 'See test comments for details')
    {
        if ($this->isLinux()) {
            $this->markTestSkipped($info);
        }
    }

    protected function isLinux()
    {
        return (PHP_OS === 'Linux');
    }

    /**
     * Method used instead of classic fwrite() when there's a risk that fread() will be performed too fast for OS
     *  network stack to delivery data (yes, it was replicated many times).
     */
    protected function safeWrite($stream, $data)
    {
        for ($i = 0; $i < self::MAX_SAFE_WRITE_TRIES; $i++) {
            $bytesWritten = fwrite($stream, $data);
            $data = substr($data, $bytesWritten);
            usleep(100000);

            if (empty($data)) {
                return;
            }
        }

        $this->fail('safeWrite() reached max count writing before writting full data set');
    }

}
