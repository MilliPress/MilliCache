<?php

/**
 * MilliCache\Deps\MilliRules Main API
 *
 * Main entry point for MilliCache\Deps\MilliRules package system and rule execution.
 * Provides high-level API for package initialization and rule execution.
 *
 * @package     MilliCache\Deps\MilliRules
 * @author      Philipp Wellmer
 * @since 		0.1.0
 */

namespace MilliCache\Deps\MilliRules;

use MilliCache\Deps\MilliRules\Logger;
use MilliCache\Deps\MilliRules\Packages\PackageManager;
use MilliCache\Deps\MilliRules\Packages\PackageInterface;
use MilliCache\Deps\MilliRules\Packages\PHP\Package as PhpPackage;
use MilliCache\Deps\MilliRules\Packages\WordPress\Package as WordPressPackage;

/**
 * Class MilliCache\Deps\MilliRules
 *
 * Static utility class providing high-level API for MilliRules.
 * Cannot be instantiated - all methods are static.
 *
 * Core Methods:
 * - init()            - Initialize and load packages
 * - execute_rules()   - Execute rules with immediate action execution
 * - get_loaded_packages() - Get list of loaded package names
 * - build_context()   - Build aggregated context from packages
 *
 * Execution Model:
 * - All rules execute in order based on priority
 * - Actions execute immediately when their rule matches
 * - No batching, no deferred execution, no stopping
 * - WordPress hooks and rule order control execution flow
 *
 * Return Structure:
 * All execution methods return:
 * array(
 *     'rules_processed'  => int,  // Total rules evaluated
 *     'rules_skipped'    => int,  // Rules skipped (disabled or missing packages)
 *     'rules_matched'    => int,  // Rules where conditions matched
 *     'actions_executed' => int,  // Actions executed
 *     'context'          => array, // Execution context data
 *     'error'            => string // Only present if exception occurred
 * )
 *
 * @since 0.1.0
 */
class MilliRules
{
    /**
     * Private constructor prevents instantiation.
     *
     * @since 0.1.0
     */
    private function __construct()
    {
        // Static utility class - no instantiation allowed.
    }

    /**
     * Initialize MilliCache\Deps\MilliRules by registering and loading packages.
     *
     * This method performs two steps:
     * 1. Package Registration: Makes packages available to the system
     * 2. Package Loading: Loads packages that are available in current environment
     *
     * If $package_names is null, auto-loads all available packages.
     * If $packages is null, registers default packages (PHP, WordPress).
     *
     * Usage examples:
     * - init() - Registers PHP+WordPress, loads all available
     * - init(['PHP']) - Registers PHP+WordPress, loads only PHP
     * - init(['PHP', 'WordPress']) - Registers defaults, loads both
     * - init(null, [new CustomPackage()]) - Registers only custom package, auto-loads
     * - init(['Custom'], [new CustomPackage()]) - Registers and loads custom package
     *
     * @since 0.1.0
     *
     * @param array<int, string>|null           $package_names Array of package names to load,
     *                                                          or null to auto-load all available.
     * @param array<int, PackageInterface>|null $packages      Array of PackageInterface instances to register,
     *                                                          or null for defaults (PHP, WordPress).
     * @return array<int, string> Array of successfully loaded package names.
     */
    public static function init(?array $package_names = null, ?array $packages = null): array
    {
        try {
            // Step 1: Register packages.
            if (null === $packages) {
                // Register default packages (PHP + WordPress).
                $php_package = new PhpPackage();
                $wp_package  = new WordPressPackage();

                PackageManager::register_package($php_package);
                PackageManager::register_package($wp_package);
            } else {
                // Register provided packages.
                foreach ($packages as $package) {
                    // Validate package implements PackageInterface.
                    if (! $package instanceof PackageInterface) {
                        $class_name = is_object($package) ? get_class($package) : gettype($package);
                        Logger::error("Cannot register package - '{$class_name}' does not implement PackageInterface");
                        continue;
                    }

                    PackageManager::register_package($package);
                }
            }

            // Step 2: Load packages.
            $loaded = PackageManager::load_packages($package_names);

            if (empty($loaded)) {
                Logger::warning('No packages were loaded');
            }

            return $loaded;
        } catch (\Exception $e) {
            Logger::error('Error in MilliRules::init(): ' . $e->getMessage());
            return array();
        }
    }

	/**
	 * Load registered packages with dependency resolution.
	 *
	 * This is a convenience wrapper around PackageManager::load_packages().
	 * It assumes packages have already been registered (either via init()
	 * or manually via PackageManager::register_package()).
	 *
	 * Behavior:
	 * - If $package_names is null, auto-detects and loads all available packages.
	 * - If $package_names is provided, attempts to load only those packages.
	 * - Resolves dependencies recursively and skips unavailable or missing packages.
	 *
	 * Usage examples:
	 * - MilliRules::load_packages();              // Load all available registered packages
	 * - MilliRules::load_packages(['PHP']);       // Load only PHP (and its dependencies)
	 * - MilliRules::load_packages(['PHP', 'WP']); // Load PHP + WP (with dependency resolution)
	 *
	 * @since 0.1.1
	 *
	 * @param array<int, string>|null $package_names Optional package names to load or null for all.
	 * @return array<int, string> Array of successfully loaded package names.
	 */
	public static function load_packages(?array $package_names = null): array
	{
		try {
			$loaded = PackageManager::load_packages($package_names);

			if (empty($loaded)) {
				Logger::warning('No packages were loaded via MilliRules::load_packages()');
			}

			return $loaded;
		} catch (\Exception $e) {
			Logger::error('Error in MilliRules::load_packages(): ' . $e->getMessage());
			return array();
		}
	}

