<?php
namespace noFlash\CherryHttp;

use Psr\Log\LoggerInterface;

/**
 * Object represents single HTTP request made by client.
 *
 * @package noFlash\CherryHttp
 */
class HttpRequest extends HttpMessage
{
    /** @var integer Maximum URI length */
    const MAX_URI_LENGTH = 2048;
    /** @var integer Max allowed request headers length */
    const MAX_ENTITY_LENGTH = 8192; //If you need to mess with this value you have bigger problems than this :D
    /** @var LoggerInterface */
    protected $logger;
    protected $isRequestCollected = false;

    protected $method      = '';
    protected $queryString = '';
    protected $uri         = '';

    /**
     * @param string $headers HTTP request headers (along with status line)
     * @param LoggerInterface $logger
     *
     * @throws HttpException
     */
    public function __construct($headers, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->parseHeader($headers);
    }

    /**
     * Parses HTTP header & populates http headers
     *
     * @param string $header HTTP request header including status line & all headers
     *
     * @throws HttpException Raised if request header is not RFC-compilant
     */
    private function parseHeader($header)
    {
        //$this->logger->debug("Parsing request header");
        $header = explode("\r\n", $header); //Now it contains header lines
        $statusLine = explode(" ", $header[0], 3); //Eg.: GET /file HTTP/1.1 will be parsed into [GET, /file, HTTP/1.1]
        if (!isset($statusLine[2]) || substr($statusLine[2], 0, 5) !== "HTTP/") {
            throw new HttpException("Request is not HTTP compliant.", HttpCode::BAD_REQUEST, array(), true);
        }

        if (isset($statusLine[1][self::MAX_URI_LENGTH])) { //Much faster than using strlen(...) > MAX_URI_LENGTH
            throw new HttpException("URI should be equal or less than " . self::MAX_URI_LENGTH . " characters",
                HttpCode::REQUEST_URI_TOO_LONG);
        }

        //TODO shouldn't it convert method to uppercase?
        $this->method = $statusLine[0];

        $fullUri = explode("?", $statusLine[1], 2); //URL + query string, eg. [/file, var1=test&var2=foo&bar=derp]
        $this->uri = $fullUri[0];

        if (isset($fullUri[1])) {
            $this->queryString = $fullUri[1];
        }

        $this->protocolVersion = substr($statusLine[2], 5); //Everything after HTTP/ is http version number
        if ($this->protocolVersion !== "1.1" && $this->protocolVersion !== "1.0") {
            throw new HttpException("Requested HTTP version is not supported", HttpCode::VERSION_NOT_SUPPORTED, array(),
                true);
        }

        unset($header[0]); //Status line
        $this->populateHeaders($header); //Everything left from header is actually http headers

        $this->isRequestCollected = true;
        //$this->logger->debug("Got HTTP headers, request is collected");
    }

    /**
     * Parses HTTP headers into $this->headers array.
     * Note: This parser intentional doesn't implement folded headers (WTF is that anyway?!).
     *
     * @param $header
     *
     * @todo Check if fully conforms to RFC (trims)
     */
    private function populateHeaders($header)
    {
        $this->headers = array();

        //$this->logger->debug("Parsing HTTP request headers");
        foreach ($header as $headersLine) {
            $headersLine = explode(":", $headersLine, 2);
            $headerName = trim($headersLine[0]);
            $lowercaseName = strtolower($headerName);
            $headerValue = (isset($headersLine[1])) ? trim($headersLine[1]) : "";

            if (isset($this->headers[$lowercaseName])) { //Duplicated header with the same name (eg. Set-Cookie)
                if (is_array($this->headers[$lowercaseName][1])) {
                    $this->headers[$lowercaseName][1][] = $headerValue;
                } else {
                    $this->headers[$lowercaseName][1] = array($this->headers[$lowercaseName][1], $headerValue);
                }

            } else {
                $this->headers[$lowercaseName] = array($headerName, $headerValue);
            }
        }
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
     * States whatever request has been fully received from client. Method will return true when all headers
     * has been received & all POST data are in place.
     *
     * @return bool
     */
    public function isReady()
    {
        return $this->isRequestCollected;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        if (empty($this->messageCache)) {
            //@formatter:off PHPStorm formatter acts weird on such constructions and reformat it to single looong line
            $requestUri = $this->uri;
            if(!empty($this->queryString)) {
                $requestUri .= "?".$this->queryString;
            }

            $this->messageCache = $this->method . " " . $requestUri . " HTTP/" . $this->protocolVersion . "\r\n" .
                                  $this->getHeadersAsText() .
                                  "\r\n" .
                                  $this->body;
            //@formatter:on
        }

        return $this->messageCache;
    }
}
