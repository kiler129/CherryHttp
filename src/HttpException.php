<?php
namespace noFlash\CherryHttp;

use Exception;

/**
 * Standard HTTP exception - client request cannot be handled for whatever reason (404, 500, 400...)
 *
 * @package noFlash\CherryHttp
 */
class HttpException extends Exception
{
    /** @var HttpResponse */
    private $response;

    /**
     * @param string $message Human-readable text message.
     * @param int $code Any valid HTTP code. Codes can be found as constants in HttpResponse class.
     * @param array $extraHeaders Extra headers which should be send along with error response.
     * @param bool $disconnect Whatever to drop client after sending response. It depends on code & application
     * implementation. However it's recommended to leave connection in case of "soft" errors like 404 or 401 and drop
     * it in case of codes which, likely, will client not recover (eg. 400)
     *
     * @see HttpResponse
     */
    public function __construct(
        $message = "",
        $code = HttpCode::INTERNAL_SERVER_ERROR,
        $extraHeaders = array(),
        $disconnect = false
    ) {
        $this->response = new HttpResponse($message, $extraHeaders, $code);

        if ($disconnect) {
            $this->response->setHeader("connection", "close");
        }

        parent::__construct($message, $code);
    }

    /**
     * Provides valid HttpResponse, which finally is converted into string and send down the wire to client
     *
     * @return HttpResponse
     */
    public function getResponse()
    {
        return $this->response;
    }
}
