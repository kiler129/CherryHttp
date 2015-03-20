<?php
namespace noFlash\CherryHttp;

use InvalidArgumentException;

/**
 * Generic representation of ane HTTP message, which consists of request line, headers and (sometimes non-empty) body.
 *
 * @package noFlash\CherryHttp
 * @see HttpRequest
 * @see HttpResponse
 */
abstract class HttpMessage
{

    protected $protocolVersion = '1.1';

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
    protected $headers = array();

    protected $body;

    protected $messageCache;

    /**
     * Provides HTTP version in RFC form
     *
     * @return string RFC HTTP version (eg. 1.1)
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * @param string
     *
     * @throws InvalidArgumentException Invalid HTTP version was set. Note that HttpResponse supports 1.0 & 1.1
     *     venison only.
     */
    public function setProtocolVersion($httpVersion)
    {
        $httpVersion = (string)$httpVersion;

        if ($httpVersion !== '1.1' && $httpVersion !== '1.0') { //SPDY is not supported currently
            throw new InvalidArgumentException('Invalid (non-RFC) HTTP version.');
        }

        $this->protocolVersion = $httpVersion;
        $this->messageCache = null;
    }

    /**
     * Provides all headers.
     *
     * @return string[] All headers
     */
    public function getHeaders()
    {
        $headers = array();
        foreach($this->headers as $header) {
            $headers[$header[0]] = $header[1];
        }

        return $headers;
    }

    /**
     * Returns raw headers array.
     * Every key contain lowercase version of header name. Value contains 2 elements array where first one represents
     * original header name. Second elements contains array of strings representing values of header.
     *
     * @return array Format defined by {@see HttpMessage::$headers}
     */
    public function getIndexedHeaders()
    {
        return $this->headers;
    }

    /**
     * Provides value for specified header name.
     * Note: Some headers can be send multiple times. In this case this method will return all of them comma-separated.
     *
     * @param string $name Case-insensitive header name
     *
     * @return string|null
     *
     * @see self::getHeaderLines()
     */
    public function getHeader($name)
    {
        $name = strtolower($name);

        if (!isset($this->headers[$name])) {
            return null;
        }

        return implode(',', $this->headers[$name][1]);
    }

    /**
     * Provides value for specified header name.
     * If header doesn't exists it will return empty array.
     *
     * @param string $name Name of header
     *
     * @return string[]
     */
    public function getHeaderLines($name)
    {
        $name = strtolower($name);

        if (!isset($this->headers[$name])) {
            return array();
        }

        return $this->headers[$name][1];
    }

    /**
     * Adds or replace header with given name with given value.
     *
     * @param string $name Header name
     * @param string|array $value Header value. In case of multiple headers with the same name (eg. Set-Cookie) it's
     *     possible to pass an array.
     * @param bool $replace Indicates if current call should replace header (default) with the same name or just add
     *     another with the same name
     */
    public function setHeader($name, $value, $replace = true)
    {
        $lowercaseName = strtolower($name);
        if($replace) {
            $this->headers[$lowercaseName] = array($name, array($value));
        } else {
            $this->headers[$lowercaseName][1][] .= $value;
        }

        $this->messageCache = null;
    }

    /**
     * Removes previously set header.
     * Note: It doesn't check header existence, so it will not warn you if you try to delete nonexistent header.
     *
     * @param string $name Header name
     */
    public function removeReader($name)
    {
        unset($this->headers[strtolower($name)]);
        $this->messageCache = null;
    }

    /**
     * Returns message body (if set).
     *
     * @return string|null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Provides information is TCP connection should be terminated after sending this request.
     * Note: this function DOES NOT support headers like "Connection: close,keep-alive" and assumes default HTTP version
     * behaviour (no RFC describes how to handle such values) for such values.
     *
     * @return bool
     */
    public function isConnectionClose()
    {
        $connectionHeader = strtolower($this->getHeader('connection'));

        return ($connectionHeader === 'close' || //Explicitly declared connection as close
            ((float)$this->getProtocolVersion() <= 1.1 && $connectionHeader !== 'keep-alive')); //Protocols older than 1.1 assumes "close" by default unless "keep-alive" specified
    }

    /**
     * Returns all headers in text form to embed into HTTP message.
     *
     * @return string
     */
    protected function getHeadersAsText()
    {
        $headers = '';

        foreach ($this->headers as $header) {
            foreach($header[1] as $headerValue) {
                $headers .= $header[0] . ': ' . $headerValue . "\r\n";
            }
        }

        return $headers;
    }

    /**
     * Provides raw representation ready to be sent down the wire.
     *
     * @return string
     */
    abstract public function __toString();
}
