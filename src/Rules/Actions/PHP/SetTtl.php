<?php
/**
 * Set TTL Action
 *
 * Sets the cache time-to-live.
 *
 * @link        https://www.millipress.com
 * @since       1.0.0
 *
 * @package     MilliCache
 * @subpackage  Rules\Actions\PHP
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Rules\Actions\PHP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MilliRules\Actions\ActionMeta;
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
	 * Tracks the highest rule order that has set a TTL.
	 *
	 * @since 1.5.0
	 * @var int
	 */
	private static int $last_order = PHP_INT_MIN;

	/**
	 * Declare metadata for the set_ttl action.
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
			->label( __( 'Set TTL', 'millicache' ) )
			->description( __( 'Override the cache time-to-live for the current request.', 'millicache' ) )
			->categories( 'caching' )
			->args()
				->integer( 'ttl' )
					->format( 'seconds' )
					->label( __( 'TTL', 'millicache' ) )
					->description( __( 'How long to cache the response, in seconds.', 'millicache' ) )
					->default( 3600 )
					->min( 0 );
	}

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
		$order       = $context->get( 'rule.order' );
		$order       = is_int( $order ) ? $order : 10;

		// Skip if a higher-order rule already set the TTL.
		if ( $order < self::$last_order ) {
			return;
		}
		self::$last_order = $order;

		$ttl = $this->get_arg( 0 )->int();
		millicache()->options()->set_ttl( $ttl );
	}
}
