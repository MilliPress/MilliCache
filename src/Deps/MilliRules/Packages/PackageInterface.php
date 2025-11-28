<?php

/**
 * Package Interface
 *
 * Defines the contract for MilliCache\Deps\MilliRules packages that provide conditions, actions,
 * and context building capabilities for different environments (PHP, WordPress, Laravel, etc.).
 *
 * Packages encapsulate:
 * - Condition/Action class namespaces
 * - Context building logic
 * - Placeholder resolution
 * - Rule registration and execution
 * - Availability detection
 * - Dependency management
 *
 * Example package names: 'PHP', 'WordPress', 'Laravel'
 *
 * @package     MilliCache\Deps\MilliRules
 * @author      Philipp Wellmer
 * @since 0.1.0
 */

namespace MilliCache\Deps\MilliRules\Packages;

/**
 * Interface PackageInterface
 *
 * Contract for MilliCache\Deps\MilliRules packages that provide environment-specific functionality.
 *
 * @since 0.1.0
 */
interface PackageInterface
{
    /**
     * Get the unique package identifier.
     *
     * Returns a unique name for this package (e.g., 'PHP', 'WordPress', 'Laravel').
     * This name is used for package registration, dependency resolution, and namespace mapping.
     *
     * @since 0.1.0
     *
     * @return string The package name.
     */
    public function get_name(): string;

    /**
     * Get the namespaces provided by this package.
     *
     * Returns an array of fully-qualified namespaces that contain conditions and actions
     * provided by this package.
     *
     * Example: ['MilliCache\Deps\MilliRules\Packages\PHP\Conditions', 'MilliCache\Deps\MilliRules\Packages\PHP\Actions']
     *
     * @since 0.1.0
     *
     * @return array<int, string> Array of namespace strings.
     */
    public function get_namespaces(): array;

    /**
     * Check if this package is available in the current environment.
     *
     * Performs runtime detection to determine if the package can be used.
     * For example, WordPress package checks if WordPress functions exist.
     *
     * @since 0.1.0
     *
     * @return bool True if package is available, false otherwise.
     */
    public function is_available(): bool;

    /**
     * Get the names of packages required by this package.
     *
     * Returns an array of package names that must be loaded before this package.
     * Used for dependency resolution during package loading.
     *
     * Example: WordPress package requires ['PHP']
     *
     * @since 0.1.0
     *
     * @return array<int, string> Array of required package names.
     */
    public function get_required_packages(): array;

    /**
     * Register this package's namespaces with the RuleEngine.
     *
     * Called during package loading to make conditions and actions discoverable
     * by the RuleEngine's auto-resolution system.
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function register_namespaces(): void;

    /**
     * Build the context array for this package.
     *
     * Creates and returns a context array containing all data needed for
     * condition evaluation and action execution in this package's environment.
     *
     * The context structure varies by package:
     * - PHP: ['request' => [...]]
     * - WordPress: ['request' => [...], 'wp' => [...]]
     *
     * @since 0.1.0
     *
     * @return array<string, mixed> The context array.
     */
    public function build_context(): array;

    /**
     * Get a placeholder resolver instance for this package.
     *
     * Returns a placeholder resolver configured with the given context,
     * or null if this package doesn't provide placeholder resolution.
     *
     * @since 0.1.0
     *
     * @param \MilliCache\Deps\MilliRules\Context $context The execution context.
     * @return object|null PlaceholderResolver instance or null.
     */
    public function get_placeholder_resolver(\MilliCache\Deps\MilliRules\Context $context);

    /**
     * Register a rule with this package.
     *
     * Stores the rule for later execution by this package. The metadata contains
     * package-specific information like execution hooks, priority, etc.
     *
     * @since 0.1.0
     *
     * @param array<string, mixed> $rule     The rule configuration.
     * @param array<string, mixed> $metadata Additional metadata (order, hooks, etc.).
     * @return void
     */
    public function register_rule(array $rule, array $metadata): void;

    /**
     * Execute rules for this package.
     *
     * Executes the given rules with the provided context and returns execution results.
     * The result includes information about stopped execution, trigger actions, and debug data.
     *
     * @since 0.1.0
     *
     * @param array<int, array<string, mixed>> $rules   The rules to execute.
     * @param \MilliCache\Deps\MilliRules\Context     $context The execution context.
     * @return array<string, mixed> Execution result with 'stopped', 'trigger_actions', and 'debug' keys.
     */
    public function execute_rules(array $rules, \MilliCache\Deps\MilliRules\Context $context): array;

    /**
     * Get all rules registered with this package.
     *
     * Returns an array of all rules that have been registered with this package.
     * Used by PackageManager to collect rules from all packages.
     *
     * @since 0.1.0
     *
     * @return array<int, array<string, mixed>> Array of registered rules.
     */
    public function get_rules(): array;

    /**
     * Resolve a condition/action type to a fully-qualified class name.
     *
     * Allows packages to provide custom class resolution logic, including
     * fallback patterns and special naming conventions.
     *
     * Return null if the package cannot resolve this type.
     *
     * @since 0.1.0
     *
     * @param string $type      The type string (e.g., 'is_user_logged_in').
     * @param string $category  The category: 'Conditions' or 'Actions'.
     * @return string|null Fully-qualified class name or null if not resolved.
     */
    public function resolve_class_name(string $type, string $category): ?string;
}
