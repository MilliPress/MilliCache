<?php

/**
 * Action Interface
 *
 * Contract for all action classes in the rules system.
 *
 * @package MilliRules
 * @author  Philipp Wellmer
 * @since   0.1.0
 */

namespace MilliCache\Deps\MilliRules\Actions;

use MilliCache\Deps\MilliRules\Context;

/**
 * Interface ActionInterface
 *
 * Defines the contract that all action classes must implement.
 *
 * @since 0.1.0
 */
interface ActionInterface
{
    /**
     * Execute the action based on the provided context.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context containing request and application data.
     * @return void
     */
    public function execute(Context $context): void;

    /**
     * Get the type identifier for this action.
     *
     * @since 0.1.0
     *
     * @return string The action type (e.g., 'callback', 'custom_action').
     */
    public function get_type(): string;
}
