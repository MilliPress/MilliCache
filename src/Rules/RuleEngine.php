<?php
/**
 * Rule Engine
 *
 * Executes rules and manages execution context.
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Rules;

use MilliCache\Rules\Conditions\ConditionInterface;
use MilliCache\Rules\Actions\ActionInterface;

/**
 * Class RuleEngine
 *
 * Handles the execution of rules and manages the execution context.
 *
 * @since 1.0.0
 */
class RuleEngine {
	/**
	 * Execution context data.
	 *
	 * @since 1.0.0
	 * @var array<string, mixed>
	 */
	private array $context = array();

	/**
	 * Trigger actions collected during execution.
	 *
	 * @since 1.0.0
	 * @var array<int, array<string, mixed>>
	 */
	private array $trigger_actions = array();

	/**
	 * Execution statistics.
	 *
	 * @since 1.0.0
	 * @var array<string, int>
	 */
	private array $stats = array(
		'rules_processed'   => 0,
		'rules_matched'     => 0,
		'actions_executed'  => 0,
	);

	/**
	 * Whether rule processing was stopped.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	private bool $stopped = false;

	/**
	 * Execute rules.
	 *
	 * All rules now execute after WordPress loads with full context available.
	 * Base rules have been removed for simplification.
	 *
	 * @since 1.0.0
	 *
	 * @param array<int, array<string, mixed>> $rules The rules to execute.
	 * @return array<string, mixed> Execution result with 'stopped', 'trigger_actions', and 'debug' keys.
	 */
	public function execute( array $rules ): array {
		// Build execution context.
		$this->build_context();

		// Execute each rule until stopped.
		foreach ( $rules as $rule ) {
			if ( $this->stopped ) {
				break;
			}

			$this->execute_rule( $rule );
		}

		return array(
			'stopped'          => $this->stopped,
			'trigger_actions'  => $this->trigger_actions,
			'debug'            => array(
				'rules_processed'  => $this->stats['rules_processed'],
				'rules_matched'    => $this->stats['rules_matched'],
				'actions_executed' => $this->stats['actions_executed'],
				'context'          => $this->context,
			),
		);
	}

	/**
	 * Execute a single rule.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $rule The rule to execute.
	 * @return void
	 */
	private function execute_rule( array $rule ): void {
		$this->stats['rules_processed']++;

		// Check if the rule is enabled.
		if ( isset( $rule['enabled'] ) && ! $rule['enabled'] ) {
			return;
		}

		// Check conditions.
		$conditions_value = $rule['conditions'] ?? array();
		$conditions = is_array( $conditions_value ) ? $conditions_value : array();
		$match_type_value = $rule['match_type'] ?? 'all';
		$match_type = is_string( $match_type_value ) ? $match_type_value : 'all';
		$rule_id_value = $rule['id'] ?? 'unknown';
		$rule_id = is_string( $rule_id_value ) ? $rule_id_value : 'unknown';

		if ( ! $this->check_conditions( $conditions, $match_type ) ) {
			return;
		}

		// Rule matched.
		$this->stats['rules_matched']++;

		// Execute actions.
		$actions_value = $rule['actions'] ?? array();
		$actions = is_array( $actions_value ) ? $actions_value : array();
		$this->execute_actions( $actions, $rule_id );
	}

	/**
	 * Check if conditions match.
	 *
	 * @since 1.0.0
	 *
	 * @param array<int, array<string, mixed>> $conditions The conditions to check.
	 * @param string                           $match_type The match type ('all', 'any', 'none').
	 * @return bool True if conditions match, false otherwise.
	 */
	private function check_conditions( array $conditions, string $match_type ): bool {
		if ( empty( $conditions ) ) {
			return true;
		}

		$matches = array();

		foreach ( $conditions as $condition_config ) {
			$condition = $this->create_condition( $condition_config );
			if ( ! $condition ) {
				$matches[] = false;
				continue;
			}

			try {
				$matches[] = $condition->matches( $this->context );
			} catch ( \Exception $e ) {
				error_log( 'MilliCache Rules: Error checking condition: ' . $e->getMessage() );
				$matches[] = false;
			}
		}

		// Apply match type logic.
		switch ( $match_type ) {
			case 'all':
				return ! in_array( false, $matches, true );

			case 'none':
				return ! in_array( true, $matches, true );

			case 'any':
			default:
				return in_array( true, $matches, true );
		}
	}

	/**
	 * Execute actions.
	 *
	 * @since 1.0.0
	 *
	 * @param array<int, array<string, mixed>> $actions The actions to execute.
	 * @param string                           $rule_id The rule ID.
	 * @return void
	 */
	private function execute_actions( array $actions, string $rule_id ): void {
		foreach ( $actions as $action_config ) {
			$action = $this->create_action( $action_config );
			if ( ! $action ) {
				continue;
			}

			// Check if trigger action.
			if ( $action->is_trigger_action() ) {
				// Collect trigger actions for batch execution.
				$this->trigger_actions[] = array(
					'action'  => $action,
					'rule_id' => $rule_id,
				);
			} else {
				// Execute stop action and halt processing.
				try {
					$action->execute( $this->context );
					$this->stats['actions_executed']++;
				} catch ( \Exception $e ) {
					error_log( 'MilliCache Rules: Error executing action: ' . $e->getMessage() );
				}
				$this->stopped = true;
				break;
			}
		}
	}

