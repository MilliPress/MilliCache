<?php
/**
 * Clear Site Cache Action
 *
 * Clears cache for a specific site or current site.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package     MilliCache
 * @subpackage  Rules\Actions\WP
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Rules\Actions\WP;

use MilliRules\Actions\ActionMeta;
use MilliRules\Actions\BaseAction;
use MilliRules\Context;

/**
 * Class FlushBySiteAction
 *
 * Clears cache entries for a specific site (stop action).
 * Accepts optional site_id parameter, defaults to current site.
 *
 * @since 1.0.0
 */
class ClearSiteCache extends BaseAction {
	/**
	 * Declare metadata for the clear_site_cache action.
	 *
	 * @since 1.5.0
	 *
	 * @param ActionMeta $meta The metadata object to configure.
	 * @return void
	 */
	public static function set_meta( ActionMeta $meta ): void {
		$meta
			->label( __( 'Clear Site Cache', 'millicache' ) )
			->description( __( 'Clear all cache entries for the current site.', 'millicache' ) )
			->categories( 'caching' )
			->args()
				->boolean( 'expire' )
					->label( __( 'Expire Only', 'millicache' ) )
					->description( __( 'Mark entries as stale instead of deleting, allowing grace-period serving.', 'millicache' ) )
					->default( false );
	}

	/**
	 * Get the action type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The action type identifier.
	 */
	public function get_type(): string {
		return 'clear_site_cache';
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
		/**
		 * Site IDs to clear cache for.
		 *
		 * @var array<int>      $site_ids
		 */
		$site_ids = $this->get_arg( 0 )->array();
		$network_id = $this->get_arg( 1 )->int();
		$expire = $this->get_arg( 2, false )->bool();

		// Call Engine to clear cache for specified site(s) or current site.
		millicache()->clear()->sites( $site_ids, $network_id, $expire );
	}
}
