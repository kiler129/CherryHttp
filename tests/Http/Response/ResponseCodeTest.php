<?php
/*
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible
 *  for everything that code will do.
 *
 * (c) Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace noFlash\CherryHttp\Tests\Http\Response;

use noFlash\CherryHttp\Http\Response\ResponseCode;

class ResponseCodeTest extends \PHPUnit_Framework_TestCase
{
    public function testClassContainsConstantsForAllAssignedCodesWithProperValues()
    {
        $this->assertSame(100, ResponseCode::CONTINUE_INFORMATION);
        $this->assertSame(101, ResponseCode::SWITCHING_PROTOCOLS);
        $this->assertSame(102, ResponseCode::PROCESSING);
        $this->assertSame(200, ResponseCode::OK);
        $this->assertSame(201, ResponseCode::CREATED);
        $this->assertSame(202, ResponseCode::ACCEPTED);
        $this->assertSame(203, ResponseCode::NON_AUTHORITATIVE_INFORMATION);
        $this->assertSame(204, ResponseCode::NO_CONTENT);
        $this->assertSame(205, ResponseCode::RESET_CONTENT);
        $this->assertSame(206, ResponseCode::PARTIAL_CONTENT);
        $this->assertSame(207, ResponseCode::MULTI_STATUS);
        $this->assertSame(208, ResponseCode::ALREADY_REPORTED);
        $this->assertSame(226, ResponseCode::IM_USED);
        $this->assertSame(300, ResponseCode::MULTIPLE_CHOICES);
        $this->assertSame(301, ResponseCode::MOVED_PERMANENTLY);
        $this->assertSame(302, ResponseCode::FOUND);
        $this->assertSame(303, ResponseCode::SEE_OTHER);
        $this->assertSame(304, ResponseCode::NOT_MODIFIED);
        $this->assertSame(305, ResponseCode::USE_PROXY);
        $this->assertSame(306, ResponseCode::SWITCH_PROXY);
        $this->assertSame(307, ResponseCode::TEMPORARY_REDIRECT);
        $this->assertSame(308, ResponseCode::PERMANENT_REDIRECT);
        $this->assertSame(400, ResponseCode::BAD_REQUEST);
        $this->assertSame(401, ResponseCode::UNAUTHORIZED);
        $this->assertSame(402, ResponseCode::PAYMENT_REQUIRED);
        $this->assertSame(403, ResponseCode::FORBIDDEN);
        $this->assertSame(404, ResponseCode::NOT_FOUND);
        $this->assertSame(405, ResponseCode::METHOD_NOT_ALLOWED);
        $this->assertSame(406, ResponseCode::NOT_ACCEPTABLE);
        $this->assertSame(407, ResponseCode::PROXY_AUTHENTICATION_REQUIRED);
        $this->assertSame(408, ResponseCode::REQUEST_TIMEOUT);
        $this->assertSame(409, ResponseCode::CONFLICT);
        $this->assertSame(410, ResponseCode::GONE);
        $this->assertSame(411, ResponseCode::LENGTH_REQUIRED);
        $this->assertSame(412, ResponseCode::PRECONDITION_FAILED);
        $this->assertSame(413, ResponseCode::PAYLOAD_TOO_LARGE);
        $this->assertSame(414, ResponseCode::URI_TOO_LONG);
        $this->assertSame(415, ResponseCode::UNSUPPORTED_MEDIA_TYPE);
        $this->assertSame(416, ResponseCode::RANGE_NOT_SATISFIABLE);
        $this->assertSame(417, ResponseCode::EXPECTATION_FAILED);
        $this->assertSame(421, ResponseCode::MISDIRECTED_REQUEST);
        $this->assertSame(422, ResponseCode::UNPROCESSABLE_ENTITY);
        $this->assertSame(423, ResponseCode::LOCKED);
        $this->assertSame(424, ResponseCode::FAILED_DEPENDENCY);
        $this->assertSame(426, ResponseCode::UPGRADE_REQUIRED);
        $this->assertSame(428, ResponseCode::PRECONDITION_REQUIRED);
        $this->assertSame(429, ResponseCode::TOO_MANY_REQUESTS);
        $this->assertSame(431, ResponseCode::REQUEST_HEADER_FIELDS_TOO_LARGE);
        $this->assertSame(451, ResponseCode::UNAVAILABLE_FOR_LEGAL_REASONS);
        $this->assertSame(500, ResponseCode::INTERNAL_SERVER_ERROR);
        $this->assertSame(501, ResponseCode::NOT_IMPLEMENTED);
        $this->assertSame(502, ResponseCode::BAD_GATEWAY);
        $this->assertSame(503, ResponseCode::SERVICE_UNAVAILABLE);
        $this->assertSame(504, ResponseCode::GATEWAY_TIMEOUT);
        $this->assertSame(505, ResponseCode::HTTP_VERSION_NOT_SUPPORTED);
        $this->assertSame(506, ResponseCode::VARIANT_ALSO_NEGOTIATES);
        $this->assertSame(507, ResponseCode::INSUFFICIENT_STORAGE);
        $this->assertSame(508, ResponseCode::LOOP_DETECTED);
        $this->assertSame(510, ResponseCode::NOT_EXTENDED);
        $this->assertSame(511, ResponseCode::NETWORK_AUTHENTICATION_REQUIRED);

    }

    public function ianaCodesWithGroupsProvider()
    {
        return [
            [100, 100],
            [101, 100],
            [102, 100],
            [200, 200],
            [201, 200],
            [202, 200],
            [203, 200],
            [204, 200],
            [205, 200],
            [206, 200],
            [207, 200],
            [208, 200],
            [226, 200],
            [300, 300],
            [301, 300],
            [302, 300],
            [303, 300],
            [304, 300],
            [305, 300],
            [306, 300],
            [307, 300],
            [308, 300],
            [400, 400],
            [401, 400],
            [402, 400],
            [403, 400],
            [404, 400],
            [405, 400],
            [406, 400],
            [407, 400],
            [408, 400],
            [409, 400],
            [410, 400],
            [411, 400],
            [412, 400],
            [413, 400],
            [414, 400],
            [415, 400],
            [416, 400],
            [417, 400],
            [421, 400],
            [422, 400],
            [423, 400],
            [424, 400],
            [425, 400],
            [426, 400],
            [427, 400],
            [428, 400],
            [429, 400],
            [430, 400],
            [431, 400],
            [451, 400],
            [500, 500],
            [501, 500],
            [502, 500],
            [503, 500],
            [504, 500],
            [505, 500],
            [506, 500],
            [507, 500],
            [508, 500],
            [509, 500],
            [510, 500],
            [511, 500]
        ];
    }

    public function testSemanticallyValidCodesAreConsideredValid()
    {
        $this->assertTrue(ResponseCode::isCodeValid(100));
        $this->assertTrue(ResponseCode::isCodeValid(110));
        $this->assertTrue(ResponseCode::isCodeValid(199));
        $this->assertTrue(ResponseCode::isCodeValid(214));
        $this->assertTrue(ResponseCode::isCodeValid(227));
        $this->assertTrue(ResponseCode::isCodeValid(350));
        $this->assertTrue(ResponseCode::isCodeValid(499));
        $this->assertTrue(ResponseCode::isCodeValid(555));
        $this->assertTrue(ResponseCode::isCodeValid(600));
        $this->assertTrue(ResponseCode::isCodeValid(700));
        $this->assertTrue(ResponseCode::isCodeValid(888));
        $this->assertTrue(ResponseCode::isCodeValid(999));
    }

    /**
     * @dataProvider ianaCodesWithGroupsProvider
     */
    public function testAllRegisteredCodesAreConsideredSemanticallyValid($code)
    {
        $this->assertTrue(ResponseCode::isCodeValid($code));
    }

    public function testSemanticCodeValidationRejectsInvalidValues()
    {
        $this->assertFalse(ResponseCode::isCodeValid(99));
        $this->assertFalse(ResponseCode::isCodeValid(1000));
        $this->assertFalse(ResponseCode::isCodeValid(100.1));
        $this->assertFalse(ResponseCode::isCodeValid(false));
        $this->assertFalse(ResponseCode::isCodeValid(0));
        $this->assertFalse(ResponseCode::isCodeValid(null));
        $this->assertFalse(ResponseCode::isCodeValid(true));
    }

    /**
     * @dataProvider ianaCodesWithGroupsProvider
     */
    public function testIsCodeRegisteredReturnsTrueForAllIanaRegisteredCodes($code)
    {
        $this->assertTrue(ResponseCode::isCodeRegistered($code));
    }

    public function testSemanticallyInvalidCodesAreNotConsideredRegistered()
    {
        $this->assertFalse(ResponseCode::isCodeRegistered(99));
        $this->assertFalse(ResponseCode::isCodeRegistered(999));
        $this->assertFalse(ResponseCode::isCodeRegistered(400.3));
        $this->assertFalse(ResponseCode::isCodeRegistered(1111));
        $this->assertFalse(ResponseCode::isCodeRegistered(false));
        $this->assertFalse(ResponseCode::isCodeRegistered(0));
        $this->assertFalse(ResponseCode::isCodeRegistered(null));
        $this->assertFalse(ResponseCode::isCodeRegistered(true));
    }

    public function testCodesFromUnassignedRangesAreConsideredNotRegistered()
    {
        $this->assertFalse(ResponseCode::isCodeRegistered(103));
        $this->assertFalse(ResponseCode::isCodeRegistered(110));
        $this->assertFalse(ResponseCode::isCodeRegistered(199));
        $this->assertFalse(ResponseCode::isCodeRegistered(209));
        $this->assertFalse(ResponseCode::isCodeRegistered(211));
        $this->assertFalse(ResponseCode::isCodeRegistered(225));
        $this->assertFalse(ResponseCode::isCodeRegistered(227));
        $this->assertFalse(ResponseCode::isCodeRegistered(260));
        $this->assertFalse(ResponseCode::isCodeRegistered(299));
        $this->assertFalse(ResponseCode::isCodeRegistered(309));
        $this->assertFalse(ResponseCode::isCodeRegistered(372));
        $this->assertFalse(ResponseCode::isCodeRegistered(399));
        $this->assertFalse(ResponseCode::isCodeRegistered(418)); //Sorry teapot ;<
        $this->assertFalse(ResponseCode::isCodeRegistered(419));
        $this->assertFalse(ResponseCode::isCodeRegistered(420));
        $this->assertFalse(ResponseCode::isCodeRegistered(432));
        $this->assertFalse(ResponseCode::isCodeRegistered(438));
        $this->assertFalse(ResponseCode::isCodeRegistered(450));
        $this->assertFalse(ResponseCode::isCodeRegistered(452));
        $this->assertFalse(ResponseCode::isCodeRegistered(499));
        $this->assertFalse(ResponseCode::isCodeRegistered(512));
        $this->assertFalse(ResponseCode::isCodeRegistered(530));
        $this->assertFalse(ResponseCode::isCodeRegistered(599));
    }

    /**
     * @dataProvider ianaCodesWithGroupsProvider
     */
    public function testValidGroupIsReturnedForAllRegisteredCodes($code, $group)
    {
        $this->assertSame($group, ResponseCode::getGroupFromCode($code));
    }

    public function testCodesFromRegisteredRangesHaveValidGroupsAssigned()
    {
        //All codes below are unassigned, but belongs to valid groups
        $this->assertSame(100, ResponseCode::getGroupFromCode(103));
        $this->assertSame(100, ResponseCode::getGroupFromCode(110));
        $this->assertSame(100, ResponseCode::getGroupFromCode(199));
        $this->assertSame(200, ResponseCode::getGroupFromCode(209));
        $this->assertSame(200, ResponseCode::getGroupFromCode(211));
        $this->assertSame(200, ResponseCode::getGroupFromCode(225));
        $this->assertSame(200, ResponseCode::getGroupFromCode(227));
        $this->assertSame(200, ResponseCode::getGroupFromCode(260));
        $this->assertSame(200, ResponseCode::getGroupFromCode(299));
        $this->assertSame(300, ResponseCode::getGroupFromCode(309));
        $this->assertSame(300, ResponseCode::getGroupFromCode(372));
        $this->assertSame(300, ResponseCode::getGroupFromCode(399));
        $this->assertSame(400, ResponseCode::getGroupFromCode(418));
        $this->assertSame(400, ResponseCode::getGroupFromCode(419));
        $this->assertSame(400, ResponseCode::getGroupFromCode(420));
        $this->assertSame(400, ResponseCode::getGroupFromCode(432));
        $this->assertSame(400, ResponseCode::getGroupFromCode(438));
        $this->assertSame(400, ResponseCode::getGroupFromCode(450));
        $this->assertSame(400, ResponseCode::getGroupFromCode(452));
        $this->assertSame(400, ResponseCode::getGroupFromCode(499));
        $this->assertSame(500, ResponseCode::getGroupFromCode(512));
        $this->assertSame(500, ResponseCode::getGroupFromCode(530));
        $this->assertSame(500, ResponseCode::getGroupFromCode(599));

        //Invalid BUT it could happen - DO NOT consider this as expected behaviour
        $this->assertSame(400, ResponseCode::getGroupFromCode(499.4));
    }

    public function testUnknownCodesAreAssignedZeroGroup()
    {
        $this->assertSame(0, ResponseCode::getGroupFromCode(false));
        $this->assertSame(0, ResponseCode::getGroupFromCode(true));
        $this->assertSame(0, ResponseCode::getGroupFromCode(null));
        $this->assertSame(0, ResponseCode::getGroupFromCode(0));
        $this->assertSame(0, ResponseCode::getGroupFromCode(99));
        $this->assertSame(0, ResponseCode::getGroupFromCode(600));
        $this->assertSame(0, ResponseCode::getGroupFromCode(700));
        $this->assertSame(0, ResponseCode::getGroupFromCode(898));
        $this->assertSame(0, ResponseCode::getGroupFromCode(999));
    }

    public function testRegisteredGroupsGetsProperDescription()
    {
        $this->assertSame('Information', ResponseCode::getReasonPhraseByGroup(100));
        $this->assertSame('Success', ResponseCode::getReasonPhraseByGroup(200));
        $this->assertSame('Redirecting', ResponseCode::getReasonPhraseByGroup(300));
        $this->assertSame('Client Error', ResponseCode::getReasonPhraseByGroup(400));
        $this->assertSame('Server Error', ResponseCode::getReasonPhraseByGroup(500));
    }

    public function testUnknownGroupsGetsDefaultDescription()
    {
        static $defaultDescription = 'Unknown';

        $this->assertSame($defaultDescription, ResponseCode::getReasonPhraseByGroup(false));
        $this->assertSame($defaultDescription, ResponseCode::getReasonPhraseByGroup(true));
        $this->assertSame($defaultDescription, ResponseCode::getReasonPhraseByGroup(null));
        $this->assertSame($defaultDescription, ResponseCode::getReasonPhraseByGroup(0));
        $this->assertSame($defaultDescription, ResponseCode::getReasonPhraseByGroup(9));
        $this->assertSame($defaultDescription, ResponseCode::getReasonPhraseByGroup(99));
        $this->assertSame($defaultDescription, ResponseCode::getReasonPhraseByGroup(999));
        $this->assertSame($defaultDescription, ResponseCode::getReasonPhraseByGroup(600));
        $this->assertSame($defaultDescription, ResponseCode::getReasonPhraseByGroup(700));
        $this->assertSame($defaultDescription, ResponseCode::getReasonPhraseByGroup(800));
        $this->assertSame($defaultDescription, ResponseCode::getReasonPhraseByGroup(900));
    }

    public function ianaCodesDescriptionsProvider()
    {
        return [
            [100, 'Continue'],
            [101, 'Switching Protocols'],
            [102, 'Processing'],
            [200, 'OK'],
            [201, 'Created'],
            [202, 'Accepted'],
            [203, 'Non-Authoritative Information'],
            [204, 'No Content'],
            [205, 'Reset Content'],
            [206, 'Partial Content'],
            [207, 'Multi-Status'],
            [208, 'Already Reported'],
            [226, 'IM Used'],
            [300, 'Multiple Choices'],
            [301, 'Moved Permanently'],
            [302, 'Found'],
            [303, 'See Other'],
            [304, 'Not Modified'],
            [305, 'Use Proxy'],
            [306, 'Switch Proxy'],
            [307, 'Temporary Redirect'],
            [308, 'Permanent Redirect'],
            [400, 'Bad Request'],
            [401, 'Unauthorized'],
            [402, 'Payment Required'],
            [403, 'Forbidden'],
            [404, 'Not Found'],
            [405, 'Method Not Allowed'],
            [406, 'Not Acceptable'],
            [407, 'Proxy Authentication Required'],
            [408, 'Request Timeout'],
            [409, 'Conflict'],
            [410, 'Gone'],
            [411, 'Length Required'],
            [412, 'Precondition Failed'],
            [413, 'Payload Too Large'],
            [414, 'URI Too Long'],
            [415, 'Unsupported Media Type'],
            [416, 'Range Not Satisfiable'],
            [417, 'Expectation Failed'],
            [421, 'Misdirected Request'],
            [422, 'Unprocessable Entity'],
            [423, 'Locked'],
            [424, 'Failed Dependency'],
            [425, 'Unassigned'],
            [426, 'Upgrade Required'],
            [427, 'Unassigned'],
            [428, 'Precondition Required'],
            [429, 'Too Many Requests'],
            [430, 'Unassigned'],
            [431, 'Request Header Fields Too Large'],
            [451, 'Unavailable for Legal Reasons'],
            [500, 'Internal Server Error'],
            [501, 'Not Implemented'],
            [502, 'Bad Gateway'],
            [503, 'Service Unavailable'],
            [504, 'Gateway Timeout'],
            [505, 'HTTP Version Not Supported'],
            [506, 'Variant Also Negotiates'],
            [507, 'Insufficient Storage'],
            [508, 'Loop Detected'],
            [509, 'Unassigned'],
            [510, 'Not Extended'],
            [511, 'Network Authentication Required']
        ];
    }

    /**
     * @dataProvider ianaCodesDescriptionsProvider
     */
    public function testAllRegisteredCodesGetsValidDescription($code, $rfcDescription)
    {
        $this->assertSame($rfcDescription, ResponseCode::getReasonPhraseByCode($code));
    }

    public function testNotRegisteredCodesGetsGroupDescription()
    {
        //All codes below are unassigned, but belongs to valid groups
        $this->assertSame('Information', ResponseCode::getReasonPhraseByCode(103));
        $this->assertSame('Information', ResponseCode::getReasonPhraseByCode(110));
        $this->assertSame('Information', ResponseCode::getReasonPhraseByCode(199));
        $this->assertSame('Success', ResponseCode::getReasonPhraseByCode(209));
        $this->assertSame('Success', ResponseCode::getReasonPhraseByCode(211));
        $this->assertSame('Success', ResponseCode::getReasonPhraseByCode(225));
        $this->assertSame('Success', ResponseCode::getReasonPhraseByCode(227));
        $this->assertSame('Success', ResponseCode::getReasonPhraseByCode(260));
        $this->assertSame('Success', ResponseCode::getReasonPhraseByCode(299));
        $this->assertSame('Redirecting', ResponseCode::getReasonPhraseByCode(309));
        $this->assertSame('Redirecting', ResponseCode::getReasonPhraseByCode(372));
        $this->assertSame('Redirecting', ResponseCode::getReasonPhraseByCode(399));
        $this->assertSame('Client Error', ResponseCode::getReasonPhraseByCode(418));
        $this->assertSame('Client Error', ResponseCode::getReasonPhraseByCode(419));
        $this->assertSame('Client Error', ResponseCode::getReasonPhraseByCode(420));
        $this->assertSame('Client Error', ResponseCode::getReasonPhraseByCode(432));
        $this->assertSame('Client Error', ResponseCode::getReasonPhraseByCode(438));
        $this->assertSame('Client Error', ResponseCode::getReasonPhraseByCode(450));
        $this->assertSame('Client Error', ResponseCode::getReasonPhraseByCode(452));
        $this->assertSame('Client Error', ResponseCode::getReasonPhraseByCode(499));
        $this->assertSame('Server Error', ResponseCode::getReasonPhraseByCode(512));
        $this->assertSame('Server Error', ResponseCode::getReasonPhraseByCode(530));
        $this->assertSame('Server Error', ResponseCode::getReasonPhraseByCode(599));
    }

    public function testUnknownCodesGetsDefaultGroupDescription()
    {
        static $defaultDescription = 'Unknown';

        $this->assertSame($defaultDescription, ResponseCode::getReasonPhraseByCode(false));
        $this->assertSame($defaultDescription, ResponseCode::getReasonPhraseByCode(true));
        $this->assertSame($defaultDescription, ResponseCode::getReasonPhraseByCode(null));
        $this->assertSame($defaultDescription, ResponseCode::getReasonPhraseByCode(0));
        $this->assertSame($defaultDescription, ResponseCode::getReasonPhraseByCode(99));
        $this->assertSame($defaultDescription, ResponseCode::getReasonPhraseByCode(600));
        $this->assertSame($defaultDescription, ResponseCode::getReasonPhraseByCode(700));
        $this->assertSame($defaultDescription, ResponseCode::getReasonPhraseByCode(898));
        $this->assertSame($defaultDescription, ResponseCode::getReasonPhraseByCode(999));
    }
}
