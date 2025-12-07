<?php
/**
 * Drop-in plugin file for MilliCache, enabling advanced WordPress caching.
 *
 * This file (advanced-cache.php) is placed in wp-content by MilliCache during activation.
 * It can be either a symlink or a copy, depending on server support.
 *
 * It initializes the caching engine by loading the autoloader, creating a \MilliCache\Engine instance, and calling start().
 *
 *
 * Description: MilliCache Drop-in file, enabling advanced WordPress caching.
 * Plugin URI:  https://www.millipress.com/cache
 * Version:     1.1.0
 * Author:      Philipp Wellmer <hello@millipress.com>
 *
 * @package MilliCache
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$engine_path = dirname( is_link( __FILE__ ) ? (string) readlink( __FILE__ ) : __FILE__ );
$engine_file = realpath( $engine_path . '/src/Engine.php' );

if ( file_exists( (string) $engine_file ) ) {
	require_once $engine_file;

	// Start the caching engine.
	\MilliCache\Engine::instance()->start();
}
