<?php
namespace noFlash\CherryHttp;

/**
 * Generic object for every client connected to server.
 *
 * @package noFlash\CherryHttp
 */
class HttpClient extends StreamServerNode
{
    /** @var HttpRequest Currently handled HTTP request */
    public $request;

    /**
     * Handles HTTP request collection
     *
     * @todo This method needs refactoring along with HttpRequest class. In current shape it cannot accept requests
     *     with additional data attached (eg. POST or PUT).
     * @throws HttpException
     */
    protected function processInputBuffer()
    {
        //$this->logger->debug("Trying to process buffer of HttpClient");

        if ($this->request === null) { //New request
            if (isset($this->inputBuffer[HttpRequest::MAX_ENTITY_LENGTH])) { //Much faster than strlen ;)
                $this->logger->warning("Client $this sent headers larger than " . HttpRequest::MAX_ENTITY_LENGTH . " bytes");
                $this->inputBuffer = "";
                throw new HttpException("Server refused request exceeding " . HttpRequest::MAX_ENTITY_LENGTH . " bytes",
                    HttpCode::REQUEST_ENTITY_TOO_LARGE, array(), true);
            }

            $headersBreakpoint = strpos($this->inputBuffer, "\r\n\r\n"); //Try to find headers breakpoint
            if ($headersBreakpoint === false) { //Not found, nothing to do
                //$this->logger->debug("Buffer doesn't contain full HTTP headers [yet]");
                return;
            }

            //TODO: Refactor to 2x substr (based on $headersBreakpoint) - it's ~5% faster [but it need to be changed along with payloads support]
            list($headers, $this->inputBuffer) = explode("\r\n\r\n", $this->inputBuffer, 2);
            //$this->logger->debug("Got HTTP headers, creating HttpRequest...");
            $this->request = new HttpRequest($headers, $this->logger);
        }
        //In future: else { add data to already created request }
    }
}