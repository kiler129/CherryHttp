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

namespace noFlash\CherryHttp\Application\Exception;

use noFlash\CherryHttp\Server\Exception\ServerException;

/**
 * Possible use-cases of that exception:
 *  - Second node with the same id
 *  - Attempting to node already added to current loop
 *  - Attempting to add node already attached to another loop
 */
class NodeConflictException extends ServerException
{

}
