<?php
/**
 * PHPStan stubs for MilliPress API functions.
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
 * Clear cache by given Targets.
 *
 * @param string|array<string|int> $targets The targets (Flags, Post-IDs or URLs) to clear the cache for.
 * @param bool                     $expire Expire cache if set to true, or delete by default.
 * @return void
 */
function millipress_clear_cache_by_targets( $targets, bool $expire = false ): void {
}

/**
 * Clear cache by given URLs.
 *
 * @param string|array<string> $urls A string or array of URLs to execute.
 * @param bool                 $expire Expire cache if set to true, or delete by default.
 * @return void
 */
function millipress_clear_cache_by_urls( $urls, bool $expire = false ): void {
}

/**
 * Expire caches by post id.
 *
 * @param int|array<int> $post_ids The post-IDs to expire.
 * @param bool           $expire Expire cache if set to true, or delete by default.
 * @return void
 */
function millipress_clear_cache_by_post_ids( $post_ids, bool $expire = false ): void {
}

/**
 * Clears cache by given flags.
 *
 * @param string|array<string> $flags A string or array of flags to expire.
 * @param bool                 $expire Expire cache if set to true, or delete by default.
 * @param bool                 $add_prefix Add the flag prefix to the flags.
 * @return void
 */
function millipress_clear_cache_by_flags( $flags, bool $expire = false, bool $add_prefix = true ): void {
}

/**
 * Clear the full cache of a given website.
 *
 * @param int|array<int> $site_ids The site IDs to clear.
 * @param int|null       $network_id The network ID.
 * @param bool           $expire Expire cache if set to true, or delete by default.
 * @return void
 */
function millipress_clear_cache_by_site_ids( $site_ids = null, ?int $network_id = null, bool $expire = false ): void {
}

/**
 * Clear the full cache of each site in a given network.
 *
 * @param int|null $network_id The network ID.
 * @param bool     $expire Expire cache.
 * @return void
 */
function millipress_clear_cache_by_network_id( ?int $network_id = null, bool $expire = false ): void {
}

/**
 * Clear all cache (network-wide or site-wide).
 *
 * @param bool $expire Expire cache.
 * @return void
 */
function millipress_clear_cache_all( bool $expire = false ): void {
}
