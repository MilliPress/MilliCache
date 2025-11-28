<?php

/**
 * Request Parameter Condition
 *
 * Checks URL/query parameter values.
 *
 * @package     MilliCache\Deps\MilliRules
 * @author      Philipp Wellmer
 */

namespace MilliCache\Deps\MilliRules\Packages\PHP\Conditions;

use MilliCache\Deps\MilliRules\Conditions\BaseCondition;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class RequestParam
 *
 * Checks URL query parameter existence and values.
 * Works with GET/POST parameters and query strings.
 *
 * Supported operators:
 * - EXISTS/IS: Check if parameter exists (when no value specified)
 * - IS NOT/!=: Check if parameter doesn't exist (when no value specified)
 * - =: Exact value match
 * - !=: Value doesn't match
 * - LIKE: Pattern matching with wildcards (* and ?)
 * - REGEXP: Regular expression matching
 * - IN: Check if value is in array
 * - >, >=, <, <=: Numeric or alphabetic comparison
 *
 * Examples:
 * Array syntax:
 * - Check existence: ['type' => 'request_param', 'name' => 'utm_source']
 * - Check value: ['type' => 'request_param', 'name' => 'action', 'value' => 'edit']
 * - Pattern match: ['type' => 'request_param', 'name' => 'ref', 'value' => 'google*', 'operator' => 'LIKE']
 * - Multiple values: ['type' => 'request_param', 'name' => 'status', 'value' => ['active', 'pending'], 'operator' => 'IN']
 * - Param doesn't exist: ['type' => 'request_param', 'name' => 'debug', 'operator' => 'IS NOT']
 *
 * Builder syntax:
 * - ->request_param('utm_source') // exists
 * - ->request_param('action', 'edit') // exact match
 * - ->request_param('ref', 'google*', 'LIKE') // pattern match
 *
 * @since 0.1.0
 */
class RequestParam extends BaseCondition
{
    /**
     * Define argument mapping for RequestParam condition.
     *
     * RequestParam is name-based: first arg = parameter name, second arg = value to compare.
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
        return 'request_param';
    }

    /**
     * Get the actual value from context.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context.
     * @return string The parameter value or empty string if not found.
     */
    protected function get_actual_value(Context $context): string
    {
        // Ensure request parameters are loaded.
        $context->load('param');

        $param_name = $this->config['name'] ?? $this->config['param'] ?? '';

        if (empty($param_name)) {
            return '';
        }

        // Get parameter value from context.
        $params = $context->get('param', array());

        if (! is_array($params)) {
            return '';
        }

        $value = $params[ $param_name ] ?? '';
        return is_string($value) ? $value : '';
    }
}
