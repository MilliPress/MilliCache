<?php

/**
 * Base Context Class
 *
 * Abstract base class for context providers that supply data to conditions and actions.
 * Contexts are discovered via namespace scanning and auto-registered with Context.
 *
 * @package     MilliRules
 * @author      Philipp Wellmer
 * @since       0.1.0
 */

namespace MilliCache\Deps\MilliRules\Contexts;

use MilliCache\Deps\MilliRules\Context;

/**
 * Abstract Class BaseContext
 *
 * Provides foundation for context provider classes that:
 * - Register themselves via namespace discovery
 * - Provide lazy-loaded context data
 * - Auto-ensure dependencies before building
 * - Check availability in current environment
 *
 * Example context class:
 * ```php
 * class Post extends BaseContext {
 *     public function get_key(): string {
 *         return 'post';
 *     }
 *
 *     protected function build(): array {
 *         return ['post' => $this->get_post_data()];
 *     }
 *
 *     public function get_dependencies(): array {
 *         return ['user']; // If post context needs user data
 *     }
 * }
 * ```
 *
 * @since 0.1.0
 */
abstract class BaseContext
{
    /**
     * The execution context instance.
     *
     * @since 0.1.0
     * @var Context
     */
    protected Context $context;

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
     * Build context data with automatic dependency resolution.
     *
     * Automatically ensures all declared dependencies are loaded before
     * building this context's data.
     *
     * @since 0.1.0
     *
     * @return array<string, mixed> The context data.
     */
    final public function build_with_dependencies(): array
    {
        // Auto-ensure all dependencies first.
        foreach ($this->get_dependencies() as $dependency) {
            $this->context->load($dependency);
        }

        // Then build this context.
        return $this->build();
    }

    /**
     * Get the context key this provider registers.
     *
     * The key is used to register this provider with ExecutionContext
     * and to access the data via $context->get('key').
     *
     * Example: 'post', 'user', 'query_vars'
     *
     * @since 0.1.0
     *
     * @return string The context key.
     */
    abstract public function get_key(): string;

    /**
     * Build the context data.
     *
     * Implemented by concrete context classes to provide their data.
     * Dependencies declared in get_dependencies() are guaranteed to be
     * available when this method is called.
     *
     * @since 0.1.0
     *
     * @return array<string, mixed> The context data to merge into Context.
     */
    abstract protected function build(): array;

    /**
     * Get dependencies (other context keys needed by this context).
     *
     * Return an array of context keys that must be loaded before building
     * this context. Dependencies are automatically ensured via load().
     *
     * Example: ['post', 'user']
     *
     * @since 0.1.0
     *
     * @return array<int, string> Array of context keys this context depends on.
     */
    public function get_dependencies(): array
    {
        return array();
    }

    /**
     * Check if this context is available in the current environment.
     *
     * Override to check for required functions, classes, or globals before
     * registering this context. Returns true by default.
     *
     * Example:
     * ```php
     * public function is_available(): bool {
     *     return function_exists('get_query_var');
     * }
     * ```
     *
     * @since 0.1.0
     *
     * @return bool True if context can be used, false otherwise.
     */
    public function is_available(): bool
    {
        return true;
    }
}
