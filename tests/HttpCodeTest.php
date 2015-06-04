<?php
namespace noFlash\CherryHttp;


class HttpCodeTest extends \PHPUnit_Framework_TestCase
{
    public function httpCodesProvider()
    {
        return array(
            array('HTTP_CONTINUE', 100),
            array('SWITCHING_PROTOCOLS', 101),
            array('PROCESSING', 102),
            array('CONNECTION_TIMEOUT', 110),
            array('CONNECTION_REFUSED', 111),
            array('OK', 200),
            array('CREATED', 201),
            array('ACCEPTED', 202),
            array('NONAUTHORITATIVE_INFORMATION', 203),
            array('NO_CONTENT', 204),
            array('RESET_CONTENT', 205),
            array('PARTIAL_CONTENT', 206),
            array('IM_USED', 226),
            array('MULTIPLE_CHOICES', 300),
            array('MOVED_PERMANENTLY', 301),
            array('FOUND', 302),
            array('SEE_OTHER', 303),
            array('NOT_MODIFIED', 304),
            array('USE_PROXY', 305),
            array('SWITCHING_PROXY', 306),
            array('TEMPORARY_REDIRECT', 307),
            array('PERMANENT_REDIRECT', 308),
            array('TOO_MANY_REDIRECTS', 310),
            array('BAD_REQUEST', 400),
            array('UNAUTHORIZED', 401),
            array('PAYMENT_REQUIRED', 402),
            array('FORBIDDEN', 403),
            array('NOT_FOUND', 404),
            array('METHOD_NOT_ALLOWED', 405),
            array('NOT_ACCEPTABLE', 406),
            array('PROXY_AUTHENTICATION_REQUIRED', 407),
            array('REQUEST_TIMEOUT', 408),
            array('CONFLICT', 409),
            array('GONE', 410),
            array('LENGTH_REQUIRED', 411),
            array('PRECONDITION_FAILED', 412),
            array('REQUEST_ENTITY_TOO_LARGE', 413),
            array('REQUEST_URI_TOO_LONG', 414),
            array('UNSUPPORTED_MEDIA_TYPE', 415),
            array('REQUESTED_RANGE_NOT_SATISFIABLE', 416),
            array('EXPECTATION_FAILED', 417),
            array('IM_A_TEAPOT', 418),
            array('AUTHENTICATION_TIMEOUT', 419),
            array('UPGRADE_REQUIRED', 426),
            array('PRECONDITION_REQUIRED', 428),
            array('TOO_MANY_REQUESTS', 429),
            array('REQUEST_HEADER_FIELDS_TOO_LARGE', 431),
            array('INTERNAL_SERVER_ERROR', 500),
            array('NOT_IMPLEMENTED', 501),
            array('BAD_GATEWAY', 502),
            array('SERVICE_UNAVAILABLE', 503),
            array('GATEWAY_TIMEOUT', 504),
            array('VERSION_NOT_SUPPORTED', 505),
            array('VARIANT_ALSO_NEGOTIATES', 506),
            array('NOT_EXTENDED', 510),
            array('NETWORK_AUTHENTICATION_REQUIRED', 511)
        );
    }

    /**
     * @dataProvider httpCodesProvider
     */
    public function testClassContainsAllHttpCodesConstants($constantName, $code)
    {
        $constantName = '\noFlash\CherryHttp\HttpCode::' . $constantName;

        $this->assertTrue(defined($constantName), "Constant $constantName not found");
        $this->assertSame(constant($constantName), $code, "Invalid value for constant $constantName");
    }
}
