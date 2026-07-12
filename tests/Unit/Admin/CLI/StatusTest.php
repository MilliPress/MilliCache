<?php
/**
 * Tests for CLI Status command.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Admin\CLI\Status;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/' );
}

describe( 'CLI/Status', function () {

	describe( 'class structure', function () {
		it( 'is a final class', function () {
			$reflection = new ReflectionClass( Status::class );
			expect( $reflection->isFinal() )->toBeTrue();
		} );

		it( 'has no constructor', function () {
			$reflection = new ReflectionClass( Status::class );
			expect( $reflection->getConstructor() )->toBeNull();
		} );

		it( 'has __invoke method', function () {
			$reflection = new ReflectionClass( Status::class );
			expect( $reflection->hasMethod( '__invoke' ) )->toBeTrue();
		} );

		it( 'uses OutputTrait', function () {
			$reflection = new ReflectionClass( Status::class );
			expect( $reflection->getTraitNames() )->toContain( \MilliCache\Admin\CLI\OutputTrait::class );
		} );
	} );

	describe( '__invoke method', function () {
		it( 'is public', function () {
			$method = new ReflectionMethod( Status::class, '__invoke' );
			expect( $method->isPublic() )->toBeTrue();
		} );

		it( 'returns void', function () {
			$method = new ReflectionMethod( Status::class, '__invoke' );
			$return_type = $method->getReturnType();
			expect( $return_type )->not->toBeNull();
			expect( $return_type->getName() )->toBe( 'void' );
		} );

		it( 'takes two array parameters', function () {
			$method = new ReflectionMethod( Status::class, '__invoke' );
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
			$method = new ReflectionMethod( Status::class, '__invoke' );
			expect( $method->getDocComment() )->toContain( '## DESCRIPTION' );
		} );

		it( 'has OPTIONS section in docblock', function () {
			$method = new ReflectionMethod( Status::class, '__invoke' );
			expect( $method->getDocComment() )->toContain( '## OPTIONS' );
		} );

		it( 'documents --format with default table', function () {
			$docblock = ( new ReflectionMethod( Status::class, '__invoke' ) )->getDocComment();
			expect( $docblock )->toContain( '[--format=<format>]' );
			expect( $docblock )->toContain( 'default: table' );
		} );

		it( 'lists markdown and json formats', function () {
			$docblock = ( new ReflectionMethod( Status::class, '__invoke' ) )->getDocComment();
			expect( $docblock )->toContain( '- markdown' );
			expect( $docblock )->toContain( '- json' );
		} );

		it( 'documents the --network flag', function () {
			$docblock = ( new ReflectionMethod( Status::class, '__invoke' ) )->getDocComment();
			expect( $docblock )->toContain( '[--network]' );
		} );

		it( 'has EXAMPLES section in docblock', function () {
			$method = new ReflectionMethod( Status::class, '__invoke' );
			expect( $method->getDocComment() )->toContain( '## EXAMPLES' );
		} );

		it( 'has @when after_wp_load annotation', function () {
			$method = new ReflectionMethod( Status::class, '__invoke' );
			expect( $method->getDocComment() )->toContain( '@when after_wp_load' );
		} );
	} );
} );
