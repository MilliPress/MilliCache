<?php

/**
 * Rules API
 *
 * Main API class with builder pattern and rule management.
 * Provides a fluent interface for creating and registering rules.
 *
 * @package     MilliCache\Deps\MilliRules
 * @author      Philipp Wellmer <hello@millipress.com>
 * @since 0.1.0
 */

namespace MilliCache\Deps\MilliRules;

use MilliCache\Deps\MilliRules\Logger;
use MilliCache\Deps\MilliRules\Builders\ConditionBuilder;
use MilliCache\Deps\MilliRules\Builders\ActionBuilder;
use MilliCache\Deps\MilliRules\Packages\PackageManager;

/**
 * Class Rules
 *
 * Main public API for MilliRules.
 *
 * Provides a fluent interface for creating and managing rules, plus static methods
 * for all registration operations. This is the single entry point for the MilliCache\Deps\MilliRules API.
 *
 * Registration Methods:
 * - Rules::register_condition()    - Register custom condition callbacks
 * - Rules::register_action()       - Register custom action callbacks
 * - Rules::register_namespace()    - Register namespaces for condition/action resolution
 * - Rules::register_placeholder()  - Register custom placeholder resolvers
 *
 * Utility Methods:
 * - Rules::compare_values()        - Compare values using operators (=, !=, >, LIKE, etc.)
 *
 * Builder Methods:
 * - Rules::create()                - Create a new rule builder instance
 *
 * Features:
 * - Fluent API for building rules
 * - Auto-detection of required packages
 * - Auto-detection of rule types (php vs wp)
 * - Intelligent hook registration
 * - Immediate action execution when rules match
 *
 * Execution Model:
 * - Rules execute in priority order (set via ->order())
 * - Actions execute immediately when their rule matches
 * - WordPress hooks (set via ->on()) control when rules execute
 *
 * @package     MilliCache\Deps\MilliRules
 * @author      Philipp Wellmer <hello@millipress.com>
 * @since 0.1.0
 */
class Rules
{
    /**
     * Custom condition callbacks registry.
     *
     * @since 0.1.0
     * @var array<string, callable>
     */
    private static array $custom_conditions = array();

    /**
     * Custom action callbacks registry.
     *
     * @since 0.1.0
     * @var array<string, callable>
     */
    private static array $custom_actions = array();

    /**
     * Hard-coded namespace to package mapping.
     *
     * Used as fallback when PackageManager is not available or doesn't have mapping.
     * Maps namespace prefixes to package names for dependency detection.
     *
     * @since 0.1.0
     * @var array<string, string>
     */
    private static array $namespace_package_map = array(
        'MilliCache\Deps\MilliRules\\Packages\\PHP\\'        => 'PHP',
        'MilliCache\Deps\MilliRules\\Packages\\WordPress\\'  => 'WP',
        'MilliCache\Deps\MilliRules\\Conditions\\'           => 'Core',
        'MilliCache\Deps\MilliRules\\Actions\\'              => 'Core',
    );

    /**
     * Rule configuration being built.
     *
     * @since 0.1.0
     * @var array<string, mixed>
     */
    private array $rule = array();

    /**
     * Explicitly set type from create() method.
     *
     * Stored to bypass auto-detection when user provides explicit type.
     *
     * @since 0.1.0
     * @var string|null
     */
    private ?string $explicit_type = null;

    /**
     * The hook on which this rule should execute.
     *
     * @since 0.1.0
     * @var string
     */
    private string $hook = 'wp';

    /**
     * The priority for the hook.
     *
     * @since 0.1.0
     * @var int
     */
    private int $hook_priority = 10;

