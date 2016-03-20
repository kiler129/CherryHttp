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

use noFlash\CherryHttp\Http\Response\Response;
use noFlash\CherryHttp\Http\Response\ResponseFactory;
use noFlash\CherryHttp\Http\Response\ResponseInterface;
use noFlash\CherryHttp\Tests\TestHelpers\CloneAwareResponse;
use PHPUnit_Framework_MockObject_MockObject;

class ResponseFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Since by design ResponseFactory uses cloning and testing it is a nightmare (specialized response stub with
     * half-mocker logic etc.) it's actually better to access private property holding base response.
     *
     * @var string Name of private field with base response.
     */
    const INTERNAL_RESPONSE_FACTORY_FIELD_NAME = 'baseResponse';
    /**
     * @var ResponseFactory
     */
    private $subjectUnderTest;

    public function setUp()
    {
        $this->subjectUnderTest = new ResponseFactory();
    }

    public function testBaseResponseIsAvailableOnFreshObject()
    {
        $instance = $this->subjectUnderTest->getBaseResponse();
        $this->assertInstanceOf(ResponseInterface::class, $instance);
    }

    /**
     * @testdox Base response is CherryHTTP default Response instance
     */
    public function testBaseResponseIsCherryHttpDefaultResponseInstance()
    {
        $instance = $this->subjectUnderTest->getBaseResponse();
        $this->assertInstanceOf(Response::class, $instance);
    }

    public function testBaseResponseCanBeSet()
    {
        /** @var ResponseInterface $baseResponse */
        $baseResponse = $this->getMock(ResponseInterface::class);
        $this->subjectUnderTest->setBaseResponse($baseResponse);

        $instance = $this->subjectUnderTest->getBaseResponse();
        $this->assertEquals($baseResponse, $instance);
    }

    public function testCustomResponseIsClonedOnSet()
    {
        /** @var CloneAwareResponse $baseResponse */
        $baseResponse = $this->getMockBuilder(CloneAwareResponse::class)->getMockForAbstractClass();
        $baseResponse->_publicField = 'I am the original mock object';

        $this->subjectUnderTest->setBaseResponse($baseResponse); //This method should clone the object
        $baseResponse->_publicField = 'I was changed after set'; //...so this change will not reflect it

        /** @var CloneAwareResponse $sutBaseResponse */
        $sutBaseResponse = $this->subjectUnderTest->getBaseResponse(); //This method also should clone object internally
        $this->assertSame('I am the original mock object', $sutBaseResponse->_publicField); //Check if cloned on set
        $this->assertSame(2, $sutBaseResponse->_getCloneNumber()); //1 for set, 1 for get
    }

    public function testReturnedDefaultBaseResponseIsAlwaysNewObject()
    {
        $instance1 = $this->subjectUnderTest->getBaseResponse();
        $instance2 = $this->subjectUnderTest->getBaseResponse();

        $this->assertEquals($instance1, $instance2);
        $this->assertNotSame($instance1, $instance2);
    }

    public function testReturnedCustomBaseResponseIsAlwaysNewObject()
    {
        $baseResponse = $this->getMock(ResponseInterface::class);
        $this->subjectUnderTest->setBaseResponse($baseResponse);
        $this->assertEquals($baseResponse, $this->subjectUnderTest->getBaseResponse());
        $this->assertNotSame($baseResponse, $this->subjectUnderTest->getBaseResponse());
    }

    public function testGetHeadersGetsHeaderFromBaseResponse()
    {
        $baseHeaders = ['X-Foo' => 'val1', 'X-Baz' => 'val2'];

        $baseMock = $this->setMockedBaseResponse();
        $baseMock->expects($this->atLeastOnce())->method('getHeaders')->willReturn($baseHeaders);

        $headers = $this->subjectUnderTest->getDefaultHeaders();
        $this->assertArraySubset($baseHeaders, $headers);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|ResponseInterface
     */
    private function setMockedBaseResponse(PHPUnit_Framework_MockObject_MockObject $mock = null)
    {
        if ($mock === null) {
            $mock = $this->getMockForAbstractClass(ResponseInterface::class);
        }

        $sutReflection = new \ReflectionObject($this->subjectUnderTest);
        $this->assertTrue(
            $sutReflection->hasProperty(self::INTERNAL_RESPONSE_FACTORY_FIELD_NAME),
            'Invalid base response field name'
        );

        $baseResponseProperty = $sutReflection->getProperty(self::INTERNAL_RESPONSE_FACTORY_FIELD_NAME);
        $baseResponseProperty->setAccessible(true);
        $baseResponseProperty->setValue($this->subjectUnderTest, $mock);

        return $mock;
    }

    public function testAddDefaultHeaderAddsNewHeader()
    {
        $baseMock = $this->setMockedBaseResponse();
        $baseMock->expects($this->once())->method('addHeader')->with(
            'X-Foo',
            'bazz'
        );

        $this->subjectUnderTest->addDefaultHeader('X-Foo', 'bazz');
    }

    public function testSetDefaultHeaderSetsNewHeader()
    {
        $baseMock = $this->setMockedBaseResponse();
        $baseMock->expects($this->once())->method('setHeader')->with(
            'X-Bazz',
            'Foooo'
        );

        $this->subjectUnderTest->setDefaultHeader('X-Bazz', 'Foooo');
    }

    public function testUnsetDefaultHeaderRemovesHeader()
    {
        $baseMock = $this->setMockedBaseResponse();
        $baseMock->expects($this->once())->method('unsetHeader')->with(
            'X-FooBaz'
        );

        $this->subjectUnderTest->unsetDefaultHeader('X-FooBaz');
    }

    public function testCheckingNonExistingDefaultHeaderIsPerformedCorrectly()
    {
        $baseMock = $this->setMockedBaseResponse();
        $baseMock->expects($this->once())->method('hasHeader')->with('X-LoL')->willReturn(false);

        $this->subjectUnderTest->isDefaultHeaderSet('X-LoL');
    }

    public function testCheckingExistingDefaultHeaderIsPerformedCorrectly()
    {
        $baseMock = $this->setMockedBaseResponse();
        $baseMock->expects($this->once())->method('hasHeader')->with('X-LonelyDeveloper')->willReturn(true);

        $this->subjectUnderTest->isDefaultHeaderSet('X-LonelyDeveloper');
    }

    public function testResponseIsGeneratedBasingOnBaseObject()
    {
        /** @var CloneAwareResponse $baseResponse */
        $baseResponse = $this->getMockBuilder(CloneAwareResponse::class)->getMockForAbstractClass();
        $baseResponse->_publicField = 'I am original object';
        $this->setMockedBaseResponse($baseResponse);

        /** @var CloneAwareResponse $response */
        $response = $this->subjectUnderTest->getResponse();
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('I am original object', $response->_publicField, 'Response is not based on base response');

        $baseResponse->_publicField = 'I am modified original object';
        $this->assertSame('I am original object', $response->_publicField, 'Response was not cloned before return');
    }

    public function testResponseIsGeneratedWithDefaultCodeAndBody()
    {
        $baseMock = $this->setMockedBaseResponse();
        $baseMock->expects($this->atLeastOnce())->method('setStatus')->with(204);
        $baseMock->expects($this->atLeastOnce())->method('setBody')->with(null);

        $this->subjectUnderTest->setBaseResponse($baseMock);
        $this->subjectUnderTest->getResponse();
    }

    public function testGeneratedResponseIsCreatedWithGivenCode()
    {
        $baseMock = $this->setMockedBaseResponse();
        $baseMock->expects($this->atLeastOnce())->method('setStatus')->with(304);

        $this->subjectUnderTest->setBaseResponse($baseMock);
        $this->subjectUnderTest->getResponse(304);
    }

    public function testObjectDefinesConstantsForHeadersModes()
    {
        $this->assertTrue(defined(ResponseFactory::class . '::HEADERS_MODE_ADD'));
        $this->assertTrue(defined(ResponseFactory::class . '::HEADERS_MODE_REPLACE'));
    }

    public function testHeadersModeIsValidated()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->subjectUnderTest->setHeadersMode('invalid mode');
    }

    public function testGeneratedResponseIsGeneratedWithAddedHeadersByDefault()
    {
        $baseMock = $this->setMockedBaseResponse();
        $baseMock->expects($this->at(0))->method('addHeader')->with('X-Foo', ['Bazz', 'Foo']);
        $baseMock->expects($this->at(1))->method('addHeader')->with('X-Bar', 'Derp');

        $this->subjectUnderTest->setBaseResponse($baseMock);
        $this->subjectUnderTest->getResponse(false, false, ['X-Foo' => ['Bazz', 'Foo'], 'X-Bar' => 'Derp']);
    }

    public function testGeneratedResponseIsGeneratedWithAddedHeadersWhenSet()
    {
        $baseMock = $this->setMockedBaseResponse();
        $baseMock->expects($this->at(0))->method('addHeader')->with('X-Bar', 'Derp');
        $baseMock->expects($this->at(1))->method('addHeader')->with('X-Foo', ['Bazz', 'Foo']);

        $this->subjectUnderTest->setBaseResponse($baseMock);
        $this->subjectUnderTest->setHeadersMode(ResponseFactory::HEADERS_MODE_ADD);
        $this->subjectUnderTest->getResponse(false, false, ['X-Bar' => 'Derp', 'X-Foo' => ['Bazz', 'Foo']]);
    }

    public function testGeneratedResponseIsGeneratedWithReplacedHeadersWhenSet()
    {
        $baseMock = $this->setMockedBaseResponse();
        $baseMock->expects($this->at(0))->method('setHeader')->with('X-Foo', ['Bazz', 'Foo']);
        $baseMock->expects($this->at(1))->method('setHeader')->with('X-Bar', 'Derp');

        $this->subjectUnderTest->setBaseResponse($baseMock);
        $this->subjectUnderTest->setHeadersMode(ResponseFactory::HEADERS_MODE_REPLACE);
        $this->subjectUnderTest->getResponse(false, false, ['X-Foo' => ['Bazz', 'Foo'], 'X-Bar' => 'Derp']);
    }

    /**
     * @todo Interface lacks information for this test
     */
    //public function testGeneratedResponseIsCreatedWithGivenBody()
    //{
    //}

    public function testResponseIsGeneratedWithBaseResponseCodeIfFalseWasPassedAsCode()
    {
        $baseMock = $this->setMockedBaseResponse();
        $baseMock->expects($this->never())->method('setStatus');

        $this->subjectUnderTest->getResponse(false);
    }

    public function testResponseIsGeneratedWithBaseResponseBodyIfFalseWasPassedAsBody()
    {
        $baseMock = $this->setMockedBaseResponse();
        $baseMock->expects($this->never())->method('setBody');

        $this->subjectUnderTest->getResponse(200, false);
    }

    /*
     * If you're struggling why there's a test for that imagine following situation:
     * ResponseFactory::setBaseResponse({code: 200})
     * ResponseFactory::getResponse(204) -> will return {code: 200}
     * ResponseFactory::getResponse(false) -> and here we are... it should return it with code=200, not 204
     */
    public function testBaseObjectIsNotChangedDuringGeneration()
    {
        /** @var CloneAwareResponse $baseResponse */
        $baseResponse = $this->getMockBuilder(CloneAwareResponse::class)->getMockForAbstractClass();
        $baseResponse->setStatus(200);
        $baseResponse->setBody('foo');
        $this->setMockedBaseResponse($baseResponse);

        /** @var CloneAwareResponse $response */
        $response = $this->subjectUnderTest->getResponse(204, 'bar');
        $this->assertSame([204, ''], $response->_status);
        $this->assertSame('bar', $response->_body);
        $this->assertSame([200, ''], $baseResponse->_status);
        $this->assertSame('foo', $baseResponse->_body);

        $response = $this->subjectUnderTest->getResponse(false, false);
        $this->assertSame([200, ''], $response->_status);
        $this->assertSame('foo', $response->_body);
    }


    public function testObjectIsNotClonedDuringModificationCall()
    {
        $responseMock = $this->getMockForAbstractClass(ResponseInterface::class);
        $responseMock->expects($this->any())->method('getHeaders')->willReturn([]);

        $this->assertSame($responseMock, $this->subjectUnderTest->getModifiedResponse($responseMock));
    }

    public function testModifiedResponseIsGeneratedWithAddedHeadersByDefault()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|ResponseInterface $baseMock */
        $baseMock = $this->getMockForAbstractClass(ResponseInterface::class);
        $baseMock->expects($this->any())->method('getHeaders')->willReturn(
            [
                'X-Foo' => ['Bar', 'Bzz'],
                'X-Ufo' => 'Derp'
            ]
        );
        $this->subjectUnderTest->setBaseResponse($baseMock);

        $responseMock = $this->getMockForAbstractClass(ResponseInterface::class);
        $responseMock->expects($this->at(0))->method('addHeader')->with('X-Foo', ['Bar', 'Bzz']);
        $responseMock->expects($this->at(1))->method('addHeader')->with('X-Ufo', 'Derp');

        $this->subjectUnderTest->getModifiedResponse($responseMock);
    }

    public function testModifiedResponseIsGeneratedWithAddedHeadersWhenSet()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|ResponseInterface $baseMock */
        $baseMock = $this->getMockForAbstractClass(ResponseInterface::class);
        $baseMock->expects($this->any())->method('getHeaders')->willReturn(
            [
                'X-Foo' => ['Bar', 'Bzz'],
                'X-Ufo' => 'Derp'
            ]
        );
        $this->subjectUnderTest->setBaseResponse($baseMock);

        $responseMock = $this->getMockForAbstractClass(ResponseInterface::class);
        $responseMock->expects($this->at(0))->method('addHeader')->with('X-Foo', ['Bar', 'Bzz']);
        $responseMock->expects($this->at(1))->method('addHeader')->with('X-Ufo', 'Derp');

        $this->subjectUnderTest->setHeadersMode(ResponseFactory::HEADERS_MODE_ADD);
        $this->subjectUnderTest->getModifiedResponse($responseMock);
    }

    public function testModifiedResponseIsGeneratedWithReplacedHeadersWhenSet()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|ResponseInterface $baseMock */
        $baseMock = $this->getMockForAbstractClass(ResponseInterface::class);
        $baseMock->expects($this->any())->method('getHeaders')->willReturn(
            [
                'X-Foo' => ['Bar', 'Bzz'],
                'X-Ufo' => 'Derp'
            ]
        );
        $this->subjectUnderTest->setBaseResponse($baseMock);

        $responseMock = $this->getMockForAbstractClass(ResponseInterface::class);
        $responseMock->expects($this->at(0))->method('setHeader')->with('X-Foo', ['Bar', 'Bzz']);
        $responseMock->expects($this->at(1))->method('setHeader')->with('X-Ufo', 'Derp');

        $this->subjectUnderTest->setHeadersMode(ResponseFactory::HEADERS_MODE_REPLACE);
        $this->subjectUnderTest->getModifiedResponse($responseMock);
    }
}
