<?php
/**
 * Clear Site Cache Action
 *
 * Clears the cache of the current site or of the given sites.
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
 * Clears every cache entry of one or more sites (stop action). Without site
 * IDs the current site is cleared; the optional `expire` argument marks the
 * entries stale instead of deleting them.
 *
 * @since 1.0.0
 * @since 1.8.3 Signature is ( $site_ids = null, $expire = false ).
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
			->description( __( 'Clear all cache entries of the current site or of the given sites.', 'millicache' ) )
			->categories( 'caching' )
			->args()
				->string( 'site_ids' )
					->label( __( 'Site IDs', 'millicache' ) )
					->description( __( 'Comma-separated site IDs to clear. Leave empty for the current site.', 'millicache' ) )
					->also_accepts( 'integer' )
					->also_accepts( 'array' )
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
	 * Stored rules carry the schema keys, builder rules positions. Reading the
	 * stored form by name keeps a rule that omits `site_ids` from having its
	 * `expire` value mistaken for a site ID.
	 *
	 * @since 1.0.0
	 *
	 * @param Context $context The execution context.
	 * @return void
	 */
	public function execute( Context $context ): void {
		$named    = array_key_exists( 'site_ids', $this->args ) || array_key_exists( 'expire', $this->args );
		$site_ids = $this->site_ids( $this->get_arg( $named ? 'site_ids' : 0 )->raw() );
		$expire   = $this->get_arg( $named ? 'expire' : 1, false )->bool();

		if ( array() === $site_ids ) {
			millicache()->logger()->warning( 'clear_site_cache skipped: no valid site IDs in the given value.' );

			return;
		}

		millicache()->clear()->sites( $site_ids, null, $expire );
	}

	/**
	 * Normalize the site_ids argument.
	 *
	 * Accepts an integer, a list of integers or a comma-separated string.
	 *
	 * @since 1.8.3
	 *
	 * @param mixed $value The raw argument value.
	 * @return array<int>|null The site IDs, null for the current site, or an
	 *                         empty array when the value held no valid ID.
	 */
	private function site_ids( $value ): ?array {
		if ( null === $value || '' === $value || array() === $value ) {
			return null;
		}

		if ( is_string( $value ) ) {
			$value = explode( ',', $value );
		}

		$site_ids = array();
		foreach ( (array) $value as $site_id ) {
			if ( is_string( $site_id ) ) {
				$site_id = trim( $site_id );
			}
			if ( is_numeric( $site_id ) && (int) $site_id > 0 ) {
				$site_ids[] = (int) $site_id;
			}
		}

		return array_values( array_unique( $site_ids ) );
	}
}
