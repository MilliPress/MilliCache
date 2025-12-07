<?php

/**
 * Request URL Condition
 *
 * Checks the request URL/URI.
 *
 * @package     MilliRules
 * @author      Philipp Wellmer
 */

namespace MilliCache\Deps\MilliRules\Packages\PHP\Conditions;

use MilliCache\Deps\MilliRules\Conditions\BaseCondition;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class RequestUrl
 *
 * Checks the request URL/URI path against patterns or exact values.
 * Works with the URI path component (e.g., '/wp-admin/post.php').
 *
 * Supported operators:
 * - =: Exact URL match
 * - !=: URL doesn't match
 * - LIKE: Pattern matching with wildcards (* and ?) (default for strings with wildcards)
 * - REGEXP: Regular expression matching
 * - IN: Check if URL is in array
 * - >, >=, <, <=: Alphabetic comparison
 *
 * Examples:
 * Array syntax:
 * - Exact match: ['type' => 'request_url', 'value' => '/wp-admin/post.php']
 * - Pattern match: ['type' => 'request_url', 'value' => '/wp-admin/*', 'operator' => 'LIKE']
 * - Starts with: ['type' => 'request_url', 'value' => '/api/*', 'operator' => 'LIKE']
 * - Multiple URLs: ['type' => 'request_url', 'value' => ['/login', '/register', '/forgot-password'], 'operator' => 'IN']
 * - Regex match: ['type' => 'request_url', 'value' => '/^\\/post-\\d+$/', 'operator' => 'REGEXP']
 *
 * Builder syntax:
 * - ->request_url('/wp-admin/*') // pattern match (auto-detected)
 * - ->request_url('/api/*', 'LIKE') // explicit pattern match
 * - ->request_url('/login', '=') // exact match
 *
 * @since 0.1.0
 */
class RequestUrl extends BaseCondition
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
        return 'request_url';
    }

    /**
     * Get the actual value from context.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context.
     * @return string The request URI.
     */
    protected function get_actual_value(Context $context): string
    {
        // Ensure HTTP request data is loaded.
        $context->load('request');

        // Get the URI from context.
        $uri = $context->get('request.uri', '');
        return is_string($uri) ? $uri : '';
    }
}
