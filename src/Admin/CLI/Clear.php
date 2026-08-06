<?php
/**
 * CLI command for clearing cache.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/CLI
 */

namespace MilliCache\Admin\CLI;

use MilliCache\Admin\Utils;
use MilliCache\Engine\Cache\Invalidation\Manager as InvalidationManager;
use MilliCache\MilliCache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clear cache command.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/CLI
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Clear {

	/**
	 * Clear the cache.
	 *
	 * ## OPTIONS
	 *
	 * [--id=<id>]
	 * : Comma separated list of post IDs.
	 *
	 * [--uri=<uri>]
	 * : Comma separated list of URIs (paths or full URLs). Use the global
	 *   `--url=<site-url>` to scope to a specific site on multisite.
	 *
	 * [--flag=<flag>]
	 * : Comma separated list of flags.
	 *
	 * [--site=<site>]
	 * : Comma separated list of site IDs.
	 *
	 * [--network=<network>]
	 * : Comma separated list of network IDs.
	 *
	 * [--related]
	 * : Also clear related content (archives, taxonomies, author, home, feed). Only applies to --id.
	 *
	 * [--expire]
	 * : Expire the cache instead of deleting. Default is false.
	 *
	 * ## EXAMPLES
	 *
	 *     # Clear specific posts
	 *     wp millicache clear --id=1,2,3
	 *
	 *     # Clear posts with related archives and taxonomies
	 *     wp millicache clear --id=123 --related
	 *
	 * @when after_wp_load
	 *
	 * @since 1.0.0
	 *
	 * @param array<string> $args The list of arguments.
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		$assoc_args = wp_parse_args(
			$assoc_args,
			array(
				'id'      => '',
				'uri'     => '',
				'flag'    => '',
				'site'    => '',
				'network' => '',
				'related' => false,
				'expire'  => false,
			)
		);

		$expire  = (bool) $assoc_args['expire'];
		$related = (bool) $assoc_args['related'];

		if ( $related && '' === $assoc_args['id'] ) {
			\WP_CLI::warning( esc_html__( 'The --related flag only applies to --id.', 'millicache' ) );
		}

		$clear = millicache()->clear();

		// No arguments means a full clear.
		if ( '' === $assoc_args['id'] && '' === $assoc_args['uri'] && '' === $assoc_args['flag'] && '' === $assoc_args['site'] && '' === $assoc_args['network'] ) {
			$clear->all( $expire );
		}

		if ( '' !== $assoc_args['network'] ) {
			foreach ( array_map( 'intval', explode( ',', $assoc_args['network'] ) ) as $network_id ) {
				$clear->networks( $network_id, $expire );
			}
		}

		if ( '' !== $assoc_args['site'] ) {
			foreach ( array_map( 'intval', explode( ',', $assoc_args['site'] ) ) as $site_id ) {
				$clear->sites( $site_id, null, $expire );
			}
		}

		if ( '' !== $assoc_args['id'] ) {
			$this->clear_posts( $clear, array_map( 'intval', explode( ',', $assoc_args['id'] ) ), $expire, $related );
		}

		if ( '' !== $assoc_args['uri'] ) {
			foreach ( array_map( 'trim', explode( ',', $assoc_args['uri'] ) ) as $url ) {
				$clear->urls( $url, $expire );
			}
		}

		if ( '' !== $assoc_args['flag'] ) {
			foreach ( array_map( 'trim', explode( ',', $assoc_args['flag'] ) ) as $flag ) {
				$clear->flags( $flag, $expire, false );
			}
		}

		$cleared = $clear->execute_queue();
		$message = esc_html( Utils::cleared_entries_message( $cleared, $expire ) );

		if ( 0 === $cleared ) {
			\WP_CLI::warning( $message );
		} else {
			\WP_CLI::success( $message );
		}
	}

	/**
	 * Clear cache for posts, optionally including related content.
	 *
	 * @since 1.0.0
	 *
	 * @param InvalidationManager $clear    The invalidation manager.
	 * @param array<int>          $post_ids The post-IDs to clear.
	 * @param bool                $expire   Expire (true) or delete (false).
	 * @param bool                $related  Include related content.
	 * @return void
	 */
	private function clear_posts( InvalidationManager $clear, array $post_ids, bool $expire, bool $related ): void {
		if ( $related ) {
			// Clear with related content (archives, taxonomies, author, etc.).
			foreach ( $post_ids as $post_id ) {
				$post = get_post( $post_id );
				if ( $post ) {
					MilliCache::instance()->clear_post_and_related_cache( $post, $expire );
				} else {
					// Post not found, fall back to basic clearing.
					$clear->posts( $post_id, $expire );
				}
			}
		} else {
			// Clear just the post and feeds.
			foreach ( $post_ids as $post_id ) {
				$clear->posts( $post_id, $expire );
			}
		}
	}

}
