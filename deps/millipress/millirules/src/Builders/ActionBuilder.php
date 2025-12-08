<?php

/**
 * Action Builder
 *
 * Fluent API for building rule actions.
 *
 * @package     MilliRules
 * @subpackage  Builders
 * @author      Philipp Wellmer <hello@millirules.com>
 */
namespace MilliCache\Deps\MilliRules\Builders;

use MilliCache\Deps\MilliRules\Rules;
/**
 * Action Builder
 *
 * Fluent API for building rule actions.
 *
 * Provides dynamic method resolution for actions - any method call is converted to an action
 * configuration. Method names are converted from camelCase to snake_case for action types.
 *
 * Example: ->sendEmail('user@example.com', 'high') becomes ['type' => 'send_email', 0 => 'user@example.com', 1 => 'high']
 *
 * Custom Actions:
 * For actions registered via Rules::register_action() by other plugins, use the custom() method.
 *
 * Finalization Methods (delegated to Rules):
 * @method bool register() Register the rule and return success status
 *
 * Auto-delegation:
 * When a Rules method is called (e.g., ->register()), automatically transfers actions to Rules
 * and delegates the method call, allowing seamless chaining from ActionBuilder to Rules.
 *
 * @package \MilliRules
 * @subpackage Builders
 * @since 0.1.0
 *
 * @see custom() For adding custom actions from third-party plugins
 * @see Rules::register_action() For registering custom action callbacks
 */
class ActionBuilder
{
    /**
     * Collected actions.
     *
     * @since 0.1.0
     * @var array<int, array<string, mixed>>
     */
    private array $actions = array();
    /**
     * Registered namespaces to search for actions.
     *
     * @since 0.1.0
     * @var array<int, string>
     */
    private array $namespaces;
    /**
     * Reference to parent Rules object.
     *
     * @since 0.1.0
     * @var Rules
     */
    private Rules $rule_builder;
    /**
     * Constructor.
     *
     * @since 0.1.0
     *
     * @param Rules $rule_builder The parent Rules object.
     */
    public function __construct(Rules $rule_builder)
    {
        $this->rule_builder = $rule_builder;
        // Initialize default namespaces to search for actions.
        $this->namespaces = array('MilliCache\Deps\MilliRules\Actions\\');
    }
    /**
     * Add a namespace to search for actions.
     *
     * @since 0.1.0
     *
     * @param string $namespace The fully-qualified namespace to add.
     * @return self
     */
    public function add_namespace(string $namespace): self
    {
        if (!in_array($namespace, $this->namespaces, true)) {
            $this->namespaces[] = $namespace;
        }
        return $this;
    }
    /**
     * Get registered namespaces.
     *
     * @since 0.1.0
     *
     * @return array<int, string>
     */
    public function get_namespaces(): array
    {
        return $this->namespaces;
    }
    /**
     * Add custom action (for 3rd-party extensions and dynamically registered actions).
     *
     * Use this method for actions registered via Rules::register_action() by other plugins.
     *
     * When passing a callable as the second parameter (inline callback), the callback
     * receives only the Context parameter for a cleaner signature.
     *
     * Examples:
     *   ->custom('send_notification')
     *   ->custom('log_event', ['level' => 'info', 'message' => 'Triggered'])
     *   ->custom('my_action', function(Context $context) {
     *       // Inline action logic - only receives Context
     *       $value = $context->get('some.key');
     *   })
     *
     * @since 0.1.0
     *
     * @param string                                   $type The action type identifier.
     * @param array<string, mixed>|callable(\MilliCache\Deps\MilliRules\Context): void $arg The action configuration array or a callable function.
     *                                                        Inline callback signature: function(\MilliCache\Deps\MilliRules\Context $context): void
     *                                                        Registered callback signature: function(array $args, \MilliCache\Deps\MilliRules\Context $context): void
     * @return self
     */
    public function custom(string $type, $arg = array()): self
    {
        // Handle callback passed as the second parameter.
        if (is_callable($arg)) {
            // Wrap callback to pass only Context (args is redundant for inline callbacks).
            $wrappedCallback = function ($args, $context) use ($arg) {
                // Call original callback with only Context.
                return call_user_func($arg, $context);
            };
            // Register the wrapped callback.
            Rules::register_action($type, $wrappedCallback);
            // Use the provided type with empty config.
            $this->actions[] = array('type' => $type);
            return $this;
        }
        $arg['type'] = $type;
        $this->actions[] = $arg;
        return $this;
    }
    /**
     * Mark the last action as locked.
     *
     * Locked actions prevent subsequent actions of the same type from executing
     * in later rules. The first matching locked action wins.
     *
     * Example:
     *   ->do_cache(false)->lock()  // Lock cache setting
     *
     * @since 0.1.0
     *
     * @return self
     */
    public function lock(): self
    {
        // Get the last action from the stack.
        $last_index = count($this->actions) - 1;
        if ($last_index >= 0) {
            // Add locked flag to the action config.
            $this->actions[$last_index]['_locked'] = true;
        }
        return $this;
    }
    /**
     * End action building and return Rules object.
     *
     * @since 0.1.0
     * @deprecated No longer needed - use magic method delegation instead
     *
     * @return Rules
     */
    public function end(): Rules
    {
        return $this->rule_builder->set_actions($this->actions);
    }
    /**
     * Magic method to handle auto-delegation and auto-resolution of action methods.
     *
     * Automatically switches context from ActionBuilder to Rules when a Rules method is called,
     * or creates action configurations from method names.
     *
     * Method name resolution:
     * - Converts camelCase to snake_case (e.g., sendEmail → send_email)
     * - Arguments are stored as numeric keys (0, 1, 2, etc.) for direct access
     *
     * Auto-delegation:
     * - If method exists on Rules object, transfers actions and delegates the call
     * - Allows seamless chaining like ->customAction()->register()
     *
     * @since 0.1.0
     *
     * @param string            $method The method name.
     * @param array<int, mixed> $args The method arguments.
     * @return self|Rules|mixed Returns self for action methods, Rules for delegated methods, or mixed for other cases.
     * @throws \BadMethodCallException If the method doesn't exist on Rules or as an action.
     */
    public function __call(string $method, array $args)
    {
        // Check if this is a Rules method (auto-delegation).
        if (method_exists($this->rule_builder, $method)) {
            $this->rule_builder->set_actions($this->actions);
            $callable = array($this->rule_builder, $method);
            assert(is_callable($callable));
            return call_user_func_array($callable, $args);
        }
        // Check if this is a special method.
        $special_methods = array('custom', 'add_namespace', 'get_namespaces');
        if (in_array($method, $special_methods, true) && method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $args);
        }
        // Auto-resolve action type and build config inline.
        // Convert camelCase to snake_case.
        $replaced = preg_replace('/(?<!^)[A-Z]/', '_$0', $method);
        $type = strtolower(is_string($replaced) ? $replaced : $method);
        // Merge arguments directly into config.
        // This provides consistent structure with custom() method calls.
        if (count($args) === 1 && is_array($args[0])) {
            // Single array argument: merge as named parameters.
            $config = array_merge(array('type' => $type), $args[0]);
        } else {
            // Multiple arguments or single scalar: store as positional.
            $config = array('type' => $type);
            foreach ($args as $i => $arg) {
                $config[$i] = $arg;
            }
        }
        $this->actions[] = $config;
        return $this;
    }
    /**
     * Get collected actions.
     *
     * @since 0.1.0
     *
     * @return array<int, array<string, mixed>>
     */
    public function get_actions(): array
    {
        return $this->actions;
    }
}