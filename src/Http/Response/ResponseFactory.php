<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Http\Response;

/**
 * This class produces responses on demand.
 * By default it uses standard \noFlash\CherryHttp\Http\Response\Response object as an base, but you can set your own.
 */
class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * See getModifiedResponse() for details
     */
    const HEADERS_MODE_REPLACE = 'set';
    const HEADERS_MODE_ADD     = 'add';

    /**
     * @var string See setHeadersMode() for details.
     */
    private $headersMode = self::HEADERS_MODE_ADD;

    /**
     * @var ResponseInterface
     */
    private $baseResponse;

    /**
     * Will set create factory with given (or default) base response.
     *
     * @param ResponseInterface $baseResponse Specified base response to use or null to use new instance of
     *                                        \noFlash\CherryHttp\Http\Response\Response. Constructor called with
     *                                        baseResponse will act the same as call to
     *                                        ResponseFactory::setBaseResponse() (but without wasting time for
     *                                        unnecessary default Response bootstrap)
     */
    public function __construct(ResponseInterface $baseResponse = null)
    {
        $this->baseResponse = ($baseResponse) ?: new Response();
    }

    /**
     * Returns **clone** of response object which is used internally to create new responses.
     * For details see documentation for ResponseFactory::setBaseResponse().
     *
     * @return ResponseInterface
     */
    public function getBaseResponse()
    {
        return clone $this->baseResponse;
    }

    /**
     * Every response must implement ResponseInterface. However you could have multiple response templates which are
     * pretty static and don't require whole new factory.
     * This factory is smart enough to allow you to set your own already configured response which will be than
     * cloned on demand.
     * After using this method given object will be internally cloned and used as an template, so if you change given
     * object outside of this factory knowledge and you will need to set the base response again.
     * Please keep in mind all values set by setDefault*() methods in this factory WILL BE LOST if you replace
     * base response!
     *
     * @param ResponseInterface $response
     */
    public function setBaseResponse(ResponseInterface $response)
    {
        $this->baseResponse = clone $response;
    }

    /**
     * Everytime you get factored object default headers are applied to it.
     * This method sets how the headers are applied - using setHeader() or addHeader().
     *
     * By default headers are added.
     *
     * @param string $mode ResponseFactory::HEADERS_MODE_REPLACE or ResponseFactory::HEADERS_MODE_ADD
     *
     * @throws \InvalidArgumentException
     */
    public function setHeadersMode($mode)
    {
        if ($mode === self::HEADERS_MODE_REPLACE || $mode === self::HEADERS_MODE_ADD) {
            $this->headersMode = $mode;

        } else {
            throw new \InvalidArgumentException('Invalid mode specified');
        }
    }

    /**
     * Produces response with default values.
     *
     * @param int|false   $code    Code to use. If you pass false code will be derived from base response.
     * @param null|false  $content Content to use in new response. If you pass false content from default response will
     *                             be used.
     * @param array|false $headers Additional headers to add to new response.
     *
     * @return ResponseInterface
     */
    public function getResponse($code = ResponseCode::NO_CONTENT, $content = null, $headers = [])
    {
        $response = clone $this->baseResponse;

        if ($code !== false) {
            $response->setStatus($code);
        }

        if ($content !== false) {
            $response->setBody($content);
        }

        if ($this->headersMode === self::HEADERS_MODE_ADD) {
            foreach ($headers as $name => $value) {
                $response->addHeader($name, $value);
            }

        } else {
            foreach ($headers as $name => $value) {
                $response->setHeader($name, $value);
            }
        }

        return $response;
    }

    /**
     * Returns all default headers.
     *
     * @return array
     */
    public function getDefaultHeaders()
    {
        return $this->baseResponse->getHeaders();
    }

    /**
     * @inheritdoc
     */
    final public function setDefaultHeaders($headers)
    {
        foreach ($headers as $headerName => $headerValues) {
            $this->baseResponse->setHeader($headerName, $headerValues);
        }
    }

    /**
     * Adds default header. If header exists another one with the same name & value provided will be added.
     *
     * @param string $name  Case-sensitive header name. Header not defined by RFC should be prefixed with "X-".
     * @param string $value Header value.
     *
     * @return void
     */
    public function addDefaultHeader($name, $value)
    {
        $this->baseResponse->addHeader($name, $value);
    }

    /**
     * Sets default header. If header with the same name exists it will it's value will be replaced with new one.
     * Note: keep in mind if multiple headers with the same name exists and you use this method all of them will be
     * replaced with single header with provided value!
     *
     * While setting lookup for existing one is done using case-insensitive routing, but cases are preserved on set.
     *
     * @param string $name  Header name. Header not defined by RFC should be prefixed with "X-".
     * @param string $value Header value.
     *
     * @return void
     */
    public function setDefaultHeader($name, $value)
    {
        $this->baseResponse->setHeader($name, $value);
    }

    /**
     * Removes default header. If header doesn't exists method will just return doing nothing (similar to PHPs unset()).
     * If there's multiple headers under the same name all of them will be removed.
     *
     * @param string $name Case-insensitive header name.
     *
     * @return void
     * @see isDefaultHeaderSet()
     */
    public function unsetDefaultHeader($name)
    {
        $this->baseResponse->unsetHeader($name);
    }

    /**
     * Checks if default header was set.
     *
     * @param string $name Case-insensitive header name.
     *
     * @return bool
     */
    public function isDefaultHeaderSet($name)
    {
        return $this->baseResponse->hasHeader($name);
    }

    /**
     * This method performs the same factoring routine as for getResponse() setting default values etc. but in this
     * case it uses already prepared response.
     * This may be useful if you have configured factory for standard responses but you want to use it once for e.g.
     * ErrorResponse.
     *
     * This method will modify object passed. Keep in mind this routine is not really fast one (unfortunately :().
     *
     * @param ResponseInterface $baseResponse
     *
     * @return ResponseInterface
     */
    public function getModifiedResponse(ResponseInterface $baseResponse)
    {
        if ($this->headersMode === self::HEADERS_MODE_ADD) {
            foreach ($this->baseResponse->getHeaders() as $name => $value) {
                $baseResponse->addHeader($name, $value);
            }

        } else {
            foreach ($this->baseResponse->getHeaders() as $name => $value) {
                $baseResponse->setHeader($name, $value);
            }
        }

        return $baseResponse;
    }
}
