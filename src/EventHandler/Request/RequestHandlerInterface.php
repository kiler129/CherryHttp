<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\EventHandler\Request;

use noFlash\CherryHttp\Http\Response\ResponseFactoryInterface;

/**
 * Prototype of an interface for HTTP request handler.
 *
 * --- WARNING ---
 * This specification is subject to change!
 */
interface RequestHandlerInterface
{
    public function onRequestStart();

    public function onRequestUpdate();

    public function onRequestHeadersComplete();

    public function onRequestComplete();

    /**
     * Returns factory used to create HTTP responses.
     *
     * @return ResponseFactoryInterface
     */
    public function getResponseFactory();
}
