<?php

/**
 * Context
 *
 * Smart context wrapper that supports lazy loading of context sections.
 *
 * @package     MilliCache\Deps\MilliRules
 * @author      Philipp Wellmer
 * @since       2.0.0
 */

namespace MilliCache\Deps\MilliRules;

use MilliCache\Deps\MilliRules\Logger;

/**
 * Class Context
 *
 * Provides lazy-loading context management for rule execution.
 * Context sections are loaded on-demand via registered providers.
 *
 * Key Features:
 * - Lazy Loading: Context sections load only when needed
 * - Memoization: Each section loads at most once
 * - Dot Notation: Access nested values with 'post.type', 'request.uri', etc.
 * - Provider Registry: Packages register callables to provide context data
 *
 * Example Usage:
 * ```php
 * $context = new Context();
 * $context->register_provider( 'post', function() {
 *     return [ 'post' => [ 'id' => 123, 'type' => 'page' ] ];
 * } );
 *
 * // Lazy load when needed
 * $context->load( 'post' );
 * echo $context->get( 'post.type' ); // 'page'
 * ```
 *
 * @since 0.1.0
 */
class Context
{
    /**
     * Context data storage.
     *
     * @since 0.1.0
     * @var array<string, mixed>
     */
    private array $data = array();

    /**
     * Registered lazy providers.
     *
     * @since 0.1.0
     * @var array<string, callable>
     */
    private array $providers = array();

    /**
     * Track which keys have been loaded.
     *
     * @since 0.1.0
     * @var array<string, bool>
     */
    private array $loaded = array();

    /**
     * Register a lazy provider for a context key.
     *
     * The provider callable should return an associative array that will be
     * merged into the context when the key is loaded.
     *
     * Example:
     * ```php
     * $context->register_provider( 'post', function() {
     *     return [ 'post' => [ 'id' => 123, 'type' => 'page' ] ];
     * } );
     * ```
     *
     * @since 0.1.0
     *
     * @param string   $key      Context key (e.g., 'request', 'post', 'cookie').
     * @param callable(): array<string, mixed> $provider Callable that returns array to merge into context.
     *                                                    Signature: function(): array<string, mixed>
     * @return self Fluent interface.
     */
    public function register_provider(string $key, callable $provider): self
    {
        $this->providers[ $key ] = $provider;
        return $this;
    }

    /**
     * Ensure a context section is loaded.
     *
     * If the section hasn't been loaded yet and a provider exists,
     * invoke the provider and merge results into context.
     *
     * This method is idempotent - calling it multiple times with the same
     * key will only load the provider once.
     *
     * @since 0.1.0
     *
     * @param string $key Context key to load.
     * @return self Fluent interface.
     */
    public function load(string $key): self
    {
        // Already loaded or being loaded? Done.
        if (isset($this->loaded[ $key ])) {
            return $this;
        }

        // Mark as loading (prevents recursion).
        $this->loaded[ $key ] = true;

        // No provider? Nothing to load.
        if (! isset($this->providers[ $key ])) {
            return $this;
        }

        try {
            $provider = $this->providers[ $key ];
            $result   = call_user_func($provider);

            if (is_array($result)) {
                // Deep merge provider result into context data.
                $this->data = $this->array_merge_recursive_distinct($this->data, $result);
            }
        } catch (\Exception $e) {
            Logger::error('Error loading context key "' . $key . '": ' . $e->getMessage());
        }

        return $this;
    }

    /**
     * Get a value using dot notation.
     *
     * Supports nested path access like 'post.type' or 'request.headers.user-agent'.
     * Also supports object property access like 'hook.args.0.ID' or 'hook.args.2.permalink'.
     *
     * @since 0.1.0
     *
     * @param string $path    Path like 'post.type' or 'request.uri'.
     * @param mixed  $default Default value if not found.
     * @return mixed The value or default if not found.
     */
    public function get(string $path, $default = null)
    {
        $parts   = explode('.', $path);

        // Autoloads the top-level key is loaded
        if (! empty($parts)) {
            $this->load($parts[0]);
        }

        $current = $this->data;

        foreach ($parts as $part) {
            // Handle array access
            if (is_array($current)) {
                if (! isset($current[ $part ])) {
                    return $default;
                }
                $current = $current[ $part ];
                continue;
            }

            // Handle object property access
            if (is_object($current)) {
                // Try public property first
                if (property_exists($current, $part)) {
                    $current = $current->{$part};
                    continue;
                }

                // Try magic __get() method
                if (method_exists($current, '__get')) {
                    $current = $current->{$part};
                    continue;
                }

                // Property doesn't exist
                return $default;
            }

            // Not an array or object
            return $default;
        }

        return $current;
    }

    /**
     * Set a value using dot notation.
     *
     * Creates nested arrays as needed.
     *
     * @since 0.1.0
     *
     * @param string $path  Path like 'post.type'.
     * @param mixed  $value Value to set.
     * @return self Fluent interface.
     */
    public function set(string $path, $value): self
    {
        $parts = explode('.', $path);
        $key   = array_shift($parts);

        if (empty($parts)) {
            $this->data[ $key ] = $value;
        } else {
            if (! isset($this->data[ $key ])) {
                $this->data[ $key ] = array();
            }
            $this->set_nested($this->data[ $key ], $parts, $value);
        }

        return $this;
    }

    /**
     * Check if a path exists.
     *
     * @since 0.1.0
     *
     * @param string $path Path like 'post.type'.
     * @return bool True if the path exists, false otherwise.
     */
    public function has(string $path): bool
    {
        return null !== $this->get($path);
    }

    /**
     * Get entire context as plain array.
     *
     * Useful for passing to external systems or debugging.
     *
     * @since 0.1.0
     *
     * @return array<string, mixed> The complete context data.
     */
    public function to_array(): array
    {
        return $this->data;
    }

    /**
     * Helper to set nested array values.
     *
     * @since 0.1.0
     *
     * @param array<string, mixed> &$array Reference to array.
     * @param array<int, string>   $parts  Path parts.
     * @param mixed                $value  Value to set.
     * @return void
     */
    private function set_nested(array &$array, array $parts, $value): void
    {
        $key = array_shift($parts);

        if (empty($parts)) {
            $array[ $key ] = $value;
        } else {
            if (! isset($array[ $key ]) || ! is_array($array[ $key ])) {
                $array[ $key ] = array();
            }
            $this->set_nested($array[ $key ], $parts, $value);
        }
    }

    /**
     * Merge arrays recursively, with later values overwriting earlier ones.
     *
     * Unlike array_merge_recursive, this doesn't create arrays of values.
     *
     * @since 0.1.0
     *
     * @param array<string, mixed> $array1 Base array.
     * @param array<string, mixed> $array2 Array to merge in.
     * @return array<string, mixed> Merged array.
     */
    private function array_merge_recursive_distinct(array $array1, array $array2): array
    {
        $merged = $array1;

        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($merged[ $key ]) && is_array($merged[ $key ])) {
                $merged[ $key ] = $this->array_merge_recursive_distinct($merged[ $key ], $value);
            } else {
                $merged[ $key ] = $value;
            }
        }

        return $merged;
    }
}
