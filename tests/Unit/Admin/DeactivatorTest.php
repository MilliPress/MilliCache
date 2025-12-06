<?php
/**
 * Tests for Deactivator.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Admin\Deactivator;

// Ensure constants are defined.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/' );
}

if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', '/tmp/wp-content' );
}

if ( ! defined( 'MILLICACHE_DIR' ) ) {
	define( 'MILLICACHE_DIR', '/tmp/plugins/millicache' );
}

// Mock WordPress functions.
if ( ! function_exists( 'wp_clear_scheduled_hook' ) ) {
	function wp_clear_scheduled_hook( $hook ) {
		global $test_wp_clear_scheduled_hook_called;
		$test_wp_clear_scheduled_hook_called = $hook;
		return true;
	}
}

if ( ! function_exists( 'wp_delete_file' ) ) {
	function wp_delete_file( $file ) {
		global $test_wp_delete_file_called;
		$test_wp_delete_file_called = $file;
		return true;
	}
}

uses()->beforeEach( function () {
	global $test_wp_clear_scheduled_hook_called, $test_wp_delete_file_called;
	$test_wp_clear_scheduled_hook_called = null;
	$test_wp_delete_file_called = null;
} );

describe( 'Deactivator', function () {

	describe( 'class structure', function () {
		it( 'is a final class', function () {
			$reflection = new ReflectionClass( Deactivator::class );
			expect( $reflection->isFinal() )->toBeTrue();
		} );

		it( 'has deactivate method', function () {
			$reflection = new ReflectionClass( Deactivator::class );
			expect( $reflection->hasMethod( 'deactivate' ) )->toBeTrue();
		} );

		it( 'has public static deactivate method', function () {
			$method = new ReflectionMethod( Deactivator::class, 'deactivate' );
			expect( $method->isPublic() )->toBeTrue();
			expect( $method->isStatic() )->toBeTrue();
		} );
	} );

	describe( 'private methods', function () {
		it( 'has unschedule_events method', function () {
			$reflection = new ReflectionClass( Deactivator::class );
			expect( $reflection->hasMethod( 'unschedule_events' ) )->toBeTrue();
		} );

		it( 'has remove_advanced_cache_file method', function () {
			$reflection = new ReflectionClass( Deactivator::class );
			expect( $reflection->hasMethod( 'remove_advanced_cache_file' ) )->toBeTrue();
		} );

		it( 'unschedule_events is private and static', function () {
			$method = new ReflectionMethod( Deactivator::class, 'unschedule_events' );
			expect( $method->isPrivate() )->toBeTrue();
			expect( $method->isStatic() )->toBeTrue();
		} );

		it( 'remove_advanced_cache_file is private and static', function () {
			$method = new ReflectionMethod( Deactivator::class, 'remove_advanced_cache_file' );
			expect( $method->isPrivate() )->toBeTrue();
			expect( $method->isStatic() )->toBeTrue();
		} );
	} );

	describe( 'method signatures', function () {
		it( 'deactivate method returns void', function () {
			$method = new ReflectionMethod( Deactivator::class, 'deactivate' );
			$return_type = $method->getReturnType();
			if ( $return_type !== null ) {
				expect( $return_type->getName() )->toBe( 'void' );
			} else {
				expect( $return_type )->toBeNull();
			}
		} );

		it( 'deactivate method takes no parameters', function () {
			$method = new ReflectionMethod( Deactivator::class, 'deactivate' );
			expect( $method->getNumberOfParameters() )->toBe( 0 );
		} );

		it( 'unschedule_events returns void', function () {
			$method = new ReflectionMethod( Deactivator::class, 'unschedule_events' );
			$return_type = $method->getReturnType();
			if ( $return_type !== null ) {
				expect( $return_type->getName() )->toBe( 'void' );
			} else {
				expect( $return_type )->toBeNull();
			}
		} );

		it( 'remove_advanced_cache_file returns void', function () {
			$method = new ReflectionMethod( Deactivator::class, 'remove_advanced_cache_file' );
			$return_type = $method->getReturnType();
			if ( $return_type !== null ) {
				expect( $return_type->getName() )->toBe( 'void' );
			} else {
				expect( $return_type )->toBeNull();
			}
		} );
	} );

	describe( 'unschedule_events behavior', function () {
		it( 'clears the millipress_nightly hook', function () {
			global $test_wp_clear_scheduled_hook_called;

			$method = new ReflectionMethod( Deactivator::class, 'unschedule_events' );
			$method->setAccessible( true );
			$method->invoke( null );

			expect( $test_wp_clear_scheduled_hook_called )->toBe( 'millipress_nightly' );
		} );
	} );
} );
