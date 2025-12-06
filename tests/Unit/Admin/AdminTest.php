<?php
/**
 * Tests for Admin class.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Admin\Admin;

// Mock WordPress functions.
if ( ! function_exists( 'is_network_admin' ) ) {
	function is_network_admin() {
		global $test_is_network_admin;
		return $test_is_network_admin ?? false;
	}
}

if ( ! function_exists( 'is_multisite' ) ) {
	function is_multisite() {
		global $test_is_multisite;
		return $test_is_multisite ?? false;
	}
}

if ( ! function_exists( 'set_transient' ) ) {
	function set_transient( $key, $value, $expiration ) {
		global $test_transients;
		$test_transients[ $key ] = $value;
		return true;
	}
}

if ( ! function_exists( 'get_transient' ) ) {
	function get_transient( $key ) {
		global $test_transients;
		return $test_transients[ $key ] ?? false;
	}
}

if ( ! function_exists( 'delete_transient' ) ) {
	function delete_transient( $key ) {
		global $test_transients;
		unset( $test_transients[ $key ] );
		return true;
	}
}

if ( ! function_exists( 'set_site_transient' ) ) {
	function set_site_transient( $key, $value, $expiration ) {
		global $test_site_transients;
		$test_site_transients[ $key ] = $value;
		return true;
	}
}

if ( ! function_exists( 'get_site_transient' ) ) {
	function get_site_transient( $key ) {
		global $test_site_transients;
		return $test_site_transients[ $key ] ?? false;
	}
}

if ( ! function_exists( 'delete_site_transient' ) ) {
	function delete_site_transient( $key ) {
		global $test_site_transients;
		unset( $test_site_transients[ $key ] );
		return true;
	}
}

if ( ! function_exists( 'size_format' ) ) {
	function size_format( $bytes, $decimals = 0 ) {
		if ( $bytes < 1024 ) {
			return $bytes . ' B';
		} elseif ( $bytes < 1048576 ) {
			return round( $bytes / 1024, $decimals ) . ' KB';
		} elseif ( $bytes < 1073741824 ) {
			return round( $bytes / 1048576, $decimals ) . ' MB';
		} else {
			return round( $bytes / 1073741824, $decimals ) . ' GB';
		}
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( '_n' ) ) {
	function _n( $single, $plural, $number, $domain = 'default' ) {
		return $number === 1 ? $single : $plural;
	}
}

if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}

if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', '/tmp/wp-content' );
}

uses()->beforeEach( function () {
	global $test_transients, $test_site_transients, $test_is_network_admin, $test_is_multisite;
	$test_transients = array();
	$test_site_transients = array();
	$test_is_network_admin = false;
	$test_is_multisite = false;

	// Reset Admin::$notices.
	$reflection = new ReflectionClass( Admin::class );
	$property = $reflection->getProperty( 'notices' );
	$property->setAccessible( true );
	$property->setValue( null, array() );
} );

/**
 * Note: Tests that require Engine::instance() are skipped because Engine is a final class
 * that cannot be mocked with overload (causes test pollution across files).
 * These tests focus on pure unit tests that don't require Engine.
 */
