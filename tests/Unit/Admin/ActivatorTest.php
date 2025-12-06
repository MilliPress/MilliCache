<?php
/**
 * Tests for Activator.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Admin\Activator;

// Mock WordPress functions.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/' );
}

if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', '/tmp/wp-content' );
}

if ( ! defined( 'MILLICACHE_DIR' ) ) {
	define( 'MILLICACHE_DIR', '/tmp/plugins/millicache' );
}

if ( ! function_exists( 'wp_next_scheduled' ) ) {
	function wp_next_scheduled( $hook ) {
		global $test_wp_next_scheduled;
		return $test_wp_next_scheduled ?? false;
	}
}

if ( ! function_exists( 'wp_schedule_event' ) ) {
	function wp_schedule_event( $timestamp, $recurrence, $hook ) {
		global $test_wp_schedule_event_called;
		$test_wp_schedule_event_called = true;
		return true;
	}
}

if ( ! function_exists( 'wp_set_option_autoload' ) ) {
	function wp_set_option_autoload( $option, $autoload ) {
		global $test_wp_set_option_autoload_called;
		$test_wp_set_option_autoload_called = array(
			'option'   => $option,
			'autoload' => $autoload,
		);
		return true;
	}
}

uses()->beforeEach( function () {
	global $test_wp_next_scheduled, $test_wp_schedule_event_called, $test_wp_set_option_autoload_called;
	$test_wp_next_scheduled = false;
	$test_wp_schedule_event_called = false;
	$test_wp_set_option_autoload_called = null;
} );

describe( 'Activator', function () {

	describe( 'class structure', function () {
		it( 'is a final class', function () {
			$reflection = new ReflectionClass( Activator::class );
			expect( $reflection->isFinal() )->toBeTrue();
		} );

		it( 'has activate method', function () {
			$reflection = new ReflectionClass( Activator::class );
			expect( $reflection->hasMethod( 'activate' ) )->toBeTrue();
		} );

		it( 'has public static activate method', function () {
			$method = new ReflectionMethod( Activator::class, 'activate' );
			expect( $method->isPublic() )->toBeTrue();
			expect( $method->isStatic() )->toBeTrue();
		} );
	} );

	describe( 'private methods', function () {
		it( 'has schedule_events method', function () {
			$reflection = new ReflectionClass( Activator::class );
			expect( $reflection->hasMethod( 'schedule_events' ) )->toBeTrue();
		} );

		it( 'has create_advanced_cache_file method', function () {
			$reflection = new ReflectionClass( Activator::class );
			expect( $reflection->hasMethod( 'create_advanced_cache_file' ) )->toBeTrue();
		} );

		it( 'schedule_events is private and static', function () {
			$method = new ReflectionMethod( Activator::class, 'schedule_events' );
			expect( $method->isPrivate() )->toBeTrue();
			expect( $method->isStatic() )->toBeTrue();
		} );

		it( 'create_advanced_cache_file is private and static', function () {
			$method = new ReflectionMethod( Activator::class, 'create_advanced_cache_file' );
			expect( $method->isPrivate() )->toBeTrue();
			expect( $method->isStatic() )->toBeTrue();
		} );
	} );

	describe( 'method signatures', function () {
		it( 'activate method returns void', function () {
			$method = new ReflectionMethod( Activator::class, 'activate' );
			$return_type = $method->getReturnType();
			// May be void or no return type.
			if ( $return_type !== null ) {
				expect( $return_type->getName() )->toBe( 'void' );
			} else {
				expect( $return_type )->toBeNull();
			}
		} );

		it( 'activate method takes no parameters', function () {
			$method = new ReflectionMethod( Activator::class, 'activate' );
			expect( $method->getNumberOfParameters() )->toBe( 0 );
		} );

		it( 'schedule_events returns void', function () {
			$method = new ReflectionMethod( Activator::class, 'schedule_events' );
			$return_type = $method->getReturnType();
			if ( $return_type !== null ) {
				expect( $return_type->getName() )->toBe( 'void' );
			} else {
				expect( $return_type )->toBeNull();
			}
		} );

		it( 'create_advanced_cache_file returns void', function () {
			$method = new ReflectionMethod( Activator::class, 'create_advanced_cache_file' );
			$return_type = $method->getReturnType();
			expect( $return_type )->not->toBeNull();
			expect( $return_type->getName() )->toBe( 'void' );
		} );
	} );

	describe( 'schedule_events behavior', function () {
		it( 'schedules event when not already scheduled', function () {
			global $test_wp_next_scheduled, $test_wp_schedule_event_called;
			$test_wp_next_scheduled = false;
			$test_wp_schedule_event_called = false;

			// Use reflection to call private method.
			$method = new ReflectionMethod( Activator::class, 'schedule_events' );
			$method->setAccessible( true );
			$method->invoke( null );

			expect( $test_wp_schedule_event_called )->toBeTrue();
		} );

		it( 'does not schedule event when already scheduled', function () {
			global $test_wp_next_scheduled, $test_wp_schedule_event_called;
			$test_wp_next_scheduled = time() + 3600; // Already scheduled.
			$test_wp_schedule_event_called = false;

			$method = new ReflectionMethod( Activator::class, 'schedule_events' );
			$method->setAccessible( true );
			$method->invoke( null );

			expect( $test_wp_schedule_event_called )->toBeFalse();
		} );
	} );
} );
