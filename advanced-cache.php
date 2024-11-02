<?php
/**
 * Drop-in plugin file for MilliCache, enabling advanced WordPress caching.
 *
 * This file (advanced-cache.php) is placed in wp-content by MilliCache during activation.
 * It can be either a symlink or a copy, depending on server support.
 *
 * It initializes the caching engine defined in '/includes/class-millicache-engine.php' by invoking \\MilliCache\\Engine::start().
 *
 *
 * Description: MilliCache Drop-in file, enabling advanced WordPress caching.
 * Plugin URI:  https://www.millipress.com/cache
 * Version:     1.0.0
 * Author:      MilliPress Team
 *
 * @package MilliCache
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$engine_path = dirname( is_link( __FILE__ ) ? (string) readlink( __FILE__ ) : __FILE__ );
$engine_file = realpath( $engine_path . '/includes/class-engine.php' );

if ( file_exists( (string) $engine_file ) ) {
	require_once $engine_file;

	if ( class_exists( '\MilliCache\Engine' ) ) {
		\MilliCache\Engine::start();
	}
}
