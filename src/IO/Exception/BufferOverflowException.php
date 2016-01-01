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

namespace noFlash\CherryHttp\IO\Exception;

/**
 * Exception used to indicate overflow on any kind of buffers.
 */
class BufferOverflowException extends \OverflowException
{
    /**
     * @var
     */
    private $overflowMagnitude;

    /**
     * Returns information how big (if known) that overflow was.
     *
     * @return number|null Will return positive numeric value (depending on the context) or null if overflow magnitude
     *                     is unknown.
     */
    public function getOverflowMagnitude()
    {
        return $this->overflowMagnitude;
    }

    /**
     * Sets how big overflow is.
     *
     * @param number $magnitude Positive number.
     *
     * @throws \InvalidArgumentException Non-numeric magnitude specified.
     * @throws \LogicException Negative magnitude specified.
     */
    public function setOverflowMagnitude($magnitude)
    {
        if ($magnitude < 0) {
            throw new \LogicException('Overflow magnitude cannot be negative.');
        }

        if (!is_numeric($magnitude)) {
            throw new \InvalidArgumentException('Overflow magnitude should be a number.');
        }

        $this->overflowMagnitude = $magnitude;
    }

}
