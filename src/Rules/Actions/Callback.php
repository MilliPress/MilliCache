<?php
/**
 * Callback Action
 *
 * Wrapper class for callback-based actions registered via Rules::registerAction().
 *
 * @package MilliCache
 * @subpackage Rules\Actions
 * @since 1.0.0
 */

namespace MilliCache\Rules\Actions;

/**
 * Class CallbackAction
 *
 * Allows developers to use closures or callables as action logic without
 * creating dedicated action classes.
 *
 * @since 1.0.0
 */
class Callback implements ActionInterface {
	/**
	 * The callback function to execute.
	 *
	 * @since 1.0.0
	 * @var callable
	 */
	private $callback;

	/**
	 * The action type identifier.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $type;

	/**
	 * Full action configuration.
	 *
	 * @since 1.0.0
	 * @var array<string, mixed>
	 */
	private $config;

	/**
	 * Execution context.
	 *
	 * @since 1.0.0
	 * @var array<string, mixed>
	 */
	private $context;

	/**
	 * Whether this is a trigger action.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	private $is_trigger;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $type The action type identifier.
	 * @param callable             $callback The callback function to execute.
	 * @param array<string, mixed> $config The action configuration.
	 * @param array<string, mixed> $context The execution context.
	 * @param bool                 $is_trigger Whether this is a trigger action (default: true).
	 */
	public function __construct( string $type, callable $callback, array $config, array $context, bool $is_trigger = true ) {
		$this->type       = $type;
		$this->callback   = $callback;
		$this->config     = $config;
		$this->context    = $context;
		$this->is_trigger = $is_trigger;
	}

	/**
	 * Execute the action by calling the callback.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context (unused, already stored in constructor).
	 * @return void
	 */
	public function execute( array $context ): void {
		try {
			// Call the callback with context and config.
			call_user_func( $this->callback, $this->context, $this->config );

		} catch ( \Exception $e ) {
			error_log(
				sprintf(
					'MilliCache Rules: Error in callback action "%s": %s',
					$this->type,
					$e->getMessage()
				)
			);
		} catch ( \Throwable $e ) {
			error_log(
				sprintf(
					'MilliCache Rules: Fatal error in callback action "%s": %s',
					$this->type,
					$e->getMessage()
				)
			);
		}
	}

	/**
	 * Check if this is a trigger action.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if trigger action, false if stop action.
	 */
	public function is_trigger_action(): bool {
		return $this->is_trigger;
	}

	/**
	 * Get the action type identifier.
	 *
	 * @since 1.0.0
	 *
	 * @return string The action type.
	 */
	public function get_type(): string {
		return $this->type;
	}
}
