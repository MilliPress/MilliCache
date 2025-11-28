<?php
/**
 * Pest PHP configuration file.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

// Define ABSPATH for WordPress compatibility.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../' );
}

// Set up autoloading.
require_once __DIR__ . '/../vendor/autoload.php';

// Register custom expectations if needed.
uses()->in( 'Unit' );
