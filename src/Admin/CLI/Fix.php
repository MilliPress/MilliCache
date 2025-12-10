<?php
/**
 * CLI command for fixing advanced-cache.php drop-in.
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
 * Fix command.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/CLI
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Fix {

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
	 *     wp millicache fix
	 *     wp millicache fix --force
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
		$destination = WP_CONTENT_DIR . '/advanced-cache.php';
		$source = MILLICACHE_DIR . '/advanced-cache.php';

		// Check current status.
		if ( file_exists( $destination ) && ! $force ) {
			$info = Admin::validate_advanced_cache_file();
			if ( ! empty( $info ) && 'symlink' === $info['type'] ) {
				$target = readlink( $destination );
				if ( $target === $source ) {
					\WP_CLI::success( __( 'advanced-cache.php symlink is already correctly configured.', 'millicache' ) );
					return;
				}
			}
		}

		// Check if wp-content is writable.
		if ( ! is_writable( WP_CONTENT_DIR ) ) {
			\WP_CLI::error( __( 'The wp-content directory is not writable.', 'millicache' ) );
		}

		// Remove existing file.
		if ( file_exists( $destination ) || is_link( $destination ) ) {
			if ( ! unlink( $destination ) ) {
				\WP_CLI::error( __( 'Could not remove existing advanced-cache.php file.', 'millicache' ) );
			}
			\WP_CLI::line( __( 'Removed existing advanced-cache.php.', 'millicache' ) );
		}

		// Check source file.
		if ( ! is_readable( $source ) ) {
			\WP_CLI::error( __( 'Source advanced-cache.php file is not readable.', 'millicache' ) );
		}

		// Try to create symlink first.
		if ( @symlink( $source, $destination ) ) {
			\WP_CLI::success( __( 'Created symlink for advanced-cache.php.', 'millicache' ) );
			return;
		}

		// Fallback: copy file with path replacement.
		$source_content = file_get_contents( $source );
		if ( false === $source_content ) {
			\WP_CLI::error( __( 'Could not read source advanced-cache.php file.', 'millicache' ) );
		}

		// Replace the path to the engine file.
		$source_content = preg_replace(
			'/(\$engine_path\s*=\s*)dirname.*?;/s',
			"$1'" . dirname( __DIR__, 2 ) . "';",
			$source_content
		);

		if ( file_put_contents( $destination, $source_content, LOCK_EX ) ) {
			\WP_CLI::success( __( 'Copied advanced-cache.php to wp-content directory.', 'millicache' ) );
		} else {
			\WP_CLI::error( __( 'Could not create advanced-cache.php file.', 'millicache' ) );
		}
	}
}
