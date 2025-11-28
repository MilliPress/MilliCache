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

// Close Mockery after each test to prevent memory leaks and test pollution.
uses()
	->afterEach( function () {
		Mockery::close();
	} )
	->in( 'Unit' );
