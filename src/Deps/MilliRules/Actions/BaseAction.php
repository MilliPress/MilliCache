<?php

/**
 * Base Action
 *
 * Abstract base class for all action implementations.
 *
 * @package MilliCache\Deps\MilliRules
 * @author  Philipp Wellmer
 * @since   0.1.0
 */

namespace MilliCache\Deps\MilliRules\Actions;

use MilliCache\Deps\MilliRules\Context;
use MilliCache\Deps\MilliRules\PlaceholderResolver;

/**
 * Class BaseAction
 *
 * Provides common functionality for all actions including placeholder resolution.
 *
 * @since 0.1.0
 */
abstract class BaseAction implements ActionInterface
{
    /**
     * Action type identifier.
     *
     * @since 0.1.0
     * @var string
     */
    protected string $type;

    /**
     * Action arguments (both positional and named).
     *
     * Contains all arguments from dynamic method calls (numeric keys)
     * and custom() calls (string keys), excluding 'type'.
     *
     * @since 0.1.0
     * @var array<int|string, mixed>
     */
    protected array $args;

    /**
     * Placeholder resolver instance.
     *
     * @since 0.1.0
     * @var PlaceholderResolver
     */
    protected PlaceholderResolver $resolver;

    /**
     * Constructor.
     *
     * @since 0.1.0
     *
     * @param array<int|string, mixed> $config  The action configuration.
     * @param Context     $context The execution context.
     */
    public function __construct(array $config, Context $context)
    {
        $this->type = $config['type'] ?? '';

        // Extract arguments: all config keys except 'type'.
        // Both dynamic methods and custom() calls now use the same structure.
        $this->args = array_filter(
            $config,
            fn($key) => $key !== 'type',
            ARRAY_FILTER_USE_KEY
        );

        $this->resolver = new PlaceholderResolver($context);
    }

    /**
     * Resolve placeholders in a value.
     *
     * @since 0.1.0
     *
     * @param string $value The value to resolve.
     * @return string The resolved value.
     */
    protected function resolve_value(string $value): string
    {
        return $this->resolver->resolve($value);
    }
}
