<?php

/**
 * WordPress Placeholder Resolver
 *
 * Extends PHP PlaceholderResolver with WordPress-specific placeholder support.
 *
 * @package     MilliRules
 * @subpackage  WordPress
 * @author      Philipp Wellmer
 * @since 0.1.0
 */

namespace MilliCache\Deps\MilliRules\Packages\WordPress;

use MilliCache\Deps\MilliRules\Packages\PHP\PlaceholderResolver as PhpPlaceholderResolver;

/**
 * Class PlaceholderResolver
 *
 * Adds WordPress-specific placeholder resolvers for post, user, query, and constants.
 *
 * Supported placeholders:
 * - {post.id} - Post ID
 * - {post.type} - Post type
 * - {post.status} - Post status
 * - {post.author} - Post author ID
 * - {post.parent} - Post parent ID
 * - {post.name} - Post slug
 * - {post.title} - Post title
 * - {user.id} - User ID
 * - {user.logged_in} - Whether user is logged in
 * - {user.login} - User login name
 * - {user.email} - User email
 * - {user.display_name} - User display name
 * - {query.post_type} - Query variable (post type)
 * - {query.paged} - Query variable (pagination)
 * - {query.s} - Query variable (search term)
 * - {constants.WP_DEBUG} - Constant value
 * - {constants.WP_CACHE} - Constant value
 *
 * @since 0.1.0
 */
class PlaceholderResolver extends PhpPlaceholderResolver
{
    /**
     * Constructor.
     *
     * Registers default WordPress placeholder resolvers.
     *
     * @since 0.1.0
     *
     * @param \MilliCache\Deps\MilliRules\Context $context The execution context.
     */
    public function __construct(\MilliCache\Deps\MilliRules\Context $context)
    {
        parent::__construct($context);
        $this->register_wordpress_resolvers();
    }

    /**
     * Register WordPress placeholder resolvers.
     *
     * @since 0.1.0
     *
     * @return void
     */
    protected function register_wordpress_resolvers(): void
    {
        // WordPress resolver: {post.id}, {user.login}, {query.is_singular}, {constants.WP_DEBUG}
        self::register_placeholder(
            'wp',
            function ($context, $parts) {
                if (empty($parts) || ! isset($context['wp'])) {
                    return null;
                }

                // Use the resolve_nested helper to navigate nested paths.
                return $this->resolve_nested($context['wp'], $parts);
            }
        );
    }

    /**
     * Resolve built-in placeholder categories.
     *
     * Extends parent method to handle WordPress-specific placeholders.
     *
     * @since 0.1.0
     *
     * @param string             $category The top-level category.
     * @param array<int, string> $parts The remaining parts after the category.
     * @return mixed|null The resolved value or null if not found.
     */
    protected function resolve_builtin_placeholder(string $category, array $parts): mixed
    {
        // Handle 'wp' category with nested paths.
        if ('wp' === $category && ! empty($parts)) {
            return $this->resolve_nested($this->context['wp'] ?? array(), $parts);
        }

        // Delegate to parent for other categories (request, cookie, param, header).
        return parent::resolve_builtin_placeholder($category, $parts);
    }
}
