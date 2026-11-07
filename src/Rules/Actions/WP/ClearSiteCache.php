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
use MilliRules\ArgumentValue;
use MilliRules\Context;

/**
 * Class ClearSiteCache
 *
 * Clears every cache entry of one or more sites (stop action). Without site
 * IDs the current site is cleared; the optional `expire` argument marks the
 * entries stale instead of deleting them.
 *
 * @since 1.0.0
 * @since 1.8.3 Signature is ( $site_ids = null, $expire = false ); the network ID is deprecated.
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
	 * Declare the builder argument order.
	 *
	 * Mirrors the keys declared in {@see self::set_meta()} so positional builder
	 * arguments and stored named arguments resolve to the same values.
	 *
	 * @since 1.8.3
	 *
	 * @return array<int, string> Argument names in positional order.
	 */
	public static function get_argument_mapping(): array {
		return array( 'site_ids', 'expire' );
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
		$raw_site_ids = $this->argument( 'site_ids', 0 )->raw();
		$network_id   = null;
		$expire       = $this->argument( 'expire', 1 );

		// Builder calls written against the pre-1.8.3 stub passed a network ID
		// second: clear_site_cache( $site_id, $network_id, $expire ).
		if ( is_int( $expire->raw() ) ) {
			millicache()->logger()->warning(
				'clear_site_cache: the network ID argument is deprecated. Use clear_site_cache( $site_ids, $expire ).'
			);
			$network_id = $expire->int();
			$expire     = $this->get_arg( 2, false );
		}

		$site_ids = $this->site_ids( $raw_site_ids );

		if ( array() === $site_ids ) {
			millicache()->logger()->warning( 'clear_site_cache skipped: no valid site IDs in the given value.' );

			return;
		}

		millicache()->clear()->sites( $site_ids, $network_id, $expire->bool() );
	}

	/**
	 * Read an argument by its schema name, falling back to its builder position.
	 *
	 * Stored rules carry named keys, builder rules positional ones. Reading the
	 * name first keeps a stored rule that omits `site_ids` from having its
	 * `expire` value mistaken for a site ID.
	 *
	 * @since 1.8.3
	 *
	 * @param string $name     The schema key.
	 * @param int    $position The builder position.
	 * @return ArgumentValue The argument value.
	 */
	private function argument( string $name, int $position ): ArgumentValue {
		foreach ( array_keys( $this->args ) as $key ) {
			if ( is_string( $key ) && 0 !== strpos( $key, '_' ) ) {
				return $this->get_arg( $name );
			}
		}

		return $this->get_arg( $position );
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
