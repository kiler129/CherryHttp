<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Http\Response;

/**
 * Class HttpResponseCode holds all HTTP codes registered by IANA.
 * All descriptions are consistent with registry entries, except code groups where grammatical form was modified.
 *
 * Class was marked final to prevent abusing it by adding non-standard HTTP codes.
 *
 * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
 */

final class ResponseCode
{
    /******* Groups *******/
    const GROUP_UNKNOWN       = 0;
    const GROUP_INFORMATIONAL = 100;
    const GROUP_SUCCESS       = 200;
    const GROUP_REDIRECTION   = 300;
    const GROUP_CLIENT_ERROR  = 400;
    const GROUP_SERVER_ERROR  = 500;


    /******* Information group *******/
    const CONTINUE_INFORMATION = 100;
    const SWITCHING_PROTOCOLS  = 101;
    const PROCESSING           = 102;
    //103-199 => 'Unassigned'

    /******* Success group *******/
    const OK                            = 200;
    const CREATED                       = 201;
    const ACCEPTED                      = 202;
    const NON_AUTHORITATIVE_INFORMATION = 203;
    const NO_CONTENT                    = 204;
    const RESET_CONTENT                 = 205;
    const PARTIAL_CONTENT               = 206;
    const MULTI_STATUS                  = 207;
    const ALREADY_REPORTED              = 208;
    //209-225 => 'Unassigned'
    const IM_USED = 226;
    //227-299 => 'Unassigned'

    /******* Redirect group *******/
    const MULTIPLE_CHOICES   = 300;
    const MOVED_PERMANENTLY  = 301;
    const FOUND              = 302;
    const SEE_OTHER          = 303;
    const NOT_MODIFIED       = 304;
    const USE_PROXY          = 305;
    const SWITCH_PROXY       = 306; //No longer used
    const TEMPORARY_REDIRECT = 307;
    const PERMANENT_REDIRECT = 308;

    /******* Client error group *******/
    const BAD_REQUEST                   = 400;
    const UNAUTHORIZED                  = 401;
    const PAYMENT_REQUIRED              = 402;
    const FORBIDDEN                     = 403;
    const NOT_FOUND                     = 404;
    const METHOD_NOT_ALLOWED            = 405;
    const NOT_ACCEPTABLE                = 406;
    const PROXY_AUTHENTICATION_REQUIRED = 407;
    const REQUEST_TIMEOUT               = 408;
    const CONFLICT                      = 409;
    const GONE                          = 410;
    const LENGTH_REQUIRED               = 411;
    const PRECONDITION_FAILED           = 412;
    const PAYLOAD_TOO_LARGE             = 413;
    const URI_TOO_LONG                  = 414;
    const UNSUPPORTED_MEDIA_TYPE        = 415;
    const RANGE_NOT_SATISFIABLE         = 416;
    const EXPECTATION_FAILED            = 417;
    //418-420 => 'Unassigned'
    const MISDIRECTED_REQUEST             = 421;
    const UNPROCESSABLE_ENTITY            = 422;
    const LOCKED                          = 423;
    const FAILED_DEPENDENCY               = 424;
    const UPGRADE_REQUIRED                = 426;
    const PRECONDITION_REQUIRED           = 428;
    const TOO_MANY_REQUESTS               = 429;
    const REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    //432-450 => 'Unassigned'
    const UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    //452-499 => 'Unassigned'

    /******* Server error group *******/
    const INTERNAL_SERVER_ERROR           = 500;
    const NOT_IMPLEMENTED                 = 501;
    const BAD_GATEWAY                     = 502;
    const SERVICE_UNAVAILABLE             = 503;
    const GATEWAY_TIMEOUT                 = 504;
    const HTTP_VERSION_NOT_SUPPORTED      = 505;
    const VARIANT_ALSO_NEGOTIATES         = 506;
    const INSUFFICIENT_STORAGE            = 507;
    const LOOP_DETECTED                   = 508;
    const NOT_EXTENDED                    = 510;
    const NETWORK_AUTHENTICATION_REQUIRED = 511;
    //512-599 => 'Unassigned'

    private static $codeGroups = [
        self::GROUP_UNKNOWN       => 'Unknown', //Internally reserved group for out-of-spec response codes
        self::GROUP_INFORMATIONAL => 'Information', //Request received, continuing process
        self::GROUP_SUCCESS       => 'Success', //The action was successfully received, understood, and accepted
        self::GROUP_REDIRECTION   => 'Redirecting', //Further action must be taken in order to complete the request
        self::GROUP_CLIENT_ERROR  => 'Client Error', //The request contains bad syntax or cannot be fulfilled
        self::GROUP_SERVER_ERROR  => 'Server Error' //The server failed to fulfill an apparently valid request
    ];

