<?php
/**
 * Cache invalidation orchestrator.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

namespace MilliCache\Engine\Clearing;

use MilliCache\Core\Storage;
use MilliCache\Engine\Multisite;
use MilliCache\Engine\Request\Handler as RequestHandler;

! defined( 'ABSPATH' ) && exit;

/**
 * Orchestrates cache invalidation operations.
 *
 * High-level API for clearing cache by various targets (URLs, posts, flags, sites).
 * Delegates to Resolver and Flusher for the actual work.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Handler {

	/**
	 * Target resolver.
	 *
	 * @var Resolver
	 */
	private Resolver $resolver;

	/**
	 * Flag flusher.
	 *
	 * @var Flusher
	 */
	private Flusher $flusher;

	/**
	 * Multisite helper.
	 *
	 * @var Multisite
	 */
	private Multisite $multisite;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Storage        $storage         Storage instance.
	 * @param RequestHandler $request_handler Request handler for URL hashing.
	 * @param Multisite      $multisite       Multisite helper.
	 * @param int            $default_ttl     Default TTL for expiration.
	 */
	public function __construct(
		Storage $storage,
		RequestHandler $request_handler,
		Multisite $multisite,
		int $default_ttl = 3600
	) {
		$this->multisite = $multisite;
		$this->resolver  = new Resolver( $request_handler, $multisite );
		$this->flusher   = new Flusher( $storage, $multisite, $default_ttl );
	}

	/**
	 * Get target resolver.
	 *
	 * @since 1.0.0
	 *
	 * @return Resolver The resolver instance.
	 */
	public function get_resolver(): Resolver {
		return $this->resolver;
	}

	/**
	 * Get flag flusher.
	 *
	 * @since 1.0.0
	 *
	 * @return Flusher The flusher instance.
	 */
	public function get_flusher(): Flusher {
		return $this->flusher;
	}

	/**
	 * Clear cache by mixed targets.
	 *
	 * Accepts URLs, post-IDs, or flags and clears appropriate cache entries.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array<string|int> $targets Target(s) to clear.
	 * @param bool                     $expire  Expire (true) or delete (false).
	 * @return void
	 */
	public function clear_by_targets( $targets, bool $expire = false ): void {
		// Convert to array.
		if ( ! is_array( $targets ) ) {
			$targets = array( $targets );
		}

		// Empty targets means clear entire site.
		if ( empty( $targets ) ) {
			$this->clear_by_site_ids();
			return;
		}

		// Resolve each target and clear.
		foreach ( $targets as $target ) {
			$target_str = (string) $target;

			if ( $this->resolver->is_url( $target_str ) ) {
				// Only clear URLs from current site.
				if ( function_exists( 'get_home_url' ) && strpos( $target_str, get_home_url() ) === 0 ) {
					$this->clear_by_urls( $target_str, $expire );
				}
			} elseif ( $this->resolver->is_post_id( $target_str ) ) {
				$this->clear_by_post_ids( (int) $target, $expire );
			} else {
				// Flag - limit to current site if not network admin.
				$add_prefix = $this->multisite->is_enabled() &&
							  function_exists( 'is_network_admin' ) &&
							  ! is_network_admin();
				$this->clear_by_flags( $target_str, $expire, $add_prefix );
			}
		}
	}

	/**
	 * Clear cache by URLs.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array<string> $urls   URL(s) to clear.
	 * @param bool                 $expire Expire (true) or delete (false).
	 * @return void
	 */
	public function clear_by_urls( $urls, bool $expire = false ): void {
		// Convert to array.
		$urls = is_string( $urls ) ? array( $urls ) : $urls;

		// Resolve URLs to flags.
		$flags = array();
		foreach ( $urls as $url ) {
			$flags = array_merge( $flags, $this->resolver->resolve_url( $url ) );
		}

		// Add to flusher queue.
		if ( $expire ) {
			$this->flusher->add_to_expire( $flags, false );
		} else {
			$this->flusher->add_to_delete( $flags, false );
		}
	}

	/**
	 * Clear cache by post-IDs.
	 *
	 * @since 1.0.0
	 *
	 * @param int|array<int> $post_ids Post ID(s) to clear.
	 * @param bool           $expire   Expire (true) or delete (false).
	 * @return void
	 */
	public function clear_by_post_ids( $post_ids, bool $expire = false ): void {
		// Convert to array.
		$post_ids = ! is_array( $post_ids ) ? array( $post_ids ) : $post_ids;

		// Resolve post-IDs to flags.
		$flags = array();
		foreach ( $post_ids as $post_id ) {
			$flags = array_merge( $flags, $this->resolver->resolve_post_id( $post_id ) );
		}

		// Add to clearer queue.
		$this->clear_by_flags( $flags, $expire );

		// Fire WordPress action.
		if ( function_exists( 'do_action' ) ) {
			do_action( 'millicache_cache_cleared_by_posts', $post_ids, $expire );
		}
	}

	/**
	 * Clear cache by flags.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array<string> $flags      Flag(s) to clear.
	 * @param bool                 $expire     Expire (true) or delete (false).
	 * @param bool                 $add_prefix Whether to add multisite prefix.
	 * @return void
	 */
	public function clear_by_flags( $flags, bool $expire = false, bool $add_prefix = true ): void {
		// Convert to array.
		$flags = is_string( $flags ) ? array( $flags ) : $flags;

		// Add to flusher queue.
		if ( $expire ) {
			$this->flusher->add_to_expire( $flags, $add_prefix );
		} else {
			$this->flusher->add_to_delete( $flags, $add_prefix );
		}

		// Fire WordPress action.
		if ( function_exists( 'do_action' ) ) {
			do_action( 'millicache_cache_cleared_by_flags', $flags, $expire );
		}
	}

	/**
	 * Clear cache for entire sites.
	 *
	 * @since 1.0.0
	 *
	 * @param int|array<int>|null $site_ids   Site ID(s) to clear (null for current).
	 * @param int|null            $network_id Network ID.
	 * @param bool                $expire     Expire (true) or delete (false).
	 * @return void
	 */
	public function clear_by_site_ids( $site_ids = null, ?int $network_id = null, bool $expire = false ): void {
		// Resolve sites to flags.
		$flags = $this->resolver->resolve_site_ids( $site_ids, $network_id );

		// Add to flusher queue (no prefix, already includes site prefix with wildcard).
		if ( $expire ) {
			$this->flusher->add_to_expire( $flags, false );
		} else {
			$this->flusher->add_to_delete( $flags, false );
		}

		// Fire WordPress action.
		if ( function_exists( 'do_action' ) ) {
			do_action( 'millicache_cache_cleared_by_sites', $site_ids, $network_id, $expire );
		}
	}

	/**
	 * Clear cache for entire network.
	 *
	 * @since 1.0.0
	 *
	 * @param int|null $network_id Network ID (null for current).
	 * @param bool     $expire     Expire (true) or delete (false).
	 * @return void
	 */
	public function clear_by_network_id( ?int $network_id = null, bool $expire = false ): void {
		$site_ids = $this->resolver->resolve_network_to_sites( $network_id );

		foreach ( $site_ids as $site_id ) {
			$this->clear_by_site_ids( $site_id, $network_id, $expire );
		}

		// Fire WordPress action.
		if ( function_exists( 'do_action' ) ) {
			do_action( 'millicache_cleared_by_network_id', $network_id, $expire );
		}
	}

	/**
	 * Clear all cache across all networks.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $expire Expire (true) or delete (false).
	 * @return void
	 */
	public function clear_all( bool $expire = false ): void {
		$network_ids = $this->resolver->get_all_networks();

		foreach ( $network_ids as $network_id ) {
			$this->clear_by_network_id( $network_id, $expire );
		}

		// Fire WordPress action.
		if ( function_exists( 'do_action' ) ) {
			do_action( 'millicache_cache_cleared', $expire );
		}
	}

	/**
	 * Flush queued invalidations immediately.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if flushed successfully.
	 */
	public function flush(): bool {
		return $this->flusher->flush();
	}

	/**
	 * Flush on shutdown.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function flush_on_shutdown(): void {
		$this->flusher->flush_on_shutdown();
	}
}
