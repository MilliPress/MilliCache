<?php
/**
 * Resolves cache invalidation targets to flags.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage Engine/Cache/Invalidation
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Engine\Cache\Invalidation;

use MilliCache\Engine\Request\Processor as RequestManager;
use MilliCache\Engine\Utilities\Multisite;

! defined( 'ABSPATH' ) && exit;

/**
 * Resolves URLs, post-IDs, and other targets to cache flags.
 *
 * Converts high-level invalidation targets (URLs, post IDs) into
 * low-level cache flags that can be used to clear specific entries.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @subpackage Engine/Cache/Invalidation
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Resolver {

	/**
	 * Request handler for URL hashing.
	 *
	 * @var RequestManager
	 */
	private RequestManager $request_manager;

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
	 * @param RequestManager $request_manager Request handler instance.
	 * @param Multisite      $multisite      Multisite helper instance.
	 */
	public function __construct( RequestManager $request_manager, Multisite $multisite ) {
		$this->request_manager = $request_manager;
		$this->multisite       = $multisite;
	}

	/**
	 * Resolve targets to flags.
	 *
	 * Accepts mixed targets (URLs, post IDs, flags) and resolves them
	 * to appropriate cache flags.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array<string|int> $targets Target(s) to resolve.
	 * @return array<string> Array of resolved flags.
	 */
	public function resolve( $targets ): array {
		// Convert to array.
		if ( ! is_array( $targets ) ) {
			$targets = array( $targets );
		}

		$flags = array();

		foreach ( $targets as $target ) {
			$target_str = (string) $target;

			if ( filter_var( $target_str, FILTER_VALIDATE_URL ) ) {
				// URL target.
				$flags = array_merge( $flags, $this->resolve_url( $target_str ) );
			} elseif ( is_numeric( $target ) ) {
				// Post-ID target.
				$flags = array_merge( $flags, $this->resolve_post_id( (int) $target ) );
			} else {
				// Flag target.
				$flags[] = $target_str;
			}
		}

		return $flags;
	}

	/**
	 * Resolve URL to cache flags.
	 *
	 * Generates flags for both trailing slash and non-trailing slash versions.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url The URL to resolve.
	 * @return array<string> Array of flags for this URL.
	 */
	public function resolve_url( string $url ): array {
		$flags = array();

		// Add URL with trailing slash.
		$flags[] = 'url:' . $this->request_manager->get_url_hash( trailingslashit( $url ) );

		// Add URL without trailing slash.
		$flags[] = 'url:' . $this->request_manager->get_url_hash( untrailingslashit( $url ) );

		return $flags;
	}

	/**
	 * Resolve post-ID to cache flags.
	 *
	 * Generates flags for the post and related feeds.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id The post ID to resolve.
	 * @return array<string> Array of flags for this post.
	 */
	public function resolve_post_id( int $post_id ): array {
		return array(
			"post:$post_id",
			'feed', // Also clear feeds when posts change.
		);
	}

	/**
	 * Resolve site IDs to cache flags.
	 *
	 * Generates wildcard flags for clearing entire sites.
	 *
	 * @since 1.0.0
	 *
	 * @param int|array<int>|null $site_ids   The site ID(s) to resolve.
	 * @param int|null            $network_id The network ID.
	 * @return array<string> Array of flags for these sites.
	 */
	public function resolve_site_ids( $site_ids = null, ?int $network_id = null ): array {
		// Convert to array.
		$site_ids = ! is_array( $site_ids ) ? array( $site_ids ) : $site_ids;

		return array_map(
			function ( $site_id ) use ( $network_id ) {
				return $this->multisite->get_flag_prefix( $site_id, $network_id ) . '*';
			},
			$site_ids
		);
	}

	/**
	 * Resolve network ID to site IDs.
	 *
	 * @since 1.0.0
	 *
	 * @param int|null $network_id The network ID.
	 * @return array<int> Array of site IDs in this network.
	 */
	public function resolve_network_to_sites( ?int $network_id = null ): array {
		if ( ! $this->multisite->is_enabled() ) {
			return array( 1 ); // Default site ID.
		}

		$network_id = $network_id ?? get_current_network_id();
		return $this->multisite->get_site_ids( $network_id );
	}

	/**
	 * Get all network IDs.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int> Array of network IDs.
	 */
	public function get_all_networks(): array {
		return $this->multisite->get_network_ids();
	}

	/**
	 * Check if a target is a URL.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $target The target to check.
	 * @return bool True if target is a URL.
	 */
	public function is_url( $target ): bool {
		return is_string( $target ) && filter_var( $target, FILTER_VALIDATE_URL ) !== false;
	}

	/**
	 * Check if a target is a post ID.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $target The target to check.
	 * @return bool True if target is a post ID.
	 */
	public function is_post_id( $target ): bool {
		return is_numeric( $target ) && $target > 0;
	}

	/**
	 * Check if a target is a flag.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $target The target to check.
	 * @return bool True if target is a flag (string, not URL, not numeric).
	 */
	public function is_flag( $target ): bool {
		return is_string( $target ) && ! $this->is_url( $target );
	}
}
