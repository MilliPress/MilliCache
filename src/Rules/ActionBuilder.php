<?php
/**
 * Action Builder
 *
 * Fluent API for building rule actions.
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Rules;

/**
 * Action Builder
 *
 * Fluent API for building rule actions.
 *
 * @package MilliCache
 * @subpackage Rules
 * @since 1.0.0
 *
 * Action Methods:
 * @method self add_flag(string $flag) Add a cache flag to this request for grouped invalidation
 * @method self remove_flag(string $flag) Remove a cache flag from this request
 * @method self do_cache(string $reason = '') Explicitly cache this request
 * @method self do_not_cache(string $reason = '') Prevent caching for this request
 * @method self flush_by_flag(string|array $flags, bool $expire = false) Flush cache entries by flag(s)
 * @method self flush_by_site(int|null $site_id = null, bool $expire = false) Flush entire site cache (current site if null)
 * @method self set_ttl(int $seconds) Set cache TTL in seconds for this request
 * @method self set_grace(int $seconds) Set the grace period in seconds for stale-while-revalidate
 *
 * Finalization:
 * @method Rules register() Register and activate the rule on the specified hook
 *
 * Custom Actions:
 * For actions registered via Rules::registerAction() by other plugins, use the custom() method.
 *
 * @see custom() For adding custom actions from third-party plugins
 */
class ActionBuilder {
	/**
	 * Collected actions.
	 *
	 * @since 1.0.0
	 * @var array<int, array<string, mixed>>
	 */
	private array $actions = array();

	/**
	 * Reference to parent Rules object.
	 *
	 * @since 1.0.0
	 * @var Rules
	 */
	private Rules $rules;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Rules $rules The parent Rules object.
	 */
	public function __construct( Rules $rules ) {
		$this->rules = $rules;
	}

	/**
	 * Add custom action (for 3rd-party extensions and dynamically registered actions).
	 *
	 * Use this method for actions registered via Rules::registerAction() by other plugins.
	 *
	 * Examples:
	 *   ->custom('send_notification')
	 *   ->custom('log_cache_event', ['level' => 'info', 'message' => 'Cached'])
	 *   ->custom('my_action', function($context, $config) {
	 *       // Custom action logic
	 *   })
	 *
	 * @since 1.0.0
	 *
	 * @param string                        $type The action type identifier.
	 * @param array<string, mixed>|callable $arg The action configuration array or a callable function.
	 *                             Callback signature: function(array $context, array $config): void.
	 * @return self
	 */
	public function custom( string $type, $arg = array() ): self {
		// Handle callback passed as the second parameter.
		if ( is_callable( $arg ) ) {
			// Register the callback with the provided type name.
			Rules::register_action( $type, $arg );

			// Use the provided type with empty config.
			$this->actions[] = array( 'type' => $type );

			return $this;
		}

		$arg['type'] = $type;
		$this->actions[] = $arg;
		return $this;
	}

	/**
	 * End action building and return Rules object.
	 *
	 * @since 1.0.0
	 * @deprecated No longer needed - use magic method delegation instead
	 *
	 * @return Rules
	 */
	public function end(): Rules {
		return $this->rules->set_actions( $this->actions );
	}

	/**
	 * Magic method to handle auto-delegation and auto-resolution of action methods.
	 *
	 * Automatically switches context from ActionBuilder to Rules when a Rules method is called,
	 * or creates action configurations from method names.
	 *
	 * @since 1.0.0
	 *
	 * @param string            $method The method name.
	 * @param array<int, mixed> $args The method arguments.
	 * @return mixed
	 * @throws \BadMethodCallException If the method doesn't exist on Rules or as an action.
	 */
	public function __call( string $method, array $args ) {
		// Check if this is a Rules method (auto-delegation).
		if ( method_exists( $this->rules, $method ) ) {
			$this->rules->set_actions( $this->actions );
			$callable = array( $this->rules, $method );
			assert( is_callable( $callable ) );
			return call_user_func_array( $callable, $args );
		}

		// Check if this is the custom method.
		if ( 'custom' === $method && method_exists( $this, $method ) ) {
			return call_user_func_array( array( $this, $method ), $args );
		}

		// Auto-resolve action type and build config inline.
		$replaced = preg_replace( '/(?<!^)[A-Z]/', '_$0', $method );
		$type = strtolower( is_string( $replaced ) ? $replaced : $method );

		$config = array( 'type' => $type );
		if ( ! empty( $args ) ) {
			$config['value'] = $args[0];
			if ( isset( $args[1] ) ) {
				$config['expire'] = $args[1];
			}
		}

		$this->actions[] = $config;
		return $this;
	}

	/**
	 * Get collected actions.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_actions(): array {
		return $this->actions;
	}
}
