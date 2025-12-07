<?php

/**
 * WordPress Package
 *
 * Provides WordPress-specific conditions, actions, and context for MilliRules.
 * This package is only available in WordPress environments and extends PHP functionality.
 *
 * Features:
 * - WordPress conditions (post type, user logged in, query conditionals, constants)
 * - WordPress context building (post, user, query, constants data)
 * - WordPress placeholder resolution ({post.id}, {user.login}, etc.)
 * - Hook-based rule execution integrated with WordPress lifecycle
 *
 * This package requires the PHP package as a dependency.
 *
 * @package     MilliRules
 * @subpackage  WordPress
 * @author      Philipp Wellmer
 * @since 		0.1.0
 */

namespace MilliCache\Deps\MilliRules\Packages\WordPress;

use MilliCache\Deps\MilliRules\Logger;
use MilliCache\Deps\MilliRules\Packages\BasePackage;
use MilliCache\Deps\MilliRules\RuleEngine;

/**
 * Class Package
 *
 * WordPress package implementation providing WP-specific conditions and context.
 * Uses hook-based rule execution strategy where rules are grouped by WordPress hook
 * and executed when the corresponding hook fires.
 *
 * @since 0.1.0
 */
class Package extends BasePackage
{
    /**
     * Rules grouped by WordPress hook name.
     *
     * Structure: ['hook_name' => [rule1, rule2, ...]]
     *
     * @since 0.1.0
     * @var array<string, array<int, array<string, mixed>>>
     */
    private array $rules_by_hook = array();

    /**
     * Tracks which hooks have been registered with WordPress.
     *
     * Prevents duplicate hook registrations.
     *
     * @since 0.1.0
     * @var array<string, bool>
     */
    private array $hooks_registered = array();

    /**
     * Hooks pending registration.
     *
     * Stores hooks that need to be registered when WordPress becomes available.
     * Format: ['hook_name' => priority]
     *
     * @since 0.1.0
     * @var array<string, int>
     */
    private array $pending_hooks = array();

    /**
     * Custom callback for hook registration.
     *
     * Allows overriding add_action() for testing or non-WordPress environments.
     * Signature: function(string $hook, callable $function, int $priority): void
     *
     * @since 0.1.0
     * @var callable|null
     */
    private static $hook_callback = null;

    /**
     * Get the unique package identifier.
     *
     * @since 0.1.0
     *
     * @return string The package name 'WP'.
     */
    public function get_name(): string
    {
        return 'WP';
    }

    /**
     * Get the namespaces provided by this package.
     *
     * Returns namespaces for WordPress conditions, actions, and contexts.
     *
     * @since 0.1.0
     *
     * @return array<int, string> Array of namespace strings.
     */
    public function get_namespaces(): array
    {
        return array(
            'MilliCache\Deps\MilliRules\Packages\WordPress\Conditions',
            'MilliCache\Deps\MilliRules\Packages\WordPress\Actions',
            'MilliCache\Deps\MilliRules\Packages\WordPress\Contexts',
        );
    }

    /**
     * Check if this package is available in the current environment.
     *
     * WordPress package is available if WordPress functions exist.
     * Checks for add_action() as a reliable indicator.
     *
     * @since 0.1.0
     *
     * @return bool True if WordPress is available, false otherwise.
     */
    public function is_available(): bool
    {
        return function_exists('add_action');
    }

    /**
     * Get the names of packages required by this package.
     *
     * WordPress package requires PHP package as it extends PHP context
     * with WordPress-specific data.
     *
     * @since 0.1.0
     *
     * @return array<int, string> Array with 'PHP' as the required dependency.
     */
    public function get_required_packages(): array
    {
        return array( 'PHP' );
    }

    /**
     * Get a placeholder resolver instance for this package.
     *
     * Returns PlaceholderResolver configured with the given context.
     * Supports WordPress placeholders like {post.id}, {user.login}, {query.is_singular}
     * plus all HTTP placeholders via inheritance.
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
     * Set a custom callback for hook registration.
     *
     * Allows overriding add_action() behavior for testing or custom environments.
     * The callback should have a signature: function(string $hook, callable $function, int $priority): void
     *
     * Example usage in tests:
     * <code>
     * WordPressPackage::set_hook_callback(function($hook, $callback, $priority) {
     *     // Custom hook registration logic
     * });
     * </code>
     *
     * @since 0.1.0
     *
     * @param callable $callback Function to call instead of add_action().
     * @return void
     */
    public static function set_hook_callback(callable $callback): void
    {
        self::$hook_callback = $callback;
    }

