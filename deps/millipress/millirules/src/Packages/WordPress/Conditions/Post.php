<?php

/**
 * Post Condition
 *
 * Checks the current post by ID or slug.
 *
 * @package     MilliRules
 * @author      Philipp Wellmer
 */

namespace MilliCache\Deps\MilliRules\Packages\WordPress\Conditions;

use MilliCache\Deps\MilliRules\Conditions\BaseCondition;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class Post
 *
 * Unified post condition that matches against ID or slug.
 * Works for posts, pages, and custom post types.
 *
 * The condition automatically matches the expected value against:
 * - Post ID (numeric values)
 * - Post slug/name (string values)
 *
 * Supported operators:
 * - =: Exact match against any field (default)
 * - !=: Does not match any field
 * - IN: Check if any value matches any field
 * - NOT IN: Check if no values match any field
 * - LIKE: Pattern matching against slug
 * - REGEXP: Regular expression matching against slug
 * - >: Greater than (ID only)
 * - <: Less than (ID only)
 *
 * Examples:
 * Array syntax:
 * - Match by ID: ['type' => 'post', 'value' => 123]
 * - Match by slug: ['type' => 'post', 'value' => 'checkout']
 * - Multiple values: ['type' => 'post', 'value' => [123, 'home', 'about'], 'operator' => 'IN']
 * - Pattern match: ['type' => 'post', 'value' => 'product-*', 'operator' => 'LIKE']
 *
 * Builder syntax:
 * - ->post(123) // match by ID
 * - ->post('checkout') // match by slug
 * - ->post([123, 'home', 'about'], 'IN') // match any
 *
 * @since 0.1.0
 */
class Post extends BaseCondition
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
        return 'post';
    }

    /**
     * Get the actual value from WordPress.
     *
     * Returns an associative array with post information.
     * Does not use context - only WordPress APIs.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context (ignored).
     * @return array<string, mixed>|null Post data array or null if no post.
     */
    protected function get_actual_value(Context $context)
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

        // If we still don't have a post, return null.
        if (null === $post) {
            return null;
        }

        return array(
            'id'   => (int) $post->ID,
            'slug' => (string) $post->post_name,
        );
    }

    /**
     * Compare actual and expected values.
     *
     * Overrides parent to handle matching against multiple fields (id, slug).
     *
     * @since 0.1.0
     *
     * @param mixed $actual   The actual value from WordPress (array with id/slug or null).
     * @param mixed $expected The expected value from config (scalar or array).
     * @return bool True if comparison matches, false otherwise.
     */
    protected function compare($actual, $expected): bool
    {
        // If no post data, handle based on operator.
        if (! is_array($actual)) {
            // For negative operators, not having a post can be considered a match.
            if (in_array($this->operator, array( '!=', 'IS NOT', 'NOT IN', 'NOT EXISTS' ), true)) {
                return true;
            }
            return false;
        }

        $actual_id   = $actual['id'] ?? 0;
        $actual_slug = $actual['slug'] ?? '';

        // Convert expected to array for unified handling.
        $expected_values = is_array($expected) ? $expected : array( $expected );

        // Check if any expected value matches any actual field.
        $has_match = false;

        foreach ($expected_values as $expected_value) {
            // Resolve placeholders if needed.
            $resolved_value = is_string($expected_value) ? $this->resolver->resolve($expected_value) : $expected_value;

            // Try matching against ID (if expected value is numeric).
            if (is_numeric($resolved_value)) {
                if (parent::compare($actual_id, (int) $resolved_value)) {
                    $has_match = true;
                    break;
                }
            }

            // Try matching against slug (for all operators).
            if (parent::compare($actual_slug, $resolved_value)) {
                $has_match = true;
                break;
            }
        }

        // Apply operator logic.
        switch ($this->operator) {
            case '!=':
            case 'IS NOT':
            case 'NOT IN':
            case 'NOT LIKE':
                return ! $has_match;

            default:
                return $has_match;
        }
    }
}
