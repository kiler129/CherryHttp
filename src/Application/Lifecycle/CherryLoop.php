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
use noFlash\CherryHttp\Application\Exception\NodeNotFoundException;

/**
 * THE Loop.
 */
class CherryLoop implements LoopInterface
{

    /**
     * @var \SplObjectStorage
     */
    private $nodes;


    /**
     * CherryLoop constructor.
     */
    public function __construct()
    {
        $this->nodes = new \SplObjectStorage();
    }

    /**
     * Attaches node to current loop and calls LoopNodeInterface::onAttach() method on given node after attaching is
     * finished.
     *
     * @param LoopNodeInterface $node Node to attach to current loop.
     *
     * @return bool
     * @throws NodeConflictException Thrown if node already is part of that loop, or node is attached to another loop.
     */
    public function attachNode(LoopNodeInterface $node)
    {
        if ($this->nodes->contains($node)) {
            throw new NodeConflictException(
                'Node ' . $this->nodes->getHash($node) . ' already exists in ' . spl_object_hash($this) . ' loop'
            );
        }

        $this->nodes->attach($node);
        $node->onAttach($this);

        return true;
    }

    /**
     * Detaches node from loop and calls LoopNodeInterface::onDetach() method on given node after detaching is finished.
     *
     * @param LoopNodeInterface $node
     *
     * @return bool
     * @throws NodeNotFoundException Thrown if node is not part of current loop.
     */
    public function detachNode(LoopNodeInterface $node)
    {
        if (!$this->nodes->contains($node)) {
            throw new NodeNotFoundException(
                'Node ' . $this->nodes->getHash($node) . ' is not attached to ' . spl_object_hash($this) . ' loop'
            );
        }

        $node->onDetach();
        $this->nodes->detach($node);

        return true;
    }

    /**
     * Starts application loop.
     *
     * @return void
     */
    public function start()
    {
        //Boo hoo
    }
}
