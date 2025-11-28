<?php
/**
 * Cache Decision Action
 *
 * Sets the cache decision for the current request.
 *
 * @package MilliCache
 * @subpackage Rules\Actions
 * @since 1.0.0
 */

namespace MilliCache\Rules\Actions\PHP;

use MilliCache\Engine;
use MilliCache\Deps\MilliRules\Actions\BaseAction;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class CacheDecision
 *
 * Explicitly sets whether the page should be cached or not (stop action).
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
		$should_cache = $this->args[0] ?? true;
		$reason       = $this->args[1] ?? ( $should_cache ? 'Rule action: do_cache() -> (cache)' : 'Rule action: do_cache() -> (do not cache)' );

		// Resolve placeholders in reason.
		if ( is_string( $reason ) ) {
			$reason = $this->resolve_value( $reason );
		}

		// Signal to Engine cache decision.
		if ( class_exists( '\\MilliCache\\Engine' ) && is_string( $reason ) ) {
			Engine::set_cache_decision( (bool) $should_cache, $reason );
		}
	}
}
