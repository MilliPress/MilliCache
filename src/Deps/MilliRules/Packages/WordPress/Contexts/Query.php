<?php

/**
 * Query Context
 *
 * Provides WordPress query conditional tags (is_home, is_archive, etc.).
 *
 * @package     MilliCache\Deps\MilliRules
 * @subpackage  WordPress\Contexts
 * @author      Philipp Wellmer
 * @since       0.1.0
 */

namespace MilliCache\Deps\MilliRules\Packages\WordPress\Contexts;

use MilliCache\Deps\MilliRules\Contexts\BaseContext;

/**
 * Class Query
 *
 * Provides 'query' context with WordPress query conditional flags:
 * - is_singular: Is singular post/page
 * - is_single: Is single post
 * - is_page: Is page
 * - is_archive: Is archive page
 * - is_home: Is home page
 * - is_front_page: Is front page
 * - is_search: Is search results
 * - is_404: Is 404 error
 * - is_admin: Is admin area
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
        if (! function_exists('is_singular')) {
            return array( 'query' => array() );
        }

        global $wp_query;

        if (! isset($wp_query)) {
            return array( 'query' => array() );
        }

        return array(
            'query' => array(
                'is_singular'   => is_singular(),
                'is_single'     => is_single(),
                'is_page'       => is_page(),
                'is_archive'    => is_archive(),
                'is_home'       => is_home(),
                'is_front_page' => is_front_page(),
                'is_search'     => is_search(),
                'is_404'        => is_404(),
                'is_admin'      => is_admin(),
            ),
        );
    }

    /**
     * Check if WordPress query functions are available.
     *
     * @since 0.1.0
     *
     * @return bool True if available, false otherwise.
     */
    public function is_available(): bool
    {
        return function_exists('is_singular');
    }
}
