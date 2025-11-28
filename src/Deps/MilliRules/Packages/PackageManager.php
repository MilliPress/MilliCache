<?php

/**
 * Package Manager
 *
 * Central manager for MilliCache\Deps\MilliRules packages. Handles package registration, loading,
 * dependency resolution, context building, and namespace mapping.
 *
 * Features:
 * - Package registration and lifecycle management
 * - Automatic dependency resolution with cycle detection
 * - Context aggregation from multiple packages
 * - Placeholder resolver prioritization
 * - Namespace-to-package mapping for fast lookups
 * - Rule delegation to appropriate packages
 *
 * @package     MilliCache\Deps\MilliRules
 * @author      Philipp Wellmer
 * @since 0.1.0
 */

namespace MilliCache\Deps\MilliRules\Packages;

use MilliCache\Deps\MilliRules\Logger;

/**
 * Class PackageManager
 *
 * Static manager for all MilliCache\Deps\MilliRules packages.
 *
 * @since 0.1.0
 */
class PackageManager
{
    /**
     * Registered package instances.
     *
     * @since 0.1.0
     * @var array<string, PackageInterface>
     */
    private static array $packages = array();

    /**
     * Names of currently loaded packages.
     *
     * @since 0.1.0
     * @var array<int, string>
     */
    private static array $loaded_packages = array();

    /**
     * Maps namespaces to package names for fast lookup.
     *
     * Structure: ['MilliCache\Deps\MilliRules\PHP\Conditions' => 'PHP', ...]
     *
     * @since 0.1.0
     * @var array<string, string>
     */
    private static array $namespace_registry = array();

    /**
     * Cache for namespace-to-package lookups.
     *
     * Stores results of map_namespace_to_package() to avoid repeated sorting
     * and string operations. Cache is cleared when packages are registered.
     *
     * Structure: ['MilliCache\Deps\MilliRules\PHP\Conditions\RequestUri' => 'PHP', ...]
     *
     * @since 0.1.0
     * @var array<string, string|null>
     */
    private static array $namespace_cache = array();

    /**
     * Queue of rules waiting for their required packages to be loaded.
     *
     * Rules are added here when registered before their packages are loaded.
     * When a package loads, pending rules are checked and registered if all
     * their dependencies are now met.
     *
     * Structure: [
     *   [
     *     'rule' => [...],
     *     'metadata' => [...],
     *     'required_packages' => ['WP', 'PHP']
     *   ],
     *   ...
     * ]
     *
     * @since 0.1.0
     * @var array<int, array{rule: array<string, mixed>, metadata: array<string, mixed>, required_packages: array<int, string>}>
     */
    private static array $pending_rules = array();

    /**
     * Register a package with the manager.
     *
     * Stores package instance and maps its namespaces for fast lookup.
     * Clears the namespace cache to ensure fresh lookups.
     * Does not load the package - call load_available_packages() to load.
     *
     * @since 0.1.0
     *
     * @param PackageInterface $package The package to register.
     * @return void
     */
    public static function register_package(PackageInterface $package): void
    {
        $name                  = $package->get_name();
        self::$packages[ $name ] = $package;

        // Map namespaces to package name.
        foreach ($package->get_namespaces() as $namespace) {
            self::$namespace_registry[ $namespace ] = $name;
        }

        // Clear namespace cache (namespace registry changed).
        self::$namespace_cache = array();
    }

    /**
     * Load available packages with dependency resolution.
     *
     * If $package_names is null, checks all registered packages with is_available()
     * and loads those that are available.
     *
     * If $package_names is provided, loads only those packages if available.
     *
     * Resolves dependencies recursively via get_required_packages().
     * Implements cycle detection for circular dependencies.
     * Calls register_namespaces() for each loaded package.
     *
     * Logs errors for:
     * - Missing dependencies
     * - Circular dependencies
     * - Unavailable packages
     *
     * @since 0.1.0
     *
     * @param array<int, string>|null $package_names Optional array of package names to load.
     * @return array<int, string> Array of successfully loaded package names.
     */
    public static function load_packages(?array $package_names = null): array
    {
        $to_load = array();

        if (null === $package_names) {
            // Auto-detect available packages.
            foreach (self::$packages as $name => $package) {
                if ($package->is_available()) {
                    $to_load[] = $name;
                }
            }
        } else {
            $to_load = $package_names;
        }

        // Load packages with dependency resolution.
        $loading_stack = array();

        foreach ($to_load as $name) {
            self::load_package_recursive($name, $loading_stack);
        }

        return self::$loaded_packages;
    }

