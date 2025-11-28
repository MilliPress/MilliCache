<?php

/**
 * Request Header Condition
 *
 * Checks HTTP request header values.
 *
 * @package     MilliCache\Deps\MilliRules
 * @author      Philipp Wellmer
 */

namespace MilliCache\Deps\MilliRules\Packages\PHP\Conditions;

use MilliCache\Deps\MilliRules\Conditions\BaseCondition;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class RequestHeader
 *
 * Checks HTTP request header existence and values.
 * Performs case-insensitive header name matching per HTTP specification.
 *
 * Supported operators:
 * - EXISTS/IS: Check if header exists (when no value specified)
 * - IS NOT/!=: Check if header doesn't exist (when no value specified)
 * - =: Exact value match
 * - !=: Value doesn't match
 * - LIKE: Pattern matching with wildcards (* and ?)
 * - REGEXP: Regular expression matching
 * - IN: Check if value is in array
 * - >, >=, <, <=: Numeric or alphabetic comparison
 *
 * Examples:
 * Array syntax:
 * - Check existence: ['type' => 'request_header', 'name' => 'X-Forwarded-For']
 * - Check value: ['type' => 'request_header', 'name' => 'User-Agent', 'value' => '*Chrome*', 'operator' => 'LIKE']
 * - Check origin: ['type' => 'request_header', 'name' => 'Origin', 'value' => 'https://example.com']
 * - Multiple values: ['type' => 'request_header', 'name' => 'Accept-Language', 'value' => ['en', 'de', 'fr'], 'operator' => 'IN']
 * - Header doesn't exist: ['type' => 'request_header', 'name' => 'X-Debug', 'operator' => 'IS NOT']
 *
 * Builder syntax:
 * - ->request_header('X-Forwarded-For') // exists
 * - ->request_header('User-Agent', '*Chrome*', 'LIKE') // pattern match
 * - ->request_header('Origin', 'https://example.com') // exact match
 *
 * @since 0.1.0
 */
class RequestHeader extends BaseCondition
{
    /**
     * Define argument mapping for RequestHeader condition.
     *
     * RequestHeader is name-based: first arg = header name, second arg = value to compare.
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
        return 'request_header';
    }

    /**
     * Get the actual value from context.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context.
     * @return string The header value or empty string if not found.
     */
    protected function get_actual_value(Context $context): string
    {
        $context->load('request');

        $header_name = $this->config['name'] ?? $this->config['header'] ?? '';

        if (! is_string($header_name) || empty($header_name)) {
            return '';
        }

        $headers = $context->get('request.headers', array());

        if (! is_array($headers)) {
            return '';
        }

        // Headers are case-insensitive, normalize to lowercase.
        $headers     = array_change_key_case($headers, CASE_LOWER);
        $header_name = strtolower($header_name);

        $value = $headers[ $header_name ] ?? '';
        return is_string($value) ? $value : '';
    }
}