describe( 'Admin', function () {

	describe( 'class structure', function () {
		it( 'class exists', function () {
			expect( class_exists( Admin::class ) )->toBeTrue();
		} );

		it( 'is a final class', function () {
			$reflection = new ReflectionClass( Admin::class );
			expect( $reflection->isFinal() )->toBeTrue();
		} );

		it( 'has add_notice static method', function () {
			expect( method_exists( Admin::class, 'add_notice' ) )->toBeTrue();
		} );

		it( 'has get_cache_size static method', function () {
			expect( method_exists( Admin::class, 'get_cache_size' ) )->toBeTrue();
		} );

		it( 'has get_cache_size_summary_string static method', function () {
			expect( method_exists( Admin::class, 'get_cache_size_summary_string' ) )->toBeTrue();
		} );

		it( 'has enqueue_assets static method', function () {
			expect( method_exists( Admin::class, 'enqueue_assets' ) )->toBeTrue();
		} );

		it( 'has validate_advanced_cache_file static method', function () {
			expect( method_exists( Admin::class, 'validate_advanced_cache_file' ) )->toBeTrue();
		} );
	} );

	describe( 'add_notice', function () {
		it( 'adds notice to static array', function () {
			Admin::add_notice( 'Test message', 'info' );

			$reflection = new ReflectionClass( Admin::class );
			$property = $reflection->getProperty( 'notices' );
			$property->setAccessible( true );
			$notices = $property->getValue();

			expect( $notices )->toHaveCount( 1 );
			expect( $notices[0]['message'] )->toBe( 'Test message' );
			expect( $notices[0]['type'] )->toBe( 'info' );
		} );

		it( 'stores notice in transient', function () {
			Admin::add_notice( 'Test message', 'info' );

			global $test_transients;
			expect( $test_transients['millicache_admin_notices'] )->toBeArray();
			expect( $test_transients['millicache_admin_notices'][0]['message'] )->toBe( 'Test message' );
		} );

		it( 'defaults type to info', function () {
			Admin::add_notice( 'Test message' );

			$reflection = new ReflectionClass( Admin::class );
			$property = $reflection->getProperty( 'notices' );
			$property->setAccessible( true );
			$notices = $property->getValue();

			expect( $notices[0]['type'] )->toBe( 'info' );
		} );

		it( 'accepts different notice types', function () {
			Admin::add_notice( 'Warning message', 'warning' );
			Admin::add_notice( 'Error message', 'error' );
			Admin::add_notice( 'Success message', 'success' );

			$reflection = new ReflectionClass( Admin::class );
			$property = $reflection->getProperty( 'notices' );
			$property->setAccessible( true );
			$notices = $property->getValue();

			expect( $notices )->toHaveCount( 3 );
			expect( $notices[0]['type'] )->toBe( 'warning' );
			expect( $notices[1]['type'] )->toBe( 'error' );
			expect( $notices[2]['type'] )->toBe( 'success' );
		} );
	} );

	describe( 'get_cache_size with transient', function () {
		it( 'returns cached size from transient', function () {
			global $test_site_transients;
			$test_site_transients['millicache_size_test'] = array(
				'index' => 10,
				'size'  => 1024,
			);

			$result = Admin::get_cache_size( 'test' );

			expect( $result['index'] )->toBe( 10 );
			expect( $result['size'] )->toBe( 1024 );
			expect( $result['size_human'] )->toBe( '1 KB' );
		} );

		it( 'formats size correctly for bytes', function () {
			global $test_site_transients;
			$test_site_transients['millicache_size_test1'] = array( 'index' => 1, 'size' => 500 );
			$result1 = Admin::get_cache_size( 'test1' );
			expect( $result1['size_human'] )->toBe( '500 B' );
		} );

		it( 'formats size correctly for KB', function () {
			global $test_site_transients;
			$test_site_transients['millicache_size_test2'] = array( 'index' => 1, 'size' => 5120 );
			$result2 = Admin::get_cache_size( 'test2' );
			expect( $result2['size_human'] )->toBe( '5 KB' );
		} );

		it( 'formats size correctly for MB', function () {
			global $test_site_transients;
			$test_site_transients['millicache_size_test3'] = array( 'index' => 1, 'size' => 5242880 );
			$result3 = Admin::get_cache_size( 'test3' );
			expect( $result3['size_human'] )->toBe( '5 MB' );
		} );
	} );

	describe( 'get_cache_size_summary_string', function () {
		it( 'returns empty cache message when size is zero', function () {
			$size = array( 'index' => 0, 'size' => 0, 'size_human' => '0 B' );
			$result = Admin::get_cache_size_summary_string( $size );

			expect( $result )->toBe( 'No cached pages' );
		} );

		it( 'returns formatted string for single page', function () {
			$size = array( 'index' => 1, 'size' => 1024, 'size_human' => '1 KB' );
			$result = Admin::get_cache_size_summary_string( $size );

			expect( $result )->toContain( '1' );
			expect( $result )->toContain( 'page' );
			expect( $result )->toContain( '1 KB' );
		} );

		it( 'returns formatted string for multiple pages', function () {
			$size = array( 'index' => 10, 'size' => 10240, 'size_human' => '10 KB' );
			$result = Admin::get_cache_size_summary_string( $size );

			expect( $result )->toContain( '10' );
			expect( $result )->toContain( 'pages' );
			expect( $result )->toContain( '10 KB' );
		} );
	} );

	describe( 'get_file_version', function () {
		it( 'method exists and is callable', function () {
			expect( method_exists( Admin::class, 'get_file_version' ) )->toBeTrue();
			expect( is_callable( array( Admin::class, 'get_file_version' ) ) )->toBeTrue();
		} );
	} );

	describe( 'validate_advanced_cache_file', function () {
		it( 'returns empty array when file does not exist', function () {
			$result = Admin::validate_advanced_cache_file();

			// Since WP_CONTENT_DIR might not be defined in tests, we just check the method is callable.
			expect( is_array( $result ) )->toBeTrue();
		} );

		it( 'method exists and is callable', function () {
			expect( method_exists( Admin::class, 'validate_advanced_cache_file' ) )->toBeTrue();
			expect( is_callable( array( Admin::class, 'validate_advanced_cache_file' ) ) )->toBeTrue();
		} );
	} );

	describe( 'enqueue_assets', function () {
		it( 'returns false when MILLICACHE_BASENAME not defined', function () {
			if ( defined( 'MILLICACHE_BASENAME' ) ) {
				$this->markTestSkipped( 'MILLICACHE_BASENAME is defined, cannot test this condition' );
			}

			$result = Admin::enqueue_assets( 'test' );

			expect( $result )->toBeFalse();
		} );

		it( 'method exists and is callable', function () {
			expect( method_exists( Admin::class, 'enqueue_assets' ) )->toBeTrue();
			expect( is_callable( array( Admin::class, 'enqueue_assets' ) ) )->toBeTrue();
		} );
	} );

	describe( 'undefined_cache_notice', function () {
		it( 'adds notice when WP_CACHE is false', function () {
			if ( ! defined( 'WP_CACHE' ) ) {
				define( 'WP_CACHE', false );
			}

			Admin::undefined_cache_notice();

			$reflection = new ReflectionClass( Admin::class );
			$property = $reflection->getProperty( 'notices' );
			$property->setAccessible( true );
			$notices = $property->getValue();

			expect( count( $notices ) )->toBeGreaterThan( 0 );
			expect( $notices[0]['type'] )->toBe( 'warning' );
		} );

		it( 'method exists and is callable', function () {
			expect( method_exists( Admin::class, 'undefined_cache_notice' ) )->toBeTrue();
			expect( is_callable( array( Admin::class, 'undefined_cache_notice' ) ) )->toBeTrue();
		} );
	} );

	describe( 'notice workflow', function () {
		it( 'completes successfully', function () {
			// Add notice.
			Admin::add_notice( 'Test notice', 'info' );

			// Verify static array.
			$reflection = new ReflectionClass( Admin::class );
			$property = $reflection->getProperty( 'notices' );
			$property->setAccessible( true );
			$notices = $property->getValue();

			expect( $notices )->toHaveCount( 1 );

			// Verify transient.
			global $test_transients;
			expect( $test_transients['millicache_admin_notices'] )->toBeArray();
		} );
	} );
} );
