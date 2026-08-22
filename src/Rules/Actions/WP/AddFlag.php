<?php
/**
 * Add Flag Action
 *
 * Adds a cache flag.
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
 * Class AddFlagAction
 *
 * Adds a flag to the cached page for later bulk invalidation (trigger action).
 *
 * @since 1.0.0
 */
class AddFlag extends BaseAction {
	/**
	 * Get the lock scope for this action.
	 *
	 * Engine-relevant. Called by Rules::get_action_scope() on the
	 * advanced-cache.php hot path to build lock keys — before WordPress
	 * loads. Must not use any WordPress functions; keep this method to
	 * plain string returns.
	 *
	 * Shares the 'flag' scope with RemoveFlag so locking add_flag('x')
	 * also blocks remove_flag('x'), while add_flag('y') remains unaffected.
	 *
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public static function get_scope(): string {
		return 'flag';
	}

	/**
	 * Declare metadata for the add_flag action.
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
			->label( __( 'Add Flag', 'millicache' ) )
			->description( __( 'Tag the response with a flag for bulk invalidation.', 'millicache' ) )
			->categories( 'caching', 'flags' )
			->args()
				->string( 'flag' )
					->label( __( 'Flag', 'millicache' ) )
					->description( __( 'The flag identifier to attach to the response.', 'millicache' ) )
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
		return 'add_flag';
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

		// Call Engine's flag manager to add the flag.
		millicache()->flags()->add( $flag );
	}
}
