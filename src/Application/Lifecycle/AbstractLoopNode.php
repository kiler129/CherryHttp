<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Application\Lifecycle;

use noFlash\CherryHttp\Application\Exception\NodeConflictException;

/**
 * Class represents the most basic loop node fully implementing LoopNodeInterface.
 * It's marked abstract since it does nothing - it can be attached, detached and pinged.
 */
abstract class AbstractLoopNode implements LoopNodeInterface
{
    /**
     * @var LoopInterface|null A loop to which stream node is attached or null if it's not attached to any
     */
    protected $loop;

    /**
     * @inheritdoc
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * @inheritdoc
     */
    public function onAttach(LoopInterface $loop)
    {
        if ($this->loop !== null) {
            throw new NodeConflictException(
                get_class($this) . ' node is already attached to a ' . get_class($this->loop) . ' loop!'
            );
        }

        $this->loop = $loop;
    }

    /*
     * @inheritdoc
     */
    public function onDetach()
    {
        if ($this->loop === null) {
            throw new NodeConflictException(
                get_class($this) . ' node is not assigned to any loop'
            );
        }
        
        $this->loop = null;
    }
}
