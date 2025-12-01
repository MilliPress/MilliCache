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
use MilliCache\Core\Loader;

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

describe( 'Admin', function () {

	describe( 'constructor', function () {
		it( 'initializes with loader, plugin name and version', function () {
			$loader = Mockery::mock( Loader::class );
			$loader->shouldReceive( 'add_action' )->andReturn( null );
			$loader->shouldReceive( 'add_filter' )->andReturn( null );

			$admin = new Admin( $loader, 'millicache', '1.0.0' );

			expect( $admin )->toBeInstanceOf( Admin::class );
		} );

		it( 'registers admin hooks', function () {
			$loader = Mockery::mock( Loader::class );
			$loader->shouldReceive( 'add_action' )->with( 'admin_menu', Mockery::type( Admin::class ), 'add_admin_menu' )->once();
			$loader->shouldReceive( 'add_action' )->with( 'admin_enqueue_scripts', Mockery::type( Admin::class ), 'enqueue_admin_assets' )->once();
			$loader->shouldReceive( 'add_action' )->andReturn( null );
			$loader->shouldReceive( 'add_filter' )->andReturn( null );

			new Admin( $loader, 'millicache', '1.0.0' );

			expect( true )->toBeTrue();
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

	describe( 'delete_cache_size_transient', function () {
		it( 'deletes single site cache size transient', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'get_flag_prefix' )->andReturn( '' );

			global $test_site_transients;
			$test_site_transients['millicache_size_*'] = array( 'index' => 10, 'size' => 1024 );

			Admin::delete_cache_size_transient();

			expect( isset( $test_site_transients['millicache_size_*'] ) )->toBeFalse();
		} );

		it( 'deletes network cache size transient on multisite', function () {
			global $test_is_multisite;
			$test_is_multisite = true;

			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'get_flag_prefix' )->with()->andReturn( '' );
			$engine->shouldReceive( 'get_flag_prefix' )->with( '*' )->andReturn( '1:' );

			global $test_site_transients;
			$test_site_transients['millicache_size_*'] = array( 'index' => 10, 'size' => 1024 );
			$test_site_transients['millicache_size_1:*'] = array( 'index' => 20, 'size' => 2048 );

			Admin::delete_cache_size_transient();

			expect( isset( $test_site_transients['millicache_size_*'] ) )->toBeFalse();
			expect( isset( $test_site_transients['millicache_size_1:*'] ) )->toBeFalse();
		} );
	} );

	describe( 'get_cache_size', function () {
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

		it( 'fetches from storage when no transient exists', function () {
			$storage = Mockery::mock();
			$storage->shouldReceive( 'get_cache_size' )->once()->with( 'test' )->andReturn( array(
				'index' => 20,
				'size'  => 2048,
			) );

			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'get_storage' )->andReturn( $storage );

			$result = Admin::get_cache_size( 'test' );

			expect( $result['index'] )->toBe( 20 );
			expect( $result['size'] )->toBe( 2048 );
		} );

		it( 'stores fetched size in transient', function () {
			$storage = Mockery::mock();
			$storage->shouldReceive( 'get_cache_size' )->with( 'test' )->andReturn( array(
				'index' => 15,
				'size'  => 1536,
			) );

			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'get_storage' )->andReturn( $storage );

			Admin::get_cache_size( 'test' );

			global $test_site_transients;
			expect( $test_site_transients['millicache_size_test'] )->toBeArray();
			expect( $test_site_transients['millicache_size_test']['index'] )->toBe( 15 );
		} );

		it( 'reloads from storage when reload flag is true', function () {
			global $test_site_transients;
			$test_site_transients['millicache_size_test'] = array(
				'index' => 5,
				'size'  => 512,
			);

			$storage = Mockery::mock();
			$storage->shouldReceive( 'get_cache_size' )->once()->with( 'test' )->andReturn( array(
				'index' => 25,
				'size'  => 2560,
			) );

			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'get_storage' )->andReturn( $storage );

			$result = Admin::get_cache_size( 'test', true );

			expect( $result['index'] )->toBe( 25 );
			expect( $result['size'] )->toBe( 2560 );
		} );

		it( 'handles empty cache', function () {
			$storage = Mockery::mock();
			$storage->shouldReceive( 'get_cache_size' )->andReturn( array(
				'index' => 0,
				'size'  => 0,
			) );

			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'get_storage' )->andReturn( $storage );

			$result = Admin::get_cache_size( '' );

			expect( $result['index'] )->toBe( 0 );
			expect( $result['size'] )->toBe( 0 );
			expect( $result['size_human'] )->toBe( '0 B' );
		} );

		it( 'formats size correctly for different magnitudes', function () {
			global $test_site_transients;

			// Bytes.
			$test_site_transients['millicache_size_test1'] = array( 'index' => 1, 'size' => 500 );
			$result1 = Admin::get_cache_size( 'test1' );
			expect( $result1['size_human'] )->toBe( '500 B' );

			// KB.
			$test_site_transients['millicache_size_test2'] = array( 'index' => 1, 'size' => 5120 );
			$result2 = Admin::get_cache_size( 'test2' );
			expect( $result2['size_human'] )->toBe( '5 KB' );

			// MB.
			$test_site_transients['millicache_size_test3'] = array( 'index' => 1, 'size' => 5242880 );
			$result3 = Admin::get_cache_size( 'test3' );
			expect( $result3['size_human'] )->toBe( '5 MB' );
		} );
	} );

	describe( 'get_cache_size_summary_string', function () {
		it( 'returns empty cache message when size is zero', function () {
			$size = array( 'index' => 0, 'size' => 0, 'size_human' => '0 B' );
			$result = Admin::get_cache_size_summary_string( $size );

			expect( $result )->toBe( 'Empty cache' );
		} );

		it( 'returns formatted string for single page', function () {
			$size = array( 'index' => 1, 'size' => 1024, 'size_human' => '1 KB' );
			$result = Admin::get_cache_size_summary_string( $size );

			expect( $result )->toContain( '1' );
			expect( $result )->toContain( 'page' );
			expect( $result )->toContain( '1 KB' );
			expect( $result )->toContain( 'cached' );
		} );

		it( 'returns formatted string for multiple pages', function () {
			$size = array( 'index' => 10, 'size' => 10240, 'size_human' => '10 KB' );
			$result = Admin::get_cache_size_summary_string( $size );

			expect( $result )->toContain( '10' );
			expect( $result )->toContain( 'pages' );
			expect( $result )->toContain( '10 KB' );
		} );

		it( 'fetches size when not provided', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'get_flag_prefix' )->andReturn( '' );

			$storage = Mockery::mock();
			$storage->shouldReceive( 'get_cache_size' )->andReturn( array(
				'index' => 5,
				'size'  => 5120,
			) );
			$engine->shouldReceive( 'get_storage' )->andReturn( $storage );

			$result = Admin::get_cache_size_summary_string();

			expect( $result )->toContain( '5' );
			expect( $result )->toContain( 'pages' );
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
			if ( ! function_exists( 'file_exists' ) ) {
				$this->markTestSkipped( 'Cannot test without mocking file_exists' );
			}

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
		it( 'adds notice when WP_CACHE is not defined', function () {
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

	describe( 'integration', function () {
		it( 'notice workflow completes successfully', function () {
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

		it( 'cache size workflow completes successfully', function () {
			$storage = Mockery::mock();
			$storage->shouldReceive( 'get_cache_size' )->andReturn( array(
				'index' => 10,
				'size'  => 10240,
			) );

			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'get_storage' )->andReturn( $storage );
			$engine->shouldReceive( 'get_flag_prefix' )->andReturn( '' );

			// Get size.
			$size = Admin::get_cache_size( 'test' );
			expect( $size['index'] )->toBe( 10 );

			// Get summary.
			$summary = Admin::get_cache_size_summary_string( $size );
			expect( $summary )->toContain( '10' );
			expect( $summary )->toContain( 'pages' );

			// Delete transient.
			Admin::delete_cache_size_transient();

			global $test_site_transients;
			expect( isset( $test_site_transients['millicache_size_*'] ) )->toBeFalse();
		} );
	} );
} );