    /**
     * Register a rule with this package using hook-based storage.
     *
     * Groups rules by their WordPress hook for execution when the hook fires.
     * If WordPress is not available yet (add_action() doesn't exist), stores the rule
     * and adds the hook to pending_hooks for later registration.
     *
     * When WordPress becomes available, pending hooks are automatically registered
     * on the next register_rule() call, or can be manually registered via
     * register_pending_hooks().
     *
     * @since 0.1.0
     *
     * @param array<string, mixed> $rule     The rule configuration.
     * @param array<string, mixed> $metadata Additional metadata (order, hooks, etc.).
     * @return void
     */
    public function register_rule(array $rule, array $metadata): void
    {
        // Skip disabled rules - don't register hooks or store them.
        if (isset($metadata['enabled']) && ! $metadata['enabled']) {
            return;
        }

        // Extract hook information from metadata.
        $hook     = $metadata['hook'] ?? 'wp';
        $priority = $metadata['hook_priority'] ?? 10;

        // Ensure metadata is in rule for consistency.
        $rule['_metadata'] = $metadata;

        // Group by hook.
        if (! isset($this->rules_by_hook[ $hook ])) {
            $this->rules_by_hook[ $hook ] = array();
        }
        $this->rules_by_hook[ $hook ][] = $rule;

        // Also add to parent's flat rules array for get_rules() compatibility.
        $this->rules[] = $rule;

        // Check if WordPress is available.
        if (! $this->is_available()) {
            // WordPress not available yet - add to pending hooks.
            if (! isset($this->pending_hooks[ $hook ])) {
                $this->pending_hooks[ $hook ] = $priority;
            }
            return;
        }

        // WordPress is available - register pending hooks if any.
        if (! empty($this->pending_hooks)) {
            $this->register_pending_hooks();
        }

        // Register this hook if not already registered.
        if (! isset($this->hooks_registered[ $hook ])) {
            $this->register_hook($hook, $priority);
            $this->hooks_registered[ $hook ] = true;
        }
    }

    /**
     * Register all pending WordPress hooks.
     *
     * This method is called automatically when WordPress becomes available and there are
     * pending hooks waiting to be registered. It can also be called manually if needed
     * for specific timing requirements.
     *
     * Processes all hooks stored in $pending_hooks, registers them with WordPress,
     * and clears the pending hooks array.
     *
     * Typically this is called automatically on the first register_rule() call after
     * WordPress becomes available, so manual calls are rarely needed.
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function register_pending_hooks(): void
    {
        // Check if WordPress is available.
        if (! $this->is_available()) {
            return;
        }

        // No pending hooks to register.
        if (empty($this->pending_hooks)) {
            return;
        }

        $count = 0;

        // Register all pending hooks.
        foreach ($this->pending_hooks as $hook => $priority) {
            if (! isset($this->hooks_registered[ $hook ])) {
                $this->register_hook($hook, $priority);
                $this->hooks_registered[ $hook ] = true;
                ++$count;
            }
        }

        // Clear pending hooks.
        $this->pending_hooks = array();

        // Log success.
        if ($count > 0) {
            Logger::info("Registered {$count} pending WordPress hooks");
        }
    }

    /**
     * Register a WordPress hook for rule execution.
     *
     * Registers a single callback with WordPress for this hook/priority combination.
     * All rules for the same hook are grouped and execute together when the hook fires.
     *
     * Hook Priority Behavior:
     * - WordPress executes hooks in priority order (lower numbers first)
     * - Multiple rules with the same hook and priority share the same callback
     * - Rule execution order within the same priority is controlled by rule order field
     * - Rule order field determines sequence: lower order = executes first (0-999)
     *
     * Example:
     * - Rule A: hook='init', priority=10, order=5
     * - Rule B: hook='init', priority=10, order=15
     * - Rule C: hook='init', priority=20, order=5
     * Execution: A → B (same priority, ordered by order field) → C (higher priority)
     *
     * If add_action() is not available (WordPress not loaded yet), this method
     * returns early. The hook will be registered later when WordPress is available.
     *
     * @since 0.1.0
     *
     * @param string $hook     The WordPress hook name.
     * @param int    $priority The hook priority.
     * @return void
     */
    private function register_hook(string $hook, int $priority): void
    {
        // Check if WordPress is available.
        if (! function_exists('add_action') && null === self::$hook_callback) {
            // WordPress not available yet - registration will happen later.
            return;
        }

        try {
            // Create closure that calls execute_rules_for_hook.
            // Accepts variadic args from WordPress hooks (e.g., save_post passes $post_id, $post, $update).
            $closure = function (...$args) use ($hook) {
                $this->execute_rules_for_hook($hook, $args);
            };

            // Register hook using custom callback or add_action.
            if (null !== self::$hook_callback) {
                call_user_func(self::$hook_callback, $hook, $closure, $priority);
            } elseif (function_exists('add_action')) {
                // Accept up to 99 arguments from the hook (e.g., save_post passes 3 args).
                add_action($hook, $closure, $priority, 99);
            }
        } catch (\Exception $e) {
            Logger::error("Error registering hook '{$hook}': " . $e->getMessage());
        }
    }

