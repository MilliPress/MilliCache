<?php
/**
 * Rules API
 *
 * Main API class with builder pattern and rule management.
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Rules;

/**
 * Class Rules
 *
 * Provides a fluent API for creating and managing rules.
 *
 * @since 1.0.0
 */
class Rules {
	/**
	 * Custom condition callbacks registry.
	 *
	 * @since 1.0.0
	 * @var array
	 * @phpstan-var array<string, callable>
	 */
	private static array $custom_conditions = array();

	/**
	 * Custom action callbacks registry.
	 *
	 * @since 1.0.0
	 * @var array<string, callable>
	 */
	private static array $custom_actions = array();

	/**
	 * Rule configuration being built.
	 *
	 * @since 1.0.0
	 * @var array<string, mixed>
	 */
	private array $rule = array();

	/**
	 * The hook on which this rule should execute.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $hook = 'template_redirect';

	/**
	 * The priority for the hook.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	private int $hook_priority = 20;

	/**
	 * Create a new rule builder.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id The rule ID.
	 * @return self The rule builder instance.
	 */
	public static function create( string $id ): self {
		$instance = new self();
		$instance->rule = array(
			'id'         => $id,
			'title'      => '',
			'priority'   => 1500,
			'enabled'    => true,
			'match_type' => 'all',
			'conditions' => array(),
			'actions'    => array(),
		);

		return $instance;
	}

	// ===========================
	// Callback Registration API
	// ===========================

	/**
	 * Register a custom condition callback.
	 *
	 * @since 1.0.0
	 *
	 * @param string   $type     The condition type identifier.
	 * @param callable $callback The callback function.
	 * @return void
	 * @throws \InvalidArgumentException If callback is not callable.
	 */
	public static function register_condition( string $type, callable $callback ): void {
		if ( ! is_callable( $callback ) ) {
			throw new \InvalidArgumentException( "Callback for condition type '{$type}' is not callable" ); // phpcs:ignore WordPress.Security.EscapeOutput
		}

		self::$custom_conditions[ $type ] = $callback;
	}

	/**
	 * Register a custom action callback.
	 *
	 * @since 1.0.0
	 *
	 * @param string   $type     The action type identifier.
	 * @param callable $callback The callback function.
	 * @return void
	 * @throws \InvalidArgumentException If callback is not callable.
	 */
	public static function register_action( string $type, callable $callback ): void {
		if ( ! is_callable( $callback ) ) {
			throw new \InvalidArgumentException( "Callback for action type '{$type}' is not callable" ); // phpcs:ignore WordPress.Security.EscapeOutput
		}

		self::$custom_actions[ $type ] = $callback;
	}

	/**
	 * Check if a custom condition is registered.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The condition type.
	 * @return bool True if registered, false otherwise.
	 */
	public static function has_custom_condition( string $type ): bool {
		return isset( self::$custom_conditions[ $type ] );
	}

	/**
	 * Check if a custom action is registered.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The action type.
	 * @return bool True if registered, false otherwise.
	 */
	public static function has_custom_action( string $type ): bool {
		return isset( self::$custom_actions[ $type ] );
	}

	/**
	 * Get a registered custom condition callback.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The condition type.
	 * @return callable|null The callback or null if not found.
	 */
	public static function get_custom_condition( string $type ): ?callable {
		return self::$custom_conditions[ $type ] ?? null;
	}

	/**
	 * Get a registered custom action callback.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The action type.
	 * @return callable|null The callback or null if not found.
	 */
	public static function get_custom_action( string $type ): ?callable {
		return self::$custom_actions[ $type ] ?? null;
	}

	/**
	 * Helper method to compare values using WP_Query-style operators.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $actual   The actual value from context.
	 * @param mixed  $expected The expected value from config.
	 * @param string $operator The comparison operator.
	 * @return bool True if comparison matches, false otherwise.
	 */
	public static function compare_values( $actual, $expected, string $operator = '=' ): bool {
		return Conditions\BaseCondition::compareValues( $actual, $expected, $operator );
	}

	// ===========================
	// Rule Builder API
	// ===========================

	/**
	 * Set the hook on which this rule should execute.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook     The WordPress hook name (default: 'template_redirect').
	 * @param int    $priority The hook priority (default: 20).
	 * @return self
	 */
	public function on( string $hook = 'template_redirect', int $priority = 20 ): self {
		$this->hook = $hook;
		$this->hook_priority = $priority;
		return $this;
	}

	/**
	 * Set the rule title.
	 *
	 * @since 1.0.0
	 *
	 * @param string $title The rule title.
	 * @return self
	 */
	public function title( string $title ): self {
		$this->rule['title'] = $title;
		return $this;
	}