    /**
     * Create a new rule builder.
     *
     * Rule Order System:
     *
     * Lower number = Executes FIRST (0, 1, 2, 3...)
     * Higher number = Executes LAST (...997, 998, 999)
     *
     * When multiple rules modify the same value (e.g., TTL),
     * the LAST executed rule (highest order) wins.
     *
     * Order Ranges:
     * - 0:      Core default rules (overridable)
     * - 10:     Normal user rules (DEFAULT)
     * - 20-50:  Later user rules
     * - 100+:   Final overrides
     *
     * Hook Priority (separate concept via ->on()):
     * - Controls WHEN in WordPress lifecycle
     * - Lower number = Earlier execution
     * - Default: 10
     *
     * Type Auto-Detection:
     * - If $type is null (default), rule type will be auto-detected during register()
     * - Auto-detection based on used conditions/actions and hooks
     * - 'php' for early execution (before WordPress), 'wp' for WordPress context
     *
     * @since 0.1.0
     *
     * @param string      $id   The rule ID.
     * @param string|null $type Optional. The rule type: 'php' or 'wp'. If null, auto-detected. Default: null.
     * @return self The rule builder instance.
     */
    public static function create(string $id, ?string $type = null): self
    {
        $instance = new self();
        $instance->rule = array(
            'id'         => $id,
            'title'      => '',
            'order'      => 10,
            'enabled'    => true,
            'match_type' => 'all',
            'conditions' => array(),
            'actions'    => array(),
            '_metadata'  => array(),
        );

        // Store explicit type if provided (bypasses auto-detection).
        if (null !== $type) {
            $instance->explicit_type = $type;
        }

        return $instance;
    }

    // ===========================
    // Callback Registration API
    // ===========================

    /**
     * Register a custom condition callback.
     *
     * @since 0.1.0
     *
     * @param string   $type     The condition type identifier.
     * @param callable(array<string, mixed>, Context): bool $callback The callback function that receives args array and Context.
     *                                                  Signature: function(array $args, Context $context): bool
     * @return void
     * @throws \InvalidArgumentException If callback is not callable.
     */
    public static function register_condition(string $type, callable $callback): void
    {
        if (! is_callable($callback)) {
            throw new \InvalidArgumentException("Callback for condition type '{$type}' is not callable"); // phpcs:ignore WordPress.Security.EscapeOutput
        }

        self::$custom_conditions[ $type ] = $callback;
    }

    /**
     * Register a custom action callback.
     *
     * @since 0.1.0
     *
     * @param string   $type     The action type identifier.
     * @param callable(array<string, mixed>, Context): void $callback The callback function that receives args array and Context.
     *                                                  Signature: function(array $args, Context $context): void
     * @return void
     * @throws \InvalidArgumentException If callback is not callable.
     */
    public static function register_action(string $type, callable $callback): void
    {
        if (! is_callable($callback)) {
            throw new \InvalidArgumentException("Callback for action type '{$type}' is not callable"); // phpcs:ignore WordPress.Security.EscapeOutput
        }

        self::$custom_actions[ $type ] = $callback;
    }

    /**
     * Register a namespace for condition/action resolution.
     *
     * Allows packages and plugins to register custom namespaces for the RuleEngine
     * to search when resolving condition and action types.
     *
     * When a package name is provided, the namespace is also mapped to that package
     * for automatic package detection. This ensures rules using conditions/actions
     * from this namespace are correctly associated with the specified package.
     *
     * @since 0.1.0
     * @since 0.1.0
     *
     * @param string      $type      The type: 'Conditions' or 'Actions'.
     * @param string      $namespace The namespace to search (e.g., 'MyPlugin\\Conditions').
     * @param string|null $package   Optional package name this namespace belongs to (e.g., 'WP', 'PHP').
     * @return void
     */
    public static function register_namespace(string $type, string $namespace, ?string $package = null): void
    {
        RuleEngine::register_namespace($type, $namespace);

        // Register package mapping for this namespace if specified.
        if (null !== $package) {
            // Ensure namespace ends with backslash for consistent matching.
            $namespace_key = rtrim($namespace, '\\') . '\\';
            self::$namespace_package_map[ $namespace_key ] = $package;
        }
    }

