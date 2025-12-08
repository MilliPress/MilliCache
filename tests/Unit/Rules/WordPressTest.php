<?php
/**
 * Tests for WordPress Rules.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Rules\WordPress;

/**
 * Note: WordPress::register() depends on MilliRules\Rules which cannot be mocked
 * without using overload (causes test pollution).
 * These tests focus on verifying the class structure and method signatures.
 */
describe( 'WordPress Rules', function () {

	describe( 'class structure', function () {
		it( 'class exists', function () {
			expect( class_exists( WordPress::class ) )->toBeTrue();
		} );

		it( 'is a final class', function () {
			$reflection = new ReflectionClass( WordPress::class );
			expect( $reflection->isFinal() )->toBeTrue();
		} );
	} );

	describe( 'register method', function () {
		it( 'method exists', function () {
			expect( method_exists( WordPress::class, 'register' ) )->toBeTrue();
		} );

		it( 'is callable', function () {
			expect( is_callable( array( WordPress::class, 'register' ) ) )->toBeTrue();
		} );

		it( 'is a static method', function () {
			$reflection = new ReflectionMethod( WordPress::class, 'register' );
			expect( $reflection->isStatic() )->toBeTrue();
		} );

		it( 'is a public method', function () {
			$reflection = new ReflectionMethod( WordPress::class, 'register' );
			expect( $reflection->isPublic() )->toBeTrue();
		} );

		it( 'takes no parameters', function () {
			$reflection = new ReflectionMethod( WordPress::class, 'register' );
			$params = $reflection->getParameters();

			expect( count( $params ) )->toBe( 0 );
		} );

		it( 'returns void', function () {
			$reflection = new ReflectionMethod( WordPress::class, 'register' );
			$return_type = $reflection->getReturnType();

			if ( $return_type !== null ) {
				expect( $return_type->getName() )->toBe( 'void' );
			} else {
				// No explicit return type is acceptable.
				expect( $return_type )->toBeNull();
			}
		} );
	} );
} );
