<?php
/**
 * Fired during plugin activation
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin
 */

namespace MilliCache\Admin;

use MilliCache\Core\Settings;

! defined( 'ABSPATH' ) && exit;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @subpackage MilliCache/Admin
 * @author     Philipp Wellmer <hello@millipress.com>
 */
class Activator {

	/**
	 * Activate the plugin.
	 *
	 * @since    1.0.0
	 *
	 * @return   void
	 */
	public static function activate() {
		// Create advanced-cache.php.
		self::create_advanced_cache_file();

		// Schedule the cron events.
		self::schedule_events();

		// Set the option autoload to false.
		wp_set_option_autoload( Settings::$option_name, false );
	}

	/**
	 * Schedule the cron events.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private static function schedule_events() {
		if ( ! wp_next_scheduled( 'millipress_nightly' ) ) {
			wp_schedule_event( strtotime( 'tomorrow 3AM' ), 'daily', 'millipress_nightly' );
		}
	}

	/**
	 * Create a symlink for advanced-cache.php.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private static function create_advanced_cache_file(): void {

		if ( ! is_writable( WP_CONTENT_DIR ) ) {
			Admin::add_notice(
				'error',
				__( 'The wp-content directory is not writable. Please make sure that the directory is writable and try again or manually copy advanced-cache.php from the plugin folder.', 'millicache' )
			);

			return;
		}

		$source_file = MILLICACHE_DIR . '/advanced-cache.php';
		$destination = WP_CONTENT_DIR . '/advanced-cache.php';

		if ( file_exists( $destination ) ) {
			if ( is_link( $destination ) ) {
				if ( readlink( $destination ) === $source_file ) {
					Admin::add_notice( __( 'The advanced-cache.php symlink already exists and is correctly configured.', 'millicache' ) );
					return;
				} else {
					unlink( $destination );
				}
			} else {
				$source_version = Admin::get_file_version( $source_file );
				$destination_version = Admin::get_file_version( $destination );

				if ( $source_version && $destination_version ) {
					if ( version_compare( $source_version, $destination_version ) > 0 ) {
						Admin::add_notice(
							__( 'Your version of advanced-cache.php is outdated. Please copy the file manually from the plugin directory to your wp-content directory.', 'millicache' ),
							'error'
						);
					}
				}
				return;
			}
		}

		// At this point, either there's no file or we've removed an incorrect symlink.
		if ( is_readable( $source_file ) ) {
			// Try to create a symlink first.
			if ( @symlink( $source_file, $destination ) ) {
				Admin::add_notice(
					__( 'Symlink created for advanced-cache.php. Please make sure to configure MilliCache to start caching.', 'millicache' ),
					'success'
				);
				return;
			} else {
				// Could not create symlink, try to copy the file.
				$source_content = file_get_contents( $source_file );
				if ( false !== $source_content ) {
					// Replace the path to the engine file.
					$source_content = preg_replace(
						'/(\$engine_path\s*=\s*)dirname.*?;/s',
						"$1'" . dirname( __DIR__ ) . "';",
						$source_content
					);

					if ( file_put_contents( $destination, $source_content, LOCK_EX ) ) {
						Admin::add_notice( __( 'The file advanced-cache.php has been copied to the /wp-content directory. Please make sure to configure MilliCache to start caching.', 'millicache' ), 'success' );
						return;
					}
				}
			}

			// Could not create symlink or copy the file.
			Admin::add_notice(
				__( 'Could not create symlink for advanced-cache.php. Please copy the file manually from the plugin directory to your /wp-content directory.', 'millicache' ),
				'error'
			);
		}
	}
}