    private static $codesDescription = [
        /******* Information group *******/
        self::CONTINUE_INFORMATION            => 'Continue',
        self::SWITCHING_PROTOCOLS             => 'Switching Protocols',
        self::PROCESSING                      => 'Processing',
        //103-199 => 'Unassigned',

        /******* Success group *******/
        self::OK                              => 'OK',
        self::CREATED                         => 'Created',
        self::ACCEPTED                        => 'Accepted',
        self::NON_AUTHORITATIVE_INFORMATION   => 'Non-Authoritative Information',
        self::NO_CONTENT                      => 'No Content',
        self::RESET_CONTENT                   => 'Reset Content',
        self::PARTIAL_CONTENT                 => 'Partial Content',
        self::MULTI_STATUS                    => 'Multi-Status',
        self::ALREADY_REPORTED                => 'Already Reported',
        //209-225 => 'Unassigned
        self::IM_USED                         => 'IM Used',
        //227-299 => 'Unassigned'

        /******* Redirection group *******/
        self::MULTIPLE_CHOICES                => 'Multiple Choices',
        self::MOVED_PERMANENTLY               => 'Moved Permanently',
        self::FOUND                           => 'Found',
        self::SEE_OTHER                       => 'See Other',
        self::NOT_MODIFIED                    => 'Not Modified',
        self::USE_PROXY                       => 'Use Proxy',
        self::SWITCH_PROXY                    => 'Switch Proxy', //No longer used
        self::TEMPORARY_REDIRECT              => 'Temporary Redirect',
        self::PERMANENT_REDIRECT              => 'Permanent Redirect',
        //309-399 => 'Unassigned'

        /******* Client error group *******/
        self::BAD_REQUEST                     => 'Bad Request',
        self::UNAUTHORIZED                    => 'Unauthorized',
        self::PAYMENT_REQUIRED                => 'Payment Required',
        self::FORBIDDEN                       => 'Forbidden',
        self::NOT_FOUND                       => 'Not Found',
        self::METHOD_NOT_ALLOWED              => 'Method Not Allowed',
        self::NOT_ACCEPTABLE                  => 'Not Acceptable',
        self::PROXY_AUTHENTICATION_REQUIRED   => 'Proxy Authentication Required',
        self::REQUEST_TIMEOUT                 => 'Request Timeout',
        self::CONFLICT                        => 'Conflict',
        self::GONE                            => 'Gone',
        self::LENGTH_REQUIRED                 => 'Length Required',
        self::PRECONDITION_FAILED             => 'Precondition Failed',
        self::PAYLOAD_TOO_LARGE               => 'Payload Too Large',
        self::URI_TOO_LONG                    => 'URI Too Long',
        self::UNSUPPORTED_MEDIA_TYPE          => 'Unsupported Media Type',
        self::RANGE_NOT_SATISFIABLE           => 'Range Not Satisfiable',
        self::EXPECTATION_FAILED              => 'Expectation Failed',
        //418-420 => 'Unassigned'
        self::MISDIRECTED_REQUEST             => 'Misdirected Request',
        self::UNPROCESSABLE_ENTITY            => 'Unprocessable Entity',
        self::LOCKED                          => 'Locked',
        self::FAILED_DEPENDENCY               => 'Failed Dependency',
        425                                   => 'Unassigned',
        self::UPGRADE_REQUIRED                => 'Upgrade Required',
        427                                   => 'Unassigned',
        self::PRECONDITION_REQUIRED           => 'Precondition Required',
        self::TOO_MANY_REQUESTS               => 'Too Many Requests',
        430                                   => 'Unassigned',
        self::REQUEST_HEADER_FIELDS_TOO_LARGE => 'Request Header Fields Too Large',
        //432-450 => 'Unassigned'
        self::UNAVAILABLE_FOR_LEGAL_REASONS   => 'Unavailable for Legal Reasons',
        //452-499 => 'Unassigned'

        /******* Server error group *******/
        self::INTERNAL_SERVER_ERROR           => 'Internal Server Error',
        self::NOT_IMPLEMENTED                 => 'Not Implemented',
        self::BAD_GATEWAY                     => 'Bad Gateway',
        self::SERVICE_UNAVAILABLE             => 'Service Unavailable',
        self::GATEWAY_TIMEOUT                 => 'Gateway Timeout',
        self::HTTP_VERSION_NOT_SUPPORTED      => 'HTTP Version Not Supported',
        self::VARIANT_ALSO_NEGOTIATES         => 'Variant Also Negotiates',
        self::INSUFFICIENT_STORAGE            => 'Insufficient Storage',
        self::LOOP_DETECTED                   => 'Loop Detected',
        509                                   => 'Unassigned',
        self::NOT_EXTENDED                    => 'Not Extended',
        self::NETWORK_AUTHENTICATION_REQUIRED => 'Network Authentication Required',
        //512-599 => 'Unassigned'
    ];

    /**
     * Verifies if given code can be used as HTTP code.
     * Please note this method DO NOT verify whatever code was registered by IANA.
     *
     * @param int $code
     *
     * @return bool
     */
    public static function isCodeValid($code)
    {
        return (!($code < 100 || $code > 999) && (int)$code === $code);
    }

    /**
     * Verifies if given code is valid HTTP code registered by IANA.
     *
     * @param int $code
     *
     * @return bool
     */
    public static function isCodeRegistered($code)
    {
        return (isset(self::$codesDescription[$code]) && (int)$code === $code);
    }

    /**
     * Provides description for IANA registered HTTP codes.
     * If not registered code was specified respective group description is returned.
     *
     * @param int $code
     *
     * @return string
     */
    public static function getReasonPhraseByCode($code)
    {
        if (isset(self::$codesDescription[$code])) {
            return self::$codesDescription[$code];
        }

        $group = self::getGroupFromCode($code);

        return self::getReasonPhraseByGroup($group);
    }

    /**
     * HTTP codes are divided into 5 groups: informational, success, redirections, client errors and server errors.
     * This method provides group number (100/200/300/400/500) for given code. If code was specified outside IANA
     * boundaries 0 is returned.
     *
     * @param int $code
     *
     * @return int
     */
    public static function getGroupFromCode($code)
    {
        $code = (int)($code - ($code % 100)); //Am I crazy using the same variable to save CPU cycles?

        return (isset(self::$codeGroups[$code])) ? $code : 0;
    }

    /**
     * Provides description for given HTTP code group.
     * If non-existing group was specified default (aka group 0) description is returned.
     *
     * @param int $group One of HTTP codes groups (e.g. 200).
     *
     * @return string
     */
    public static function getReasonPhraseByGroup($group)
    {
        if (!isset(self::$codeGroups[$group])) {
            $group = 0;
        }

        return self::$codeGroups[$group];
    }
}
