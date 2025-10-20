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

 * Version:           1.0.0-rc.2
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
define( 'MILLICACHE_VERSION', '1.0.0-rc.2' );

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
 * The code that runs during plugin activation.
 *
 * @since 1.0.0
 *
 * @return void
 */
function activate_millicache() {
	\MilliCache\Admin\Activator::activate();
}

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

register_activation_hook( MILLICACHE_FILE, 'activate_millicache' );
register_deactivation_hook( MILLICACHE_FILE, 'deactivate_millicache' );

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 *
 * @return void
 */
function run_millicache() {
	$plugin = new MilliCache\MilliCache();
	$plugin->run();

	/**
	 * Fires when MilliCache is fully loaded and ready.
	 *
	 * Use this hook to register custom rules, conditions, and actions.
	 *
	 * @since 1.1.0
	 */
	do_action( 'millicache_loaded' );
}

run_millicache();
