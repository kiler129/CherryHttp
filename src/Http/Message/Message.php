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

namespace noFlash\CherryHttp\Http\Message;

/**
 * Represents generic message exchanged between endpoints.
 */
class Message implements MessageInterface
{

    /**
     * @var string HTTP protocol version, e.g. 1.0, 1.1, 0.99
     */
    private $protocolVersion = MessageInterface::HTTP_11;

    /**
     * Contains array of headers.
     * Internal structure:
     * $headers = [
     *     'connection' => [ //Header name (lowercase)
     *         'Connection', //Header name (original case)
     *         ['close'] //Header values
     *     ],
     *     'set-cookie' => [
     *         'Set-Cookie',
     *         ['cookie1', 'cookie2', 'cookie3']
     *     ]
     * ];
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Returns HTTP protocol version as string.
     * Result of that method can be compared to MessageInterface::HTTP_10 and MessageInterface::HTTP_11 constants.
     *
     * @return string 1.0 or 1.1
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * Sets specified HTTP protocol version on current instance.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * This method should accept all semantically valid version numbers regardless of it's existence.
     *
     * @param string $version HTTP protocol version
     *
     * @return void
     *
     * @throws \InvalidArgumentException for semantically invalid HTTP version.
     */
    public function setProtocolVersion($version)
    {
        $version = (string)$version;

        if (!isset($version[2]) || //Version too short
            isset($version[3]) || //Version too long
            (string)(int)$version[0] !== $version[0] || //First character have to be digit
            $version[1] !== '.' || //Verify a dot existence
            (string)(int)$version[2] !== $version[2] //Verify second digit
        ) {
            throw new \InvalidArgumentException(
                'Invalid HTTP version - valid version should be in DIGIT.DIGIT format.'
            );
        }

        $this->protocolVersion = $version;
    }

    /**
     * Retrieves all headers.
     *
     * While headers are generally case-insensitive according to RFCs cases should be preserved.
     * However implementation MAY preserve only one variation of header cases (e.g. setting X-Test and x-Test you may
     * either get two separate headers or just X-Test with two values.
     *
     * Method returns array where every key represents header name as it will be sent over the wire and each value is
     * an array of header values.
     * Example output of that method may look like following array:
     * [
     *  'X-Test' => ['foo'],
     *  'X-Foo'  => ['foo', 'baz']
     *  'Server' => ['CherryHttp/2.0-dev']
     * ]
     *
     * @return array
     */
    public function getHeaders()
    {
        $result = [];

        foreach ($this->headers as $headerLine) {
            $result[$headerLine[0]] = $headerLine[1];
        }

        return $result;
    }

    /**
     * Returns all known values for given header name.
     * Header lookup MUST be done using case-insensitive routing.
     *
     * @param string $name Case-insensitive header name.
     *
     * @return string[] Array of header values. If headers doesn't exists empty array will be returned.
     */
    public function getHeader($name)
    {
        $name = strtolower($name);

        if (!isset($this->headers[$name])) {
            return [];
        }

        return $this->headers[$name][1];
    }

    /**
     * Checks if a header exists in current instance.
     * Name MUST be compared using case-insensitive routine.
     *
     * @param string $name Case-insensitive header name.
     *
     * @return bool
     */
    public function hasHeader($name)
    {
        return isset($this->headers[strtolower($name)]);
    }

    /**
     * Sets given header value on current instance replacing existing one.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * @param string          $name  Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     *
     * @return void
     *
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function setHeader($name, $value)
    {
        $lowercaseName = strtolower($name);
        $this->headers[$lowercaseName] = [$name, [(string)$value]];
    }

    /**
     * Appends given header to existing instance.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * @param string          $name  Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     *
     * @return void
     *
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function addHeader($name, $value)
    {
        $lowercaseName = strtolower($name);

        if (!isset($this->headers[$lowercaseName])) {
            $this->headers[$lowercaseName] = [$name, [(string)$value]];

        } else {
            $this->headers[$lowercaseName][1][] = (string)$value;
        }
    }

    /**
     * Removes given header from current instance.
     * Header resolution MUST be done without case-sensitivity.
     *
     * @param string $name Case-insensitive header field name to remove.
     *
     * @return self
     */
    public function unsetHeader($name)
    {
        unset($this->headers[strtolower($name)]);
    }

    /**
     * Returns message body.
     *
     * @return -undetermined-
     * @todo
     */
    public function getBody()
    {
        // TODO: Implement getBody() method.
    }

    /**
     * Sets given message body on current instance.
     *
     * @param -undetermined- $body Message body.
     *
     * @return void
     * @todo
     */
    public function setBody($body)
    {
        // TODO: Implement setBody() method.
    }
}
