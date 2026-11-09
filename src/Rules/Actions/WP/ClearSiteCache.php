<?php
/**
 * Clear Site Cache Action
 *
 * Clears the whole cache of the site the rule runs on.
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
 * Class ClearSiteCache
 *
 * Clears every cache entry of the current site (stop action). The optional
 * `expire` argument marks the entries stale instead of deleting them.
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
			->description( __( 'Clear the whole cache of the site this rule runs on.', 'millicache' ) )
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
	 * Builder rules store `expire` at position 0, stored rules under its
	 * schema key; MilliRules maps the position to the first named argument.
	 *
	 * @since 1.0.0
	 *
	 * @param Context $context The execution context.
	 * @return void
	 */
	public function execute( Context $context ): void {
		$expire = $this->get_arg( 0, false )->bool();

		millicache()->clear()->sites( null, null, $expire );
	}
}
