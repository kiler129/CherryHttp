<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Http\Request;

use noFlash\CherryHttp\Http\Message\Message;

/**
 * Represents message sent by endpoint requesting some resources from server.
 */
class Request extends Message implements RequestInterface
{
    /**
     * @var string HTTP request method, consult RFC7231 for details
     */
    private $method = 'HEAD';

    /**
     * @var string Request path
     */
    private $path = '/';

    /**
     * @var string Optional query string
     */
    private $queryString = '';

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTarget()
    {
        if (empty($this->queryString)) {
            return $this->path;
        }

        return $this->path . '?' . $this->queryString;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestTarget($requestTarget)
    {
        if (empty($requestTarget)) {
            throw new \InvalidArgumentException('Request-target cannot be empty.');
        }

        if (strpos($requestTarget, ' ') !== false || strpos($requestTarget, '#') !== false) {
            throw new \InvalidArgumentException('Malformed request target! It should NOT contain spaces or #');
        }

        $requestTarget = explode('?', $requestTarget, 2);

        $this->path = $requestTarget[0];
        $this->queryString = (isset($requestTarget[1])) ? $requestTarget[1] : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function setPath($path)
    {
        if (empty($path)) {
            throw new \InvalidArgumentException('Path cannot be empty.');
        }

        if (strpos($path, ' ') !== false || strpos($path, '#') !== false) {
            throw new \InvalidArgumentException('Malformed path! It should NOT contain spaces or #');
        }

        if (strpos($path, '?') !== false) {
            throw new \InvalidArgumentException(
                'Path contains ? - didn\'t you wanted to set request-target instead of path? .' .
                'Use setRequestTarget() method instead if it was your intention.'
            );
        }

        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryString($queryString)
    {
        if (strpos($queryString, ' ') !== false || strpos($queryString, '#') !== false ||
            (isset($queryString[0]) && $queryString[0] === '?')
        ) {
            throw new \InvalidArgumentException(
                'Malformed query string! It should NOT contain spaces or #. It also cannot contain ? at the beginning.'
            );
        }

        $this->queryString = $queryString;
    }
}
