<?php
/**
 * Tests for the WP Site Health integration.
 *
 * @link       https://www.millipress.com
 * @since      1.7.0
 *
 * @package    MilliCache
 */

use MilliCache\Admin\SiteHealth;
use MilliCache\Admin\UI\StatusBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/' );
}

describe( 'Admin/SiteHealth', function () {

	describe( 'class structure', function () {
		it( 'is a final class', function () {
			$reflection = new ReflectionClass( SiteHealth::class );
			expect( $reflection->isFinal() )->toBeTrue();
		} );

		it( 'has register_debug_info, register_status_tests, and three test_* methods', function () {
			$reflection = new ReflectionClass( SiteHealth::class );
			expect( $reflection->hasMethod( 'register_debug_info' ) )->toBeTrue();
			expect( $reflection->hasMethod( 'register_status_tests' ) )->toBeTrue();
			expect( $reflection->hasMethod( 'test_dropin' ) )->toBeTrue();
			expect( $reflection->hasMethod( 'test_storage' ) )->toBeTrue();
			expect( $reflection->hasMethod( 'test_wp_cache' ) )->toBeTrue();
		} );

		it( 'constructor takes a Loader and a StatusBuilder', function () {
			$method = new ReflectionMethod( SiteHealth::class, '__construct' );
			expect( $method->getNumberOfParameters() )->toBe( 2 );

			$params = $method->getParameters();
			expect( $params[0]->getName() )->toBe( 'loader' );
			expect( $params[0]->getType()->getName() )->toBe( \MilliCache\Core\Loader::class );
			expect( $params[1]->getName() )->toBe( 'status_builder' );
			expect( $params[1]->getType()->getName() )->toBe( StatusBuilder::class );
		} );
	} );

	describe( 'filter registration', function () {
		it( 'registers both Site Health filters via the loader when constructed', function () {
			$loader = new \MilliCache\Core\Loader();

			$builder = ( new ReflectionClass( StatusBuilder::class ) )
				->newInstanceWithoutConstructor();

			new SiteHealth( $loader, $builder );

			$filter_reflection = new ReflectionProperty( \MilliCache\Core\Loader::class, 'filters' );
			$filter_reflection->setAccessible( true );
			$filters = $filter_reflection->getValue( $loader );

			$hooks = is_array( $filters ) ? array_column( $filters, 'hook' ) : array();

			expect( $hooks )->toContain( 'debug_information' );
			expect( $hooks )->toContain( 'site_status_tests' );
		} );
	} );

	describe( 'register_status_tests', function () {
		it( 'returns the tests array unchanged when given a non-array', function () {
			$builder = ( new ReflectionClass( StatusBuilder::class ) )
				->newInstanceWithoutConstructor();
			$site_health = new SiteHealth( new \MilliCache\Core\Loader(), $builder );

			expect( $site_health->register_status_tests( 'nope' ) )->toBe( 'nope' );
		} );

		it( 'adds three direct tests under the millicache_ prefix', function () {
			$builder = ( new ReflectionClass( StatusBuilder::class ) )
				->newInstanceWithoutConstructor();
			$site_health = new SiteHealth( new \MilliCache\Core\Loader(), $builder );

			$result = $site_health->register_status_tests( array() );

			expect( $result )->toHaveKey( 'direct' );
			expect( $result['direct'] )->toHaveKey( 'millicache_dropin' );
			expect( $result['direct'] )->toHaveKey( 'millicache_storage' );
			expect( $result['direct'] )->toHaveKey( 'millicache_wp_cache' );
		} );
	} );

	describe( 'register_debug_info', function () {
		it( 'returns the info unchanged when given a non-array', function () {
			$builder = ( new ReflectionClass( StatusBuilder::class ) )
				->newInstanceWithoutConstructor();
			$site_health = new SiteHealth( new \MilliCache\Core\Loader(), $builder );

			expect( $site_health->register_debug_info( false ) )->toBeFalse();
		} );
	} );
} );
