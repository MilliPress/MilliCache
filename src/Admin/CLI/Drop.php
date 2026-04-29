<?php
/**
 * CLI command for creating and fixing advanced-cache.php drop-in.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/CLI
 */

namespace MilliCache\Admin\CLI;

use MilliCache\Admin\DropIn;

! defined( 'ABSPATH' ) && exit;

/**
 * Drop command.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/CLI
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Drop {

	/**
	 * Fix or reinstall the advanced-cache.php drop-in.
	 *
	 * ## DESCRIPTION
	 *
	 * Removes and recreates the advanced-cache.php file in wp-content.
	 * Useful for CD workflows where symlinks may break.
	 *
	 * ## OPTIONS
	 *
	 * [--force]
	 * : Force reinstall even if the current version matches.
	 *
	 * ## EXAMPLES
	 *
	 *     wp millicache drop
	 *     wp millicache drop --force
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
		$force = isset( $assoc_args['force'] );

		switch ( DropIn::install( 'advanced-cache.php', null, $force ) ) {
			case 'symlinked':
				\WP_CLI::success( __( 'Created symlink for advanced-cache.php.', 'millicache' ) );
				return;
			case 'copied':
				\WP_CLI::success( __( 'Copied advanced-cache.php to wp-content directory.', 'millicache' ) );
				return;
			case 'unchanged':
				\WP_CLI::success( __( 'advanced-cache.php symlink is already correctly configured.', 'millicache' ) );
				return;
			case 'preserved':
				\WP_CLI::warning( __( 'A higher-version advanced-cache.php is in place. Use --force to overwrite.', 'millicache' ) );
				return;
			case 'unwritable':
				\WP_CLI::error( __( 'The wp-content directory is not writable.', 'millicache' ) );
				// WP_CLI::error() halts execution — fallthrough is unreachable.
			default:
				\WP_CLI::error( __( 'Could not create advanced-cache.php file.', 'millicache' ) );
		}
	}
}
