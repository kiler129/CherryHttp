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

use noFlash\CherryHttp\Http\Message\MessageInterface;

/**
 * Represents outgoing message originating at server.
 */
interface ResponseInterface extends MessageInterface
{
    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     * @see ResponseCode
     */
    public function getStatusCode();

    /**
     * Returns reason phrase set for current status code.
     *
     * @return string Reason phrase; must return an empty string if none present.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     */
    public function getReasonPhrase();

    /**
     * Sets new status code, and optionally, reason phrase.
     *
     * If empty reason phrase is specified implementation SHOULD choose IANA recommended phrase for given code.
     * If specified code is unknown (not registered by IANA but semantically valid) implementation MAY use group
     * reason phrase instead.
     *
     * @param int    $code         The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use. Leave empty to assign IANA one.
     *
     * @return void
     * @throws \InvalidArgumentException Thrown for semantically invalid code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     */
    public function setStatus($code, $reasonPhrase = '');
}
