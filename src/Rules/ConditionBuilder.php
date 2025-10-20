<?php
/**
 * Condition Builder
 *
 * Fluent API for building rule conditions.
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Rules;

/**
 * Condition Builder
 *
 * Fluent API for building rule conditions.
 *
 * @package MilliCache
 * @subpackage Rules
 * @since 1.0.0
 *
 * Condition Methods:
 * @method self request_url($value, string $operator = 'LIKE') Check request URL
 * @method self request_header(string $name, $value = null, ?string $operator = null) Check request header
 * @method self request_method($value, string $operator = '=') Check request method
 * @method self request_param(string $name, $value = null, ?string $operator = null) Check request parameter
 * @method self cookie(string $name, $value = null, string $operator = 'LIKE') Check cookie exists or value
 * @method self is_user_logged_in(bool $value = true, string $operator = 'IS') Check if user is logged in
 * @method self is_singular($value = true, string $operator = 'IS') Check if singular page
 * @method self is_home($value = true, string $operator = 'IS') Check if home page
 * @method self is_archive($value = true, string $operator = 'IS') Check if archive page
 * @method self post_type($value, string $operator = '=') Check post type
 * @method self wp_constant(string $name, $value = null, ?string $operator = null) Check WordPress constant
 * @method self callback(callable $callback) Custom condition callback
 *
 * Action-Phase Methods (transitions to ActionBuilder):
 * @method ActionBuilder then(?array $actions = null) Set actions (alias for then_do)
 * @method ActionBuilder then_do(?array $actions = null) Set actions to execute
 *
 * For custom conditions registered via Rules::registerCondition(), use:
 * @see custom() method
 */
class ConditionBuilder {
	/**
	 * Collected conditions.
	 *
	 * @since 1.0.0
	 * @var array<int, array<string, mixed>>
	 */
	private array $conditions = array();

	/**
	 * Match type for conditions (all/any/none).
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $match_type = 'all';

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
	 * @param Rules  $rules      The parent Rules object.
	 * @param string $match_type The match type (all/any/none).
	 */
	public function __construct( Rules $rules, string $match_type = 'all' ) {
		$this->rules      = $rules;
		$this->match_type = $match_type;
	}

	/**
	 * Set match type to 'all'.
	 *
	 * @since 1.0.0
	 *
	 * @return self
	 */
	public function match_all(): self {
		$this->match_type = 'all';
		return $this;
	}

	/**
	 * Set the match type to 'any'.
	 *
	 * @since 1.0.0
	 *
	 * @return self
	 */
	public function match_any(): self {
		$this->match_type = 'any';
		return $this;
	}

	/**
	 * Set the match type to 'none'.
	 *
	 * @since 1.0.0
	 *
	 * @return self
	 */
	public function match_none(): self {
		$this->match_type = 'none';
		return $this;
	}

	/**
	 * Add custom condition (for 3rd-party extensions and dynamically registered conditions).
	 *
	 * Use this method for conditions registered via Rules::registerCondition() by other plugins.
	 *
	 * Example:
	 *   ->custom('is_premium_user')
	 *   ->custom('has_role', ['role' => 'editor'])
	 *   ->custom('my_check', function($context) { return true; })
	 *
	 * @since 1.0.0
	 *
	 * @param string                             $type The condition type identifier.
	 * @param array<string, mixed>|callable|null $arg  The condition configuration or a callable function.
	 *                                                  Callback signature: function(array $context): bool.
	 * @return self
	 */
	public function custom( string $type, $arg = array() ): self {
		// Handle callback passed as the second parameter.
		if ( is_callable( $arg ) ) {
			// Register the callback with the provided type name.
			Rules::register_condition( $type, $arg );

			// Use the provided type with empty config.
			$this->conditions[] = array( 'type' => $type );

			return $this;
		}

		// Ensure $arg is an array before using it.
		if ( ! is_array( $arg ) ) {
			$arg = array();
		}
		$arg['type'] = $type;
		$this->conditions[] = $arg;
		return $this;
	}

	/**
	 * End condition building and return Rules object.
	 *
	 * @since 1.0.0
	 * @deprecated No longer needed - use magic method delegation instead
	 *
	 * @return Rules
	 */
	public function end(): Rules {
		return $this->rules->set_conditions( $this->conditions, $this->match_type );
	}

