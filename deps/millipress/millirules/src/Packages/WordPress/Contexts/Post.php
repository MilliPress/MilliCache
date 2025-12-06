<?php

/**
 * Post Context
 *
 * Provides WordPress post data for the current post.
 *
 * @package     MilliRules
 * @subpackage  WordPress\Contexts
 * @author      Philipp Wellmer
 * @since       0.1.0
 */

namespace MilliCache\Deps\MilliRules\Packages\WordPress\Contexts;

use MilliCache\Deps\MilliRules\Contexts\BaseContext;

/**
 * Class Post
 *
 * Provides 'post' context with current WordPress post data:
 * - id: Post ID
 * - type: Post type (post, page, custom)
 * - status: Post status (publish, draft, etc.)
 * - author: Author ID
 * - parent: Parent post ID
 * - name: Post slug
 * - title: Post title
 *
 * @since 0.1.0
 */
class Post extends BaseContext
{
    /**
     * Get the context key.
     *
     * @since 0.1.0
     *
     * @return string The context key 'post'.
     */
    public function get_key(): string
    {
        return 'post';
    }

    /**
     * Build the post context data.
     *
     * @since 0.1.0
     *
     * @return array<string, mixed> The post context.
     */
    protected function build(): array
    {
        $post_data = array(
            'id'     => 0,
            'type'   => '',
            'status' => '',
            'author' => 0,
            'parent' => 0,
            'name'   => '',
            'title'  => '',
        );

        // Try to get queried object first.
        if (function_exists('get_queried_object')) {
            $queried_object = get_queried_object();
            if ($queried_object instanceof \WP_Post) {
                return array(
                    'post' => $this->extract_post_data($queried_object),
                );
            }
        }

        // Fallback to global $post.
        global $post;
        if ($post instanceof \WP_Post) {
            return array(
                'post' => $this->extract_post_data($post),
            );
        }

        return array(
            'post' => $post_data,
        );
    }

    /**
     * Extract post data from WP_Post object.
     *
     * @since 0.1.0
     *
     * @param \WP_Post $post The post object.
     * @return array<string, mixed> Post data.
     */
    protected function extract_post_data(\WP_Post $post): array
    {
        return array(
            'id'     => $post->ID,
            'type'   => $post->post_type,
            'status' => $post->post_status,
            'author' => (int) $post->post_author,
            'parent' => (int) $post->post_parent,
            'name'   => $post->post_name,
            'title'  => $post->post_title,
        );
    }

    /**
     * Check if WordPress post functions are available.
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
