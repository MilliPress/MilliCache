<?php
/**
 * Cache validation logic.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

namespace MilliCache\Engine\Cache;

! defined( 'ABSPATH' ) && exit;

/**
 * Validates cache entries for staleness and expiration.
 *
 * Handles TTL and grace period logic to determine if cache entries
 * are fresh, stale (but usable), or expired (must be deleted).
 *
 * @since      1.0.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Validator {

	/**
	 * Cache configuration.
	 *
	 * @var Config
	 */
	private Config $config;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Config $config Cache configuration.
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 * Check if a cache entry is stale (past TTL but within grace).
	 *
	 * @since 1.0.0
	 *
	 * @param Entry    $entry     The cache entry.
	 * @param int|null $custom_ttl Custom TTL override.
	 * @return bool True if stale.
	 */
	public function is_stale( Entry $entry, ?int $custom_ttl = null ): bool {
		$effective_ttl = $custom_ttl ?? $entry->custom_ttl ?? $this->config->ttl;
		return ( $entry->updated + $effective_ttl ) < time();
	}

	/**
	 * Check if a cache entry is too old (past TTL + grace).
	 *
	 * Entries this old should be deleted completely.
	 *
	 * @since 1.0.0
	 *
	 * @param Entry    $entry       The cache entry.
	 * @param int|null $custom_ttl   Custom TTL override.
	 * @param int|null $custom_grace Custom grace override.
	 * @return bool True if too old.
	 */
	public function is_too_old( Entry $entry, ?int $custom_ttl = null, ?int $custom_grace = null ): bool {
		$effective_ttl   = $custom_ttl ?? $entry->custom_ttl ?? $this->config->ttl;
		$effective_grace = $custom_grace ?? $entry->custom_grace ?? $this->config->grace;

		return ( $entry->updated + $effective_ttl + $effective_grace ) < time();
	}

	/**
	 * Check if a cache entry is fresh (within TTL).
	 *
	 * @since 1.0.0
	 *
	 * @param Entry    $entry     The cache entry.
	 * @param int|null $custom_ttl Custom TTL override.
	 * @return bool True if fresh.
	 */
	public function is_fresh( Entry $entry, ?int $custom_ttl = null ): bool {
		return ! $this->is_stale( $entry, $custom_ttl );
	}

	/**
	 * Get time remaining until expiration (TTL).
	 *
	 * @since 1.0.0
	 *
	 * @param Entry    $entry     The cache entry.
	 * @param int|null $custom_ttl Custom TTL override.
	 * @return int Seconds until expiration (negative if already expired).
	 */
	public function time_to_expiry( Entry $entry, ?int $custom_ttl = null ): int {
		$effective_ttl = $custom_ttl ?? $entry->custom_ttl ?? $this->config->ttl;
		return ( $entry->updated + $effective_ttl ) - time();
	}

	/**
	 * Get time remaining until deletion (TTL + grace).
	 *
	 * @since 1.0.0
	 *
	 * @param Entry    $entry       The cache entry.
	 * @param int|null $custom_ttl   Custom TTL override.
	 * @param int|null $custom_grace Custom grace override.
	 * @return int Seconds until deletion (negative if should be deleted).
	 */
	public function time_to_deletion( Entry $entry, ?int $custom_ttl = null, ?int $custom_grace = null ): int {
		$effective_ttl   = $custom_ttl ?? $entry->custom_ttl ?? $this->config->ttl;
		$effective_grace = $custom_grace ?? $entry->custom_grace ?? $this->config->grace;

		return ( $entry->updated + $effective_ttl + $effective_grace ) - time();
	}

	/**
	 * Get effective TTL for a cache entry.
	 *
	 * Returns custom TTL if set in entry, otherwise config TTL.
	 *
	 * @since 1.0.0
	 *
	 * @param Entry    $entry     The cache entry.
	 * @param int|null $custom_ttl Custom TTL override.
	 * @return int The effective TTL in seconds.
	 */
	public function get_effective_ttl( Entry $entry, ?int $custom_ttl = null ): int {
		return $custom_ttl ?? $entry->custom_ttl ?? $this->config->ttl;
	}

	/**
	 * Get effective grace period for a cache entry.
	 *
	 * Returns custom grace if set in entry, otherwise config grace.
	 *
	 * @since 1.0.0
	 *
	 * @param Entry    $entry       The cache entry.
	 * @param int|null $custom_grace Custom grace override.
	 * @return int The effective grace period in seconds.
	 */
	public function get_effective_grace( Entry $entry, ?int $custom_grace = null ): int {
		return $custom_grace ?? $entry->custom_grace ?? $this->config->grace;
	}

	/**
	 * Format time remaining as human-readable string.
	 *
	 * @since 1.0.0
	 *
	 * @param int $seconds Seconds remaining.
	 * @return string Formatted as "Xd XXh XXm XXs".
	 */
	public function format_time_remaining( int $seconds ): string {
		if ( $seconds < 0 ) {
			$seconds = abs( $seconds );
			$prefix  = '-';
		} else {
			$prefix = '';
		}

		return $prefix . sprintf(
			'%dd %02dh %02dm %02ds',
			intdiv( $seconds, 86400 ),
			intdiv( $seconds % 86400, 3600 ),
			intdiv( $seconds % 3600, 60 ),
			$seconds % 60
		);
	}
}
