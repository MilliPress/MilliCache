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

use MilliCache\Admin\Utils;

! defined( 'ABSPATH' ) && exit;

/**
 * Stats command.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/CLI
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Stats {

	use OutputTrait;

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
		$size = Utils::get_cache_size( $flag, true );

		// Average uses gross bytes — that's what each entry "weighs" pre-dedup.
		$avg = $size['index'] > 0 ? (int) ( $size['gross'] / $size['index'] ) : 0;
		$avg_human = (string) size_format( $avg, $avg > 1048576 ? 2 : 0 );

		// Dedup ratio: gross / size. 1.00 means no sharing.
		$dedup = $size['size'] > 0 ? round( $size['gross'] / $size['size'], 2 ) : 1.0;

		$stats = array(
			'flag'          => $flag,
			'entries'       => $size['index'],
			'size'          => $size['size'],
			'size_human'    => $size['size_human'],
			'gross'         => $size['gross'],
			'gross_human'   => $size['gross_human'],
			'unique'        => $size['unique'],
			'largest'       => $size['largest'],
			'largest_human' => $size['largest_human'],
			'dedup'         => $dedup,
			'avg'           => $avg,
			'avg_human'     => $avg_human,
		);

		$items = $this->build_rows_from_array( $stats, 'property', 'value' );
		$this->output_items( $items, $stats, $format, array( 'property', 'value' ) );
	}
}
