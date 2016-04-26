<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\IO\Stream;

use noFlash\CherryHttp\Http\Exception\StreamException;

/**
 * Represents abstract stream of any data.
 *
 * While generally interfaces cannot define any properties this one does due to performance reasons.
 * @property resource|null $stream Contains the same value as returned by getStreamResource()
 */
interface StreamInterface
{
    /**
     * Will provide "bool" value.
     * True is returned If the stream timed out while waiting for data on the last call to read().
     */
    const METADATA_TIMED_OUT = 'timed_out';

    /**
     * Will provide "bool" value.
     * True is returned if the stream is in blocking IO mode. See setBlocking()/setUnblocking()/isBlocking().
     */
    const METADATA_BLOCKED = 'blocked';

    /**
     * Will provide "bool" value.
     * True is returned if the stream has reached end-of-file. There're some edge-cases when this value may be "true"
     * even if some bytes are left. Consult PHP documentation for details
     *
     * It's certainly better to use isEof() method.
     */
    const METADATA_EOF = 'eof';

    /**
     * Will provide "integer" value.
     * Number of bytes currently held in internal PHPs buffer. Unless you're internal interpreter hacker you SHOULD NOT
     * use this value.
     */
    const METADATA_UNREAD_BYTES = 'unread_bytes';

    /**
     * Will provide "string" value.
     * Label describing underlying stream implementation.
     */
    const METADATA_STREAM_TYPE = 'stream_type';

    /**
     * Will provide "string" value.
     * Label describing the protocol wrapper implementation layered over the stream. Consult PHP docs for details.
     */
    const METADATA_WRAPPER_TYPE = 'wrapper_type';

    /**
     * Will provide value depending on the wrapper used - consult wrapper documentation.
     */
    const METADATA_WRAPPER_DATA = 'wrapper_data';

    /**
     * Will provide "string" value.
     * Type of access required for this stream (see Table 1 of the fopen() reference)
     */
    const METADATA_MODE = 'mode';

    /**
     * Will provide "bool" value.
     * Whether the current stream is seekable.
     */
    const METADATA_SEEKABLE = 'seekable';

    /**
     * Will provide "string" value.
     * URI/filename associated with this stream.
     */
    const METADATA_URI = 'uri';

    /**
     * Provides PHP stream resource.
     *
     * @return resource|null
     */
    public function getStreamResource();

    /**
     * Retrieves specific metadata element.
     * See specific constants for description
     *
     * @param string $key Any METADATA_* constant
     *
     * @return mixed|null Will return corresponding value or null if value wasn't found
     */
    public function getMetadataElement($key);

    /**
     * Provides stream metadata.
     * This method mimics functionality of PHPs built-in stream_get_meta_data() called without "key" argument.
     *
     * @return array Returns associative array with metadata
     *
     * @see stream_get_meta_data()
     */
    public function getCompleteMetadata();


    /**
     * Returns "true" if stream is blocked, false otherwise.
     *
     * @return bool
     */
    public function isBlocking();

    /**
     * Sets stream into blocking mode.
     *
     * @return void
     */
    public function setBlocking();

    /**
     * Set stream into non-blocking mode.
     *
     * @return void
     */
    public function setNonBlocking();

    /**
     * Provides current stream pointer position.
     *
     * @return integer
     *
     * @throws StreamException
     */
    public function tell();

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable();

    /**
     * Reset stream to the beginning.
     * For non-rewindable streams exception will be raised.
     *
     * @throws StreamException
     */
    public function rewind();

    /**
     * Set stream pointer in desired position.
     * This method works similar to fseek(..., ..., SEEK_SET)
     *
     * @param integer $position
     *
     * @return void
     *
     * @throws StreamException
     */
    public function seekTo($position);

    /**
     * Moves stream pointer by defined number of bytes.
     * This method works similar to fseek(..., $offset, SEEK_CUR)
     *
     * @param integer $offset
     *
     * @return void
     *
     * @throws StreamException
     */
    public function seekBy($offset);

    /**
     * Moves stream pointer to the end, optionally with offset.
     * This method works similar to fseek(..., $offset, SEEK_END)
     *
     * @param integer $offset Optional, by default 0
     *
     * @return void
     *
     * @throws StreamException
     */
    public function seekEnd($offset = 0);

    /**
     * Provides number of all bytes in stream.
     *
     * Note: This method SHOULD NOT be implemented in a way which require scanning whole stream!
     *
     * @return integer|null Returns number of bytes or null if information is unavailable.
     */
    public function getLength();


    /**
     * Provides information if stream can be read.
     *
     * @return bool
     */
    public function isReadable();

    /**
     * Reads chunk of data from stream.
     *
     * @param int $length Maximum number of bytes to read. Since stream may contain less than that value and that value
     *                    will be returned.
     *                    Warning: for blocked streams requesting more bytes than can be read results in blocking
     *                    code execution!
     *
     * @return string
     *
     * @throws StreamException
     */
    public function read($length);

    /**
     * This method works similar to __toString() with one difference - it will not try to rewind the stream.
     *
     * @return string Stream contents
     *
     * @throws StreamException
     */
    public function getContents();

    /**
     * Converts whole stream into string.
     *
     * This method will try to rewind stream to beginning, however if it's not possible it will just return the data
     * from current position till the end.
     * Be aware that on some streams it may generate huge blob of data potentially exhausting whole memory!
     *
     * @return string Stream contents
     */
    public function __toString();

    /**
     * Returns if stream reached it's end.
     *
     * @return bool
     */
    public function isEof();

    /**
     * Provides information if stream can be written.
     *
     * @return bool
     */
    public function isWritable();

    /**
     * Attempts stream write.
     * Warning: If stream is blocked this method will pause code execution until all data are written!
     *
     * @param string $string Data to write
     *
     * @return int Number of bytes actually written to the stream
     *
     * @throws StreamException
     */
    public function write($string);

    /**
     * Closes the stream and removes it from object.
     *
     * @return void
     */
    public function close();
}
