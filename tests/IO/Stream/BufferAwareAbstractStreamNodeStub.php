<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Tests\IO\Stream;

use noFlash\CherryHttp\Application\Lifecycle\LoopInterface;
use noFlash\CherryHttp\IO\Stream\BufferAwareAbstractStreamNode;

class BufferAwareAbstractStreamNodeStub extends BufferAwareAbstractStreamNode
{

    public $pibCallsCount = 0;
    public $pibOutputMap  = [];

    /**
     * @inheritdoc
     */
    public function onAttach(LoopInterface $loop)
    {
    }

    /**
     * @inheritdoc
     */
    public function onDetach()
    {
    }

    /**
     * @inheritdoc
     */
    public function getStreamObject()
    {
    }
    
    /**
     * @inheritdoc
     */
    protected function processInputBuffer()
    {
        $element = each($this->pibOutputMap);
        if ($element === false) {
            throw new \RuntimeException(
                "processInputBuffer() was called (call no. {$this->pibCallsCount}) but there's no more return values mapped"
            );
        }

        $this->pibCallsCount++;

        return $element['value'];
    }
}
