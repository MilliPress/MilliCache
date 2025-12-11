<?php
/**
 * Cache Decision Action
 *
 * Sets the cache decision for the current request.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package MilliCache
 * @subpackage Rules\Actions\PHP
 * @author Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Rules\Actions\PHP;

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
		$should_cache = $this->get_arg( 0, true )->bool();
		$reason       = $this->get_arg( 1, $should_cache ? 'Rule action: do_cache() -> (cache)' : 'Rule action: do_cache(false) -> (do not cache)' )->string();

		// Set cache decision.
		millicache()->options()->set_cache_decision( $should_cache, $reason );
	}
}
