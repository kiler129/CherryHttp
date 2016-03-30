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

/**
 * Generic interface for all application loop nodes.
 * Node can be virtually anything - a network connection, external process connection, tasker etc.
 */
interface LoopNodeInterface
{
    const PING_INTERVAL_ANY = -1;

    /**
     * Every node can be "married"/attached to a loop.
     * This method returns current loop to which a node is bound.
     *
     * @return LoopInterface|null Corresponding loop object or null if not bound to loop yet.
     */
    public function getLoop();

    /**
     * Node can receive periodical signals from main loop.
     * This method informs loop how often node wish to be pinged.
     * Keep in mind this value is only suggestion and should NEVER be used in any time-critical routines.
     *
     * Value is cached and retrieved only on node attachment.
     *
     * @return int Number of seconds or self::PING_INTERVAL_ANY if node can be pinged in any intervals.
     */
    public function getPingInterval();

    /**
     * Method called by loop according to interval specified by getPingInterval() method.
     *
     * @return void
     */
    public function ping();

    /**
     * Method called by loop after attaching to loop.
     *
     * @param LoopInterface $loop Loop to which loop was added.
     *
     * @return void
     */
    public function onAttach(LoopInterface $loop);

    /**
     * Method called by loop after detaching from loop.
     * Implementation should at least remove any internal references to loop object to prevent dangling objects & leaks.
     *
     * @return void
     */
    public function onDetach();
}
