<?php
namespace noFlash\CherryHttp;

use InvalidArgumentException;
use LogicException;

/**
 * Generic representation of ane HTTP message, which consists of request line, headers and (sometimes non-empty) body.
 *
 * @package noFlash\CherryHttp
 * @see HttpRequest
 * @see HttpResponse
 */
abstract class HttpMessage
{
    protected $code;
    protected $protocolVersion = "1.1";

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

        if ($httpVersion !== "1.1" && $httpVersion !== "1.0") { //SPDY is not supported currently
            throw new InvalidArgumentException("Invalid (non-RFC) HTTP version.");
        }

        $this->protocolVersion = $httpVersion;
        $this->messageCache = null;
    }

    /**
     * Provides HTTP code set for request.
     *
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Sets HTTP code for response.
     *
     * @param integer $code Any valid HTTP code
     *
     * @throws InvalidArgumentException Exception is raised if you try to set invalid HTTP code.
     * @throws LogicException Exception is raised if you try to set code which should not contain a body and body is
     *     already present.
     */
    public function setCode($code)
    {
        $code = (int)$code;

        if (!empty($this->body) && !HttpCode::isBodyAllowed($this->code)) { //InvalidArgumentException can be thrown here
            throw new LogicException("HTTP response already contains body - response \"" . HttpCode::getName($code) . "\" cannot contain body.");
        }

        $this->code = $code;
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
        foreach ($this->headers as $header) {
            $headers[$header[0]] = $header[1];
        }

        return $headers;
    }

    /**
     * Provides value for specified header name.
     * Note: Some headers can be send multiple times. In this case this method will return all of them in array.
     *
     * @param $name
     *
     * @return string|string[]|false
     */
    public function getHeader($name)
    {
        $name = strtolower($name);

        if (!isset($this->headers[$name])) {
            return false;
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
        if ($replace || !isset($this->headers[$lowercaseName])) {
            $this->headers[$lowercaseName] = array($name, $value);

        } else {
            if (is_array($this->headers[$lowercaseName][1])) {
                $this->headers[$lowercaseName][1][] = $value;
            } else {
                $this->headers[$lowercaseName][1] = array($this->headers[$lowercaseName][1], $value);
            }
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
     * Returns request body (if set).
     *
     * @return string|null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Sets body for response.
     * It also automatically sets correct Content-Length header.
     *
     * @param string $body
     *
     * @throws LogicException Thrown when you try to set body, but request code (eg. 204) denotes that no body is
     *     allowed.
     */
    public function setBody($body)
    {
        if (!empty($body) && !HttpCode::isBodyAllowed($this->code)) {
            throw new LogicException("You cannot set non-empty body for currently set code");
        }

        $this->body = (string)$body;
        $this->setHeader("Content-Length", strlen($this->body));
        $this->messageCache = null;
    }

    /**
     * Provides information is TCP connection should be terminated after sending this request.
     *
     * @return bool
     */
    public function isConnectionClose()
    {
        $connectionHeader = strtolower($this->getHeader("connection"));

        return ($connectionHeader === "close" || //Explicitly declared connection as close
            ((float)$this->getProtocolVersion() <= 1.1 && $connectionHeader !== "keep-alive")); //Protocols older than 1.1 assumes "close" by default unless "keep-alive" specified
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
            if (is_array($header[1])) {
                foreach ($header[1] as $multiHeader) {
                    $headers .= $header[0] . ": " . $multiHeader . "\r\n";
                }

            } else {
                $headers .= $header[0] . ": " . $header[1] . "\r\n";
            }
        }

        return $headers;
    }

    /**
     * Provides raw representation ready to be sent down the wire.
     *
     * @return string
     * @throws InvalidArgumentException It could be theoretically thrown by HttpCode::getName() if $this->code is
     *     unknown, but in normal circumstances is impossible
     */
    abstract public function __toString();
}
