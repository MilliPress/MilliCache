<?php
/**
 * Callback Condition
 *
 * Wrapper class for callback-based conditions registered via Rules::registerCondition().
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Rules\Conditions;

/**
 * Class CallbackCondition
 *
 * Allows developers to use closures or callables as condition logic without
 * creating dedicated condition classes.
 *
 * @since 1.0.0
 */
class Callback implements ConditionInterface {
	/**
	 * The callback function to execute.
	 *
	 * @since 1.0.0
	 * @var callable
	 */
	private $callback;

	/**
	 * The condition type identifier.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $type;

	/**
	 * Full condition configuration.
	 *
	 * @since 1.0.0
	 * @var array<string, mixed>
	 */
	private array $config;

	/**
	 * Execution context.
	 *
	 * @since 1.0.0
	 * @var array<string, mixed>
	 */
	private array $context;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $type The condition type identifier.
	 * @param callable             $callback The callback function to execute.
	 * @param array<string, mixed> $config The condition configuration.
	 * @param array<string, mixed> $context The execution context.
	 */
	public function __construct( string $type, callable $callback, array $config, array $context ) {
		$this->type     = $type;
		$this->callback = $callback;
		$this->config   = $config;
		$this->context  = $context;
	}

	/**
	 * Check if the condition matches by calling the callback.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context (unused, already stored in constructor).
	 * @return bool True if the callback returns true, false otherwise.
	 */
	public function matches( array $context ): bool {
		try {
			// Call the callback with context and config.
			$result = call_user_func( $this->callback, $this->context, $this->config );

			// Ensure boolean return value.
			return (bool) $result;

		} catch ( \Exception $e ) {
			error_log(
				sprintf(
					'MilliCache Rules: Error in callback condition "%s": %s',
					$this->type,
					$e->getMessage()
				)
			);
			return false;
		} catch ( \Throwable $e ) {
			error_log(
				sprintf(
					'MilliCache Rules: Fatal error in callback condition "%s": %s',
					$this->type,
					$e->getMessage()
				)
			);
			return false;
		}
	}

	/**
	 * Get the condition type identifier.
	 *
	 * @since 1.0.0
	 *
	 * @return string The condition type.
	 */
	public function get_type(): string {
		return $this->type;
	}
}
