<?php

/**
 * Base Package
 *
 * Abstract base class for MilliRules packages providing default implementations
 * for common package functionality.
 *
 * Subclasses must implement:
 * - get_name(): Return unique package identifier
 * - get_namespaces(): Return array of condition/action namespaces
 * - is_available(): Check if package can be used in current environment
 *
 * Subclasses can override:
 * - build_context(): Default returns empty array
 * - get_placeholder_resolver(): Default returns null
 * - get_required_packages(): Default returns empty array (no dependencies)
 * - register_rule(): Default stores in flat list
 * - execute_rules(): Default uses RuleEngine directly
 *
 * @package     MilliRules
 * @author      Philipp Wellmer
 * @since 		0.1.0
 */

namespace MilliCache\Deps\MilliRules\Packages;

use MilliCache\Deps\MilliRules\Logger;
use MilliCache\Deps\MilliRules\Rules;
use MilliCache\Deps\MilliRules\RuleEngine;

/**
 * Class BasePackage
 *
 * Base implementation of PackageInterface with sensible defaults.
 *
 * @since 0.1.0
 */
abstract class BasePackage implements PackageInterface
{
    /**
     * Registered rules for this package.
     *
     * Stores rules as flat array with metadata merged in.
     *
     * @since 0.1.0
     * @var array<int, array<string, mixed>>
     */
    protected array $rules = array();

    /**
     * Get the unique package identifier.
     *
     * Subclasses must implement this to return their package name.
     *
     * @since 0.1.0
     *
     * @return string The package name.
     */
    abstract public function get_name(): string;

    /**
     * Get the namespaces provided by this package.
     *
     * Subclasses must implement this to return their namespaces.
     *
     * @since 0.1.0
     *
     * @return array<int, string> Array of namespace strings.
     */
    abstract public function get_namespaces(): array;

    /**
     * Check if this package is available in the current environment.
     *
     * Subclasses must implement this to perform environment detection.
     *
     * @since 0.1.0
     *
     * @return bool True if package is available, false otherwise.
     */
    abstract public function is_available(): bool;

    /**
     * Get the names of packages required by this package.
     *
     * Default implementation returns no dependencies.
     * Override to specify required packages.
     *
     * @since 0.1.0
     *
     * @return array<int, string> Array of required package names.
     */
    public function get_required_packages(): array
    {
        return array();
    }

    /**
     * Register this package's namespaces for condition/action resolution.
     *
     * Iterates through namespaces from get_namespaces() and registers
     * each with the Rules API for auto-resolution of conditions and actions.
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function register_namespaces(): void
    {
        $namespaces = $this->get_namespaces();

        foreach ($namespaces as $namespace) {
            // Determine if this is a Conditions or Actions namespace.
            if (strpos($namespace, '\\Conditions') !== false) {
                Rules::register_namespace('Conditions', $namespace);
            } elseif (strpos($namespace, '\\Actions') !== false) {
                Rules::register_namespace('Actions', $namespace);
            }
        }
    }

    /**
     * Register lazy context providers with the execution context.
     *
     * Auto-discovers context classes from Contexts namespaces and registers
     * them as lazy providers with Context. Context classes are only
     * loaded when their data is actually needed.
     *
     * @since 0.1.0
     *
     * @param \MilliCache\Deps\MilliRules\Context $context The execution context.
     * @return void
     */
    public function register_context_providers(\MilliCache\Deps\MilliRules\Context $context): void
    {
        foreach ($this->discover_contexts() as $context_class) {
            if (! is_subclass_of($context_class, \MilliCache\Deps\MilliRules\Contexts\BaseContext::class)) {
                continue;
            }

            $instance = new $context_class($context);

            if (! $instance->is_available()) {
                continue;
            }

            $context->register_provider(
                $instance->get_key(),
                function () use ($instance) {
                    return $instance->build_with_dependencies();
                }
            );
        }
    }

    /**
     * Discover context classes from Contexts namespaces.
     *
     * Scans all Contexts namespaces registered by this package and discovers
     * context provider classes.
     *
     * @since 0.1.0
     *
     * @return array<int, string> Array of fully-qualified context class names.
     */
    protected function discover_contexts(): array
    {
        $contexts = array();

        foreach ($this->get_namespaces() as $namespace) {
            if (strpos($namespace, '\\Contexts') !== false) {
                $contexts = array_merge($contexts, $this->scan_context_namespace($namespace));
            }
        }

        return $contexts;
    }

    /**
     * Scan a Contexts namespace for context classes.
     *
     * @since 0.1.0
     *
     * @param string $namespace The namespace to scan.
     * @return array<int, string> Array of fully-qualified class names.
     */
    protected function scan_context_namespace(string $namespace): array
    {
        $contexts = array();

        // Get the namespace of this BasePackage class.
        $base_namespace = __NAMESPACE__;

        // Convert namespaces to directory paths.
        $base_namespace_path = str_replace('\\', DIRECTORY_SEPARATOR, $base_namespace);
        $namespace_path = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);

        // Calculate the relative namespace by removing the base namespace prefix.
        // This works for both scoped and non-scoped packages.
        if (strpos($namespace_path, $base_namespace_path) === 0) {
            // Remove the base namespace prefix to get the relative path.
            $relative_path = substr($namespace_path, strlen($base_namespace_path));
            $relative_path = ltrim($relative_path, DIRECTORY_SEPARATOR);
        } else {
            // Fallback: just use the last part of the namespace.
            $relative_path = basename($namespace_path);
        }

