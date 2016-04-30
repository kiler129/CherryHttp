<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Http\Message;

use noFlash\CherryHttp\IO\Stream\StreamInterface;

/**
 * Represents generic message exchanged between endpoints.
 */
class Message implements MessageInterface
{

    /**
     * Contains array of headers.
     * Internal structure:
     * $headers = [
     *     'connection' => [ //Header name (lowercase)
     *         'Connection', //Header name (original case)
     *         ['close'] //Header values
     *     ],
     *     'set-cookie' => [
     *         'Set-Cookie',
     *         ['cookie1', 'cookie2', 'cookie3']
     *     ]
     * ];
     *
     * @var array
     */
    protected $headers = [];

    /**
     * @var StreamInterface|string
     */
    protected $body = '';

    /**
     * @var string HTTP protocol version, e.g. 1.0, 1.1, 0.99
     */
    protected $protocolVersion = MessageInterface::HTTP_11;

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function setProtocolVersion($version)
    {
        $version = (string)$version;

        if (!isset($version[2]) || //Version too short
            isset($version[3]) || //Version too long
            (string)(int)$version[0] !== $version[0] || //First character have to be digit
            $version[1] !== '.' || //Verify a dot existence
            (string)(int)$version[2] !== $version[2] //Verify second digit
        ) {
            throw new \InvalidArgumentException(
                'Invalid HTTP version - valid version should be in DIGIT.DIGIT format.'
            );
        }

        $this->protocolVersion = $version;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        $result = [];

        foreach ($this->headers as $headerLine) {
            $result[$headerLine[0]] = $headerLine[1];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name)
    {
        $name = strtolower($name);

        if (!isset($this->headers[$name])) {
            return [];
        }

        return $this->headers[$name][1];
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name)
    {
        return isset($this->headers[strtolower($name)]);
    }

    /**
     * {@inheritdoc}
     */
    public function setHeader($name, $value)
    {
        $lowercaseName = strtolower($name);
        $this->headers[$lowercaseName] = [$name, [(string)$value]];
    }

    /**
     * {@inheritdoc}
     */
    public function addHeader($name, $value)
    {
        $lowercaseName = strtolower($name);

        if (!isset($this->headers[$lowercaseName])) {
            $this->headers[$lowercaseName] = [$name, [(string)$value]];

        } else {
            $this->headers[$lowercaseName][1][] = (string)$value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unsetHeader($name)
    {
        unset($this->headers[strtolower($name)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function setBody($body)
    {
        if(!is_string($body) && $body !== null && !($body instanceof StreamInterface)) {
            throw new \InvalidArgumentException('Body need to be a string or object implementing StreamInterface');
        }
        
        $this->body = $body;
    }
}
