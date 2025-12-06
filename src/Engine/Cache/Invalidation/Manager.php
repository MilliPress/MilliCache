<?php
/**
 * Cache invalidation management orchestrator.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage Engine/Cache/Invalidation
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Engine\Cache\Invalidation;

use MilliCache\Core\Storage;
use MilliCache\Engine\Request\Processor as RequestManager;
use MilliCache\Engine\Utilities\Multisite;

! defined( 'ABSPATH' ) && exit;

/**
 * Orchestrates cache invalidation operations.
 *
 * High-level API for clearing cache by various targets (URLs, posts, flags, sites).
 * Delegates to Resolver and Queue for the actual work.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @subpackage Engine/Cache/Invalidation
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Manager {

	/**
	 * Target resolver.
	 *
	 * @var Resolver
	 */
	private Resolver $resolver;

	/**
	 * Clearing queue.
	 *
	 * @var Queue
	 */
	private Queue $queue;

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
	 * @param RequestManager $request_manager Request manager for URL hashing.
	 * @param Multisite      $multisite       Multisite helper.
	 * @param int            $default_ttl     Default TTL for expiration.
	 */
	public function __construct(
		Storage $storage,
		RequestManager $request_manager,
		Multisite $multisite,
		int $default_ttl = 3600
	) {
		$this->multisite = $multisite;
		$this->resolver  = new Resolver( $request_manager, $multisite );
		$this->queue     = new Queue( $storage, $multisite, $default_ttl );
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
	 * Get clearing queue.
	 *
	 * @since 1.0.0
	 *
	 * @return Queue The queue instance.
	 */
	public function get_queue(): Queue {
		return $this->queue;
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
	public function targets( $targets, bool $expire = false ): void {
		// Convert to array.
		if ( ! is_array( $targets ) ) {
			$targets = array( $targets );
		}

		// Empty targets means clear entire site.
		if ( empty( $targets ) ) {
			$this->sites();
			return;
		}

		// Resolve each target and clear.
		foreach ( $targets as $target ) {
			$target_str = (string) $target;

			if ( $this->resolver->is_url( $target_str ) ) {
				// Only clear URLs from current site.
				if ( function_exists( 'get_home_url' ) && strpos( $target_str, get_home_url() ) === 0 ) {
					$this->urls( $target_str, $expire );
				}
			} elseif ( $this->resolver->is_post_id( $target_str ) ) {
				$this->posts( (int) $target, $expire );
			} else {
				// Flag - limit to current site if not network admin.
				$add_prefix = $this->multisite->is_enabled() &&
							  function_exists( 'is_network_admin' ) &&
							  ! is_network_admin();
				$this->flags( $target_str, $expire, $add_prefix );
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
	public function urls( $urls, bool $expire = false ): void {
		// Convert to array.
		$urls = is_string( $urls ) ? array( $urls ) : $urls;

		// Resolve URLs to flags.
		$flags = array();
		foreach ( $urls as $url ) {
			$flags = array_merge( $flags, $this->resolver->resolve_url( $url ) );
		}

		// Add to flusher queue.
		if ( $expire ) {
			$this->queue->add_to_expire( $flags, false );
		} else {
			$this->queue->add_to_delete( $flags, false );
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
	public function posts( $post_ids, bool $expire = false ): void {
		// Convert to array.
		$post_ids = ! is_array( $post_ids ) ? array( $post_ids ) : $post_ids;

		// Resolve post-IDs to flags.
		$flags = array();
		foreach ( $post_ids as $post_id ) {
			$flags = array_merge( $flags, $this->resolver->resolve_post_id( $post_id ) );
		}

		// Add to clearer queue.
		$this->flags( $flags, $expire );

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
	public function flags( $flags, bool $expire = false, bool $add_prefix = true ): void {
		// Convert to array.
		$flags = is_string( $flags ) ? array( $flags ) : $flags;

		// Add to flusher queue.
		if ( $expire ) {
			$this->queue->add_to_expire( $flags, $add_prefix );
		} else {
			$this->queue->add_to_delete( $flags, $add_prefix );
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
	public function sites( $site_ids = null, ?int $network_id = null, bool $expire = false ): void {
		// Resolve sites to flags.
		$flags = $this->resolver->resolve_site_ids( $site_ids, $network_id );

		// Add to flusher queue (no prefix, already includes site prefix with wildcard).
		if ( $expire ) {
			$this->queue->add_to_expire( $flags, false );
		} else {
			$this->queue->add_to_delete( $flags, false );
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
	public function network( ?int $network_id = null, bool $expire = false ): void {
		$site_ids = $this->resolver->resolve_network_to_sites( $network_id );

		foreach ( $site_ids as $site_id ) {
			$this->sites( $site_id, $network_id, $expire );
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
	public function all( bool $expire = false ): void {
		$network_ids = $this->resolver->get_all_networks();

		foreach ( $network_ids as $network_id ) {
			$this->network( $network_id, $expire );
		}

		// Fire WordPress action.
		if ( function_exists( 'do_action' ) ) {
			do_action( 'millicache_cache_cleared', $expire );
		}
	}
}