    /**
     * Recursively load a package and its dependencies with cycle detection.
     *
     * Implements cycle detection by tracking packages currently being loaded in the
     * $loading_stack parameter. If a package is encountered that's already in the stack,
     * a circular dependency is detected and logged.
     *
     * Algorithm:
     * 1. Check if package already loaded (skip if yes)
     * 2. Validate package exists and is available
     * 3. Check if package is in loading_stack (cycle detection)
     * 4. Add package to loading_stack
     * 5. Recursively load all required packages
     * 6. Register package namespaces
     * 7. Mark as loaded
     * 8. Remove from loading_stack
     *
     * Example cycle: A → B → C → A would be detected when loading A the second time.
     *
     * @since 0.1.0
     *
     * @param string               $name          The package name to load.
     * @param array<int, string>   $loading_stack Stack of packages currently being loaded (for cycle detection).
     * @return bool True if loaded successfully, false otherwise.
     */
    private static function load_package_recursive(string $name, array &$loading_stack): bool
    {
        // Already loaded - skip.
        if (in_array($name, self::$loaded_packages, true)) {
            return true;
        }

        // Check if package exists.
        if (! isset(self::$packages[ $name ])) {
            Logger::warning("Package '{$name}' not registered");
            return false;
        }

        $package = self::$packages[ $name ];

        // Check if available.
        if (! $package->is_available()) {
            Logger::warning("Package '{$name}' is not available in this environment");
            return false;
        }

        // Cycle detection.
        if (in_array($name, $loading_stack, true)) {
            $cycle_path = implode(' → ', $loading_stack) . ' → ' . $name;
            Logger::error("Circular dependency detected: {$cycle_path}");
            return false;
        }

        // Add to loading stack for cycle detection.
        $loading_stack[] = $name;

        // Load dependencies first.
        $required_packages = $package->get_required_packages();
        foreach ($required_packages as $required_name) {
            if (! self::load_package_recursive($required_name, $loading_stack)) {
                Logger::error("Failed to load required package '{$required_name}' for '{$name}'");
                // Remove from loading stack.
                array_pop($loading_stack);
                return false;
            }
        }

        // Register namespaces with RuleEngine.
        $package->register_namespaces();

        // Mark as loaded.
        self::$loaded_packages[] = $name;

        // Process pending rules - now that this package is loaded.
        self::register_pending_rules();

        // Remove from loading stack.
        array_pop($loading_stack);

        return true;
    }

    /**
     * Get all loaded package instances.
     *
     * @since 0.1.0
     *
     * @return array<int, PackageInterface> Array of loaded package instances.
     */
    public static function get_loaded_packages(): array
    {
        $packages = array();

        foreach (self::$loaded_packages as $name) {
            if (isset(self::$packages[ $name ])) {
                $packages[] = self::$packages[ $name ];
            }
        }

        return $packages;
    }

    /**
     * Get all registered package instances (loaded or not).
     *
     * Returns all packages that have been registered, regardless of
     * whether they have been loaded. This allows querying package
     * capabilities (like resolve_class_name) without triggering loading.
     *
     * @since 0.1.0
     *
     * @return array<int, PackageInterface> Array of all registered package instances.
     */
    public static function get_all_packages(): array
    {
        return array_values(self::$packages);
    }

    /**
     * Get loaded package names.
     *
     * @since 0.1.0
     *
     * @return array<int, string> Array of loaded package names.
     */
    public static function get_loaded_package_names(): array
    {
        return self::$loaded_packages;
    }

    /**
     * Check if a package is loaded.
     *
     * @since 0.1.0
     *
     * @param string $name The package name.
     * @return bool True if loaded, false otherwise.
     */
    public static function is_package_loaded(string $name): bool
    {
        return in_array($name, self::$loaded_packages, true);
    }

    /**
     * Get a package instance by name.
     *
     * @since 0.1.0
     *
     * @param string $name The package name.
     * @return PackageInterface|null The package instance or null if not registered.
     */
    public static function get_package(string $name): ?PackageInterface
    {
        return self::$packages[ $name ] ?? null;
    }

    /**
     * Build aggregated context from all loaded packages.
     *
     * Creates an ExecutionContext and registers all lazy providers from loaded packages.
     *
     * @since 0.1.0
     *
     * @return \MilliCache\Deps\MilliRules\Context ExecutionContext with all providers registered.
     */
    public static function build_context(): \MilliCache\Deps\MilliRules\Context
    {
        $context = new \MilliCache\Deps\MilliRules\Context();

        foreach (self::get_loaded_packages() as $package) {
            // Use the new register_providers method from Package
            if (method_exists($package, 'register_context_providers')) {
                $package->register_context_providers($context);
            }
        }

        return $context;
    }

