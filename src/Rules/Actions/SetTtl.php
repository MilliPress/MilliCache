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

namespace MilliCache\Rules\Actions;

use MilliCache\Engine;

/**
 * Class SetTtlAction
 *
 * Sets a custom TTL (time-to-live) for the cached page (trigger action).
 *
 * @since 1.0.0
 */
class SetTtl extends BaseAction {
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
		return 'set_ttl';
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
		$ttl = $this->value;

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
