<?php
namespace noFlash\CherryHttp;

use InvalidArgumentException;

/**
 * Class holds HTTP codes with it's metadata.
 * WebDav & exotic codes are NOT listed here.
 *
 * @package noFlash\CherryHttp
 */
class HttpCode
{
    const HTTP_CONTINUE                   = 100;
    const SWITCHING_PROTOCOLS             = 101;
    const PROCESSING                      = 102;
    const CONNECTION_TIMEOUT              = 110;
    const CONNECTION_REFUSED              = 111;
    const OK                              = 200;
    const CREATED                         = 201;
    const ACCEPTED                        = 202;
    const NONAUTHORITATIVE_INFORMATION    = 203;
    const NO_CONTENT                      = 204;
    const RESET_CONTENT                   = 205;
    const PARTIAL_CONTENT                 = 206;
    const IM_USED                         = 226;
    const MULTIPLE_CHOICES                = 300;
    const MOVED_PERMANENTLY               = 301;
    const FOUND                           = 302;
    const SEE_OTHER                       = 303;
    const NOT_MODIFIED                    = 304;
    const USE_PROXY                       = 305;
    const SWITCHING_PROXY                 = 306;
    const TEMPORARY_REDIRECT              = 307;
    const PERMANENT_REDIRECT              = 308;
    const TOO_MANY_REDIRECTS              = 310;
    const BAD_REQUEST                     = 400;
    const UNAUTHORIZED                    = 401;
    const PAYMENT_REQUIRED                = 402;
    const FORBIDDEN                       = 403;
    const NOT_FOUND                       = 404;
    const METHOD_NOT_ALLOWED              = 405;
    const NOT_ACCEPTABLE                  = 406;
    const PROXY_AUTHENTICATION_REQUIRED   = 407;
    const REQUEST_TIMEOUT                 = 408;
    const CONFLICT                        = 409;
    const GONE                            = 410;
    const LENGTH_REQUIRED                 = 411;
    const PRECONDITION_FAILED             = 412;
    const REQUEST_ENTITY_TOO_LARGE        = 413;
    const REQUEST_URI_TOO_LONG            = 414;
    const UNSUPPORTED_MEDIA_TYPE          = 415;
    const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const EXPECTATION_FAILED              = 417;
    const IM_A_TEAPOT                     = 418;
    const AUTHENTICATION_TIMEOUT          = 419;
    const UPGRADE_REQUIRED                = 426;
    const PRECONDITION_REQUIRED           = 428;
    const TOO_MANY_REQUESTS               = 429;
    const REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    const INTERNAL_SERVER_ERROR           = 500;
    const NOT_IMPLEMENTED                 = 501;
    const BAD_GATEWAY                     = 502;
    const SERVICE_UNAVAILABLE             = 503;
    const GATEWAY_TIMEOUT                 = 504;
    const VERSION_NOT_SUPPORTED           = 505;
    const VARIANT_ALSO_NEGOTIATES         = 506;
    const NOT_EXTENDED                    = 510;
    const NETWORK_AUTHENTICATION_REQUIRED = 511;