    /**
     * Get placeholder resolver from prioritized package.
     *
     * Prioritizes packages in order: WP > PHP
     * Returns resolver from first available package that provides one.
     *
     * @since 0.1.0
     *
     * @param \MilliCache\Deps\MilliRules\Context $context The execution context.
     * @return object|null PlaceholderResolver instance or null.
     */
    public static function get_placeholder_resolver(\MilliCache\Deps\MilliRules\Context $context)
    {
        $priority_order = array( 'WP', 'PHP' );

        // Try priority packages first.
        foreach ($priority_order as $name) {
            if (self::is_package_loaded($name)) {
                $package  = self::get_package($name);
                $resolver = $package ? $package->get_placeholder_resolver($context) : null;

                if ($resolver) {
                    return $resolver;
                }
            }
        }

        // Try any loaded package.
        foreach (self::get_loaded_packages() as $package) {
            $resolver = $package->get_placeholder_resolver($context);
            if ($resolver) {
                return $resolver;
            }
        }

        return null;
    }

    /**
     * Map a class name to its package name using longest-match algorithm with caching.
     *
     * Searches namespace registry to find which package provides the namespace
     * for the given class. Uses longest-match algorithm to handle overlapping
     * namespaces correctly.
     *
     * Performance:
     * - Results are cached to avoid repeated sorting and string operations
     * - First lookup performs full algorithm, subsequent lookups use cache
     * - Cache is cleared when packages are registered
     *
     * Algorithm:
     * 1. Check cache for previous result
     * 2. If not cached, sort registered namespaces by length (descending)
     * 3. Find first namespace that matches class name
     * 4. Cache and return associated package name
     *
     * This ensures that when multiple namespaces match, the most specific
     * (longest) namespace is used.
     *
     * Example:
     * - Registered: 'MilliCache\Deps\MilliRules\PHP\' → 'PHP'
     * - Registered: 'MilliCache\Deps\MilliRules\PHP\Extended\' → 'PHPExtended'
     * - Class: 'MilliCache\Deps\MilliRules\PHP\Extended\CustomCondition'
     * - Result: 'PHPExtended' (longest match)
     *
     * @since 0.1.0
     *
     * @param string $class_name Fully-qualified class name.
     * @return string|null Package name or null if not found.
     */
    public static function map_namespace_to_package(string $class_name): ?string
    {
        // Check cache first.
        if (isset(self::$namespace_cache[ $class_name ])) {
            return self::$namespace_cache[ $class_name ];
        }

        // Get all namespaces as keys.
        $namespaces = array_keys(self::$namespace_registry);

        // Sort by length descending (longest first).
        usort(
            $namespaces,
            function ($a, $b) {
                return strlen($b) - strlen($a);
            }
        );

        // Find first (longest) matching namespace.
        foreach ($namespaces as $namespace) {
            if (strpos($class_name, $namespace) === 0) {
                $result = self::$namespace_registry[ $namespace ];
                // Cache the result.
                self::$namespace_cache[ $class_name ] = $result;
                return $result;
            }
        }

        // Cache null result to avoid repeated failed lookups.
        self::$namespace_cache[ $class_name ] = null;
        return null;
    }

    /**
     * Check if any packages are registered.
     *
     * @since 0.1.0
     *
     * @return bool True if at least one package is registered.
     */
    public static function has_packages(): bool
    {
        return ! empty(self::$packages);
    }

    /**
     * Register a rule by delegating to appropriate packages.
     *
     * Extracts package names from metadata['required_packages'] and
     * delegates to those packages via their register_rule() method.
     *
     * If a rule requires packages that are not yet loaded, the rule is
     * added to a pending queue and will be registered automatically when
     * the packages are loaded.
     *
     * @since 0.1.0
     *
     * @param array<string, mixed> $rule     The rule configuration.
     * @param array<string, mixed> $metadata Additional metadata including 'required_packages'.
     * @return void
     */
    public static function register_rule(array $rule, array $metadata): void
    {
        $required_packages = $metadata['required_packages'] ?? array();
        $rule_id           = $rule['id'] ?? 'unknown';

        // Check if all required packages are loaded.
        $all_packages_loaded = true;
        foreach ($required_packages as $package_name) {
            if (! self::is_package_loaded($package_name)) {
                $all_packages_loaded = false;
                break;
            }
        }

        // If not all packages are loaded, defer registration.
        if (! $all_packages_loaded) {
            self::$pending_rules[] = array(
                'rule'              => $rule,
                'metadata'          => $metadata,
                'required_packages' => $required_packages,
            );
            return;
        }

        // All packages are loaded - register immediately.
        foreach ($required_packages as $package_name) {
            if (isset(self::$packages[ $package_name ])) {
                self::$packages[ $package_name ]->register_rule($rule, $metadata);
            } else {
                Logger::warning("Cannot register rule '{$rule_id}' - package '{$package_name}' not registered");
            }
        }
    }

