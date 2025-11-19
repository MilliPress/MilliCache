<?php
/**
 * Set TTL Action
 *
 * Sets the cache time-to-live.
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
 * Class SetTtlAction
 *
 * Sets a custom TTL (time-to-live) for the cached page (trigger action).
 *
 * @since 1.0.0
 */
class SetTtl extends BaseAction {
	/**
	 * Get the action type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The action type identifier.
	 */
	public function get_type(): string {
		return 'set_ttl';
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
		$ttl = $this->args[0];

		if ( ! is_numeric( $ttl ) ) {
			return;
		}

		$ttl_int = (int) $ttl;

		if ( $ttl_int <= 0 ) {
			return;
		}

		// Call Engine to set TTL.
		if ( class_exists( '\\MilliCache\\Engine' ) ) {
			Engine::set_ttl( $ttl_int );
		}
	}
}
