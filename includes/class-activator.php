<?php
/**
 * Fired during plugin activation
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/includes
 */

namespace MilliCache;

! defined( 'ABSPATH' ) && exit;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @subpackage MilliCache/includes
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
		}

		$source_path = dirname( plugin_dir_path( __FILE__ ) );
		$source_file = $source_path . '/advanced-cache.php';
		$destination = WP_CONTENT_DIR . '/advanced-cache.php';

		if ( is_readable( $source_file ) && ! file_exists( $destination ) ) {
			// File does not exist, create symlink.
			if ( symlink( $source_file, $destination ) ) {
				Admin::add_notice(
					'success',
					__( 'Symlink for advanced-cache.php created.', 'millicache' )
				);
			}

			// Could not create symlink, try to copy the file.
			$source_content = file_get_contents( $source_file );
			if ( false !== $source_content ) {
				// Replace the path to the engine file.
				$source_content = str_replace( 'dirname( is_link( __FILE__ ) ? readlink( __FILE__ ) : __FILE__ )', "'" . dirname( __DIR__ ) . "'", $source_content );

				if ( file_put_contents( $destination, $source_content, LOCK_EX ) ) {
					Admin::add_notice( 'success', __( 'advanced-cache.php copied to wp-content directory.', 'millicache' ) );
				}
			}

			// Could not create symlink or copy the file.
			Admin::add_notice(
				'error',
				__( 'Could not create symlink for advanced-cache.php. Please copy the file manually from the plugin directory to your wp-content directory.', 'millicache' )
			);

		} elseif ( ! is_link( $destination ) ) {
			$source_version = Admin::get_file_version( $source_file );
			$destination_version = Admin::get_file_version( $destination );

			if ( $source_version && $destination_version ) {
				if ( version_compare( $source_version, $destination_version ) > 0 ) {
					Admin::add_notice(
						'error',
						__( 'Your version of advanced-cache.php is outdated. Please copy the file manually from the plugin directory to your wp-content directory.', 'millicache' )
					);
				}
			}
		} else {
			Admin::add_notice(
				'error',
				__( 'advanced-cache.php already exists in your wp-content directory. Please remove it and try again.', 'millicache' )
			);
		}
	}
}
