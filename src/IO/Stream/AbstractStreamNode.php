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

use noFlash\CherryHttp\Application\Lifecycle\AbstractLoopNode;
use noFlash\CherryHttp\IO\StreamNodeInterface;

/**
 * Class AbstractStreamNode
 */
abstract class AbstractStreamNode extends AbstractLoopNode implements StreamNodeInterface
{

    /**
     * @var resource PHP stream resource
     */
    public $stream;

    /**
     * Provides PHP stream resource.
     * Output of that method MUST BE consistent with $this->stream contents.
     *
     * @return resource|null
     */
    public function getStreamResource()
    {
        return $this->stream;
    }
    
    /**
     * @inheritdoc
     */
    public function onStreamError()
    {
        /** @noinspection PhpUsageOfSilenceOperatorInspection There's no way to overcome that */
        @fclose($this->stream); //Well... I don't think it's gonna work really
        $this->stream = null;
    }
}
