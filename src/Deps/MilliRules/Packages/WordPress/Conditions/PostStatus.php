<?php

/**
 * Post Status Condition
 *
 * Checks the current WordPress post status.
 *
 * @package     MilliCache\Deps\MilliRules
 * @author      Philipp Wellmer
 */

namespace MilliCache\Deps\MilliRules\Packages\WordPress\Conditions;

use MilliCache\Deps\MilliRules\Conditions\BaseCondition;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class PostStatus
 *
 * Checks the current WordPress post status (publish, draft, pending, etc.).
 * Uses WordPress APIs to determine the current post status.
 *
 * Supported operators:
 * - =: Exact post status match (default)
 * - !=: Post status doesn't match
 * - IN: Check if post status is in array
 * - NOT IN: Check if post status is not in array
 *
 * Examples:
 * Array syntax:
 * - Check for published: ['type' => 'post_status', 'value' => 'publish']
 * - Check not draft: ['type' => 'post_status', 'value' => 'draft', 'operator' => '!=']
 * - Multiple statuses: ['type' => 'post_status', 'value' => ['publish', 'private'], 'operator' => 'IN']
 *
 * Builder syntax:
 * - ->post_status('publish') // exact match
 * - ->post_status(['publish', 'private'], 'IN') // multiple statuses
 *
 * @since 0.1.0
 */
class PostStatus extends BaseCondition
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
        return 'post_status';
    }

    /**
     * Get the actual value from WordPress.
     *
     * Does not use context - only WordPress APIs.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context (ignored).
     * @return string The current post status.
     */
    protected function get_actual_value(Context $context): string
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

        // If we have a post, return its status.
        if (null !== $post) {
            return (string) $post->post_status;
        }

        return '';
    }
}
