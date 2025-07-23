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
 * Description:       Redis Full Page Cache for WordPress
 * Version:           1.0.0-beta.5
 * Network:           true
 * Author:            MilliPress Team
 * Author URI:        https://www.millipress.com/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
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
define( 'MILLICACHE_VERSION', '1.0.0-beta.5' );

if ( ! defined( 'MILLICACHE_BASENAME' ) ) {
	define( 'MILLICACHE_BASENAME', plugin_basename( __FILE__ ) );

	if ( ! defined( 'MILLICACHE_FILE' ) ) {
		define( 'MILLICACHE_FILE', __FILE__ );
		define( 'MILLICACHE_DIR', __DIR__ );
	}
}

/**
 * The code that runs during plugin activation.
 *
 * @since 1.0.0
 *
 * @return void
 */
function activate_millicache() {
	require_once MILLICACHE_DIR . '/includes/class-activator.php';
	MilliCache\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 *
 * @since 1.0.0
 *
 * @return void
 */
function deactivate_millicache() {
	require_once MILLICACHE_DIR . '/includes/class-deactivator.php';
	MilliCache\Deactivator::deactivate();
}

register_activation_hook( MILLICACHE_FILE, 'activate_millicache' );
register_deactivation_hook( MILLICACHE_FILE, 'deactivate_millicache' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require MILLICACHE_DIR . '/includes/class-millicache.php';

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
}

run_millicache();