    /**
     * Execute rules from loaded packages with validation.
     *
     * This method:
     * 1. Builds context (auto from packages or uses provided)
     * 2. Validates allowed_packages (checks if loaded, logs warnings)
     * 3. Collects rules from loaded packages (optionally filtered by package)
     * 4. Executes rules via RuleEngine
     * 5. Returns execution result
     *
     * Validation:
     * - If allowed_packages contains non-loaded packages, they are skipped with a warning
     * - If no rules are found, logs info message and returns empty result (not an error)
     * - All exceptions are caught and logged, returning error result structure
     *
     * Usage examples:
     * - execute_rules() - Auto context, all packages, all rules
     * - execute_rules(['PHP']) - Auto context, only PHP rules (early execution)
     * - execute_rules(['WordPress']) - Auto context, only WordPress rules
     * - execute_rules(null, $custom_context) - Custom context, all packages
     * - execute_rules(['PHP'], $custom_context) - Custom context, only PHP rules
     *
     * @since 0.1.0
     *
     * @param array<int, string>|null          $allowed_packages Optional package names to filter rules,
     *                                                            or null to execute rules from all loaded packages.
     * @param Context|null            $context          Execution context, or null for auto-build from packages.
     * @return array<string, mixed> Execution result with statistics and context.
     */
    public static function execute_rules(?array $allowed_packages = null, ?Context $context = null): array
    {
        try {
            // Step 1: Build or use context.
            if (null === $context) {
                $context = PackageManager::build_context();
            }

            // Step 2: Validate and filter allowed_packages.
            if (! empty($allowed_packages)) {
                $loaded_package_names = PackageManager::get_loaded_package_names();
                $validated_packages   = array();

                foreach ($allowed_packages as $package_name) {
                    if (! is_string($package_name)) {
                        continue;
                    }

                    if (in_array($package_name, $loaded_package_names, true)) {
                        $validated_packages[] = $package_name;
                    } else {
                        Logger::warning("Package '{$package_name}' specified in allowed_packages but not loaded - skipping");
                    }
                }

                // Use validated packages.
                $allowed_packages = $validated_packages;

                // If all packages were invalid, log warning.
                if (empty($allowed_packages)) {
                    Logger::warning('No valid packages in allowed_packages filter - no rules will execute');
                }
            }

            // Step 3: Collect rules from packages.
            $all_rules = array();
            $packages  = PackageManager::get_loaded_packages();

            // Filter packages if allowed_packages specified.
            if (! empty($allowed_packages)) {
                $packages = array_filter(
                    $packages,
                    function ($package) use ($allowed_packages) {
                        return in_array($package->get_name(), $allowed_packages, true);
                    }
                );
            }

            // Collect rules from each package.
            foreach ($packages as $package) {
                try {
                    $package_rules = $package->get_rules();
                    if (is_array($package_rules)) {
                        $all_rules = array_merge($all_rules, $package_rules);
                    }
                } catch (\Exception $e) {
                    $package_name = $package->get_name();
                    Logger::error("Error collecting rules from package '{$package_name}': " . $e->getMessage());
                }
            }

            // Check if any rules were collected.
            if (empty($all_rules)) {
                Logger::info('No rules found for execution');

                // Return empty result (valid state, not an error).
                return array(
                    'rules_processed'  => 0,
                    'rules_skipped'    => 0,
                    'rules_matched'    => 0,
                    'actions_executed' => 0,
                    'context'          => $context->to_array(),
                );
            }

            // Step 4: Execute via RuleEngine.
            $engine = new RuleEngine();
            return $engine->execute($all_rules, $context, $allowed_packages);
        } catch (\Exception $e) {
            Logger::error('Error in MilliRules::execute_rules(): ' . $e->getMessage());

            // Return error result.
            return array(
                'rules_processed'  => 0,
                'rules_skipped'    => 0,
                'rules_matched'    => 0,
                'actions_executed' => 0,
                'context'          => $context ? $context->to_array() : array(),
                'error'            => $e->getMessage(),
            );
        }
    }

    /**
     * Get loaded package names.
     *
     * Convenience wrapper for PackageManager::get_loaded_package_names().
     *
     * @since 0.1.0
     *
     * @return array<int, string> Array of loaded package names (e.g., ['HTTP', 'WordPress']).
     */
    public static function get_loaded_packages(): array
    {
        return PackageManager::get_loaded_package_names();
    }

    /**
     * Build aggregated context from all loaded packages.
     *
     * Convenience wrapper for PackageManager::build_context().
     * Creates ExecutionContext with all lazy providers registered.
     *
     * @since 0.1.0
     *
     * @return Context Execution context with all providers registered.
     */
    public static function build_context(): Context
    {
        return PackageManager::build_context();
    }
}