    /**
     * Register a custom placeholder resolver.
     *
     * Allows registering custom placeholder resolvers for use in rule values.
     * Placeholders are resolved at execution time and can reference dynamic values
     * from the execution context.
     *
     * Example:
     * Rules::register_placeholder('custom', function($context, $parts) {
     *     return $context['custom'][$parts[0]] ?? '';
     * });
     *
     * Usage: ['value' => '{custom:key}']
     *
     * @since 0.1.0
     *
     * @param string   $placeholder The placeholder name (e.g., 'custom' for {custom:value}).
     * @param callable(Context, array<int, string>): mixed $resolver The resolver callback that receives Context and parts array.
     *                                                   Signature: function(Context $context, array $parts): mixed
     * @return void
     */
    public static function register_placeholder(string $placeholder, callable $resolver): void
    {
        PlaceholderResolver::register_placeholder($placeholder, $resolver);
    }

    /**
     * Check if a custom condition is registered.
     *
     * @since 0.1.0
     *
     * @param string $type The condition type.
     * @return bool True if registered, false otherwise.
     */
    public static function has_custom_condition(string $type): bool
    {
        return isset(self::$custom_conditions[ $type ]);
    }

    /**
     * Check if a custom action is registered.
     *
     * @since 0.1.0
     *
     * @param string $type The action type.
     * @return bool True if registered, false otherwise.
     */
    public static function has_custom_action(string $type): bool
    {
        return isset(self::$custom_actions[ $type ]);
    }

    /**
     * Get a registered custom condition callback.
     *
     * @since 0.1.0
     *
     * @param string $type The condition type.
     * @return callable|null The callback or null if not found.
     */
    public static function get_custom_condition(string $type): ?callable
    {
        return self::$custom_conditions[ $type ] ?? null;
    }

    /**
     * Get a registered custom action callback.
     *
     * @since 0.1.0
     *
     * @param string $type The action type.
     * @return callable|null The callback or null if not found.
     */
    public static function get_custom_action(string $type): ?callable
    {
        return self::$custom_actions[ $type ] ?? null;
    }

    /**
     * Helper method to compare values using WP_Query-style operators.
     *
     * @since 0.1.0
     *
     * @param mixed  $actual   The actual value from context.
     * @param mixed  $expected The expected value from config.
     * @param string $operator The comparison operator.
     * @return bool True if comparison matches, false otherwise.
     */
    public static function compare_values($actual, $expected, string $operator = '='): bool
    {
        return Conditions\BaseCondition::compare_values($actual, $expected, $operator);
    }

    // ===========================
    // Rule Builder API
    // ===========================

    /**
     * Set the hook on which this rule should execute.
     *
     * Note: Using on() automatically sets the rule type to 'wp' as hooks are WordPress-specific.
     * If you explicitly set type='php' and then call on(), the type will be overridden to 'wp'
     * with a warning logged.
     *
     * @since 0.1.0
     * @since 0.1.0
     *
     * @param string $hook     The WordPress hook name (default: 'wp').
     * @param int    $priority The hook priority (default: 10).
     * @return self
     */
    public function on(string $hook = 'wp', int $priority = 10): self
    {
        $this->hook = $hook;
        $this->hook_priority = $priority;
        return $this;
    }

    /**
     * Set the rule title.
     *
     * @since 0.1.0
     *
     * @param string $title The rule title.
     * @return self
     */
    public function title(string $title): self
    {
        $this->rule['title'] = $title;
        return $this;
    }

    /**
     * Set the rule order (execution sequence within a hook).
     *
     * Order determines the sequence rules execute:
     * - Lower number = Executes FIRST (0 is first)
     * - Higher number = Executes LAST (999 is last)
     *
     * When rules modify the same values (e.g., TTL), the LAST-executed rule wins.
     *
     * @since 0.1.0
     *
     * @param int $order The rule order (0-999). Default: 10.
     * @return self
     */
    public function order(int $order): self
    {
        $this->rule['order'] = $order;
        return $this;
    }

    /**
     * Set the rule-enabled state.
     *
     * @since 0.1.0
     *
     * @param bool $enabled Whether the rule is enabled.
     * @return self
     */
    public function enabled(bool $enabled = true): self
    {
        $this->rule['enabled'] = $enabled;
        return $this;
    }

    /**
     * Set conditions with the 'all' match type.
     *
     * @since 0.1.0
     *
     * @param array<int, array<string, mixed>>|null $conditions AArray of condition configurations (null for builder).
     * @return ($conditions is null ? ConditionBuilder : self)
     */
    public function when(?array $conditions = null)
    {
        return $this->when_all($conditions);
    }

