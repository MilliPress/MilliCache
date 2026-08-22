<?php
/**
 * Remove Flag Action
 *
 * Removes a cache flag.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package     MilliCache
 * @subpackage  Rules\Actions\WP
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Rules\Actions\WP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MilliRules\Actions\ActionMeta;
use MilliRules\Actions\BaseAction;
use MilliRules\Context;

/**
 * Class RemoveFlagAction
 *
 * Removes a flag from the cached page (trigger action).
 *
 * @since 1.0.0
 */
class RemoveFlag extends BaseAction {
	/**
	 * Get the lock scope for this action.
	 *
	 * Engine-relevant. Called by Rules::get_action_scope() on the
	 * advanced-cache.php hot path to build lock keys — before WordPress
	 * loads. Must not use any WordPress functions; keep this method to
	 * plain string returns.
	 *
	 * Shares the 'flag' scope with AddFlag so locking remove_flag('x')
	 * also blocks add_flag('x'), while remove_flag('y') remains unaffected.
	 *
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public static function get_scope(): string {
		return 'flag';
	}

	/**
	 * Declare metadata for the remove_flag action.
	 *
	 * Consumer-relevant metadata only. The engine never calls set_meta()
	 * on the lock-key hot path — it reads scope via {@see self::get_scope()}
	 * — so WordPress translation functions are safe here. set_meta() is
	 * invoked lazily by UIs/REST endpoints that request full metadata,
	 * after WordPress has loaded.
	 *
	 * @since 1.5.0
	 *
	 * @param ActionMeta $meta The metadata object to configure.
	 * @return void
	 */
	public static function set_meta( ActionMeta $meta ): void {
		$meta
			->label( __( 'Remove Flag', 'millicache' ) )
			->description( __( 'Remove a previously attached flag from the response.', 'millicache' ) )
			->categories( 'caching', 'flags' )
			->args()
				->string( 'flag' )
					->label( __( 'Flag', 'millicache' ) )
					->description( __( 'The flag identifier to remove from the response.', 'millicache' ) )
					->required();
	}

	/**
	 * Get the action type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The action type identifier.
	 */
	public function get_type(): string {
		return 'remove_flag';
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
		$flag = $this->usable_arg( 0 );

		if ( null === $flag ) {
			return;
		}

		// Call Engine's flag manager to remove the flag.
		millicache()->flags()->remove( $flag );
	}
}
