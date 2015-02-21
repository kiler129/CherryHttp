<?php
namespace noFlash\CherryHttp;

use InvalidArgumentException;
use LogicException;

/**
 * Represents generic HTTP response containing all information to send to client
 *
 * @package noFlash\CherryHttp
 */
class HttpResponse
{
    private $httpVersion = "1.1";
    private $headers     = array(
        //TODO hmm, it should be possible to change default "server" header for every response made by child library which use CherryHttp (no idea how to do it)
        "server" => array("Server", "CherryHttp/1.0"),
        "connection" => array("Connection", "keep-alive")
    );
    private $responseCache;
    private $body;
    private $code;

    /**
     * @param string $body Response body
     * @param array $headers Response headers. Header "connection" and "server" are set automatically.
     * @param int $code HTTP code, see HttpCode class
     *
     * @throws InvalidArgumentException Raised when invalid code is provided.
     * @throws LogicException Raised if you try to set code which should not contain body (eg. 204 No Content) and
     *     provide body.
     */
    public function __construct($body = null, array $headers = array(), $code = HttpCode::OK)
    {
        $this->setCode($code);
        if ($body !== null) {
            $this->setBody($body);
        }

        foreach ($headers as $headerName => $headerValue) {
            $this->headers[strtolower($headerName)] = array($headerName, $headerValue);
        }
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

        $this->httpVersion = $httpVersion;
        $this->responseCache = null;
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
        $this->responseCache = null;
    }

    /**
     * Adds or replace header with given name with given value.
     *
     * @param string $name Header name
     * @param string|array $value Header value. In case of multiple headers with the same name (eg. Set-Cookie) it's
     *     possible to pass an array.
     */
    public function setHeader($name, $value)
    {
        $this->headers[strtolower($name)] = array($name, $value);
        $this->responseCache = null;
    }

    /**
     * Provides value for specified header name.
     * Note: Some headers can be send multiple times. In this case this method will return all of them in array.
     *
     * @param $name
     *
     * @return string|false
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
     * Removes previously set header.
     *
     * @param string $name Header name
     */
    public function removeReader($name)
    {
        unset($this->headers[strtolower($name)]);
        $this->responseCache = null;
    }

    /**
     * Provides raw HTTP response representation ready to be sent down the wire.
     *
     * @return string
     */
    public function __toString()
    {
        if (empty($this->responseCache)) {
            $this->responseCache = "HTTP/" . $this->httpVersion . " " . HttpCode::getName($this->code) . "\r\n";

            foreach ($this->headers as $header) {
                $this->responseCache .= $header[0] . ": " . $header[1] . "\r\n";
            }

            $this->responseCache .= "\r\n" . $this->body;
        }

        return $this->responseCache;
    }

    /**
     * Returns request body (if set).
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Sets body for response.
     * It also automatically sets correct Content-Length header.
     *
     * @param $body
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
        $this->responseCache = null;
    }

    /**
     * Provides information is TCP should be connection after sending this request.
     *
     * @return bool
     */
    public function isConnectionClose()
    {
        $connectionHeader = strtolower($this->getHeader("connection"));

        return ($connectionHeader === "close" || ((float)$this->getProtocolVersion() <= 1.1 && $connectionHeader !== "keep-alive"));
    }

    /**
     * Provides HTTP version in RFC form
     *
     * @return string RFC HTTP version (eg. 1.1)
     */
    public function getProtocolVersion()
    {
        return $this->httpVersion;
    }
}