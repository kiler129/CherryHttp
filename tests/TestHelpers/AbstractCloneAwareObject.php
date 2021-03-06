<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Tests\TestHelpers;

/**
 * Unfortunately it's impossible (or I don't know a way) to check mock object has been cloned.
 * So, this class is used in a clever way to detect cloning by setting marker on clone.
 */
abstract class AbstractCloneAwareObject
{
    public  $_publicField = null;
    private $cloneNumber  = 0;

    public function __clone()
    {
        $this->cloneNumber++;
    }

    public function _getCloneNumber()
    {
        return $this->cloneNumber;
    }
}