	/**
	 * Set the rule priority.
	 *
	 * @since 1.0.0
	 *
	 * @param int $priority The rule priority.
	 * @return self
	 */
	public function priority( int $priority ): self {
		$this->rule['priority'] = $priority;
		return $this;
	}

	/**
	 * Set the rule-enabled state.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $enabled Whether the rule is enabled.
	 * @return self
	 */
	public function enabled( bool $enabled = true ): self {
		$this->rule['enabled'] = $enabled;
		return $this;
	}

	/**
	 * Set conditions with the 'all' match type.
	 *
	 * @since 1.0.0
	 *
	 * @param array<int, array<string, mixed>>|null $conditions Array of condition configurations (null for builder).
	 * @return self|ConditionBuilder
	 */
	public function when_all( ?array $conditions = null ) {
		if ( null === $conditions ) {
			return new ConditionBuilder( $this, 'all' );
		}

		$this->rule['match_type'] = 'all';
		$this->rule['conditions'] = $conditions;
		return $this;
	}

	/**
	 * Set conditions with 'any' match type.
	 *
	 * @since 1.0.0
	 *
	 * @param array<int, array<string, mixed>>|null $conditions Array of condition configurations (null for builder).
	 * @return self|ConditionBuilder
	 */
	public function when_any( ?array $conditions = null ) {
		if ( null === $conditions ) {
			return new ConditionBuilder( $this, 'any' );
		}

		$this->rule['match_type'] = 'any';
		$this->rule['conditions'] = $conditions;
		return $this;
	}

	/**
	 * Set conditions with the 'none' match type.
	 *
	 * @since 1.0.0
	 *
	 * @param array<int, array<string, mixed>>|null $conditions Array of condition configurations (null for builder).
	 * @return self|ConditionBuilder
	 */
	public function when_none( ?array $conditions = null ) {
		if ( null === $conditions ) {
			return new ConditionBuilder( $this, 'none' );
		}

		$this->rule['match_type'] = 'none';
		$this->rule['conditions'] = $conditions;
		return $this;
	}

	/**
	 * Set actions to execute.
	 *
	 * @since 1.0.0
	 *
	 * @param array<int, array<string, mixed>>|null $actions Array of action configurations (null for builder).
	 * @return self|ActionBuilder
	 */
	public function then( ?array $actions = null ) {
		if ( null === $actions ) {
			return new ActionBuilder( $this );
		}

		$this->rule['actions'] = $actions;
		return $this;
	}

	/**
	 * Set conditions directly (used by Builder).
	 *
	 * @since 1.0.0
	 *
	 * @param array<int, array<string, mixed>> $conditions The condition array.
	 * @param string                           $match_type The match type (all/any/none).
	 * @return self
	 */
	public function set_conditions( array $conditions, string $match_type = 'all' ): self {
		$this->rule['match_type'] = $match_type;
		$this->rule['conditions'] = $conditions;
		return $this;
	}

	/**
	 * Set actions directly (used by Builder).
	 *
	 * @since 1.0.0
	 *
	 * @param array<int, array<string, mixed>> $actions The action array.
	 * @return self
	 */
	public function set_actions( array $actions ): self {
		$this->rule['actions'] = $actions;
		return $this;
	}

	/**
	 * Register the rule.
	 *
	 * Automatically wraps the rule in add_action() for the specified hook.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if registration successful, false otherwise.
	 */
	public function register(): bool {
		if ( ! self::validate_rule( $this->rule ) ) {
			error_log( 'MilliCache Rules: Failed to validate rule: ' . ( $this->rule['id'] ?? 'unknown' ) );
			return false;
		}

		// Capture the rule config and hook settings.
		$rule_config = $this->rule;
		$hook = $this->hook;
		$priority = $this->hook_priority;

		// Register the rule to execute on the specified hook.
		RuleRegistry::add_rule( $this->rule, $this->hook, $this->hook_priority );

		// Fire action hook for tracking.
		do_action( 'millicache_rules_registered_rule', $rule_config, $hook, $priority );

		return true;
	}

	/**
	 * Validate a rule configuration.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $rule The rule to validate.
	 * @return bool True if valid, false otherwise.
	 */
	private static function validate_rule( array $rule ): bool {
		$required_fields = array( 'id', 'conditions', 'actions' );

		foreach ( $required_fields as $field ) {
			if ( ! isset( $rule[ $field ] ) ) {
				error_log( "MilliCache Rules: Missing required field: {$field}" );
				return false;
			}
		}

		$conditions = $rule['conditions'] ?? null;
		$actions = $rule['actions'] ?? null;

		if ( ! is_array( $conditions ) || ! is_array( $actions ) ) {
			error_log( 'MilliCache Rules: Conditions and actions must be arrays' );
			return false;
		}

		return true;
	}
}
