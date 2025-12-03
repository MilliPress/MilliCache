<?php

/**
 * Placeholder Resolver
 *
 * Resolves dynamic placeholders in rule values.
 *
 * @package     MilliCache\Deps\MilliRules
 * @author      Philipp Wellmer
 */

namespace MilliCache\Deps\MilliRules;

use MilliCache\Deps\MilliRules\Logger;

/**
 * Class PlaceholderResolver
 *
 * Handles resolution of dynamic placeholders using dot-notation syntax.
 * Examples: {post.id}, {request.uri}, {cookie.session_id}
 *
 * Placeholders trigger lazy loading of context sections:
 * - {post.type} → ensures 'post' context is loaded
 * - {request.uri} → ensures 'request' context is loaded
 * - {cookie.name} → ensures 'cookie' context is loaded
 *
 * @since 0.1.0
 */
class PlaceholderResolver
{
    /**
     * Execution context.
     *
     * @since 0.1.0
     * @var Context
     */
    protected Context $context;

    /**
     * Custom placeholder resolvers.
     *
     * @since 0.1.0
     * @var array<string, callable>
     */
    protected static array $custom_resolvers = array();

    /**
     * Constructor.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context.
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Resolve all placeholders in a string value.
     *
     * @since 0.1.0
     *
     * @param string $value The value containing placeholders.
     * @return string The value with placeholders resolved.
     */
    public function resolve(string $value): string
    {
        $result = preg_replace_callback(
            '/\{([^}]+)\}/',
            function ($matches) {
                $placeholder = $matches[1];
                return $this->get_placeholder_value($placeholder);
            },
            $value
        );
        return is_string($result) ? $result : $value;
    }

    /**
     * Register a custom placeholder resolver.
     *
     * @since 0.1.0
     *
     * @param string   $placeholder The placeholder name (e.g., 'custom' for {custom.value}).
     * @param callable $resolver    The resolver callback (receives context and parts array).
     * @return void
     */
    public static function register_placeholder(string $placeholder, callable $resolver): void
    {
        self::$custom_resolvers[ $placeholder ] = $resolver;
    }

    /**
     * Get the value for a placeholder.
     *
     * Ensures the relevant context section is loaded before resolving.
     *
     * @since 0.1.0
     *
     * @param string $placeholder The placeholder name (e.g., 'post.id' or 'request.uri').
     * @return string The resolved value or the original placeholder if not found.
     */
    protected function get_placeholder_value(string $placeholder): string
    {
        $parts = explode('.', $placeholder);

        $category = array_shift($parts);

        if (! is_string($category) || '' === $category) {
            return '{' . $placeholder . '}';
        }

        // Ensure the context section is loaded (lazy loading trigger).
        $this->context->load($category);

        // Check custom resolvers first.
        if (isset(self::$custom_resolvers[ $category ])) {
            try {
                // Pass context as array for backward compatibility with custom resolvers.
                $value = call_user_func(self::$custom_resolvers[ $category ], $this->context->to_array(), $parts);
                return null !== $value ? (string) $value : '{' . $placeholder . '}';
            } catch (\Exception $e) {
                Logger::error('Error in custom placeholder resolver for ' . $category . ': ' . $e->getMessage());
                return '{' . $placeholder . '}';
            }
        }

        // Resolve built-in placeholders.
        $value = $this->resolve_builtin_placeholder($category, $parts);

        if (null === $value) {
            return '{' . $placeholder . '}';
        }

        return is_scalar($value) ? (string) $value : '{' . $placeholder . '}';
    }

    /**
     * Resolve built-in placeholder categories.
     *
     * @since 0.1.0
     *
     * @param string             $category The top-level category.
     * @param array<int, string> $parts    The remaining parts after the category.
     * @return mixed|null The resolved value or null if not found.
     */
    protected function resolve_builtin_placeholder(string $category, array $parts)
    {
        // If no parts, return null (can't return the entire category).
        if (empty($parts)) {
            return null;
        }

        // Build dot-notation path: category.part1.part2...
        $path = $category . '.' . implode('.', $parts);

        // Use ExecutionContext's get method.
        $value = $this->context->get($path);

        // Only return scalar values.
        return is_scalar($value) ? $value : null;
    }
}
