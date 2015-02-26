<?php
namespace noFlash\CherryHttp;

use Exception;

/**
 * Thrown everytime server occur internal exception which cannot be recovered automatically.
 *
 * @package noFlash\CherryHttp
 */
class ServerException extends Exception
{
    /**
     * @param string $message Human-readable message
     */
    public function __construct($message = '')
    {
        parent::__construct("Server occurred runtime error: $message");
    }
}
