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

namespace noFlash\CherryHttp\Http\Request;

use noFlash\CherryHttp\Http\Message\MessageInterface;

/**
 * Represents request made by client.
 */
interface RequestInterface extends MessageInterface
{
    /**
     * Provides HTTP method name.
     *
     * @return string Eg. GET, HEAD, POST etc.
     */
    public function getMethod();

    /**
     * Sets HTTP method on current instance.
     *
     * Implementation MAY choose to convert method to uppercase. That behaviour is consistent with RFC.
     *
     * @param string $method
     *
     * @return void
     */
    public function setMethod($method);

    /**
     * Returns the message request target.
     *
     * Request target is a composition of path and query from a server perspective.
     * If no request target was set method SHOULD return "/" by default.
     *
     * @return string
     */
    public function getRequestTarget();

    /**
     * Sets request target.
     *
     * Implementation MUST overwrite previously set path and/or query parameters with values provided using this method.
     *
     * @param string $requestTarget
     *
     * @return void
     */
    public function setRequestTarget($requestTarget);

    /**
     * Retrieve the path component of the URI.
     * If no path was set implementation MUST return "/" default value.
     *
     * @return string
     */
    public function getPath();

    /**
     * Seth path component of the URI.
     *
     * @param string $path
     *
     * @return string
     */
    public function setPath($path);

    /**
     * Returns URI query string.
     * If no query string was set implementation MUST return an empty string.
     *
     * Please note "?" is NOT a part of query string and MUST NOT be returned by that method.
     * Implementation should take proper care of encoding each value of query string.
     *
     * @return string The URI query string.
     */
    public function getQueryString();


    /**
     * Sets URI query string.
     *
     * Please note "?" is NOT a part of query string and MUST NOT occur at the beginning of the query string parameter
     * value.
     * Implementation should take proper care of encoding each value of query string.
     *
     * @param string $queryString
     *
     * @return void
     */
    public function setQueryString($queryString);
}
