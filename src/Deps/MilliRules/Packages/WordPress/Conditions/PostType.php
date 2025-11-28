<?php

/**
 * Post Type Condition
 *
 * Checks WordPress post type.
 *
 * @package     MilliCache\Deps\MilliRules
 * @author      Philipp Wellmer
 */

namespace MilliCache\Deps\MilliRules\Packages\WordPress\Conditions;

use MilliCache\Deps\MilliRules\Conditions\BaseCondition;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class PostType
 *
 * Checks the WordPress post type (post, page, or custom post type).
 * Uses WordPress APIs to determine the current post type.
 *
 * Supported operators:
 * - =: Exact post type match (default)
 * - !=: Post type doesn't match
 * - IN: Check if post type is in array
 * - LIKE: Pattern matching with wildcards (* and ?)
 * - REGEXP: Regular expression matching
 *
 * Examples:
 * Array syntax:
 * - Check for post: ['type' => 'post_type', 'value' => 'post']
 * - Check for page: ['type' => 'post_type', 'value' => 'page', 'operator' => '=']
 * - Check not page: ['type' => 'post_type', 'value' => 'page', 'operator' => '!=']
 * - Multiple types: ['type' => 'post_type', 'value' => ['post', 'page'], 'operator' => 'IN']
 * - Custom post type: ['type' => 'post_type', 'value' => 'product']
 * - Pattern match: ['type' => 'post_type', 'value' => 'wp_*', 'operator' => 'LIKE']
 *
 * Builder syntax:
 * - ->post_type('post') // exact match
 * - ->post_type('page') // exact match
 * - ->post_type(['post', 'page'], 'IN') // multiple types
 *
 * @since 0.1.0
 */
class PostType extends BaseCondition
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
        return 'post_type';
    }

    /**
     * Get the actual value from context.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context.
     * @return string The current post type.
     */
    protected function get_actual_value(Context $context): string
    {
        // Ensure WordPress post data is loaded.
        $context->load('post');

        // Get post type from context.
        $post_type = $context->get('post.type', '');
        return is_string($post_type) ? $post_type : '';
    }
}
