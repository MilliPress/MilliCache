<?php

/**
 * Condition Interface
 *
 * Contract for all condition classes in the rules system.
 *
 * @package     MilliCache\Deps\MilliRules
 * @author      Philipp Wellmer
 */

namespace MilliCache\Deps\MilliRules\Conditions;

use MilliCache\Deps\MilliRules\Context;

/**
 * Interface ConditionInterface
 *
 * Defines the contract that all condition classes must implement.
 *
 * @since 0.1.0
 */
interface ConditionInterface
{
    /**
     * Check if the condition matches based on the provided context.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context containing request and application data.
     * @return bool True if the condition matches, false otherwise.
     */
    public function matches(Context $context): bool;

    /**
     * Get the type identifier for this condition.
     *
     * @since 0.1.0
     *
     * @return string The condition type (e.g., 'request_url', 'post_type').
     */
    public function get_type(): string;
}
