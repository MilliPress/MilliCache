<?php
/**
 * CLI command for cache statistics.
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
 * Stats command.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/CLI
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Stats {

	/**
	 * Get cache statistics.
	 *
	 * ## DESCRIPTION
	 *
	 * Displays cache statistics including entry count, total size, and average size.
	 *
	 * ## OPTIONS
	 *
	 * [--flag=<flag>]
	 * : The flag to search for. Wildcards are allowed. Default: *.
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
	 *     wp millicache stats
	 *     wp millicache stats --flag=1:*
	 *     wp millicache stats --format=json
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
		$flag = $assoc_args['flag'] ?? '*';
		$format = $assoc_args['format'] ?? 'table';
		$size = Admin::get_cache_size( $flag, true );

		// Calculate average size.
		$avg_size = $size['index'] > 0 ? (int) ( $size['size'] / $size['index'] ) : 0;
		$avg_size_human = (string) size_format( $avg_size, $avg_size > 1024 ? 2 : 0 );

		// Build stats data.
		$stats = array(
			'flag'           => $flag,
			'entries'        => $size['index'],
			'size'           => $size['size'],
			'size_human'     => $size['size_human'],
			'avg_size'       => $avg_size,
			'avg_size_human' => $avg_size_human,
		);

		// Output based on format.
		if ( 'json' === $format ) {
			\WP_CLI::line( (string) wp_json_encode( $stats, JSON_PRETTY_PRINT ) );
		} elseif ( 'yaml' === $format ) {
			$yaml = '';
			foreach ( $stats as $key => $value ) {
				$yaml .= sprintf( "%s: %s\n", $key, $value );
			}
			\WP_CLI::line( $yaml );
		} else {
			// Table format.
			$items = array();
			foreach ( $stats as $key => $value ) {
				$items[] = array(
					'property' => $key,
					'value'    => $value,
				);
			}
			\WP_CLI\Utils\format_items( 'table', $items, array( 'property', 'value' ) );
		}
	}
}
