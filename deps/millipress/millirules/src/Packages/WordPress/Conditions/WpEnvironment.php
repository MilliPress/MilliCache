<?php

/**
 * WordPress Environment Condition
 *
 * Checks the WordPress environment type.
 *
 * @package     MilliRules
 * @author      Philipp Wellmer
 */

namespace MilliCache\Deps\MilliRules\Packages\WordPress\Conditions;

use MilliCache\Deps\MilliRules\Conditions\BaseCondition;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class WpEnvironment
 *
 * Checks the WordPress environment type (production, staging, development, local).
 * Uses wp_get_environment_type() if available (WordPress 5.5+).
 * Returns 'production' as the default if the function is unavailable.
 *
 * Supported operators:
 * - =: Exact environment match (default)
 * - !=: Environment doesn't match
 * - IN: Check if environment is in array
 * - NOT IN: Check if environment is not in array
 *
 * Examples:
 * Array syntax:
 * - Check production: ['type' => 'wp_environment', 'value' => 'production']
 * - Check not production: ['type' => 'wp_environment', 'value' => 'production', 'operator' => '!=']
 * - Check dev/staging: ['type' => 'wp_environment', 'value' => ['development', 'staging'], 'operator' => 'IN']
 *
 * Builder syntax:
 * - ->wp_environment('production') // exact match
 * - ->wp_environment(['development', 'staging'], 'IN') // dev or staging
 *
 * @since 0.1.0
 */
class WpEnvironment extends BaseCondition
{
    /**
     * Get the condition type.
     *
     * @since 0.1.0
     *
     * @return string The condition type identifier.
     */
    public function get_type(): string
    {
        return 'wp_environment';
    }

    /**
     * Get the actual value from WordPress.
     *
     * Does not use context - only WordPress APIs.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context (ignored).
     * @return string The WordPress environment type.
     */
    protected function get_actual_value(Context $context): string
    {
        // Use WordPress function.
        if (function_exists('wp_get_environment_type')) {
            return wp_get_environment_type();
        }

        // Default to production.
        return 'production';
    }
}
