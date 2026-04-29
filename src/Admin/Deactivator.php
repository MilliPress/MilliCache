<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin
 */

namespace MilliCache\Admin;

use MilliCache\Engine;

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
 * @subpackage MilliCache/Admin
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Deactivator {

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

		// Reset the cache.
		Engine::instance()->clear()->all();

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
		wp_clear_scheduled_hook( 'millicache_nightly' );
	}

	/**
	 * Remove the advanced-cache.php file.
	 *
	 * DropIn::remove() owns the customization safeguard; this method maps the
	 * outcome to an admin notice.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private static function remove_advanced_cache_file() {
		switch ( DropIn::remove() ) {
			case 'removed':
				Admin::add_notice( __( 'MilliCache deactivated & advanced-cache.php removed.', 'millicache' ), 'success' );
				return;
			case 'preserved':
				Admin::add_notice( __( 'Your version of advanced-cache.php is higher than the original plugin version. We did not delete it, please do it yourself.', 'millicache' ), 'error' );
				return;
			case 'absent':
				return;
			case 'unwritable':
				Admin::add_notice( __( 'The wp-content directory is not writable. Please remove advanced-cache.php manually.', 'millicache' ), 'error' );
				return;
			default:
				Admin::add_notice( __( 'Could not remove advanced-cache.php. Please remove it manually.', 'millicache' ), 'error' );
		}
	}
}