    /**
     * Set conditions with the 'all' match type.
     *
     * @since 0.1.0
     *
     * @param array<int, array<string, mixed>>|null $conditions Array of condition configurations (null for builder).
     * @return ($conditions is null ? ConditionBuilder : self)
     */
    public function when_all(?array $conditions = null)
    {
        if (null === $conditions) {
            return new ConditionBuilder($this, 'all');
        }

        $this->rule['match_type'] = 'all';
        $this->rule['conditions'] = $conditions;
        return $this;
    }

    /**
     * Set conditions with 'any' match type.
     *
     * @since 0.1.0
     *
     * @param array<int, array<string, mixed>>|null $conditions Array of condition configurations (null for builder).
     * @return ($conditions is null ? ConditionBuilder : self)
     */
    public function when_any(?array $conditions = null)
    {
        if (null === $conditions) {
            return new ConditionBuilder($this, 'any');
        }

        $this->rule['match_type'] = 'any';
        $this->rule['conditions'] = $conditions;
        return $this;
    }

    /**
     * Set conditions with the 'none' match type.
     *
     * @since 0.1.0
     *
     * @param array<int, array<string, mixed>>|null $conditions Array of condition configurations (null for builder).
     * @return ($conditions is null ? ConditionBuilder : self)
     */
    public function when_none(?array $conditions = null)
    {
        if (null === $conditions) {
            return new ConditionBuilder($this, 'none');
        }

        $this->rule['match_type'] = 'none';
        $this->rule['conditions'] = $conditions;
        return $this;
    }

    /**
     * Set actions to execute.
     *
     * @since 0.1.0
     *
     * @param array<int, array<string, mixed>>|null $actions Array of action configurations (null for builder).
     * @return ($actions is null ? ActionBuilder : self)
     */
    public function then(?array $actions = null)
    {
        if (null === $actions) {
            return new ActionBuilder($this);
        }

        $this->rule['actions'] = $actions;
        return $this;
    }

    /**
     * Set conditions directly (used by Builder).
     *
     * @since 0.1.0
     *
     * @param array<int, array<string, mixed>> $conditions The condition array.
     * @param string                           $match_type The match type (all/any/none).
     * @return self
     */
    public function set_conditions(array $conditions, string $match_type = 'all'): self
    {
        $this->rule['match_type'] = $match_type;
        $this->rule['conditions'] = $conditions;
        return $this;
    }

    /**
     * Set actions directly (used by Builder).
     *
     * @since 0.1.0
     *
     * @param array<int, array<string, mixed>> $actions The action array.
     * @return self
     */
    public function set_actions(array $actions): self
    {
        $this->rule['actions'] = $actions;
        return $this;
    }

    /**
     * Register the rule.
     *
     * Performs auto-detection of required packages and rule type,
     * then registers the rule with PackageManager.
     *
     * Auto-detection includes:
     * - Required packages based on conditions/actions used
     * - Rule type ('php' or 'wp') based on packages and hooks
     *
     * @since 0.1.0
     * @since 0.1.0
     *
     * @return bool True if registration successful, false otherwise.
     */
    public function register(): bool
    {
        if (! self::validate_rule($this->rule)) {
            Logger::error('Failed to validate rule: ' . ( $this->rule['id'] ?? 'unknown' ));
            return false;
        }

        // Auto-detect required packages.
        $required_packages = $this->detect_required_packages();

        // Determine type (explicit or auto-detected).
        if (null !== $this->explicit_type) {
            $type = $this->explicit_type;
        } else {
            $type = $this->detect_type($required_packages);
        }

        // Validate type conflicts and potentially override type.
        $type = $this->validate_type_conflicts($type, $required_packages);

        // Auto-add WP package if type is 'wp' and not already included.
        // This handles cases where hooks are used (->on()) but no WP conditions/actions are present.
        if ($type === 'wp' && ! in_array('WP', $required_packages, true)) {
            $required_packages[] = 'WP';
        }

        // Build metadata.
        $metadata = array(
            'required_packages' => $required_packages,
            'type'              => $type,
            'order'             => $this->rule['order'],
            'enabled'           => $this->rule['enabled'],
        );

        // Add hook info only for 'wp' type.
        if ($type === 'wp') {
            $metadata['hook']          = $this->hook;
            $metadata['hook_priority'] = $this->hook_priority;
        }

        // Store metadata in rule.
        $this->rule['_metadata'] = $metadata;

        // Remove order and enabled from top-level (now in metadata).
        unset($this->rule['order']);
        unset($this->rule['enabled']);

        // Validate metadata is properly formed (defensive programming).
        if (! is_array($this->rule['_metadata'])) {
            Logger::error('Failed to create metadata for rule: ' . ( $this->rule['id'] ?? 'unknown' ));
            return false;
        }

        // Register the rule with new signature (rule + metadata).
        PackageManager::register_rule($this->rule, $this->rule['_metadata']);

        return true;
    }

