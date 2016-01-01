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

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Mutable version of PSR-7 message.
 */
interface MutableMessageInterface extends MessageInterface
{
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
    public function setProtocolVersion($version);

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
    public function setHeader($name, $value);

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
    public function addHeader($name, $value);

    /**
     * Removes given header from current instance.
     * Header resolution MUST be done without case-sensitivity.
     *
     * @param string $name Case-insensitive header field name to remove.
     *
     * @return self
     */
    public function unsetHeader($name);

    /**
     * Sets given message body on current instance.
     * The body MUST be a StreamInterface object.
     *
     * @param StreamInterface $body Body.
     *
     * @return void
     *
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function setBody(StreamInterface $body);
}
