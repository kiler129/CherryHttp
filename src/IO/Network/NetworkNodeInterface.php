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

namespace noFlash\CherryHttp\IO\Network;

use noFlash\CherryHttp\IO\StreamNodeInterface;

interface NetworkNodeInterface extends StreamNodeInterface
{
    const IP_V4 = 4;
    const IP_V6 = 6;

    public function getIpVersion();

    public function getPeerName();

    public function getIpAddress();

    public function getLocalPort();

    public function getRemotePort();

    public function isConnected();

    public function disconnect();
}
