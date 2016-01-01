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

namespace noFlash\CherryHttp\Application\Lifecycle;

use noFlash\CherryHttp\Application\Exception\NodeConflictException;
use noFlash\CherryHttp\Application\Exception\NodeNotFoundException;

/**
 * Interface represents any nodes loop.
 * The most common usage of that interface will be an application main loop.
 */
interface LoopInterface
{
    /**
     * Attaches node to current loop and calls LoopNodeInterface::onAttach() method on given node after attaching is
     * finished.
     *
     * @param LoopNodeInterface $node Node to attach to current loop.
     *
     * @return bool
     * @throws NodeConflictException Thrown if node already is part of that loop, or node is attached to another loop.
     */
    public function attachNode(LoopNodeInterface $node);


    /**
     * Detaches node from loop and calls LoopNodeInterface::onDetach() method on given node after detaching is finished.
     *
     * @param LoopNodeInterface $node
     *
     * @return bool
     * @throws NodeNotFoundException Thrown if node is not part of current loop.
     */
    public function detachNode(LoopNodeInterface $node);

    /**
     * Starts application loop.
     *
     * @return void
     */
    public function start();
}
