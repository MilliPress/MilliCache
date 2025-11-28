<?php

/**
 * QueryVars Context
 *
 * Provides WordPress query variables array.
 *
 * @package     MilliCache\Deps\MilliRules
 * @subpackage  WordPress\Contexts
 * @author      Philipp Wellmer
 * @since       0.1.0
 */

namespace MilliCache\Deps\MilliRules\Packages\WordPress\Contexts;

use MilliCache\Deps\MilliRules\Contexts\BaseContext;

/**
 * Class QueryVars
 *
 * Provides 'query_vars' context with WordPress query variables.
 * Used by QueryVar condition to check specific query variables.
 *
 * @since 0.1.0
 */
class QueryVars extends BaseContext
{
    /**
     * Get the context key.
     *
     * @since 0.1.0
     *
     * @return string The context key 'query_vars'.
     */
    public function get_key(): string
    {
        return 'query_vars';
    }

    /**
     * Build the query_vars context data.
     *
     * @since 0.1.0
     *
     * @return array<string, mixed> The query_vars context.
     */
    protected function build(): array
    {
        global $wp_query;

        if (! isset($wp_query) || ! isset($wp_query->query_vars)) {
            return array( 'query_vars' => array() );
        }

        return array(
            'query_vars' => $wp_query->query_vars,
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
