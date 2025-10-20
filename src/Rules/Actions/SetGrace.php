<?php
/**
 * Set Grace Action
 *
 * Sets the cache grace period.
 *
 * @package MilliCache
 * @subpackage Rules\Actions
 * @since 1.0.0
 */

namespace MilliCache\Rules\Actions;

use MilliCache\Engine;

/**
 * Class SetGraceAction
 *
 * Sets a custom grace period for stale cache serving (trigger action).
 *
 * @since 1.0.0
 */
class SetGrace extends BaseAction {
	/**
	 * Whether this is a trigger action.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected bool $is_trigger = true;

	/**
	 * Get the action type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The action type identifier.
	 */
	public function get_type(): string {
		return 'set_grace';
	}

	/**
	 * Execute the action.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context.
	 * @return void
	 */
	public function execute( array $context ): void {
		$grace = $this->value;

		if ( ! is_numeric( $grace ) || $grace < 0 ) {
			return;
		}

		// Call Engine to set the grace period.
		if ( class_exists( '\\MilliCache\\Engine' ) ) {
			Engine::set_grace( (int) $grace );
		}
	}
}
