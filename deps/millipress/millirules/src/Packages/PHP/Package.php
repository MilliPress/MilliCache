<?php

/**
 * PHP Package
 *
 * Provides PHP execution context with HTTP request conditions, actions, and context for MilliRules.
 * This package is always available and provides framework-agnostic PHP/HTTP functionality.
 *
 * Features:
 * - HTTP request conditions (URL, method, headers, cookies, params)
 * - PHP context building with HTTP request data from $_SERVER, $_COOKIE, $_GET
 * - Request placeholder resolution ({request:uri}, {cookie:name}, etc.)
 *
 * This package has no dependencies and is always available.
 *
 * @package     MilliRules
 * @subpackage  PHP
 * @author      Philipp Wellmer
 * @since 0.1.0
 */

namespace MilliCache\Deps\MilliRules\Packages\PHP;

use MilliCache\Deps\MilliRules\Packages\BasePackage;

/**
 * Class Package
 *
 * PHP package implementation providing PHP execution context with HTTP request-based conditions.
 *
 * @since 0.1.0
 */
class Package extends BasePackage
{
    /**
     * Get the unique package identifier.
     *
     * @since 0.1.0
     *
     * @return string The package name 'PHP'.
     */
    public function get_name(): string
    {
        return 'PHP';
    }

    /**
     * Get the namespaces provided by this package.
     *
     * Returns namespaces for PHP conditions, actions, and contexts.
     *
     * @since 0.1.0
     *
     * @return array<int, string> Array of namespace strings.
     */
    public function get_namespaces(): array
    {
        return array(
            'MilliCache\Deps\MilliRules\Packages\PHP\Conditions',
            'MilliCache\Deps\MilliRules\Packages\PHP\Actions',
            'MilliCache\Deps\MilliRules\Packages\PHP\Contexts',
        );
    }

    /**
     * Check if this package is available in the current environment.
     *
     * PHP package is always available as it only requires basic PHP functionality.
     *
     * @since 0.1.0
     *
     * @return bool Always returns true.
     */
    public function is_available(): bool
    {
        return true;
    }

    /**
     * Get the names of packages required by this package.
     *
     * PHP package has no dependencies.
     *
     * @since 0.1.0
     *
     * @return array<int, string> Empty array (no dependencies).
     */
    public function get_required_packages(): array
    {
        return array();
    }

    /**
     * Get a placeholder resolver instance for this package.
     *
     * Returns PlaceholderResolver configured with the given context.
     * Supports placeholders like {request:uri}, {cookie:name}, {param:key}, {header:name}.
     *
     * @since 0.1.0
     *
     * @param \MilliCache\Deps\MilliRules\Context $context The execution context.
     * @return PlaceholderResolver PlaceholderResolver instance.
     */
    public function get_placeholder_resolver(\MilliCache\Deps\MilliRules\Context $context)
    {
        return new PlaceholderResolver($context);
    }

    /**
     * Resolve a condition/action type to a fully-qualified class name.
     *
     * PHP package uses standard class resolution (no custom fallbacks).
     *
     * @since 0.1.0
     *
     * @param string $type      The type string (e.g., 'request_url').
     * @param string $category  The category: 'Conditions' or 'Actions'.
     * @return string|null Always null (no custom resolution).
     */
    public function resolve_class_name(string $type, string $category): ?string
    {
        return null;
    }
}
