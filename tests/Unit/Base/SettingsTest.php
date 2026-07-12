<?php
/**
 * Tests for Settings integration.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Base\Network;
use MilliCache\Base\Site;

// Mock WordPress constants.
if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}
if ( ! defined( 'MONTH_IN_SECONDS' ) ) {
	define( 'MONTH_IN_SECONDS', 2592000 );
}
if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}
if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', '/tmp/wp-content' );
}
if ( ! defined( 'AUTH_KEY' ) ) {
	define( 'AUTH_KEY', 'test-auth-key' );
}
if ( ! defined( 'SECURE_AUTH_KEY' ) ) {
	define( 'SECURE_AUTH_KEY', 'test-secure-auth-key' );
}

// Mock WordPress functions.
if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $hook, $value ) {
		return $value;
	}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( $hook, $callback, $priority = 10, $args = 1 ) {
		return true;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $hook, $callback, $priority = 10, $args = 1 ) {
		return true;
	}
}

if ( ! function_exists( 'register_setting' ) ) {
	function register_setting( $group, $name, $args ) {
		return true;
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $name, $default = false ) {
		return $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( $name, $value ) {
		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	function delete_option( $name ) {
		return true;
	}
}

if ( ! function_exists( 'set_transient' ) ) {
	function set_transient( $name, $value, $expiration ) {
		return true;
	}
}

if ( ! function_exists( 'get_transient' ) ) {
	function get_transient( $name ) {
		return false;
	}
}

if ( ! function_exists( 'delete_transient' ) ) {
	function delete_transient( $name ) {
		return true;
	}
}

if ( ! function_exists( 'wp_mkdir_p' ) ) {
	function wp_mkdir_p( $path ) {
		return true;
	}
}

if ( ! function_exists( 'sanitize_file_name' ) ) {
	function sanitize_file_name( $name ) {
		return $name;
	}
}

if ( ! function_exists( 'do_action' ) ) {
	function do_action( $hook, ...$args ) {
		return true;
	}
}

describe( 'Settings', function () {

	it( 'returns a MilliBase Settings instance', function () {
		$settings = Site::settings();

		expect( $settings )->toBeInstanceOf( \MilliBase\Settings::class );
	} );

	it( 'returns the same instance on repeated calls', function () {
		$settings1 = Site::settings();
		$settings2 = Site::settings();

		expect( $settings1 )->toBe( $settings2 );
	} );

	it( 'provides default settings', function () {
		$defaults = Site::settings()->get_default_settings();

		expect( $defaults )->toBeArray();
		expect( $defaults )->toHaveKeys( array( 'storage', 'cache', 'rules', 'metrics' ) );
		expect( $defaults['storage']['host'] )->toBe( '127.0.0.1' );
		expect( $defaults['storage']['port'] )->toBe( 6379 );
		expect( $defaults['storage']['username'] )->toBe( '' );
		expect( $defaults['cache']['ttl'] )->toBe( DAY_IN_SECONDS );
		expect( $defaults['cache']['grace'] )->toBe( MONTH_IN_SECONDS );
		expect( $defaults['cache']['gzip'] )->toBeTrue();
		// Registered so the resolver keeps a persisted `metrics.active` (Pro's
		// detailed-metrics gate) instead of dropping it as an unknown key.
		expect( $defaults['metrics']['active'] )->toBeFalse();
	} );

	it( 'provides merged settings', function () {
		$result = Site::settings()->get();

		expect( $result )->toBeArray();
		expect( $result )->toHaveKey( 'storage' );
		expect( $result )->toHaveKey( 'cache' );
	} );

	it( 'provides module-scoped settings', function () {
		$result = Site::settings()->get( 'cache' );

		expect( $result )->toHaveKey( 'ttl' );
		expect( $result )->toHaveKey( 'grace' );
	} );

	it( 'delegates Network::settings() to Site::settings() on single-site', function () {
		expect( Network::settings() )->toBe( Site::settings() );
	} );

} );
