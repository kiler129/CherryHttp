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
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    const MAX_SAFE_WRITE_TRIES = 100;

    protected $subjectUnderTest;

    /**
     * @var \ReflectionObject
     */
    protected $subjectUnderTestObjectReflection;

    protected $streamsToDestroy = [];

    /**
     * Allows catching typehint errors on PHP <7 & >=7
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

    /**
     * @inheritdoc
     *
     * @throws \LogicException $this->subjectUnderTest was not defined in child class
     */
    protected function setUp()
    {
        if (empty($this->subjectUnderTest)) {
            throw new \LogicException('You need to overwrite setUp() function and set $this->subjectUnderTest');
        }

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

    /**
     * Checks if current environment is running under OS X or Darwin
     *
     * @return bool
     */
    protected function isOSX()
    {
        return (PHP_OS === 'Darwin');
    }

    /**
     * Gets protected or private property value from current SUT.
     *
     * @param string $name Variable name
     *
     * @return mixed
     * @throws \RuntimeException Property not found in object
     */
    protected function getRestrictedPropertyValue($name)
    {
        if (!$this->subjectUnderTestObjectReflection->hasProperty($name)) {
            throw new \RuntimeException('There is no property named ' . $name);
        }

        $property = $this->subjectUnderTestObjectReflection->getProperty($name);
        $property->setAccessible(true);

        return $property->getValue($this->subjectUnderTest);
    }

    /**
     * Sets protected or private property value from current SUT.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @throws \RuntimeException Property not found in object
     */
    protected function setRestrictedPropertyValue($name, $value)
    {
        if (!$this->subjectUnderTestObjectReflection->hasProperty($name)) {
            throw new \RuntimeException('There is no property named ' . $name);
        }

        $property = $this->subjectUnderTestObjectReflection->getProperty($name);
        $property->setAccessible(true);
        $property->setValue($this->subjectUnderTest, $value);
    }

    /**
     * Skip current test if IPv6 is not available
     *
     * @see https://github.com/symfony/http-foundation/blob/master/IpUtils.php#L100 - method source
     */
    protected function markTestSkippedIfNoIpV6TestEnvironment()
    {
        if (!((extension_loaded('sockets') && defined('AF_INET6')) || @inet_pton('::1'))) {
            $this->markTestSkipped('Unable to check IPv6. Check that PHP was not compiled with option "disable-ipv6".');
        }
    }

    /**
     * Assertion for isMethodImplementedByClass()
     *
     * @param string $expectedMethodName
     * @param string $className FQCN
     *
     * @see isMethodImplementedByClass
     */
    protected function assertClassImplementsMethod($expectedMethodName, $className)
    {
        $this->assertTrue(
            $this->isMethodImplementedByClass($className, $expectedMethodName),
            "Class does not implement \"$expectedMethodName\" method"
        );
    }

    /**
     * Checks if given class really implements given method.
     * No PHP built-in methods could be used here since they will fail in scenario where abstract class implements
     * interface and than mock is build for that class.
     *
     * @param string $className FQCN
     * @param string $methodName
     *
     * @return bool
     */
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

    /**
     * Checks if given class is abstract
     *
     * @param string $className FQCN
     */
    protected function assertIsAbstractClass($className)
    {
        $this->assertTrue(class_exists($className), 'Given class does not exists');

        $classReflection = new \ReflectionClass($className);
        $this->assertTrue($classReflection->isAbstract(), 'Class is not abstract');
    }

    /**
     * Creates TCP/IP socket server and connects client to it.
     *
     * @return array [
     *                  'server' => (resource for server socket),
     *                  'clientOnServer' => (resource for client on the sever side),
     *                  'clientOnClient' => (resource for client on the client side)
     *               ]
     *
     * @throws \PHPUnit_Framework_Exception
     */
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

    /**
     * Skips current test on HHVM
     *
     * @param string $info
     *
     * @throws \PHPUnit_Framework_SkippedTestError
     */
    protected function skipTestOnHHVM($info = 'See test comments for details')
    {
        if ($this->isHHVM()) {
            $this->markTestSkipped($info);
        }
    }

    /**
     * Checks if code runs under HHVM interpreter
     *
     * @return bool
     */
    public function isHHVM()
    {
        return defined('HHVM_VERSION');
    }

    /**
     * Yes, some things are in fact broken on Linux and working on other OSs! (yes, I was also surprised)
     *
     * @param string $info
     *
     * @throws \PHPUnit_Framework_SkippedTestError
     */
    protected function skipTestOnLinux($info = 'See test comments for details')
    {
        if ($this->isLinux()) {
            $this->markTestSkipped($info);
        }
    }

    /**
     * Checks if currently running on Linux
     *
     * @return bool
     */
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

        $this->fail('safeWrite() reached max count writing before writing full data set');
    }

    /**
     * Method made essentially to test for HHVM bug, which was fixed in later releases
     * https://github.com/facebook/hhvm/issues/6938
     *
     * @return bool
     *
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    protected function verifyStreamBlockingBug()
    {
        $stream = stream_socket_client("udp://127.0.0.1:9999");
        stream_set_blocking($stream, 0);

        if (stream_get_meta_data($stream)['blocked'] === false) {
            return;
        }

        if ($this->isHHVM()) {
            $this->fail(
                "Your HHVM is affected by stream-blocking bug (https://github.com/facebook/hhvm/issues/6938).\n" .
                "If you're sure that your version is newer than described there report this to HHVM developers."
            );

        } else {
            $this->fail(
                "Your interpreter is affected by stream-blocking bug. Non-blocking stream is reported as \n" .
                " blocked one (or it's just blocked after setting to non-blocking). This bug is similar to \n" .
                " HHVM one at https://github.com/facebook/hhvm/issues/6938.\n" .
                "Report it to your interpreter maintainers"
            );
        }
    }

}
