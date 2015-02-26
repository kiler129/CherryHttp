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
    protected $headers = array(
        "server" => array("Server", "CherryHttp/1.0"),
        "connection" => array("Connection", "keep-alive")
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

        foreach ($headers as $headerName => $headerValue) {
            $this->headers[strtolower($headerName)] = array($headerName, $headerValue);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        if (empty($this->messageCache)) {
            //@formatter:off PHPStorm formatter acts weird on such constructions and reformat it to single looong line
            $this->messageCache = "HTTP/" . $this->protocolVersion . " " . HttpCode::getName($this->code) . "\r\n" .
                                  $this->getHeadersAsText() .
                                  "\r\n" .
                                  $this->body;
            //@formatter:on
        }

        return $this->messageCache;
    }
}
