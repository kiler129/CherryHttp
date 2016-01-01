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

namespace noFlash\CherryHttp\Server\Node;

use noFlash\CherryHttp\Application\Lifecycle\LoopNodeInterface;

interface NodeFactoryInterface
{
    /**
     * Returns ready-to-use node.
     *
     * @return LoopNodeInterface
     */
    public function getNode();
}
