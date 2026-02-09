<?php
/**
 * PHPStan stubs for MilliCache API functions.
 *
 * @package MilliCache
 */

/**
 * Get the MilliCache Engine instance.
 *
 * @return \MilliCache\Engine The Engine instance.
 */
function millicache(): \MilliCache\Engine {
}

/**
 * Clear cache by given Flags, Post-IDs or URLs.
 *
 * @param string|array<string|int> $targets The targets (Flags, Post-IDs or URLs) to clear the cache for.
 * @param bool                     $expire Expire cache if set to true, or delete by default.
 * @return void
 */
function millicache_clear_cache( $targets, bool $expire = false ): void {
}

/**
 * Clear cache by given URLs.
 *
 * @param string|array<string> $urls A string or array of URLs to execute.
 * @param bool                 $expire Expire cache if set to true, or delete by default.
 * @return void
 */
function millicache_clear_cache_by_urls( $urls, bool $expire = false ): void {
}

/**
 * Expire caches by post id.
 *
 * @param int|array<int> $post_ids The post-IDs to expire.
 * @param bool           $expire Expire cache if set to true, or delete by default.
 * @return void
 */
function millicache_clear_cache_by_post_ids( $post_ids, bool $expire = false ): void {
}

/**
 * Clears cache by given flags.
 *
 * @param string|array<string> $flags A string or array of flags to expire.
 * @param bool                 $expire Expire cache if set to true, or delete by default.
 * @param bool                 $add_prefix Add the flag prefix to the flags.
 * @return void
 */
function millicache_clear_cache_by_flags( $flags, bool $expire = false, bool $add_prefix = true ): void {
}

/**
 * Clear the full cache of a given website.
 *
 * @param int|array<int> $site_ids The site IDs to clear.
 * @param int|null       $network_id The network ID.
 * @param bool           $expire Expire cache if set to true, or delete by default.
 * @return void
 */
function millicache_clear_cache_by_site_ids( $site_ids = null, ?int $network_id = null, bool $expire = false ): void {
}

/**
 * Clear the full cache of each site in a given network.
 *
 * @param int|null $network_id The network ID.
 * @param bool     $expire Expire cache.
 * @return void
 */
function millicache_clear_cache_by_network_id( ?int $network_id = null, bool $expire = false ): void {
}

/**
 * Reset the complete cache.
 *
 * @param bool $expire Expire cache.
 * @return void
 */
function millicache_reset_cache( bool $expire = false ): void {
}

/**
 * Add a flag to the current request.
 *
 * @param string $flag The flag name (e.g., 'post:123', 'custom-flag').
 * @return void
 */
function millicache_add_flag( string $flag ): void {
}

/**
 * Remove a flag from the current request.
 *
 * @param string $flag The flag name to remove.
 * @return void
 */
function millicache_remove_flag( string $flag ): void {
}

/**
 * Get the prefix for flags (site:network: or empty).
 *
 * @param int|string|null $site_id    Site ID (null for current).
 * @param int|string|null $network_id Network ID (null for current).
 * @return string The prefix string.
 */
function millicache_get_flag_prefix( $site_id = null, $network_id = null ): string {
}

/**
 * Prefix an array of flags with site/network prefix.
 *
 * @param string|array<string> $flags      Flags to prefix.
 * @param int|string|null      $site_id    Site ID (null for current).
 * @param int|string|null      $network_id Network ID (null for current).
 * @return array<string> Array of prefixed flags.
 */
function millicache_prefix_flags( $flags, $site_id = null, $network_id = null ): array {
}

/**
 * Override the cache TTL for the current request.
 *
 * @param int $ttl Time-to-live in seconds.
 * @return void
 */
function millicache_set_ttl( int $ttl ): void {
}

/**
 * Override the cache grace period for the current request.
 *
 * @param int $grace Grace period in seconds.
 * @return void
 */
function millicache_set_grace( int $grace ): void {
}