    public static $codesDescriptions = array(
        HttpCode::HTTP_CONTINUE => '100 Continue',
        HttpCode::SWITCHING_PROTOCOLS => '101 Switching Protocols',
        HttpCode::PROCESSING => '102 Processing',
        HttpCode::CONNECTION_TIMEOUT => '110 Connection Timed Out',
        HttpCode::CONNECTION_REFUSED => '111 Connection refused',
        HttpCode::OK => '200 OK',
        HttpCode::CREATED => '201 Created',
        HttpCode::ACCEPTED => '202 Accepted',
        HttpCode::NONAUTHORITATIVE_INFORMATION => '203 Non-Authoritative Information',
        HttpCode::NO_CONTENT => '204 No Content',
        HttpCode::RESET_CONTENT => '205 Reset Content',
        HttpCode::PARTIAL_CONTENT => '206 Partial Content',
        HttpCode::IM_USED => '226 IM Used',
        HttpCode::MULTIPLE_CHOICES => '300 Multiple Choices',
        HttpCode::MOVED_PERMANENTLY => '301 Moved Permanently',
        HttpCode::FOUND => '302 Found',
        HttpCode::SEE_OTHER => '303 See Other',
        HttpCode::NOT_MODIFIED => '304 Not Modified',
        HttpCode::USE_PROXY => '305 Use Proxy',
        HttpCode::SWITCHING_PROXY => '306 Switching Proxy',
        HttpCode::TEMPORARY_REDIRECT => '307 Temporary Redirect',
        HttpCode::PERMANENT_REDIRECT => '308 Permanent Redirect',
        HttpCode::TOO_MANY_REDIRECTS => '310 Too many redirects',
        HttpCode::BAD_REQUEST => '400 Bad Request',
        HttpCode::UNAUTHORIZED => '401 Unauthorized',
        HttpCode::PAYMENT_REQUIRED => '402 Payment Required',
        HttpCode::FORBIDDEN => '403 Forbidden',
        HttpCode::NOT_FOUND => '404 Not Found',
        HttpCode::METHOD_NOT_ALLOWED => '405 Method Not Allowed',
        HttpCode::NOT_ACCEPTABLE => '406 Not Acceptable',
        HttpCode::PROXY_AUTHENTICATION_REQUIRED => '407 Proxy Authentication Required',
        HttpCode::REQUEST_TIMEOUT => '408 Request Timeout',
        HttpCode::CONFLICT => '409 Conflict',
        HttpCode::GONE => '410 Gone',
        HttpCode::LENGTH_REQUIRED => '411 Length Required',
        HttpCode::PRECONDITION_FAILED => '412 Precondition Failed',
        HttpCode::REQUEST_ENTITY_TOO_LARGE => '413 Request Entity Too Large',
        HttpCode::REQUEST_URI_TOO_LONG => '414 Request-URI Too Long',
        HttpCode::UNSUPPORTED_MEDIA_TYPE => '415 Unsupported Media Type',
        HttpCode::REQUESTED_RANGE_NOT_SATISFIABLE => '416 Requested Range Not Satisfiable',
        HttpCode::EXPECTATION_FAILED => '417 Expectation Failed',
        HttpCode::IM_A_TEAPOT => '418 I\'m a teapot',
        HttpCode::AUTHENTICATION_TIMEOUT => '419 Authentication Timeout',
        HttpCode::UPGRADE_REQUIRED => '426 Upgrade Required',
        HttpCode::PRECONDITION_REQUIRED => '428 Precondition Required',
        HttpCode::TOO_MANY_REQUESTS => '429 Too Many Requests',
        HttpCode::REQUEST_HEADER_FIELDS_TOO_LARGE => '431 Request Header Fields Too Large',
        HttpCode::INTERNAL_SERVER_ERROR => '500 Internal Server Error',
        HttpCode::NOT_IMPLEMENTED => '501 Not Implemented',
        HttpCode::BAD_GATEWAY => '502 Bad Gateway',
        HttpCode::SERVICE_UNAVAILABLE => '503 Service Unavailable',
        HttpCode::GATEWAY_TIMEOUT => '504 Gateway Timeout',
        HttpCode::VERSION_NOT_SUPPORTED => '505 HTTP Version Not Supported',
        HttpCode::VARIANT_ALSO_NEGOTIATES => '506 Variant Also Negotiates',
        HttpCode::NOT_EXTENDED => '510 Not Extended',
        HttpCode::NETWORK_AUTHENTICATION_REQUIRED => '511 Network Authentication Required'
    );

    /**
     * Provides HTTP description for given code along with code itself (eg. 200 OK).
     *
     * @param $code
     *
     * @return string
     * @throws InvalidArgumentException Raised for invalid codes.
     */
    public static function getName($code)
    {
        if (!isset(self::$codesDescriptions[$code])) {
            throw new InvalidArgumentException("Invalid code $code specified");
        }

        return self::$codesDescriptions[$code];
    }

    /**
     * Provides information if provided code allow to contain body.
     *
     * @param integer $code Valid HTTP code.
     *
     * @return bool
     * @throws InvalidArgumentException Invalid code was provided.
     */
    public static function isBodyAllowed($code)
    {
        if (!isset(self::$codesDescriptions[$code])) {
            throw new InvalidArgumentException("Invalid code $code specified");
        }

        return ($code >= self::OK && $code !== self::NO_CONTENT && $code !== self::NOT_MODIFIED);
    }
}
