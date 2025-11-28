<?php

/**
 * Request Method Condition
 *
 * Checks the HTTP request method.
 *
 * @package     MilliCache\Deps\MilliRules
 * @author      Philipp Wellmer
 */

namespace MilliCache\Deps\MilliRules\Packages\PHP\Conditions;

use MilliCache\Deps\MilliRules\Conditions\BaseCondition;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class RequestMethod
 *
 * Checks the HTTP request method (GET, POST, PUT, DELETE, etc.).
 * Method comparison is case-insensitive (internally normalized to uppercase).
 *
 * Supported operators:
 * - =: Exact method match (default)
 * - !=: Method doesn't match
 * - IN: Check if method is in array
 * - LIKE: Pattern matching with wildcards (* and ?)
 * - REGEXP: Regular expression matching
 *
 * Examples:
 * Array syntax:
 * - Check GET: ['type' => 'request_method', 'value' => 'GET']
 * - Check POST: ['type' => 'request_method', 'value' => 'POST', 'operator' => '=']
 * - Check not GET: ['type' => 'request_method', 'value' => 'GET', 'operator' => '!=']
 * - Multiple methods: ['type' => 'request_method', 'value' => ['GET', 'HEAD'], 'operator' => 'IN']
 * - Safe methods: ['type' => 'request_method', 'value' => ['GET', 'HEAD', 'OPTIONS'], 'operator' => 'IN']
 *
 * Builder syntax:
 * - ->request_method('GET') // exact match
 * - ->request_method('POST') // exact match
 * - ->request_method(['GET', 'HEAD'], 'IN') // multiple methods
 *
 * @since 0.1.0
 */
class RequestMethod extends BaseCondition
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
        return 'request_method';
    }

    /**
     * Get the actual value from context.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context.
     * @return string The HTTP request method (uppercase).
     */
    protected function get_actual_value(Context $context): string
    {
        $context->load('request');
        $method = $context->get('request.method', '');
        return is_string($method) ? strtoupper($method) : '';
    }
}