    /**
     * Process pending rules and register those whose packages are now loaded.
     *
     * Iterates through the pending rules queue and attempts to register each rule
     * whose required packages are all loaded. Successfully registered rules are
     * removed from the queue.
     *
     * This method is called automatically after packages are loaded, but can also
     * be called manually if needed.
     *
     * @since 0.1.0
     *
     * @return int Number of rules successfully registered.
     */
    public static function register_pending_rules(): int
    {
        $registered_count = 0;
        $remaining_rules  = array();

        foreach (self::$pending_rules as $pending) {
            $rule_id    = $pending['rule']['id'] ?? 'unknown';
            $all_loaded = true;

            // Check if all required packages are now loaded.
            foreach ($pending['required_packages'] as $package_name) {
                if (! self::is_package_loaded($package_name)) {
                    $all_loaded = false;
                    break;
                }
            }

            if ($all_loaded) {
                // All packages loaded - register now.
                foreach ($pending['required_packages'] as $package_name) {
                    if (isset(self::$packages[ $package_name ])) {
                        self::$packages[ $package_name ]->register_rule($pending['rule'], $pending['metadata']);
                    } else {
                        Logger::warning("Cannot register rule '{$rule_id}' - package '{$package_name}' not registered");
                    }
                }
                $registered_count++;
            } else {
                // Keep in queue for next time.
                $remaining_rules[] = $pending;
            }
        }

        self::$pending_rules = $remaining_rules;
        return $registered_count;
    }

    /**
     * Get pending rules waiting for packages to load.
     *
     * Returns the queue of rules that have been registered but are waiting
     * for their required packages to be loaded. Useful for debugging and
     * monitoring the rule registration process.
     *
     * @since 0.1.0
     *
     * @return array<int, array{rule: array<string, mixed>, metadata: array<string, mixed>, required_packages: array<int, string>}> Array of pending rule data.
     */
    public static function get_pending_rules(): array
    {
        return self::$pending_rules;
    }

    /**
     * Clear all registered rules from packages and reset loaded state.
     *
     * This method:
     * - Calls clear() on all registered packages to remove their rules
     * - Clears the loaded packages list
     * - Clears the pending rules queue
     * - Preserves package registrations ($packages array)
     * - Preserves namespace mappings ($namespace_registry)
     *
     * This allows rules to be cleared and re-registered without re-registering
     * packages or losing namespace mappings.
     *
     * Useful for testing scenarios where you want to reset rules between test cases
     * while keeping the package system initialized.
     *
     * Use reset() instead if you need a complete fresh start including package
     * de-registration.
     *
     * @since 0.1.0
     *
     * @return void
     */
    public static function clear(): void
    {
        // Clear all rules from registered packages.
        foreach (self::$packages as $package) {
            if (method_exists($package, 'clear')) {
                $package->clear();
            }
        }

        // Clear loaded packages list (packages remain registered).
        self::$loaded_packages = array();

        // Clear pending rules queue.
        self::$pending_rules = array();

        // Note: We keep $packages and $namespace_registry intact.
        // This allows packages to be re-loaded without re-registration.
    }

    /**
     * Perform complete reset of PackageManager state.
     *
     * This method clears EVERYTHING:
     * - All registered rules (via clear() on packages)
     * - All loaded packages
     * - All registered packages
     * - All namespace mappings
     * - Namespace lookup cache
     * - Pending rules queue
     *
     * After calling reset(), the PackageManager is in a completely fresh state
     * as if it was never initialized. You'll need to register and load packages again.
     *
     * Difference from clear():
     * - clear(): Keeps packages registered, only clears rules and loaded state
     * - reset(): Removes everything, complete fresh start
     *
     * Use this for tests that need complete isolation or when you want to
     * reinitialize the entire package system from scratch.
     *
     * @since 0.1.0
     *
     * @return void
     */
    public static function reset(): void
    {
        // First clear all rules from packages.
        foreach (self::$packages as $package) {
            if (method_exists($package, 'clear')) {
                $package->clear();
            }
        }

        // Now clear all static arrays.
        self::$packages           = array();
        self::$loaded_packages    = array();
        self::$namespace_registry = array();
        self::$namespace_cache    = array();
        self::$pending_rules      = array();
    }

    /**
     * Check if PackageManager has been initialized with packages.
     *
     * Returns true if at least one package has been registered.
     * Useful for debugging and conditional initialization logic.
     *
     * @since 0.1.0
     *
     * @return bool True if packages are registered, false otherwise.
     */
    public static function is_initialized(): bool
    {
        return ! empty(self::$packages);
    }
}
