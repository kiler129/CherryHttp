<?php
namespace noFlash\CherryHttp;

use Psr\Log\LoggerInterface;

/**
 * Object represents single HTTP request made by client.
 *
 * @package noFlash\CherryHttp
 */
class HttpRequest
{
    /** @var integer Maximum URI length */
    const MAX_URI_LENGTH = 2048;
    /** @var integer Max allowed request headers length */
    const MAX_ENTITY_LENGTH = 8192; //If you need to mess with this value you have bigger problems than this :D
    /** @var LoggerInterface */
    protected $logger;
    protected $isRequestCollected = false;
    private   $method;
    private   $uri;
    private   $queryString;
    private   $httpVersion;
    private   $headers            = array();

    /**
     * @param string $headers HTTP request headers (along with status line)
     * @param LoggerInterface $logger
     *
     * @throws HttpException
     */
    public function __construct($headers, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->parseHeaders($headers);
    }

    /**
     * This parser intentional doesn't implement folded headers. It also doesn't implement multiple occupancies
     * of the same header properly.
     *
     * @param string $headers HTTP request headers (along with status line)
     *
     * @throws HttpException
     * @todo Implement multiple headers with the same name
     */
    private function parseHeaders($headers)
    {
        $this->headers = array();

        $headers = explode("\r\n", $headers);
        $statusLine = explode(" ", $headers[0], 3); //Eg.: GET /file HTTP/1.1
        if (!isset($statusLine[2]) || substr($statusLine[2], 0, 5) !== "HTTP/") {
            throw new HttpException("Request is not HTTP compliant.", HttpCode::BAD_REQUEST, array(), true);
        }

        if (isset($statusLine[1][self::MAX_URI_LENGTH])) { //Much faster than using strlen(...) > MAX_URI_LENGTH
            throw new HttpException("URI should be equal or less than " . self::MAX_URI_LENGTH . " characters",
                HttpCode::REQUEST_URI_TOO_LONG);
        }

        $this->method = $statusLine[0]; //TODO validate method

        $fullUri = explode("?", $statusLine[1], 2);
        $this->uri = $fullUri[0]; //TODO validate URI

        $this->queryString = (isset($fullUri[1])) ? $fullUri[1] : "";

        $this->httpVersion = substr($statusLine[2], 5); //Everything after HTTP/ is http version number
        if ($this->httpVersion !== "1.1" && $this->httpVersion !== "1.0") {
            throw new HttpException("Requested HTTP version is not supported", HttpCode::VERSION_NOT_SUPPORTED, array(),
                true);
        }
        unset($headers[0]); //Status line

        foreach ($headers as $headersLine) {
            $headersLine = explode(":", $headersLine, 2);
            $headerName = trim($headersLine[0]); //TODO Is it this RFC-complaint?


            $this->headers[strtolower($headerName)] = array( //Key is case-insensitive
                $headerName, //Real, case sensitive header name
                (isset($headersLine[1])) ? trim($headersLine[1]) : "" //Header value
            );
        }

        //$this->logger->debug("Got HTTP headers, marking request as collected");
        $this->isRequestCollected = true;
    }

    /**
     * Provides HTTP method name
     *
     * @return string Eg. GET, HEAD, POST etc.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Provides raw URI string as client specified.
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Returns HTTP query string. It can be parsed using parse_str() later on.
     *
     * @return string
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * Decides if TCP connection should be closed after request completion.
     *
     * @return bool
     * @todo Verify if this method behaviour conforms to HTTP RFC
     */
    public function closeConnection()
    {
        $connection = $this->getHeader("connection");

        if ($connection === "keep-alive" || ($connection === false && (float)$this->httpVersion >= 1.1)) {
            return false;
        } else {
            return true;
        }
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
     * Returns HTTP protocol version of current request.
     *
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->httpVersion;
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
     * States whatever request has been fully received from client. Method will return true when all headers
     * has been received & all POST data are in place.
     *
     * @return bool
     */
    public function isReady()
    {
        return $this->isRequestCollected;
    }
}