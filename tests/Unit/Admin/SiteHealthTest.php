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

/**
 * Build a SiteHealth with a pre-seeded status payload, so the check-driven
 * test generation can be exercised without a fully wired StatusBuilder.
 *
 * @param array<int, array<string, mixed>> $checks The unified checks to inject.
 * @return SiteHealth
 */
function make_site_health_with_checks( array $checks ): SiteHealth {
	$builder = ( new ReflectionClass( StatusBuilder::class ) )->newInstanceWithoutConstructor();
	$health  = new SiteHealth( new \MilliCache\Core\Loader(), $builder );

	$memo = new ReflectionProperty( SiteHealth::class, 'payload_cache' );
	$memo->setAccessible( true );
	$memo->setValue( $health, array( 'debug' => array( 'checks' => $checks ) ) );

	return $health;
}

describe( 'Admin/SiteHealth', function () {

	describe( 'class structure', function () {
		it( 'is a final class', function () {
			$reflection = new ReflectionClass( SiteHealth::class );
			expect( $reflection->isFinal() )->toBeTrue();
		} );

		it( 'exposes register_debug_info, register_status_tests, and the all-clear test', function () {
			$reflection = new ReflectionClass( SiteHealth::class );
			expect( $reflection->hasMethod( 'register_debug_info' ) )->toBeTrue();
			expect( $reflection->hasMethod( 'register_status_tests' ) )->toBeTrue();
			expect( $reflection->hasMethod( 'test_health' ) )->toBeTrue();
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

		it( 'registers a single all-clear test when every check passes', function () {
			$health = make_site_health_with_checks( array(
				array( 'id' => 'wp_cache_constant', 'label' => 'WP_CACHE constant', 'status' => 'good', 'value' => 'Enabled' ),
				array( 'id' => 'storage_mode', 'label' => 'Storage connection mode', 'status' => 'info', 'value' => 'Sentinel' ),
			) );

			$result = $health->register_status_tests( array() );

			expect( $result['direct'] )->toHaveKey( 'millicache_health' );
			expect( $result['direct'] )->not->toHaveKey( 'millicache_wp_cache' );
		} );

		it( 'registers one test per outstanding issue and folds label+value into the headline', function () {
			$health = make_site_health_with_checks( array(
				array( 'id' => 'wp_cache_constant', 'label' => 'WP_CACHE constant', 'status' => 'critical', 'value' => 'Disabled', 'description' => 'x', 'url' => 'https://example.test' ),
				array( 'id' => 'storage_max_memory', 'label' => 'Storage memory limit', 'status' => 'recommended', 'value' => 'Not set' ),
				array( 'id' => 'storage_mode', 'label' => 'Storage connection mode', 'status' => 'info', 'value' => 'Sentinel' ),
				array( 'id' => 'dropin_present', 'label' => 'advanced-cache.php drop-in', 'status' => 'good', 'value' => 'Installed' ),
			) );

			$result = $health->register_status_tests( array() );

			// One test per recommended/critical check; the all-clear entry is absent.
			expect( $result['direct'] )->toHaveKey( 'millicache_wp_cache_constant' );
			expect( $result['direct'] )->toHaveKey( 'millicache_storage_max_memory' );
			expect( $result['direct'] )->not->toHaveKey( 'millicache_health' );

			// info + good checks are not surfaced individually.
			expect( $result['direct'] )->not->toHaveKey( 'millicache_storage_mode' );
			expect( $result['direct'] )->not->toHaveKey( 'millicache_dropin_present' );

			// Headline folds the verdict value into the subject label.
			expect( $result['direct']['millicache_wp_cache_constant']['label'] )->toBe( 'WP_CACHE constant: Disabled' );
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
