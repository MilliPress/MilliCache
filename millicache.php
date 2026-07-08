<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://www.millipress.com
 * @since             1.0.0
 * @package           MilliCache
 *
 * @wordpress-plugin
 * Plugin Name:       MilliCache
 * Plugin URI:        https://www.millipress.com/millicache
 * Description:       The most flexible Full Page Cache for scaling WordPress sites. Enterprise-grade in-memory store with Redis, ValKey, Dragonfly, KeyDB, or any alternative.

 * x-release-please-start-version
 * Version:           1.7.0-beta.6
 * x-release-please-end
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Network:           true
 * Author:            MilliPress Team
 * Author URI:        https://www.millipress.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       millicache
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define constants for the plugin.
 *
 * @since 1.0.0
 */
if ( ! defined( 'MILLICACHE_VERSION' ) ) {
	define( 'MILLICACHE_VERSION', '1.7.0-beta.6' ); // x-release-please-version.
}

if ( ! defined( 'MILLICACHE_BASENAME' ) ) {
	define( 'MILLICACHE_BASENAME', plugin_basename( __FILE__ ) );

	if ( ! defined( 'MILLICACHE_FILE' ) ) {
		define( 'MILLICACHE_FILE', __FILE__ );
		define( 'MILLICACHE_DIR', __DIR__ );
	}
}

/**
 * Autoloader.
 *
 * @since 1.0.0
 */
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Load MilliPress API functions.
 *
 * @since 1.0.0
 */
if ( file_exists( __DIR__ . '/functions.php' ) ) {
	require_once __DIR__ . '/functions.php';
}

if ( ! function_exists( 'activate_millicache' ) ) {
	/**
	 * The code that runs during plugin activation.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function activate_millicache() {
		\MilliCache\Admin\Activator::activate();
	}
}

if ( ! function_exists( 'deactivate_millicache' ) ) {
	/**
	 * The code that runs during plugin deactivation.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function deactivate_millicache() {
		\MilliCache\Admin\Deactivator::deactivate();
	}
}

register_activation_hook( __FILE__, 'activate_millicache' );
register_deactivation_hook( __FILE__, 'deactivate_millicache' );

// Begin execution of the plugin.
MilliCache\MilliCache::instance()->run();
