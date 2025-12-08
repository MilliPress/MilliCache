<?php

/**
 * PHP Placeholder Resolver
 *
 * Extends PlaceholderResolver with PHP context placeholder support for HTTP request data.
 *
 * @package     MilliRules
 * @subpackage  PHP
 * @author      Philipp Wellmer
 * @since 0.1.0
 */

namespace MilliCache\Deps\MilliRules\Packages\PHP;

use MilliCache\Deps\MilliRules\PlaceholderResolver as BasePlaceholderResolver;

/**
 * Class PlaceholderResolver
 *
 * Adds PHP context placeholder resolvers for HTTP request data, cookies, params, and headers.
 *
 * Supported placeholders:
 * - {request:uri} - Request URI
 * - {request:method} - Request method
 * - {request:path} - URL path
 * - {request:scheme} - Request scheme (http/https)
 * - {request:host} - Host name
 * - {request:query} - Query string
 * - {cookie:name} - Cookie value
 * - {param:name} - Request parameter
 * - {header:name} - Header value
 *
 * @since 0.1.0
 */
class PlaceholderResolver extends BasePlaceholderResolver
{
    /**
     * Constructor.
     *
     * Registers default HTTP placeholder resolvers.
     *
     * @since 0.1.0
     *
     * @param \MilliCache\Deps\MilliRules\Context $context The execution context.
     */
    public function __construct(\MilliCache\Deps\MilliRules\Context $context)
    {
        parent::__construct($context);
        $this->register_default_resolvers();
    }

    /**
     * Register default HTTP placeholder resolvers.
     *
     * @since 0.1.0
     *
     * @return void
     */
    protected function register_default_resolvers(): void
    {
        // Cookie resolver: {cookie:name}
        self::register_placeholder(
            'cookie',
            function ($context, $parts) {
                if (empty($parts)) {
                    return null;
                }

                $cookie_name = $parts[0];
                $cookies = $context['request']['cookies'] ?? array();

                if (! is_array($cookies)) {
                    return null;
                }

                // Case-insensitive cookie lookup.
                $cookies_lower = array_change_key_case($cookies, CASE_LOWER);
                $cookie_name_lower = strtolower($cookie_name);

                return $cookies_lower[ $cookie_name_lower ] ?? null;
            }
        );

        // Param resolver: {param:name}
        self::register_placeholder(
            'param',
            function ($context, $parts) {
                if (empty($parts)) {
                    return null;
                }

                $param_name = $parts[0];
                $params = $context['request']['params'] ?? array();

                if (! is_array($params)) {
                    return null;
                }

                return $params[ $param_name ] ?? null;
            }
        );

        // Header resolver: {header:name}
        self::register_placeholder(
            'header',
            function ($context, $parts) {
                if (empty($parts)) {
                    return null;
                }

                $header_name = $parts[0];
                $headers = $context['request']['headers'] ?? array();

                if (! is_array($headers)) {
                    return null;
                }

                // Case-insensitive header lookup.
                $headers_lower = array_change_key_case($headers, CASE_LOWER);
                $header_name_lower = strtolower($header_name);

                return $headers_lower[ $header_name_lower ] ?? null;
            }
        );
    }

    /**
     * Resolve built-in placeholder categories.
     *
     * Extends parent method to handle nested request paths.
     *
     * @since 0.1.0
     *
     * @param string             $category The top-level category.
     * @param array<int, string> $parts The remaining parts after the category.
     * @return mixed|null The resolved value or null if not found.
     */
    protected function resolve_builtin_placeholder(string $category, array $parts): mixed
    {
        // Handle request category with nested paths.
        if ('request' === $category && ! empty($parts)) {
            return $this->resolve_nested($this->context['request'] ?? array(), $parts);
        }

        // Delegate to parent for other categories.
        return parent::resolve_builtin_placeholder($category, $parts);
    }

    /**
     * Resolve nested path in an array.
     *
     * Helper method to navigate through nested array structure.
     *
     * @since 0.1.0
     *
     * @param mixed                $data  The data to navigate.
     * @param array<int, string>   $parts The path parts.
     * @return mixed|null The resolved value or null if not found.
     */
    protected function resolve_nested($data, array $parts)
    {
        $current = $data;

        foreach ($parts as $key) {
            if (! is_array($current)) {
                return null;
            }

            if (! isset($current[ $key ])) {
                return null;
            }

            $current = $current[ $key ];
        }

        // Only return scalar values.
        return is_scalar($current) ? $current : null;
    }
}
