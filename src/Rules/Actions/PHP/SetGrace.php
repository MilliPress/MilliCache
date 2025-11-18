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

namespace MilliCache\Rules\Actions\PHP;

use MilliCache\Engine;
use MilliRules\Actions\BaseAction;
use MilliRules\Context;

/**
 * Class SetGraceAction
 *
 * Sets a custom grace period for stale cache serving (trigger action).
 *
 * @since 1.0.0
 */
class SetGrace extends BaseAction {
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
	 * @param Context $context The execution context.
	 * @return void
	 */
	public function execute( Context $context ): void {
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
