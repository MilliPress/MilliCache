<?php
/**
 * CLI command for plugin status.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/CLI
 */

namespace MilliCache\Admin\CLI;

use MilliCache\Admin\Admin;

! defined( 'ABSPATH' ) && exit;

/**
 * Status command.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/CLI
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Status {

	/**
	 * Show the plugin and cache status.
	 *
	 * ## DESCRIPTION
	 *
	 * Displays comprehensive status information including Redis connection,
	 * server info, advanced-cache.php status, and cache statistics.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Output format. Default: table.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - json
	 *   - yaml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp millicache status
	 *     wp millicache status --format=json
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
		$format = $assoc_args['format'] ?? 'table';

		$status = array();

		// Plugin version.
		$status['plugin_version'] = defined( 'MILLICACHE_VERSION' ) ? MILLICACHE_VERSION : '1.0.0';

		// WP_CACHE constant.
		$status['wp_cache'] = defined( 'WP_CACHE' ) && WP_CACHE ? 'enabled' : 'disabled';

		// Advanced-cache.php status.
		$dropin_info = Admin::validate_advanced_cache_file();
		if ( empty( $dropin_info ) ) {
			$status['advanced_cache'] = 'missing';
		} else {
			$status['advanced_cache'] = $dropin_info['type'];
			if ( ! empty( $dropin_info['outdated'] ) ) {
				$status['advanced_cache'] .= ' (outdated)';
			}
		}

		// Storage/Redis status.
		$storage = millicache()->storage();
		$storage_status = $storage->get_status();

		$status['storage_connected'] = $storage_status['connected'] ? 'yes' : 'no';

		if ( ! empty( $storage_status['error'] ) ) {
			$status['storage_error'] = $storage_status['error'];
		}

		if ( $storage_status['connected'] ) {
			// Server version.
			if ( ! empty( $storage_status['info']['Server']['version'] ) ) {
				$status['storage_server'] = $storage_status['info']['Server']['version'];
			}

			// Memory info.
			if ( ! empty( $storage_status['info']['Memory']['used_memory_human'] ) ) {
				$status['storage_memory_used'] = $storage_status['info']['Memory']['used_memory_human'];
			}
			if ( ! empty( $storage_status['info']['Memory']['maxmemory_human'] ) && '0B' !== $storage_status['info']['Memory']['maxmemory_human'] ) {
				$status['storage_memory_max'] = $storage_status['info']['Memory']['maxmemory_human'];
			}
		}

		// Cache statistics.
		$flag = millicache()->flags()->get_prefix( is_multisite() && is_network_admin() ? '*' : null ) . '*';
		$cache_size = Admin::get_cache_size( $flag, true );
		$status['cache_entries'] = $cache_size['index'];
		$status['cache_size'] = $cache_size['size_human'];

		// Output based on format.
		if ( 'json' === $format ) {
			\WP_CLI::line( (string) wp_json_encode( $status, JSON_PRETTY_PRINT ) );
		} elseif ( 'yaml' === $format ) {
			$yaml = '';
			foreach ( $status as $key => $value ) {
				$yaml .= sprintf( "%s: %s\n", $key, $value );
			}
			\WP_CLI::line( $yaml );
		} else {
			// Table format.
			$items = array();
			foreach ( $status as $key => $value ) {
				$items[] = array(
					'property' => $key,
					'status'   => $value,
				);
			}
			\WP_CLI\Utils\format_items( 'table', $items, array( 'property', 'status' ) );
		}
	}
}
