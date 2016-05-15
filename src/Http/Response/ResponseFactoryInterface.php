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
 * Every HTTP response
 */
interface ResponseFactoryInterface
{
    /**
     * Produces response with default values.
     *
     * @return ResponseInterface
     */
    public function getResponse($code = ResponseCode::NO_CONTENT, $content = null, $headers = []);

    /**
     * Returns all default headers.
     *
     * @return array
     */
    public function getDefaultHeaders();

    /**
     * Merges current set of default headers with given one:
     * - If header doesn't exists yet it will be created
     * - If header exists it will be replaced with given one, previous set of values will be discarded and new one used
     *
     * @param array $headers Format should be the same as returned by MessageInterface::getHeaders()
     */
    public function setDefaultHeaders($headers);

    /**
     * Adds default header. If header exists another one with the same name & value provided will be added.
     *
     * @param string $name  Case-sensitive header name. Header not defined by RFC should be prefixed with "X-".
     * @param string $value Header value.
     *
     * @return void
     */
    public function addDefaultHeader($name, $value);

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
    public function setDefaultHeader($name, $value);

    /**
     * Removes default header. If header doesn't exists method will just return doing nothing (similar to PHPs unset()).
     * If there's multiple headers under the same name all of them will be removed.
     *
     * @param string $name Case-insensitive header name.
     *
     * @return void
     * @see isDefaultHeaderSet()
     */
    public function unsetDefaultHeader($name);

    /**
     * Checks if default header was set.
     *
     * @param string $name Case-insensitive header name.
     *
     * @return bool
     */
    public function isDefaultHeaderSet($name);
}
