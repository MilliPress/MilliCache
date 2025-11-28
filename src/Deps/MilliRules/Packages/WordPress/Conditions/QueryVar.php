<?php

/**
 * Query Var Condition
 *
 * Checks WordPress query variables.
 *
 * @package     MilliCache\Deps\MilliRules
 * @author      Philipp Wellmer
 */

namespace MilliCache\Deps\MilliRules\Packages\WordPress\Conditions;

use MilliCache\Deps\MilliRules\Conditions\BaseCondition;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class QueryVar
 *
 * Checks WordPress query variables (e.g., 'paged', 'post_type', 's', 'm', etc.).
 * Uses get_query_var() to retrieve the value or falls back to context.
 *
 * Supported operators:
 * - =: Exact value match (default)
 * - !=: Value doesn't match
 * - IN: Check if value is in array
 * - NOT IN: Check if value is not in array
 * - LIKE: Pattern matching with wildcards (* and ?)
 * - EXISTS: Check if query var exists
 * - NOT EXISTS: Check if query var doesn't exist
 *
 * Examples:
 * Array syntax:
 * - Check paged: ['type' => 'query_var', 'name' => 'paged', 'value' => 2]
 * - Check search exists: ['type' => 'query_var', 'name' => 's', 'operator' => 'EXISTS']
 * - Check post type: ['type' => 'query_var', 'name' => 'post_type', 'value' => 'product']
 *
 * Builder syntax:
 * - ->query_var('paged', 2) // exact match
 * - ->query_var('s', null, 'EXISTS') // search exists
 *
 * @since 0.1.0
 */
class QueryVar extends BaseCondition
{
    /**
     * Define argument mapping for QueryVar condition.
     *
     * QueryVar is name-based: first arg = query var name, second arg = value to compare.
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
     * Query variable name.
     *
     * @since 0.1.0
     * @var string
     */
    private string $query_var_name;

    /**
     * Constructor.
     *
     * @since 0.1.0
     *
     * @param array<string, mixed> $config  The condition configuration.
     * @param Context $context The execution context.
     */
    public function __construct(array $config, Context $context)
    {
        // Default to EXISTS operator when no value specified.
        if (! isset($config['value']) && ! isset($config['operator'])) {
            $config['operator'] = 'EXISTS';
        }

        parent::__construct($config, $context);

        $name_value = $config['name'] ?? '';
        $this->query_var_name = is_string($name_value) ? $name_value : '';
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
        return 'query_var';
    }

    /**
     * Get the actual value from context.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context.
     * @return mixed The query var value, or null if not set.
     */
    protected function get_actual_value(Context $context)
    {
        if (empty($this->query_var_name)) {
            return null;
        }

        // Try to get from context first.
        $context->load('query_vars');
        $query_vars = $context->get('query_vars');

        if (is_array($query_vars) && isset($query_vars[ $this->query_var_name ])) {
            return $query_vars[ $this->query_var_name ];
        }

        // Fall back to WordPress function.
        if (function_exists('get_query_var')) {
            $value = get_query_var($this->query_var_name);
            // get_query_var returns false or empty string for non-existent vars.
            // Return null to make EXISTS/NOT EXISTS operators work correctly.
            if (false === $value || '' === $value) {
                return null;
            }
            return $value;
        }

        return null;
    }
}
