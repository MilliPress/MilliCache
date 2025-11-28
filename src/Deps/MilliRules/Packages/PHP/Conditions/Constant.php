<?php

/**
 * PHP Constant Condition
 *
 * Checks PHP constant values (including WordPress constants defined in wp-config.php).
 *
 * @package     MilliCache\Deps\MilliRules
 * @author      Philipp Wellmer
 */

namespace MilliCache\Deps\MilliRules\Packages\PHP\Conditions;

use MilliCache\Deps\MilliRules\Conditions\BaseCondition;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class Constant
 *
 * Checks if a PHP constant exists and optionally matches a value.
 * Works with all PHP constants, including WordPress constants defined in wp-config.php
 * which are available before WordPress fully loads.
 *
 * Supports checking:
 * - Existence: Use 'EXISTS' operator to check if constant is defined
 * - Value matching: Use '=', '!=', '>', etc. to compare constant value
 * - Boolean checks: Use 'IS' operator for true/false comparison
 *
 * Examples:
 * - Check if WP_DEBUG exists: ['type' => 'constant', 'name' => 'WP_DEBUG', 'operator' => 'EXISTS']
 * - Check if WP_DEBUG is true: ['type' => 'constant', 'name' => 'WP_DEBUG', 'value' => true]
 * - Check specific value: ['type' => 'constant', 'name' => 'WP_ENVIRONMENT_TYPE', 'value' => 'production']
 * - Check PHP_VERSION_ID: ['type' => 'constant', 'name' => 'PHP_VERSION_ID', 'operator' => '>=', 'value' => 80000]
 * - Using builder: ->constant('DOING_CRON', true) // checks if DOING_CRON is true
 *
 * @since 0.1.0
 */
class Constant extends BaseCondition
{
    /**
     * Define argument mapping for Constant condition.
     *
     * Constant is name-based: first arg = constant name, second arg = value to compare.
     * See BaseCondition::get_argument_mapping() for detailed explanation.
     *
     * @since 0.1.0
     *
     * @return array<int, string>
     */
    public static function get_argument_mapping(): array
    {
        return ['name', 'value'];
    }

    /**
     * Get the condition type.
     *
     * @since 0.1.0
     *
     * @return string The condition type identifier.
     */
    public function get_type(): string
    {
        return 'constant';
    }

    /**
     * Get the actual value from context.
     *
     * Note: This condition doesn't use context as PHP constants are globally available.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context (unused).
     * @return mixed The constant value, or null if not defined.
     */
    protected function get_actual_value(Context $context)
    {
        $constant_name = $this->config['name'] ?? $this->config['constant'] ?? '';

        if (! is_string($constant_name) || empty($constant_name)) {
            return null;
        }

        if (! defined($constant_name)) {
            return null;
        }

        return constant($constant_name);
    }
}
