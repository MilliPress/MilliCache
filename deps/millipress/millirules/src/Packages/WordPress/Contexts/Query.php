<?php

/**
 * Query Context
 *
 * Provides WordPress query variables from $wp_query->query_vars.
 *
 * @package     MilliRules
 * @subpackage  WordPress\Contexts
 * @author      Philipp Wellmer
 * @since       0.1.0
 */

namespace MilliCache\Deps\MilliRules\Packages\WordPress\Contexts;

use MilliCache\Deps\MilliRules\Contexts\BaseContext;

/**
 * Class Query
 *
 * Provides 'query' context with WordPress query variables.
 * Allows access to WordPress query variables like post_type, paged, s, m, etc.
 * Used by QueryVar condition to check specific query variables.
 *
 * @since 0.1.0
 */
class Query extends BaseContext
{
    /**
     * Get the context key.
     *
     * @since 0.1.0
     *
     * @return string The context key 'query'.
     */
    public function get_key(): string
    {
        return 'query';
    }

    /**
     * Build the query context data.
     *
     * @since 0.1.0
     *
     * @return array<string, mixed> The query context.
     */
    protected function build(): array
    {
        global $wp_query;

        if (! isset($wp_query) || ! isset($wp_query->query_vars)) {
            return array( 'query' => array() );
        }

        return array(
            'query' => $wp_query->query_vars,
        );
    }

    /**
     * Check if WordPress query is available.
     *
     * @since 0.1.0
     *
     * @return bool True if available, false otherwise.
     */
    public function is_available(): bool
    {
        return function_exists('get_query_var');
    }
}
