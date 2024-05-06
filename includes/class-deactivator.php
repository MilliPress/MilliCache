<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/includes
 */

namespace MilliCache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @subpackage MilliCache/includes
 * @author     Philipp Wellmer <hello@millipress.com>
 */
class Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public static function deactivate() {
		// Remove the cron events.
		self::unschedule_events();

		// Flush the cache.
		Engine::clear_cache();

		// Remove advanced-cache.php.
		self::remove_advanced_cache_file();
	}

	/**
	 * Remove the cron events.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private static function unschedule_events() {
		wp_clear_scheduled_hook( 'millipress_nightly' );
	}

	/**
	 * Remove the advanced-cache.php file.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private static function remove_advanced_cache_file() {
		$dropin_file = WP_CONTENT_DIR . '/advanced-cache.php';
		$plugin_file = dirname( plugin_dir_path( __FILE__ ) ) . '/advanced-cache.php';

		if ( file_exists( $dropin_file ) ) {
			$dropin_version = Admin::get_file_version( $dropin_file );
			$plugin_version = Admin::get_file_version( $plugin_file );

			// Delete the advanced-cache.php file if it is a symlink or if the version is equal or lower than the plugin version.
			if ( is_link( $dropin_file ) || ( $dropin_version && $plugin_version && version_compare( $dropin_version, $plugin_version ) <= 0 ) ) {
				wp_delete_file( $dropin_file );
				Admin::add_notice( 'success', __( 'MilliCache deactivated & advanced-cache.php removed.', 'millicache' ) );
			} else {
				Admin::add_notice( 'error', __( 'Your version of advanced-cache.php is higher than the original plugin version. We did not delete it, please do it yourself.', 'millicache' ) );
			}
		}
	}
}
