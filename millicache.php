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
 * Version:           1.7.4
 * x-release-please-end
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Network:           true
 * Author:            MilliPress Team
 * Author URI:        https://www.millipress.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       millicache
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
	define( 'MILLICACHE_VERSION', '1.7.4' ); // x-release-please-version.
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

/*
 * Closures, not shared function names: with two co-resident copies each hook
 * must install the drop-in of its own folder — __DIR__ pins that.
 */
register_activation_hook(
	__FILE__,
	static function () {
		\MilliCache\Admin\Activator::activate( __DIR__ );
	}
);
register_deactivation_hook(
	__FILE__,
	static function () {
		\MilliCache\Admin\Deactivator::deactivate();
	}
);

// Begin execution of the plugin.
MilliCache\MilliCache::instance()->run();
