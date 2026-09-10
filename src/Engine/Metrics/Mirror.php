<?php
/**
 * Database mirror of the daily metrics counters.
 *
 * @link       https://www.millipress.com
 * @since      1.8.2
 *
 * @package     MilliCache
 * @subpackage  Engine\Metrics
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Engine\Metrics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keeps a copy of each prefix's daily counters in the WordPress database, so
 * the long-range history survives a storage-server restart or eviction. The
 * cache itself regenerates; the counters cannot. Written by the nightly
 * rollup, read back by {@see Manager::restore()}.
 *
 * Uses the network-scoped option helpers, which fall back to plain
 * non-autoloaded options on single site.
 *
 * @since      1.8.2
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Mirror {

	/**
	 * Option listing every mirrored prefix, so a restore can find sites whose
	 * counters are no longer in storage at all.
	 *
	 * @var string
	 */
	public const INDEX = 'millicache_metrics_mirror';

	/**
	 * Option name prefix for a site's daily counters (`<OPTION><site-prefix>`).
	 *
	 * @var string
	 */
	public const OPTION = 'millicache_metrics_daily:';

	/**
	 * The mirrored daily counters for a prefix.
	 *
	 * @since 1.8.2
	 *
	 * @param string $prefix Site/network prefix.
	 * @return array<string, int> Field name => value; empty when nothing is mirrored.
	 */
	public function read( string $prefix ): array {
		$stored = get_site_option( self::OPTION . $prefix, array() );
		if ( ! is_array( $stored ) ) {
			return array();
		}

		$fields = array();
		foreach ( $stored as $field => $value ) {
			if ( is_numeric( $value ) ) {
				$fields[ (string) $field ] = (int) $value;
			}
		}

		return $fields;
	}

	/**
	 * Replace the mirrored daily counters for a prefix.
	 *
	 * @since 1.8.2
	 *
	 * @param string             $prefix Site/network prefix.
	 * @param array<string, int> $fields Field name => value; empty removes the mirror.
	 * @return void
	 */
	public function write( string $prefix, array $fields ): void {
		if ( empty( $fields ) ) {
			$this->delete( $prefix );
			return;
		}

		update_site_option( self::OPTION . $prefix, $fields );

		$prefixes = $this->prefixes();
		if ( ! in_array( $prefix, $prefixes, true ) ) {
			$prefixes[] = $prefix;
			update_site_option( self::INDEX, $prefixes );
		}
	}

	/**
	 * Remove the mirror for a prefix.
	 *
	 * @since 1.8.2
	 *
	 * @param string $prefix Site/network prefix.
	 * @return void
	 */
	public function delete( string $prefix ): void {
		delete_site_option( self::OPTION . $prefix );

		$prefixes = array_values( array_diff( $this->prefixes(), array( $prefix ) ) );
		if ( empty( $prefixes ) ) {
			delete_site_option( self::INDEX );
		} else {
			update_site_option( self::INDEX, $prefixes );
		}
	}

	/**
	 * Every prefix that has a mirror.
	 *
	 * @since 1.8.2
	 *
	 * @return array<int, string>
	 */
	public function prefixes(): array {
		$stored = get_site_option( self::INDEX, array() );

		return is_array( $stored ) ? array_values( array_filter( $stored, 'is_string' ) ) : array();
	}
}