	/**
	 * Magic method to handle auto-delegation and auto-resolution of condition methods.
	 *
	 * Automatically switches context from ConditionBuilder to Rules when a Rules method is called,
	 * or creates condition configurations from method names.
	 *
	 * @since 1.0.0
	 *
	 * @param string            $method The method name.
	 * @param array<int, mixed> $args The method arguments.
	 * @return mixed
	 * @throws \BadMethodCallException If the method doesn't exist on Rules or as a condition.
	 */
	public function __call( string $method, array $args ): mixed {
		// Check if this is a Rules method (auto-delegation).
		if ( method_exists( $this->rules, $method ) ) {
			// Transfer collected conditions to Rules.
			$this->rules->set_conditions( $this->conditions, $this->match_type );

			// Delegate the call to Rules.
			$callable = array( $this->rules, $method );
			assert( is_callable( $callable ) );
			return call_user_func_array( $callable, $args );
		}

		// Check if this is one of the special builder methods.
		$special_methods = array( 'match_all', 'match_any', 'match_none', 'custom' );
		if ( in_array( $method, $special_methods, true ) && method_exists( $this, $method ) ) {
			return call_user_func_array( array( $this, $method ), $args );
		}

		// Auto-resolve condition type from method name.
		// Convert: isUserLoggedIn() → is_user_logged_in.
		$type = $this->method_to_type( $method );

		// Build condition configuration from arguments.
		$config = $this->build_condition_config( $type, $args );

		// Add to the condition array.
		$this->conditions[] = $config;

		return $this;
	}

	/**
	 * Get collected conditions.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_conditions(): array {
		return $this->conditions;
	}

	/**
	 * Get match type.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_match_type(): string {
		return $this->match_type;
	}

	// ===========================
	// Helper Methods
	// ===========================

	/**
	 * Convert the method name to a condition type.
	 *
	 * Converts camelCase method name to snake_case type.
	 * Example: isUserLoggedIn → is_user_logged_in
	 *
	 * @since 1.0.0
	 *
	 * @param string $method The method name.
	 * @return string The condition type.
	 */
	private function method_to_type( string $method ): string {
		// Convert camelCase to snake_case.
		$replaced = preg_replace( '/(?<!^)[A-Z]/', '_$0', $method );
		return strtolower( is_string( $replaced ) ? $replaced : $method );
	}

	/**
	 * Build condition configuration from method arguments.
	 *
	 * Intelligently maps method arguments to condition config based on argument count and types.
	 *
	 * @since 1.0.0
	 *
	 * @param string            $type The condition type.
	 * @param array<int, mixed> $args The method arguments.
	 * @return array<string, mixed> The condition configuration.
	 */
	private function build_condition_config( string $type, array $args ): array {
		$config = array( 'type' => $type );

		// No arguments - default condition.
		if ( empty( $args ) ) {
			return $config;
		}

		// The first argument is always the value or name parameter.
		$first_arg = $args[0];

		// Special handling for conditions that take a name parameter (header, param, cookie, constant, archive).
		$name_based_types = array( 'request_header', 'request_param', 'cookie', 'wp_constant', 'is_archive' );
		if ( in_array( $type, $name_based_types, true ) ) {
			$config['name'] = $first_arg;
			// The second argument is value.
			if ( isset( $args[1] ) ) {
				$config['value'] = $args[1];
				// Third argument is operator.
				if ( isset( $args[2] ) ) {
					$config['operator'] = $args[2];
				} else {
					// Auto-infer operator if not provided.
					$config['operator'] = $this->infer_operator( $args[1], '=' );
				}
			}
		} else {
			// Standard value-based condition.
			$config['value'] = $first_arg;
			// Second argument is operator.
			if ( isset( $args[1] ) ) {
				$config['operator'] = $args[1];
			} else {
				// Auto-infer operator.
				$config['operator'] = $this->infer_operator( $first_arg, '=' );
			}
		}

		return $config;
	}

	/**
	 * Intelligently infer operator based on value type and pattern.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $value    The value to check.
	 * @param string $operator The explicitly provided operator.
	 * @return string The inferred or provided operator.
	 */
	private function infer_operator( $value, string $operator ): string {
		// If operator explicitly provided, use it.
		if ( '=' !== $operator && 'LIKE' !== $operator ) {
			return $operator;
		}

		// Array value → IN.
		if ( is_array( $value ) ) {
			return 'IN';
		}

		// Boolean value → IS.
		if ( is_bool( $value ) ) {
			return 'IS';
		}

		// Null value → EXISTS.
		if ( null === $value ) {
			return 'EXISTS';
		}

		// String with wildcards → LIKE.
		if ( is_string( $value ) && ( strpos( $value, '*' ) !== false || strpos( $value, '?' ) !== false ) ) {
			return 'LIKE';
		}

		// String with regex pattern → REGEXP.
		if ( is_string( $value ) && preg_match( '/^\/.*\/$/', $value ) ) {
			return 'REGEXP';
		}

		// Default to provided operator.
		return $operator;
	}
}
