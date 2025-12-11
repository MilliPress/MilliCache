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

use MilliCache\Engine\Cache\Invalidation\Manager as InvalidationManager;
use MilliCache\MilliCache;

! defined( 'ABSPATH' ) && exit;

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
	 * [--url=<url>]
	 * : Comma separated list of URLs.
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
				'url'     => '',
				'flag'    => '',
				'site'    => '',
				'network' => '',
				'related' => false,
				'expire'  => false,
			)
		);

		$expire  = (bool) $assoc_args['expire'];
		$related = (bool) $assoc_args['related'];

		// Warn if --related is used without --id.
		if ( $related && '' === $assoc_args['id'] ) {
			\WP_CLI::warning( esc_html__( 'The --related flag only applies to --id.', 'millicache' ) );
		}

		// Clear the full cache if no arguments are given.
		if ( '' === $assoc_args['id'] && '' === $assoc_args['url'] && '' === $assoc_args['flag'] && '' === $assoc_args['site'] && '' === $assoc_args['network'] ) {
			millicache()->clear()->all( $expire )->execute_queue();
			\WP_CLI::success( is_multisite() ? esc_html__( 'Network cache cleared.', 'millicache' ) : esc_html__( 'Site cache cleared.', 'millicache' ) );
			return;
		}

		$clear    = millicache()->clear();
		$messages = array();

		// Queue network cache clearing.
		if ( '' !== $assoc_args['network'] ) {
			$network_ids = array_map( 'intval', explode( ',', $assoc_args['network'] ) );
			foreach ( $network_ids as $network_id ) {
				$clear->networks( $network_id, $expire );
			}
			$messages[] = sprintf(
				// translators: %s is the number of cleared network IDs.
				esc_html__( 'Cleared cache for %s networks.', 'millicache' ),
				implode( ', ', $network_ids )
			);
		}

		// Queue site cache clearing.
		if ( '' !== $assoc_args['site'] ) {
			$site_ids = array_map( 'intval', explode( ',', $assoc_args['site'] ) );
			foreach ( $site_ids as $site_id ) {
				$clear->sites( $site_id, null, $expire );
			}
			$messages[] = sprintf(
				// translators: %s is the number of cleared site IDs.
				esc_html__( 'Cleared cache for %s sites.', 'millicache' ),
				count( $site_ids )
			);
		}

		// Queue cache clearing by post-IDs.
		if ( '' !== $assoc_args['id'] ) {
			$post_ids = array_map( 'intval', explode( ',', $assoc_args['id'] ) );
			$this->clear_posts( $clear, $post_ids, $expire, $related );
			$messages[] = sprintf(
				// translators: %s is the number of cleared post-IDs.
				esc_html__( 'Cleared cache for %s posts.', 'millicache' ),
				count( $post_ids )
			);
			if ( $related ) {
				$messages[] = esc_html__( 'Included related archives and taxonomies.', 'millicache' );
			}
		}

		// Queue cache clearing by URLs.
		if ( '' !== $assoc_args['url'] ) {
			$urls = array_map( 'trim', explode( ',', $assoc_args['url'] ) );
			foreach ( $urls as $url ) {
				$clear->urls( $url, $expire );
			}
			$messages[] = sprintf(
				// translators: %s is the number of cleared URLs.
				esc_html__( 'Cleared cache for %s URLs.', 'millicache' ),
				count( $urls )
			);
		}

		// Queue cache clearing by flags.
		if ( '' !== $assoc_args['flag'] ) {
			$flags = array_map( 'trim', explode( ',', $assoc_args['flag'] ) );
			foreach ( $flags as $flag ) {
				$clear->flags( $flag, $expire, false );
			}
			$messages[] = sprintf(
				// translators: %s is the number of cleared flags.
				esc_html__( 'Cleared cache for %s flags.', 'millicache' ),
				count( $flags )
			);
		}

		// Execute all queued operations.
		$clear->execute_queue();

		// Output success messages.
		foreach ( $messages as $message ) {
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
					$flags = MilliCache::get_post_related_flags( $post );
					$clear->flags( $flags, $expire );
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
