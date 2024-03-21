<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://www.milli.press
 * @since      1.0.0
 *
 * @package    Millicache
 * @subpackage Millicache/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Millicache
 * @subpackage Millicache/includes
 * @author     Philipp Wellmer <hello@milli.press>
 */
class Millicache_Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public static function deactivate() {
		// Flush the cache.
		Millicache_Engine::clear_cache();

		// Remove advanced-cache.php.
		self::remove_advanced_cache_file();
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
			$dropin_version = Millicache_Admin::get_file_version( $dropin_file );
			$plugin_version = Millicache_Admin::get_file_version( $plugin_file );

			// Delete the advanced-cache.php file if it is a symlink or if the version is equal or lower than the plugin version.
			if ( is_link( $dropin_file ) || ( $dropin_version && $plugin_version && version_compare( $dropin_version, $plugin_version ) <= 0 ) ) {
				wp_delete_file( $dropin_file );
				Millicache_Admin::add_notice( 'success', __( 'Plugin deactivated & advanced-cache.php removed.', 'millicache' ) );
			} else {
				Millicache_Admin::add_notice( 'error', __( 'Your version of advanced-cache.php is higher than the original plugin version. We did not delete it, please do it yourself.', 'millicache' ) );
			}
		}
	}
}
