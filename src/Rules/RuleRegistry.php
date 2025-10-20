<?php
namespace MilliCache\Rules;

/**
 * Central registry for all rules (late-stage).
 *
 * @since 1.1.0
 */
class RuleRegistry {
	/**
	 * Rules grouped by hook.
	 *
	 * @var array<string, array<int, array<string, mixed>>>
	 */
	private static array $rules_by_hook = array();

	/**
	 * Whether hooks have been registered.
	 *
	 * @var array<string, bool>
	 */
	private static array $hooks_registered = array();

	/**
	 * Add a rule to the registry.
	 *
	 * @param array<string, mixed> $rule Rule configuration.
	 * @param string               $hook WordPress hook name.
	 * @param int                  $priority Hook priority.
	 */
	public static function add_rule( array $rule, string $hook, int $priority ): void {
		if ( ! isset( self::$rules_by_hook[ $hook ] ) ) {
			self::$rules_by_hook[ $hook ] = array();
		}

		self::$rules_by_hook[ $hook ][] = $rule;

		// Register hook execution only once per hook.
		if ( ! isset( self::$hooks_registered[ $hook ] ) ) {
			add_action(
				$hook,
				function () use ( $hook ) {
					self::execute_rules_for_hook( $hook );
				},
				$priority,
				0
			);

			self::$hooks_registered[ $hook ] = true;
		}
	}

	/**
	 * Execute all rules for a specific hook.
	 *
	 * @param string $hook Hook name.
	 */
	private static function execute_rules_for_hook( string $hook ): void {
		$rules = self::$rules_by_hook[ $hook ] ?? array();

		if ( empty( $rules ) ) {
			return;
		}

		// Sort by priority (lower number = higher priority).
		usort(
			$rules,
			function ( $a, $b ) {
				return ( $a['priority'] ?? 1500 ) <=> ( $b['priority'] ?? 1500 );
			}
		);

		// Execute all rules in a single engine instance.
		try {
			$engine = new RuleEngine();
			$result = $engine->execute( $rules );

			// Execute trigger actions.
			$engine->execute_trigger_actions();
		} catch ( \Exception $e ) {
			error_log( 'MilliCache Rules: Error executing rules for hook "' . $hook . '": ' . $e->getMessage() );
		}
	}

	/**
	 * Get all registered rules.
	 *
	 * @return array<string, array<int, array<string, mixed>>>
	 */
	public static function get_all_rules(): array {
		return self::$rules_by_hook;
	}

	/**
	 * Clear all registered rules (for testing).
	 */
	public static function clear(): void {
		self::$rules_by_hook = array();
		self::$hooks_registered = array();
	}
}