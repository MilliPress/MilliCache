<?php
/**
 * Do Cache Action
 *
 * Marks the page for caching.
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
 * Class DoCacheAction
 *
 * Explicitly allows the page to be cached (stop action).
 *
 * @since 1.0.0
 */
class DoCache extends BaseAction {
	/**
	 * Get the action type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The action type identifier.
	 */
	public function get_type(): string {
		return 'do_cache';
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
		$reason = $this->args[0] ?? 'Rule action: do_cache';

		// Resolve placeholders in reason.
		if ( is_string( $reason ) ) {
			$reason = $this->resolve_value( $reason );
		}

		// Signal to Engine TO cache this request (override other checks).
		if ( class_exists( '\\MilliCache\\Engine' ) && is_string( $reason ) ) {
			Engine::set_cache_decision( true, $reason );
		}
	}
}