    /**
     * Execute all rules for a specific WordPress hook.
     *
     * Called automatically when the WordPress hook fires.
     * Builds context, sorts rules by order, and executes them via RuleEngine.
     * Hook arguments are added to the context under 'hook' key for access by conditions/actions.
     *
     * @since 0.1.0
     *
     * @param string               $hook      The WordPress hook name.
     * @param array<int, mixed>    $hook_args Optional. Arguments passed by the WordPress hook. Default empty array.
     * @return void
     */
    private function execute_rules_for_hook(string $hook, array $hook_args = array()): void
    {
        // Get rules for this hook.
        $rules = $this->rules_by_hook[ $hook ] ?? array();

        if (empty($rules)) {
            return;
        }

        try {
            // Sort rules by order field.
            $sorted_rules = $this->sort_rules_by_order($rules);

            // Build context using PackageManager.
            $context = \MilliCache\Deps\MilliRules\Packages\PackageManager::build_context();

            // Add hook arguments to context if provided.
            if (! empty($hook_args)) {
                $context->set('hook', array(
                    'name' => $hook,
                    'args' => $hook_args,
                ));
            }

            // Create engine and execute.
            $engine = new RuleEngine();
            $engine->execute($sorted_rules, $context);
        } catch (\Exception $e) {
            Logger::error("Error executing rules for hook '{$hook}': " . $e->getMessage());
        }
    }

    /**
     * Get all rules registered with this package grouped by hook.
     *
     * Returns rules organized by WordPress hook name, preventing execution via
     * manual MilliRules::execute_rules() calls (which expect a flat array).
     * This ensures rules only fire when their specific WordPress hooks are triggered.
     *
     * Note: Intentionally returns grouped array instead of flat array to prevent
     * direct rule execution. Rules must execute via WordPress hooks.
     *
     * @since 0.1.0
     *
     * @return array<string, array<int, array<string, mixed>>> Array of rules grouped by hook name.
     *         Structure: ['hook_name' => [rule1, rule2, ...]]
     *
     * @phpstan-ignore-next-line
     */
    public function get_rules(): array
    {
        return $this->rules_by_hook;
    }

    /**
     * Clear all registered rules from this package.
     *
     * Removes all rules from both flat storage and hook-based storage.
     * Also clears pending hooks that haven't been registered yet.
     *
     * Note: Does not unregister WordPress hooks that are already registered
     * ($hooks_registered remains intact to prevent duplicate hook registrations).
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function clear(): void
    {
        // Clear parent's flat rules array.
        parent::clear();

        // Clear hook-based storage.
        $this->rules_by_hook = array();

        // Clear pending hooks.
        $this->pending_hooks = array();

        // Note: We keep $hooks_registered intact so hooks don't get re-registered.
        // This allows rules to be cleared and re-registered without duplicate hooks.
    }

    /**
     * Resolve WordPress-specific class names with fallback logic.
     *
     * Handles is_* conditionals using IsConditional fallback class.
     *
     * @since 0.1.0
     *
     * @param string $type      The type string (e.g., 'is_user_logged_in').
     * @param string $category  The category: 'Conditions' or 'Actions'.
     * @return string|null Fully-qualified class name or null if not resolved.
     */
    public function resolve_class_name(string $type, string $category): ?string
    {
        if ('Conditions' === $category && 0 === strpos($type, 'is_')) {
            // Check if a specific class exists first
            $class_base = str_replace('_', '', ucwords($type, '_'));
            $specific_class = 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\Conditions\\' . $class_base;

            if (class_exists($specific_class)) {
                return $specific_class;
            }

            // Fallback to IsConditional for generic is_* functions
            return 'MilliCache\\Deps\\MilliRules\\Packages\\WordPress\\Conditions\\IsConditional';
        }

        return null;
    }
}
