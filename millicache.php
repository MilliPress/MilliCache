<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://www.milli.press
 * @since             1.0.0
 * @package           Millicache
 *
 * @wordpress-plugin
 * Plugin Name:       MilliCache
 * Plugin URI:        https://www.milli.press/cache
 * Description:       Redis Full Page Cache for WordPress
 * Version:           1.0.0-beta.1
 * Author:            MilliPress Team
 * Author URI:        https://www.milli.press/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       millicache
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'MILLICACHE_VERSION', '1.0.0-beta.1' );

/**
 * The code that runs during plugin activation.
 */
function activate_millicache() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-millicache-activator.php';
	Millicache_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_millicache() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-millicache-deactivator.php';
	Millicache_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_millicache' );
register_deactivation_hook( __FILE__, 'deactivate_millicache' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-millicache.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_millicache() {

	$plugin = new Millicache();
	$plugin->run();
}
run_millicache();