    /**
     * Validate a rule configuration.
     *
     * @since 0.1.0
     *
     * @param array<string, mixed> $rule The rule to validate.
     * @return bool True if valid, false otherwise.
     */
    private static function validate_rule(array $rule): bool
    {
        $required_fields = array( 'id', 'conditions', 'actions' );

        foreach ($required_fields as $field) {
            if (! isset($rule[ $field ])) {
                Logger::error("Missing required field: {$field}");
                return false;
            }
        }

        $conditions = $rule['conditions'] ?? null;
        $actions = $rule['actions'] ?? null;

        if (! is_array($conditions) || ! is_array($actions)) {
            Logger::error('Conditions and actions must be arrays');
            return false;
        }

        return true;
    }

    // ====================================
    // Auto-Detection Helper Methods
    // ====================================

    /**
     * Map a fully-qualified class name to its package name.
     *
     * Tries PackageManager first for dynamically registered packages,
     * then falls back to hard-coded namespace map.
     *
     * @since 0.1.0
     *
     * @param string $class_name Fully-qualified class name.
     * @return string Package name or 'Core' if no match found.
     */
    private function map_class_to_package(string $class_name): string
    {
        // Try PackageManager first if packages are registered.
        if (PackageManager::has_packages()) {
            $package_name = PackageManager::map_namespace_to_package($class_name);
            if (null !== $package_name) {
                return $package_name;
            }
        }

        // Fall back to hard-coded map.
        foreach (self::$namespace_package_map as $namespace => $package_name) {
            if (strpos($class_name, $namespace) === 0) {
                return $package_name;
            }
        }

        // Default to Core if no match.
        return 'Core';
    }

    /**
     * Detect required packages from rule conditions and actions.
     *
     * Analyzes all conditions and actions to determine which packages they belong to.
     * Returns a unique, sorted list of package names (excluding 'Core').
     *
     * @since 0.1.0
     *
     * @return array<int, string> Array of required package names.
     */
    private function detect_required_packages(): array
    {
        $packages = array();

        // Detect packages from conditions.
        $conditions = $this->rule['conditions'] ?? array();
        if (is_array($conditions)) {
            foreach ($conditions as $condition) {
                if (! is_array($condition)) {
                    continue;
                }

                $type = $condition['type'] ?? '';
                if (empty($type) || ! is_string($type)) {
                    continue;
                }

                // Check if custom condition.
                if (self::has_custom_condition($type)) {
                    // Custom conditions are Core.
                    continue;
                }

                // Convert type to class name and map to package.
                $class_name = RuleEngine::type_to_class_name($type, 'Conditions');
                $package    = $this->map_class_to_package($class_name);
                $packages[] = $package;
            }
        }

        // Detect packages from actions.
        $actions = $this->rule['actions'] ?? array();
        if (is_array($actions)) {
            foreach ($actions as $action) {
                if (! is_array($action)) {
                    continue;
                }

                $type = $action['type'] ?? '';
                if (empty($type) || ! is_string($type)) {
                    continue;
                }

                // Check if custom action.
                if (self::has_custom_action($type)) {
                    // Custom actions are Core.
                    continue;
                }

                // Convert type to class name and map to package.
                $class_name = RuleEngine::type_to_class_name($type, 'Actions');
                $package    = $this->map_class_to_package($class_name);
                $packages[] = $package;
            }
        }

        // Remove duplicates and 'Core'.
        $packages = array_unique($packages);
        $packages = array_filter(
            $packages,
            function ($package) {
                return $package !== 'Core';
            }
        );

        // If no packages detected but explicit_type is set, map type to package.
        if (empty($packages) && null !== $this->explicit_type) {
            // Convert type to PascalCase package name dynamically.
            // 'php' => 'PHP', 'wp' => 'WP', 'laravel' => 'Laravel', etc.
            $package_name = strtoupper($this->explicit_type);

            // Verify package is registered before adding.
            if (PackageManager::has_packages()) {
                $package = PackageManager::get_package($package_name);
                if (null !== $package) {
                    $packages[] = $package_name;
                }
            }
        }

        // Sort and re-index.
        sort($packages);
        return array_values($packages);
    }

