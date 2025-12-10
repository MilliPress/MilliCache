<?php
/**
 * Tests for CLI Test command.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Admin\CLI\Test;

// Ensure constants are defined.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/' );
}

describe( 'CLI/Test', function () {

	describe( 'class structure', function () {
		it( 'is a final class', function () {
			$reflection = new ReflectionClass( Test::class );
			expect( $reflection->isFinal() )->toBeTrue();
		} );

		it( 'has no constructor', function () {
			$reflection = new ReflectionClass( Test::class );
			expect( $reflection->getConstructor() )->toBeNull();
		} );

		it( 'has __invoke method', function () {
			$reflection = new ReflectionClass( Test::class );
			expect( $reflection->hasMethod( '__invoke' ) )->toBeTrue();
		} );
	} );

	describe( '__invoke method', function () {
		it( 'is public', function () {
			$method = new ReflectionMethod( Test::class, '__invoke' );
			expect( $method->isPublic() )->toBeTrue();
		} );

		it( 'returns void', function () {
			$method = new ReflectionMethod( Test::class, '__invoke' );
			$return_type = $method->getReturnType();
			expect( $return_type )->not->toBeNull();
			expect( $return_type->getName() )->toBe( 'void' );
		} );

		it( 'takes two array parameters', function () {
			$method = new ReflectionMethod( Test::class, '__invoke' );
			expect( $method->getNumberOfParameters() )->toBe( 2 );

			$params = $method->getParameters();
			expect( $params[0]->getName() )->toBe( 'args' );
			expect( $params[0]->getType()->getName() )->toBe( 'array' );
			expect( $params[1]->getName() )->toBe( 'assoc_args' );
			expect( $params[1]->getType()->getName() )->toBe( 'array' );
		} );
	} );

	describe( 'WP-CLI docblock', function () {
		it( 'has DESCRIPTION section in docblock', function () {
			$method = new ReflectionMethod( Test::class, '__invoke' );
			$docblock = $method->getDocComment();
			expect( $docblock )->toContain( '## DESCRIPTION' );
		} );

		it( 'has EXAMPLES section in docblock', function () {
			$method = new ReflectionMethod( Test::class, '__invoke' );
			$docblock = $method->getDocComment();
			expect( $docblock )->toContain( '## EXAMPLES' );
		} );

		it( 'has @when after_wp_load annotation', function () {
			$method = new ReflectionMethod( Test::class, '__invoke' );
			$docblock = $method->getDocComment();
			expect( $docblock )->toContain( '@when after_wp_load' );
		} );
	} );
} );
