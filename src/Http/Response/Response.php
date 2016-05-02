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
use noFlash\CherryHttp\IO\Stream\StreamInterface;

/**
 * Represents message sent by endpoint handling request, by default it will be a server.
 */
class Response extends Message implements ResponseInterface
{
    /**
     * @var int HTTP response code.
     */
    protected $statusCode = ResponseCode::OK;

    /**
     * @var string Reason phrase
     */
    protected $reasonPhrase = 'OK';

    /**
     * {@inheritdoc}
     */
    protected $headers = [
        'server' => [
            'Server',
            ['CherryHttp/2']
        ]
    ];
    
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

    /**
     * @inheritDoc
     *
     * @throws \LogicException If you try to set response and current code is 204 or 100-199
     */
    public function setBody($body)
    {
        if ($body === '' || $body === null) {
            $this->setHeader('Content-Length', '0');
            $this->body = $body;

            return;
        }

        if ($this->statusCode === ResponseCode::NO_CONTENT || ($this->statusCode >= 100 && $this->statusCode < 200)) {
            throw new \LogicException("You cannot set body on Response with current code ({$this->statusCode})");
        }

        $this->body = $body;

        if (is_string($body)) {
            $this->setHeader('Content-Length', strlen($body));

            return;
        }

        if ($body instanceof StreamInterface) {
            $streamLength = $this->body->getLength();

            if ($streamLength !== null) {
                $this->setHeader('Content-Length', $streamLength);
            }

            return;
        }

        throw new \InvalidArgumentException('Body need to be a string or object implementing StreamInterface');
    }

    /**
     * This method provides HTTP response header section (status line + headers + empty line) as defined in RFC:
     *  https://tools.ietf.org/html/rfc7230#section-2
     *
     * @return string Example output:
     *                HTTP/1.1 200 OK\r\n
     *                Server: CherryHttp/2.0\r\n
     *                Content-Length: 10\r\n
     *                Connection: Keep-Alive\r\n
     *                \r\n
     */
    public function getHeaderSection()
    {
        $header = 'HTTP/' . $this->protocolVersion . ' ' . $this->statusCode . ' ' . $this->reasonPhrase . "\r\n";
        
        foreach($this->headers as $headerLine)
        {
            //@formatter:off
            $header .= $headerLine[0] . ': ' . //Header name
                        (
                            (isset($headerLine[1][1])) ? //More than single value?
                            implode("\r\n${headerLine[0]}: ", $headerLine[1]) :
                            $headerLine[1][0]
                        ) .
                       "\r\n";
            //@formatter:on
        }

        return $header . "\r\n";
    }

    /**
     * Generates HTTP response to send down the wire
     *
     * @return string Example output:
     *                HTTP/1.1 200 OK\r\n
     *                Server: CherryHttp/2.0\r\n
     *                Content-Length: 12\r\n
     *                Connection: Keep-Alive\r\n
     *                \r\n
     *                Hello World!
     */
    public function __toString()
    {
        return $this->getHeaderSection() . $this->body;
    }
}
