<?php
/**
 * This is the drop-in plugin file for the MilliCache plugin, responsible for advanced WordPress caching.
 *
 * This file, advanced-cache.php, is automatically placed in the wp-content directory by the MilliCache plugin during its installation.
 * WordPress recognizes this file and loads it during its initialization process.
 *
 * The file can either be a symlink to the actual advanced-cache.php file located in the MilliCache plugin directory (if the hosting environment supports symlinks),
 * or it can be a direct copy of that file.
 *
 * This file is crucial for the operation of the MilliCache plugin as it kickstarts the caching engine.
 * If this file is missing or not loaded correctly, the caching engine will not start, and the MilliCache plugin will not function as expected.
 *
 * The caching engine is defined in the '/includes/class-millicache-engine.php' file in the plugin folder, which is required by this file.
 * The caching engine is started by invoking the static method start() on the 'Millicache_Engine' class.
 *
 * Version:           1.0.0
 * Author:            MilliPress Team
 *
 * @package Millicache
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$engine_path = dirname( is_link( __FILE__ ) ? readlink( __FILE__ ) : __FILE__ );
$engine_file = realpath( $engine_path . '/includes/class-millicache-engine.php' );

if ( file_exists( $engine_file ) ) {
	require_once $engine_file;

	if ( class_exists( 'MilliCache_Engine' ) ) {
		Millicache_Engine::start();
	}
}
