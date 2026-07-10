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
final class Activator {

	/**
	 * Activate the plugin.
	 *
	 * @since    1.0.0
	 *
	 * @param string|null $source_dir Folder of the activating MilliCache copy.
	 *                                Callers should pass their own __DIR__: with
	 *                                two co-resident copies, class-location
	 *                                defaults resolve to whichever copy loaded
	 *                                first, not the one being activated.
	 * @return   void
	 */
	public static function activate( ?string $source_dir = null ) {
		// Create advanced-cache.php.
		self::create_advanced_cache_file( $source_dir );

		// Schedule the cron events.
		self::schedule_events();

		// Set the option autoload to false.
		wp_set_option_autoload( 'millicache', false );
	}

	/**
	 * Schedule the cron events.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public static function schedule_events() {
		if ( ! wp_next_scheduled( 'millicache_nightly' ) ) {
			wp_schedule_event( strtotime( 'tomorrow 3AM' ), 'daily', 'millicache_nightly' );
		}
	}

	/**
	 * Install the advanced-cache.php drop-in for the activating plugin.
	 *
	 * Routes the installation through DropIn::install(), which owns the
	 * higher-version safeguard and the already-correct-symlink shortcut.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @param string|null $source_dir Folder of the activating MilliCache copy,
	 *                                or null for DropIn's class-location default.
	 * @return   void
	 */
	private static function create_advanced_cache_file( ?string $source_dir = null ): void {
		switch ( DropIn::install( 'advanced-cache.php', $source_dir ) ) {
			case 'symlinked':
				Admin::add_notice(
					__( 'Symlink created for advanced-cache.php. Please make sure to configure MilliCache to start caching.', 'millicache' ),
					'success'
				);
				return;
			case 'copied':
				Admin::add_notice(
					__( 'The file advanced-cache.php has been copied to the /wp-content directory. Please make sure to configure MilliCache to start caching.', 'millicache' ),
					'success'
				);
				return;
			case 'unchanged':
				Admin::add_notice( __( 'The advanced-cache.php symlink already exists and is correctly configured.', 'millicache' ) );
				return;
			case 'preserved':
				Admin::add_notice(
					__( 'Your version of advanced-cache.php is higher than the one shipped with the plugin. Replacement skipped to preserve customizations.', 'millicache' ),
					'error'
				);
				return;
			case 'unwritable':
				Admin::add_notice(
					__( 'The wp-content directory is not writable. Please make sure that the directory is writable and try again or manually copy advanced-cache.php from the plugin folder.', 'millicache' ),
					'error'
				);
				return;
			default:
				Admin::add_notice(
					__( 'Could not create symlink for advanced-cache.php. Please copy the file manually from the plugin directory to your /wp-content directory.', 'millicache' ),
					'error'
				);
		}
	}
}
