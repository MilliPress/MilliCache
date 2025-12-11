<?php

/**
 * Term Context
 *
 * Provides WordPress taxonomy term data for the current queried term.
 *
 * @package     MilliRules
 * @subpackage  WordPress\Contexts
 * @author      Philipp Wellmer
 * @since       0.1.0
 */

namespace MilliCache\Deps\MilliRules\Packages\WordPress\Contexts;

use MilliCache\Deps\MilliRules\Contexts\BaseContext;

/**
 * Class Term
 *
 * Provides 'term' context with current WordPress term data:
 * - id: Term ID
 * - slug: Term slug
 * - name: Term name
 * - taxonomy: Taxonomy name (category, post_tag, etc.)
 *
 * @since 0.1.0
 */
class Term extends BaseContext
{
    /**
     * Get the context key.
     *
     * @since 0.1.0
     *
     * @return string The context key 'term'.
     */
    public function get_key(): string
    {
        return 'term';
    }

    /**
     * Build the term context data.
     *
     * @since 0.1.0
     *
     * @return array<string, mixed> The term context.
     */
    protected function build(): array
    {
        if (! function_exists('get_queried_object')) {
            return array( 'term' => array() );
        }

        $queried_object = get_queried_object();

        // Check if queried object is a term.
        if (! ( $queried_object instanceof \WP_Term )) {
            return array( 'term' => array() );
        }

        return array(
            'term' => array(
                'id'       => $queried_object->term_id,
                'slug'     => $queried_object->slug,
                'name'     => $queried_object->name,
                'taxonomy' => $queried_object->taxonomy,
            ),
        );
    }

    /**
     * Check if WordPress term functions are available.
     *
     * @since 0.1.0
     *
     * @return bool True if available, false otherwise.
     */
    public function is_available(): bool
    {
        return function_exists('get_queried_object');
    }
}