    /**
     * Detect rule type based on required packages and hook configuration.
     *
     * Detection priority:
     * 1. Explicit hook usage (non-default hook or priority) -> 'wp'
     * 2. WordPress package required -> 'wp'
     * 3. Default -> 'php'
     *
     * Validation rules (see validate_type_conflicts()):
     * - Explicit type='php' with WordPress packages logs warning (may not work)
     * - Explicit type='php' with ->on() usage auto-changes to 'wp' (hook implies WordPress)
     *
     * @since 0.1.0
     *
     * @param array<int, string> $required_packages Array of required package names.
     * @return string Rule type: 'php' or 'wp'.
     */
    private function detect_type(array $required_packages): string
    {
        // Check if explicit hook was set (non-default values).
        if ($this->hook !== 'wp' || $this->hook_priority !== 10) {
            // Explicit hook usage implies WordPress context.
            return 'wp';
        }

        // Check if WordPress package is required.
        if (in_array('WP', $required_packages, true)) {
            return 'wp';
        }

        // Default to PHP (early execution, pure HTTP context).
        // TODO: Add detection for 'laravel', 'symfony', etc. in future.
        return 'php';
    }

    /**
     * Validate type configuration for potential conflicts.
     *
     * Checks for logical inconsistencies between explicit type settings,
     * required packages, and hook configuration. Logs warnings when conflicts
     * are detected and may override the type when necessary.
     *
     * Validation rules:
     * 1. If explicit type='php' but requires WordPress package:
     *    - Logs warning (WordPress conditions/actions require type='wp')
     *    - Respects user intent (no auto-override)
     *    - Rule may not work as expected
     *
     * 2. If explicit type='php' but uses ->on() with non-default hook:
     *    - Logs warning and auto-changes type to 'wp'
     *    - Hook usage definitively requires WordPress context
     *    - Returns 'wp' instead of 'php'
     *
     * @since 0.1.0
     *
     * @param string             $type              The determined rule type.
     * @param array<int, string> $required_packages Array of required package names.
     * @return string The validated (and potentially corrected) rule type.
     */
    private function validate_type_conflicts(string $type, array $required_packages): string
    {
        $rule_id = $this->rule['id'] ?? 'unknown';

        // Validation 1: Explicit type='php' with WordPress packages.
        if ($this->explicit_type === 'php' && in_array('WordPress', $required_packages, true)) {
            Logger::warning(
                "Rule '{$rule_id}' has explicit type='php' but requires WordPress package - " .
                'this may not work as expected. Consider using type=\'wp\' or omitting type parameter for auto-detection.'
            );
            // Respect user intent - no override.
        }

        // Validation 2: Explicit type='php' with ->on() usage.
        if ($this->explicit_type === 'php' && ( $this->hook !== 'wp' || $this->hook_priority !== 10 )) {
            Logger::warning(
                "Rule '{$rule_id}' has explicit type='php' but uses WordPress hook '{$this->hook}' - " .
                'auto-changing type to \'wp\' as hooks require WordPress context.'
            );

            // Override type to 'wp' - hook usage definitively requires WordPress.
            return 'wp';
        }

        return $type;
    }
}
