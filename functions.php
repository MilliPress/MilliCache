<?php
/**
 * MilliCache API Functions
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */

! defined( 'ABSPATH' ) && exit;

if ( ! function_exists( 'millicache' ) ) {
	/**
	 * Get the MilliCache Engine instance for advanced usage.
	 *
	 * This is an advanced method that provides direct access to the Engine
	 * singleton. For common tasks, prefer the simpler helper functions:
	 *
	 * - millipress_clear_cache() - Clear cache by flags, post-IDs, or URLs
	 * - millipress_add_flag() - Add a cache flag to the current request
	 * - millipress_set_ttl() - Set cache TTL for the current request
	 *
	 * Use millicache() when you need access to Engine methods not exposed
	 * via helper functions or for complex chained operations.
	 *
	 * Example usage:
	 *
	 *     // Access storage backend directly
	 *     millicache()->storage()->get_status();
	 *
	 *     // Read configuration
	 *     millicache()->config()->get('storage.backend');
	 *
	 *     // Chain multiple invalidation operations
	 *     millicache()->clear()->posts([1, 2])->flags(['custom'])->execute_queue();
	 *
	 * @since 1.0.0
	 *
	 * @return \MilliCache\Engine The MilliCache Engine instance.
	 */
	function millicache(): \MilliCache\Engine {
		return \MilliCache\Engine::instance();
	}
}

/**
 * Clear cache by given Flags, Post-IDs or URLs.
 *
 * @since 1.0.0
 *
 * @param string|array<string|int> $targets The targets (Flags, Post-IDs or URLs) to clear the cache for.
 * @param bool                     $expire Expire cache if set to true, or delete by default.
 * @return void
 */
function millipress_clear_cache( $targets, bool $expire = false ): void {
	millicache()->clear()->targets( $targets, $expire );
}

/**
 * Clear cache by given URLs.
 *
 * @since 1.0.0
 *
 * @param string|array<string> $urls A string or array of URLs to execute.
 * @param bool                 $expire Expire cache if set to true, or delete by default.
 * @return void
 */
function millipress_clear_cache_by_urls( $urls, bool $expire = false ): void {
	millicache()->clear()->urls( $urls, $expire );
}

/**
 * Expire caches by post id.
 *
 * @since 1.0.0
 *
 * @param int|array<int> $post_ids The post-IDs to expire.
 * @param bool           $expire Expire cache if set to true, or delete by default.
 * @return void
 */
function millipress_clear_cache_by_post_ids( $post_ids, bool $expire = false ): void {
	millicache()->clear()->posts( $post_ids, $expire );
}

/**
 * Clears cache by given flags.
 *
 * @since 1.0.0
 *
 * @param string|array<string> $flags A string or array of flags to expire.
 * @param bool                 $expire Expire cache if set to true, or delete by default.
 * @param bool                 $add_prefix Add the flag prefix to the flags.
 * @return void
 */
function millipress_clear_cache_by_flags( $flags, bool $expire = false, bool $add_prefix = true ): void {
	millicache()->clear()->flags( $flags, $expire, $add_prefix );
}

/**
 * Clear the full cache of a given website.
 *
 * @since 1.0.0
 *
 * @param int|array<int> $site_ids The site IDs to clear.
 * @param int|null       $network_id The network ID.
 * @param bool           $expire Expire cache if set to true, or delete by default.
 * @return void
 */
function millipress_clear_cache_by_site_ids( $site_ids = null, ?int $network_id = null, bool $expire = false ): void {
	millicache()->clear()->sites( $site_ids, $network_id, $expire );
}

/**
 * Clear the full cache of each site in a given network.
 *
 * @since 1.0.0
 *
 * @param int|null $network_id The network ID.
 * @param bool     $expire Expire cache.
 * @return void
 */
function millipress_clear_cache_by_network_id( ?int $network_id = null, bool $expire = false ): void {
	millicache()->clear()->networks( $network_id, $expire );
}

/**
 * Reset the complete cache.
 *
 * @since 1.0.0
 *
 * @param bool $expire Expire cache.
 * @return void
 */
function millipress_reset_cache( bool $expire = false ): void {
	millicache()->clear()->all( $expire );
}

/**
 * Add a flag to the current request.
 *
 * Flags are labels attached to cache entries that allow for efficient
 * cache clearing. For example, all pages tagged with "post:123" can
 * be cleared simultaneously.
 *
 * @since 1.0.0
 *
 * @param string $flag The flag name (e.g., 'post:123', 'custom-flag').
 * @return void
 */
function millipress_add_flag( string $flag ): void {
	millicache()->flags()->add( $flag );
}

/**
 * Remove a flag from the current request.
 *
 * @since 1.0.0
 *
 * @param string $flag The flag name to remove.
 * @return void
 */
function millipress_remove_flag( string $flag ): void {
	millicache()->flags()->remove( $flag );
}

/**
 * Get the prefix for flags (site:network: or empty).
 *
 * In multisite environments, flags are automatically prefixed with
 * site and network IDs. This function returns that prefix.
 *
 * @since 1.0.0
 *
 * @param int|string|null $site_id    Site ID (null for current).
 * @param int|string|null $network_id Network ID (null for current).
 * @return string The prefix string (empty string for non-multisite).
 */
function millipress_get_flag_prefix( $site_id = null, $network_id = null ): string {
	return millicache()->flags()->get_prefix( $site_id, $network_id );
}

/**
 * Prefix an array of flags with site/network prefix.
 *
 * In multisite environments, this adds the site and network ID prefix
 * to each flag. Useful when you need to manually construct prefixed flags.
 *
 * @since 1.0.0
 *
 * @param string|array<string> $flags      Flags to prefix (string or array).
 * @param int|string|null      $site_id    Site ID (null for current).
 * @param int|string|null      $network_id Network ID (null for current).
 * @return array<string> Array of prefixed flags.
 */
function millipress_prefix_flags( $flags, $site_id = null, $network_id = null ): array {
	return millicache()->flags()->prefix( $flags, $site_id, $network_id );
}

/**
 * Override the cache TTL (time-to-live) for the current request.
 *
 * This allows dynamic control of how long content is cached. Useful for
 * setting different cache durations based on content type, user role or
 * other runtime conditions.
 *
 * Example: Cache homepage for 1 hour, product pages for 5 minutes
 *
 * @since 1.0.0
 *
 * @param int $ttl Time-to-live in seconds (must be positive).
 * @return void
 */
function millipress_set_ttl( int $ttl ): void {
	millicache()->options()->set_ttl( $ttl );
}

/**
 * Override the cache grace period for the current request.
 *
 * The grace period allows serving stale cache while regenerating in the
 * background. This prevents cache stampedes and ensures consistent
 * performance during cache regeneration.
 *
 * Example: Allow serving up to 1-hour-old cache while regenerating fresh content
 *
 * @since 1.0.0
 *
 * @param int $grace Grace period in seconds (must be non-negative, 0 to disable).
 * @return void
 */
function millipress_set_grace( int $grace ): void {
	millicache()->options()->set_grace( $grace );
}