        // Build the directory path relative to __DIR__ (where BasePackage.php lives).
        $dir = __DIR__ . DIRECTORY_SEPARATOR . $relative_path;

        if (! is_dir($dir)) {
            return array();
        }

        $files = scandir($dir);
        if (false === $files) {
            return array();
        }

        foreach ($files as $file) {
            if ('.' === $file || '..' === $file) {
                continue;
            }

            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $class_name = $namespace . '\\' . pathinfo($file, PATHINFO_FILENAME);
                if (class_exists($class_name)) {
                    $contexts[] = $class_name;
                }
            }
        }

        return $contexts;
    }

    /**
     * Build the context array for this package.
     *
     * Default implementation returns empty array.
     * Override to provide package-specific context data.
     *
     * @since 0.1.0
     *
     * @return array<string, mixed> The context array.
     */
    public function build_context(): array
    {
        return array();
    }

    /**
     * Get a placeholder resolver instance for this package.
     *
     * Default implementation returns null (no placeholder resolution).
     * Override to provide package-specific placeholder resolver.
     *
     * @since 0.1.0
     *
     * @param \MilliCache\Deps\MilliRules\Context $context The execution context.
     * @return object|null PlaceholderResolver instance or null.
     */
    public function get_placeholder_resolver(\MilliCache\Deps\MilliRules\Context $context)
    {
        return null;
    }

    /**
     * Register a rule with this package.
     *
     * Stores rule in flat array with metadata merged in.
     * Subclasses can override for hook-based or other registration patterns.
     *
     * @since 0.1.0
     *
     * @param array<string, mixed> $rule     The rule configuration.
     * @param array<string, mixed> $metadata Additional metadata (order, hooks, etc.).
     * @return void
     */
    public function register_rule(array $rule, array $metadata): void
    {
        // Merge metadata into rule for convenience.
        $rule['_metadata'] = $metadata;
        $this->rules[]     = $rule;
    }

    /**
     * Execute rules for this package with error handling.
     *
     * Default implementation:
     * 1. Sorts rules by order
     * 2. Creates RuleEngine instance
     * 3. Executes all rules
     * 4. Executes trigger actions
     * 5. Returns result
     *
     * Error handling:
     * - Catches all exceptions during execution
     * - Logs errors with package name and exception message
     * - Returns error result structure (never throws exceptions)
     * - Allows graceful degradation
     *
     * Subclasses can override for hook-based or custom execution patterns.
     *
     * @since 0.1.0
     *
     * @param array<int, array<string, mixed>> $rules   The rules to execute.
     * @param \MilliCache\Deps\MilliRules\Context     $context The execution context.
     * @return array<string, mixed> Execution result with statistics and context.
     */
    public function execute_rules(array $rules, \MilliCache\Deps\MilliRules\Context $context): array
    {
        try {
            // Sort rules by order field.
            $sorted_rules = $this->sort_rules_by_order($rules);

            // Create engine and execute.
            $engine = new RuleEngine();
            return $engine->execute($sorted_rules, $context);
        } catch (\Exception $e) {
            // Log error with package name for context.
            $package_name = $this->get_name();
            Logger::error(
                "Error executing rules for package '{$package_name}': " . $e->getMessage()
            );

            // Return error result structure (consistent with RuleEngine::execute() format).
            return array(
                'stopped'         => false,
                'trigger_actions' => array(),
                'debug'           => array(
                    'rules_processed'  => 0,
                    'rules_skipped'    => 0,
                    'rules_matched'    => 0,
                    'actions_executed' => 0,
                    'context'          => $context->to_array(),
                    'error'            => $e->getMessage(),
                ),
            );
        }
    }

    /**
     * Get all registered rules for this package.
     *
     * @since 0.1.0
     *
     * @return array<int, array<string, mixed>> Array of registered rules.
     */
    public function get_rules(): array
    {
        return $this->rules;
    }

    /**
     * Sort rules by their order field in ascending order.
     *
     * Rules without order field are treated as having order = 10.
     *
     * @since 0.1.0
     *
     * @param array<int, array<string, mixed>> $rules The rules to sort.
     * @return array<int, array<string, mixed>> Sorted rules.
     */
    protected function sort_rules_by_order(array $rules): array
    {
        usort(
            $rules,
            function ($a, $b) {
                $order_a = $a['_metadata']['order'] ?? 10;
                $order_b = $b['_metadata']['order'] ?? 10;
                return $order_a <=> $order_b;
            }
        );

        return $rules;
    }

    /**
     * Clear all registered rules from this package.
     *
     * Removes all rules from the package's storage.
     * Called by PackageManager::clear() during reset operations.
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function clear(): void
    {
        $this->rules = array();
    }

    /**
     * Resolve a condition/action type to a fully-qualified class name.
     *
     * Default implementation returns null (no custom resolution).
     * Override in subclasses to provide package-specific resolution logic.
     *
     * @since 0.1.0
     *
     * @param string $type      The type string (e.g., 'is_user_logged_in').
     * @param string $category  The category: 'Conditions' or 'Actions'.
     * @return string|null Fully-qualified class name or null if not resolved.
     */
    public function resolve_class_name(string $type, string $category): ?string
    {
        return null;
    }
}
