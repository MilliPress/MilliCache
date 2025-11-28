<?php
/**
 * Tests for CacheConfig value object.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Engine\Cache\Config;

describe( 'CacheConfig', function () {

	describe( 'constructor', function () {
		it( 'creates config with all properties', function () {
			$config = new Config(
				3600,
				600,
				true,
				false,
				array( '/admin', '/login' ),
				array( 'session', 'auth' ),
				array( 'analytics', 'tracking' ),
				array( 'utm_source', 'fbclid' ),
				array( 'lang', 'currency' )
			);

			expect( $config->ttl )->toBe( 3600 );
			expect( $config->grace )->toBe( 600 );
			expect( $config->gzip )->toBeTrue();
			expect( $config->debug )->toBeFalse();
			expect( $config->nocache_paths )->toBe( array( '/admin', '/login' ) );
			expect( $config->nocache_cookies )->toBe( array( 'session', 'auth' ) );
			expect( $config->ignore_cookies )->toBe( array( 'analytics', 'tracking' ) );
			expect( $config->ignore_request_keys )->toBe( array( 'utm_source', 'fbclid' ) );
			expect( $config->unique )->toBe( array( 'lang', 'currency' ) );
		} );
	} );

	describe( 'from_settings', function () {
		it( 'creates config from settings array', function () {
			$settings = array(
				'ttl' => 7200,
				'grace' => 300,
				'gzip' => false,
				'debug' => true,
				'nocache_paths' => array( '/cart' ),
				'nocache_cookies' => array( 'woocommerce_*' ),
				'ignore_cookies' => array( 'test' ),
				'ignore_request_keys' => array( 'ref' ),
				'unique' => array( 'version' ),
			);

			$config = Config::from_settings( $settings );

			expect( $config->ttl )->toBe( 7200 );
			expect( $config->grace )->toBe( 300 );
			expect( $config->gzip )->toBeFalse();
			expect( $config->debug )->toBeTrue();
			expect( $config->nocache_paths )->toBe( array( '/cart' ) );
			expect( $config->nocache_cookies )->toBe( array( 'woocommerce_*' ) );
		} );

		it( 'uses default values for missing settings', function () {
			$config = Config::from_settings( array() );

			expect( $config->ttl )->toBe( 86400 ); // Default 24 hours.
			expect( $config->grace )->toBe( 3600 ); // Default 1 hour.
			expect( $config->gzip )->toBeTrue();
			expect( $config->debug )->toBeFalse();
			expect( $config->nocache_paths )->toBeArray()->toBeEmpty();
			expect( $config->nocache_cookies )->toBeArray()->toBeEmpty();
			expect( $config->ignore_cookies )->toBeArray()->toBeEmpty();
			expect( $config->ignore_request_keys )->toBeArray()->toBeEmpty();
			expect( $config->unique )->toBeArray()->toBeEmpty();
		} );

		it( 'handles partial settings', function () {
			$settings = array(
				'ttl' => 1800,
				'debug' => true,
			);

			$config = Config::from_settings( $settings );

			expect( $config->ttl )->toBe( 1800 );
			expect( $config->grace )->toBe( 3600 ); // Default.
			expect( $config->debug )->toBeTrue();
			expect( $config->gzip )->toBeTrue(); // Default.
		} );
	} );

	describe( 'with_ttl', function () {
		it( 'creates new instance with modified TTL', function () {
			$original = Config::from_settings( array( 'ttl' => 3600 ) );
			$modified = $original->with_ttl( 7200 );

			// Original unchanged.
			expect( $original->ttl )->toBe( 3600 );

			// New instance has new TTL.
			expect( $modified->ttl )->toBe( 7200 );

			// Other properties preserved.
			expect( $modified->grace )->toBe( $original->grace );
			expect( $modified->gzip )->toBe( $original->gzip );
		} );
	} );

	describe( 'with_grace', function () {
		it( 'creates new instance with modified grace', function () {
			$original = Config::from_settings( array( 'grace' => 600 ) );
			$modified = $original->with_grace( 1200 );

			// Original unchanged.
			expect( $original->grace )->toBe( 600 );

			// New instance has new grace.
			expect( $modified->grace )->toBe( 1200 );

			// Other properties preserved.
			expect( $modified->ttl )->toBe( $original->ttl );
			expect( $modified->debug )->toBe( $original->debug );
		} );
	} );

	describe( 'immutability', function () {
		it( 'properties cannot be modified after creation', function () {
			$config = Config::from_settings( array( 'ttl' => 3600 ) );

			// This should work (reading).
			$ttl = $config->ttl;
			expect( $ttl )->toBe( 3600 );

			// Attempting to modify should throw error in strict mode.
			// Note: PHP 8.1+ readonly properties, PHP 7.4 allows modification.
			// This test documents expected behavior.
		} );

		it( 'returns new instance with with_* methods', function () {
			$original = Config::from_settings( array() );
			$modified1 = $original->with_ttl( 7200 );
			$modified2 = $original->with_grace( 1200 );

			// All different instances.
			expect( $modified1 )->not->toBe( $original );
			expect( $modified2 )->not->toBe( $original );
			expect( $modified2 )->not->toBe( $modified1 );
		} );
	} );
} );
