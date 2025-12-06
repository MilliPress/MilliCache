<?php
/**
 * Tests for Bootstrap Rules.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Rules\Bootstrap;
use MilliCache\Engine\Cache\Config;

/**
 * Note: Bootstrap::register() requires Engine::instance() which is a final class
 * that cannot be mocked without using overload (which causes test pollution).
 * These tests focus on verifying the class structure and method signatures.
 */
describe( 'Bootstrap Rules', function () {

	describe( 'class structure', function () {
		it( 'class exists', function () {
			expect( class_exists( Bootstrap::class ) )->toBeTrue();
		} );

		it( 'is a final class', function () {
			$reflection = new ReflectionClass( Bootstrap::class );
			expect( $reflection->isFinal() )->toBeTrue();
		} );
	} );

	describe( 'register method', function () {
		it( 'method exists', function () {
			expect( method_exists( Bootstrap::class, 'register' ) )->toBeTrue();
		} );

		it( 'is callable', function () {
			expect( is_callable( array( Bootstrap::class, 'register' ) ) )->toBeTrue();
		} );

		it( 'is a static method', function () {
			$reflection = new ReflectionMethod( Bootstrap::class, 'register' );
			expect( $reflection->isStatic() )->toBeTrue();
		} );

		it( 'is a public method', function () {
			$reflection = new ReflectionMethod( Bootstrap::class, 'register' );
			expect( $reflection->isPublic() )->toBeTrue();
		} );

		it( 'takes no parameters', function () {
			$reflection = new ReflectionMethod( Bootstrap::class, 'register' );
			$params = $reflection->getParameters();

			expect( count( $params ) )->toBe( 0 );
		} );

		it( 'returns void', function () {
			$reflection = new ReflectionMethod( Bootstrap::class, 'register' );
			$return_type = $reflection->getReturnType();

			if ( $return_type !== null ) {
				expect( $return_type->getName() )->toBe( 'void' );
			} else {
				// No explicit return type is acceptable.
				expect( $return_type )->toBeNull();
			}
		} );
	} );

	describe( 'Config integration', function () {
		it( 'Config class is available', function () {
			expect( class_exists( Config::class ) )->toBeTrue();
		} );

		it( 'Config can be instantiated', function () {
			$config = new Config( 3600, 600, true, false, array(), array(), array(), array(), array() );
			expect( $config )->toBeInstanceOf( Config::class );
		} );

		it( 'Config has nocache_paths property', function () {
			$config = new Config( 3600, 600, true, false, array( '/cart', '/checkout' ), array(), array(), array(), array() );
			expect( $config->nocache_paths )->toBeArray();
			expect( $config->nocache_paths )->toContain( '/cart' );
		} );

		it( 'Config has nocache_cookies property', function () {
			$config = new Config( 3600, 600, true, false, array(), array( 'logged_in' ), array(), array(), array() );
			expect( $config->nocache_cookies )->toBeArray();
			expect( $config->nocache_cookies )->toContain( 'logged_in' );
		} );

		it( 'Config has empty arrays by default', function () {
			$config = new Config( 3600, 600, true, false, array(), array(), array(), array(), array() );
			expect( $config->nocache_paths )->toBeEmpty();
			expect( $config->nocache_cookies )->toBeEmpty();
		} );
	} );
} );
