<?php
namespace noFlash\CherryHttp;

use InvalidArgumentException;
use LogicException;

/**
 * Represents generic HTTP response containing all information to send to client
 *
 * @package noFlash\CherryHttp
 */
class HttpResponse extends HttpMessage
{
    protected $code;

    protected $headers = array(
        'server' => array('Server', array('CherryHttp/1.0')),
        'connection' => array('Connection', array('keep-alive'))
    );

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

        //It should call $this->setHeader($headerName, $headerValue, true), but this is a lot faster
        foreach ($headers as $headerName => $headerValue) {
            $this->headers[strtolower($headerName)] = array($headerName, array($headerValue));
        }
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
            throw new LogicException('HTTP response already contains body - response "' . HttpCode::getName($code) . '" cannot contain body.');
        }

        $this->code = $code;
        $this->messageCache = null;
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
            throw new LogicException('You cannot set non-empty body for currently set code');
        }

        $this->body = (string)$body;
        $this->setHeader('Content-Length', strlen($this->body));
        $this->messageCache = null;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        if (empty($this->messageCache)) {
            //@formatter:off PHPStorm formatter acts weird on such constructions and reformat it to single looong line
            $this->messageCache = 'HTTP/' . $this->protocolVersion . ' ' . HttpCode::getName($this->code) . "\r\n" .
                                  $this->getHeadersAsText() .
                                  "\r\n" .
                                  $this->body;
            //@formatter:on
        }

        return $this->messageCache;
    }
}
