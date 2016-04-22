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

use noFlash\CherryHttp\Http\HttpNodeInterface;

/**
 * @inheritdoc
 */
abstract class CloneAwareHttpNode extends AbstractCloneAwareObject implements HttpNodeInterface
{
}
