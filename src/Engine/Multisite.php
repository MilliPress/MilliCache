<?php
/**
 * Multisite utility for WordPress multisite handling.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage Engine
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Engine;

! defined( 'ABSPATH' ) && exit;

/**
 * Handles multisite-specific cache operations.
 *
 * Provides utilities for determining multisite status, retrieving site/network IDs,
 * and generating prefixes for cache keys and flags.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @subpackage Engine
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Multisite {

	/**
	 * Check if WordPress is running in multisite mode.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if multisite is enabled.
	 */
	public function is_enabled(): bool {
		return function_exists( 'is_multisite' ) && is_multisite();
	}

	/**
	 * Get all site IDs for a given network.
	 *
	 * @since 1.0.0
	 *
	 * @param int $network_id The network ID (default: 1).
	 * @return array<int> Array of site IDs.
	 */
	public function get_site_ids( int $network_id = 1 ): array {
		if ( ! $this->is_enabled() || ! function_exists( 'get_sites' ) ) {
			return array( 1 );
		}

		return get_sites(
			array(
				'fields'     => 'ids',
				'number'     => 0,
				'network_id' => $network_id,
			)
		);
	}

	/**
	 * Get all network IDs.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int> Array of network IDs.
	 */
	public function get_network_ids(): array {
		if ( ! $this->is_enabled() || ! function_exists( 'get_networks' ) ) {
			return array( 1 );
		}

		return (array) get_networks( array( 'fields' => 'ids' ) );
	}

	/**
	 * Get the flag prefix with network and site namespace.
	 *
	 * Format: {network_id}:{site_id}: or empty string for single site.
	 *
	 * @since 1.0.0
	 *
	 * @param int|string|null $site_id    Site ID (null for current site).
	 * @param int|string|null $network_id Network ID (null for current network).
	 * @return string The flag prefix.
	 */
	public function get_flag_prefix( $site_id = null, $network_id = null ): string {
		if ( ! $this->is_enabled() ) {
			return '';
		}

		// Get site ID.
		$resolved_site_id = $this->resolve_site_id( $site_id );

		// Get network ID (only if multiple networks exist).
		$prefix = '';
		if ( count( $this->get_network_ids() ) > 1 ) {
			$resolved_network_id = $this->resolve_network_id( $network_id );
			$prefix = $resolved_network_id . ':';
		}

		// Add site ID.
		$prefix .= $resolved_site_id . ':';

		return $prefix;
	}

	/**
	 * Resolve site ID (use provided or current).
	 *
	 * @since 1.0.0
	 *
	 * @param int|string|null $site_id Provided site ID.
	 * @return int|string Resolved site ID.
	 */
	private function resolve_site_id( $site_id ) {
		if ( is_int( $site_id ) || is_string( $site_id ) ) {
			return $site_id;
		}

		return function_exists( 'get_current_blog_id' ) ? get_current_blog_id() : 1;
	}

	/**
	 * Resolve network ID (use provided or current).
	 *
	 * @since 1.0.0
	 *
	 * @param int|string|null $network_id Provided network ID.
	 * @return int|string Resolved network ID.
	 */
	private function resolve_network_id( $network_id ) {
		if ( is_int( $network_id ) || is_string( $network_id ) ) {
			return $network_id;
		}

		return function_exists( 'get_current_network_id' ) ? get_current_network_id() : 1;
	}

	/**
	 * Get the current site ID safely.
	 *
	 * @since 1.0.0
	 *
	 * @return int Current site ID.
	 */
	public function get_current_site_id(): int {
		if ( ! $this->is_enabled() || ! function_exists( 'get_current_blog_id' ) ) {
			return 1;
		}

		return get_current_blog_id();
	}

	/**
	 * Get the current network ID safely.
	 *
	 * @since 1.0.0
	 *
	 * @return int Current network ID.
	 */
	public function get_current_network_id(): int {
		if ( ! $this->is_enabled() || ! function_exists( 'get_current_network_id' ) ) {
			return 1;
		}

		return get_current_network_id();
	}
}
