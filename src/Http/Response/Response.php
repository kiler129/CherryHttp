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

use noFlash\CherryHttp\Http\Message\Message;

/**
 * Represents message sent by endpoint handling request, by default it will be a server.
 */
class Response extends Message implements ResponseInterface
{
    /**
     * @var int HTTP response code.
     */
    protected $statusCode = ResponseCode::NO_CONTENT;

    /**
     * @var string Reason phrase
     */
    protected $reasonPhrase = 'No Content';

    /**
     * {@inheritdoc}
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($code, $reasonPhrase = '')
    {
        if (!ResponseCode::isCodeValid($code)) {
            throw new \InvalidArgumentException('Invalid code specified. Consult RFC.');
        }

        $this->statusCode = $code;

        $reasonPhrase = (string)$reasonPhrase;
        $this->reasonPhrase = (empty($reasonPhrase)) ? ResponseCode::getReasonPhraseByCode($code) : $reasonPhrase;
    }
}