	/**
	 * Execute trigger actions.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function execute_trigger_actions(): void {
		foreach ( $this->trigger_actions as $trigger ) {
			if ( ! is_array( $trigger ) || ! isset( $trigger['action'] ) ) {
				continue;
			}

			$action = $trigger['action'];
			if ( ! ( $action instanceof ActionInterface ) ) {
				continue;
			}

			try {
				$action->execute( $this->context );
				$this->stats['actions_executed']++;
			} catch ( \Exception $e ) {
				error_log( 'MilliCache Rules: Error executing trigger action: ' . $e->getMessage() );
			}
		}
	}

	/**
	 * Builds full context with server variables and WordPress data.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function build_context(): void {
		// Use the ContextBuilder to create a structured context.
		$this->context = ContextBuilder::build();

		// Apply filter for custom context modifications.
		$this->context = apply_filters( 'millicache_rules_execution_context', $this->context );
	}

	/**
	 * Get a value from the execution context.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The context key.
	 * @return mixed The context value or null if not found.
	 */
	public function get_context( string $key ) {
		return $this->context[ $key ] ?? null;
	}

	/**
	 * Create a condition instance from configuration.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $config The condition configuration.
	 * @return ConditionInterface|null The condition instance or null on error.
	 */
	private function create_condition( array $config ): ?ConditionInterface {
		$type_value = $config['type'] ?? '';
		$type = is_string( $type_value ) ? $type_value : '';

		if ( empty( $type ) ) {
			error_log( 'MilliCache Rules: Condition type not specified' );
			return null;
		}

		// Check for custom callback-based condition first.
		if ( Rules::has_custom_condition( $type ) ) {
			$callback = Rules::get_custom_condition( $type );

			if ( $callback ) {
				try {
					return new Conditions\Callback( $type, $callback, $config, $this->context );
				} catch ( \Exception $e ) {
					error_log( 'MilliCache Rules: Error creating callback condition: ' . $e->getMessage() );
					return null;
				}
			}
		}

		// Auto-resolve class name from type using convention.
		// Convert: is_user_logged_in → IsUserLoggedIn.
		$class_name = $this->type_to_class_name( $type, 'Conditions' );

		// Check if the class exists.
		if ( ! class_exists( $class_name ) ) {
			error_log( 'MilliCache Rules: Unknown condition type: ' . $type );
			return null;
		}

		try {
			$instance = new $class_name( $config, $this->context );
			return $instance instanceof ConditionInterface ? $instance : null;
		} catch ( \Exception $e ) {
			error_log( 'MilliCache Rules: Error creating condition: ' . $e->getMessage() );
			return null;
		}
	}

	/**
	 * Create an action instance from the configuration.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $config The action configuration.
	 * @return ActionInterface|null The action instance or null on error.
	 */
	private function create_action( array $config ): ?ActionInterface {
		$type_value = $config['type'] ?? '';
		$type = is_string( $type_value ) ? $type_value : '';

		if ( empty( $type ) ) {
			error_log( 'MilliCache Rules: Action type not specified' );
			return null;
		}

		// Check for custom callback-based action first.
		if ( Rules::has_custom_action( $type ) ) {
			$callback = Rules::get_custom_action( $type );

			if ( $callback ) {
				try {
					// Custom actions are trigger actions by default unless specified otherwise.
					$is_trigger_value = $config['is_trigger'] ?? true;
					$is_trigger = is_bool( $is_trigger_value ) ? $is_trigger_value : true;
					return new Actions\Callback( $type, $callback, $config, $this->context, $is_trigger );
				} catch ( \Exception $e ) {
					error_log( 'MilliCache Rules: Error creating callback action: ' . $e->getMessage() );
					return null;
				}
			}
		}

		// Auto-resolve class name from type using convention.
		// Convert: add_flag → AddFlag.
		$class_name = $this->type_to_class_name( $type, 'Actions' );

		// Check if the class exists.
		if ( ! class_exists( $class_name ) ) {
			error_log( 'MilliCache Rules: Unknown action type: ' . $type );
			return null;
		}

		try {
			$instance = new $class_name( $config, $this->context );
			return $instance instanceof ActionInterface ? $instance : null;
		} catch ( \Exception $e ) {
			error_log( 'MilliCache Rules: Error creating action: ' . $e->getMessage() );
			return null;
		}
	}

	/**
	 * Convert a type string to a class name using convention.
	 *
	 * Converts snake_case type to PascalCase class name.
	 * Example: is_user_logged_in → MilliCache\Rules\Conditions\IsUserLoggedIn
	 *
	 * @since 1.0.0
	 *
	 * @param string $type      The type string (e.g., 'is_user_logged_in').
	 * @param string $namespace The namespace suffix (Conditions or Actions).
	 * @return string The fully qualified class name.
	 */
	private function type_to_class_name( string $type, string $namespace ): string {
		// Convert snake_case to PascalCase.
		$class_base = str_replace( '_', '', ucwords( $type, '_' ) );

		return 'MilliCache\\Rules\\' . $namespace . '\\' . $class_base;
	}
}
