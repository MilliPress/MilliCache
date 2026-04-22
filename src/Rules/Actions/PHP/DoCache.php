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

use MilliRules\Actions\ActionMeta;
use MilliRules\Actions\BaseAction;
use MilliRules\Context;

/**
 * Class CacheDecision
 *
 * Explicitly sets whether the page should be cached or not (stop action).
 *
 * @since 1.0.0
 */
class DoCache extends BaseAction {
	/**
	 * Tracks the highest rule order that has set a cache decision.
	 *
	 * Actions from rules with a lower order than the current value are
	 * skipped, making ->order() the authoritative knob regardless of
	 * WordPress hook priority. Resets naturally per request (fresh process).
	 *
	 * @since 1.5.0
	 * @var int
	 */
	private static int $last_order = PHP_INT_MIN;

	/**
	 * Declare metadata for the do_cache action.
	 *
	 * Consumer-relevant metadata only. The engine never calls set_meta()
	 * on the lock-key hot path — it reads scope via the static
	 * {@see BaseAction::get_scope()} instead — so WordPress translation
	 * functions are safe here. set_meta() is invoked lazily by UIs/REST
	 * endpoints that request full metadata, after WordPress has loaded.
	 *
	 * @since 1.5.0
	 *
	 * @param ActionMeta $meta The metadata object to configure.
	 * @return void
	 */
	public static function set_meta( ActionMeta $meta ): void {
		$meta
			->label( __( 'Set Cache Decision', 'millicache' ) )
			->description( __( 'Explicitly enable or disable caching for the current request.', 'millicache' ) )
			->categories( 'caching' )
			->args()
				->boolean( 'should_cache' )
					->label( __( 'Should Cache', 'millicache' ) )
					->description( __( 'Whether the current request should be cached.', 'millicache' ) )
					->default( true )
				->string( 'reason' )
					->label( __( 'Reason', 'millicache' ) )
					->description( __( 'A human-readable reason for the cache decision, for debugging and logging purposes.', 'millicache' ) )
					->default( '' );
	}

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
		$order       = $context->get( 'rule.order' );
		$order       = is_int( $order ) ? $order : 10;

		// Skip if a higher-order rule already set the cache decision.
		if ( $order < self::$last_order ) {
			return;
		}
		self::$last_order = $order;

		$should_cache = $this->get_arg( 0, true )->bool();
		$rule_id      = $context->get( 'rule.id', 'unknown' );
		$default_reason = is_string( $rule_id )
			? sprintf( 'Rule "%s" -> %s', $rule_id, $should_cache ? 'cache' : 'bypass' )
			: ( $should_cache ? 'cache' : 'bypass' );
		$reason       = $this->get_arg( 1, $default_reason )->string();

		millicache()->options()->set_cache_decision( $should_cache, $reason );
	}
}
