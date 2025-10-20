<?php
/**
 * Do Not Cache Action
 *
 * Prevents the page from being cached.
 *
 * @package MilliCache
 * @subpackage Rules\Actions
 * @since 1.0.0
 */

namespace MilliCache\Rules\Actions;

use MilliCache\Engine;

/**
 * Class DoNotCacheAction
 *
 * Explicitly prevents the page from being cached (stop action).
 *
 * @since 1.0.0
 */
class DoNotCache extends BaseAction {
	/**
	 * Whether this is a trigger action.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected bool $is_trigger = false;

	/**
	 * Get the action type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The action type identifier.
	 */
	public function get_type(): string {
		return 'do_not_cache';
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
		$reason = $this->config['reason'] ?? 'Rule action: do_not_cache';

		// Resolve placeholders in reason.
		if ( is_string( $reason ) ) {
			$reason = $this->resolve_value( $reason );
		}

		// Signal to Engine not to cache this request.
		if ( class_exists( '\\MilliCache\\Engine' ) && is_string( $reason ) ) {
			Engine::set_cache_decision( false, $reason );
		}
	}
}
