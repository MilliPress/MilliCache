<?php

/**
 * User Role Condition
 *
 * Checks the roles of the current logged-in WordPress user.
 *
 * @package     MilliRules
 * @author      Philipp Wellmer
 */

namespace MilliCache\Deps\MilliRules\Packages\WordPress\Conditions;

use MilliCache\Deps\MilliRules\Conditions\BaseCondition;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class UserRole
 *
 * Checks the roles of the current logged-in WordPress user.
 * Returns an array of roles from the execution context.
 *
 * Supported operators:
 * - IN/=: Check if any expected role is present in user's roles (default)
 * - NOT IN/!=: Check if no expected roles are present
 *
 * Examples:
 * Array syntax:
 * - Check for admin: ['type' => 'user_role', 'value' => 'administrator']
 * - Check for editor or admin: ['type' => 'user_role', 'value' => ['editor', 'administrator'], 'operator' => 'IN']
 * - Check not subscriber: ['type' => 'user_role', 'value' => 'subscriber', 'operator' => '!=']
 *
 * Builder syntax:
 * - ->user_role('administrator') // has admin role
 * - ->user_role(['editor', 'administrator'], 'IN') // has any of these roles
 *
 * @since 0.1.0
 */
class UserRole extends BaseCondition
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
        return 'user_role';
    }

    /**
     * Get the actual value from context.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context.
     * @return array<int, string> Array of user roles.
     */
    protected function get_actual_value(Context $context): array
    {
        $context->load('user');
        $user = $context->get('user');

        if (! is_array($user)) {
            return array();
        }

        $roles = $user['roles'] ?? array();
        return is_array($roles) ? $roles : array();
    }

    /**
     * Compare actual and expected values.
     *
     * Overrides parent to handle array-to-string/array comparisons for role matching.
     *
     * @since 0.1.0
     *
     * @param mixed $actual   The actual value from context (array of roles).
     * @param mixed $expected The expected value from config (string or array).
     * @return bool True if comparison matches, false otherwise.
     */
    protected function compare($actual, $expected): bool
    {
        // Ensure actual is an array.
        $actual_roles = is_array($actual) ? $actual : array();

        // Convert expected to array if it's a string.
        $expected_roles = is_array($expected) ? $expected : array( $expected );

        // Check for intersection based on operator.
        $intersection = array_intersect($actual_roles, $expected_roles);
        $has_intersection = ! empty($intersection);

        switch ($this->operator) {
            case 'IN':
            case '=':
            case 'EQUALS':
                return $has_intersection;

            case 'NOT IN':
            case '!=':
                return ! $has_intersection;

            default:
                // Fall back to parent comparison for other operators.
                return parent::compare($actual, $expected);
        }
    }
}
