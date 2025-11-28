<?php

/**
 * Post Parent Condition
 *
 * Checks the parent post ID of the current WordPress post.
 *
 * @package     MilliCache\Deps\MilliRules
 * @author      Philipp Wellmer
 */

namespace MilliCache\Deps\MilliRules\Packages\WordPress\Conditions;

use MilliCache\Deps\MilliRules\Conditions\BaseCondition;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class PostParent
 *
 * Checks the parent post ID of the current WordPress post.
 * Useful for checking hierarchical relationships (pages, custom post types).
 * Uses WordPress APIs to determine the parent post ID.
 *
 * Supported operators:
 * - =: Exact parent ID match (default)
 * - !=: Parent ID doesn't match
 * - IN: Check if parent ID is in array
 * - NOT IN: Check if parent ID is not in array
 * - >: Greater than (useful for checking if post has a parent with ID > 0)
 *
 * Examples:
 * Array syntax:
 * - Check for parent 10: ['type' => 'post_parent', 'value' => 10]
 * - Check has parent: ['type' => 'post_parent', 'value' => 0, 'operator' => '>']
 * - Check no parent: ['type' => 'post_parent', 'value' => 0, 'operator' => '=']
 *
 * Builder syntax:
 * - ->post_parent(10) // exact match
 * - ->post_parent(0, '>') // has parent
 *
 * @since 0.1.0
 */
class PostParent extends BaseCondition
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
        return 'post_parent';
    }

    /**
     * Get the actual value from WordPress.
     *
     * Does not use context - only WordPress APIs.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context (ignored).
     * @return int The parent post ID.
     */
    protected function get_actual_value(Context $context): int
    {
        $post = null;

        // Try to get post from the queried object.
        if (function_exists('get_queried_object')) {
            $queried = get_queried_object();

            // If it's a WP_Post object, use it.
            if ($queried instanceof \WP_Post) {
                $post = $queried;
            }
        }

        // Fallback to global $post if not found yet.
        if (null === $post && isset($GLOBALS['post']) && $GLOBALS['post'] instanceof \WP_Post) {
            $post = $GLOBALS['post'];
        }

        // If we have a post, return its parent.
        if (null !== $post) {
            return (int) $post->post_parent;
        }

        return 0;
    }
}
