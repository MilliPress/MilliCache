<?php
/**
 * Tests for CLI Clear command.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Admin\CLI\Clear;

// Ensure constants are defined.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/' );
}

describe( 'CLI/Clear', function () {

	describe( 'class structure', function () {
		it( 'is a final class', function () {
			$reflection = new ReflectionClass( Clear::class );
			expect( $reflection->isFinal() )->toBeTrue();
		} );

		it( 'has no constructor', function () {
			$reflection = new ReflectionClass( Clear::class );
			expect( $reflection->getConstructor() )->toBeNull();
		} );

		it( 'has __invoke method', function () {
			$reflection = new ReflectionClass( Clear::class );
			expect( $reflection->hasMethod( '__invoke' ) )->toBeTrue();
		} );

		it( 'has clear_posts method', function () {
			$reflection = new ReflectionClass( Clear::class );
			expect( $reflection->hasMethod( 'clear_posts' ) )->toBeTrue();
		} );
	} );

	describe( '__invoke method', function () {
		it( 'is public', function () {
			$method = new ReflectionMethod( Clear::class, '__invoke' );
			expect( $method->isPublic() )->toBeTrue();
		} );

		it( 'returns void', function () {
			$method = new ReflectionMethod( Clear::class, '__invoke' );
			$return_type = $method->getReturnType();
			expect( $return_type )->not->toBeNull();
			expect( $return_type->getName() )->toBe( 'void' );
		} );

		it( 'takes two array parameters', function () {
			$method = new ReflectionMethod( Clear::class, '__invoke' );
			expect( $method->getNumberOfParameters() )->toBe( 2 );

			$params = $method->getParameters();
			expect( $params[0]->getName() )->toBe( 'args' );
			expect( $params[0]->getType()->getName() )->toBe( 'array' );
			expect( $params[1]->getName() )->toBe( 'assoc_args' );
			expect( $params[1]->getType()->getName() )->toBe( 'array' );
		} );
	} );

	describe( 'clear_posts method', function () {
		it( 'is private', function () {
			$method = new ReflectionMethod( Clear::class, 'clear_posts' );
			expect( $method->isPrivate() )->toBeTrue();
		} );

		it( 'returns void', function () {
			$method = new ReflectionMethod( Clear::class, 'clear_posts' );
			$return_type = $method->getReturnType();
			expect( $return_type )->not->toBeNull();
			expect( $return_type->getName() )->toBe( 'void' );
		} );

		it( 'takes four parameters', function () {
			$method = new ReflectionMethod( Clear::class, 'clear_posts' );
			expect( $method->getNumberOfParameters() )->toBe( 4 );

			$params = $method->getParameters();
			expect( $params[0]->getName() )->toBe( 'clear' );
			expect( $params[1]->getName() )->toBe( 'post_ids' );
			expect( $params[2]->getName() )->toBe( 'expire' );
			expect( $params[3]->getName() )->toBe( 'related' );
		} );
	} );

	describe( 'WP-CLI docblock', function () {
		it( 'has OPTIONS section in docblock', function () {
			$method = new ReflectionMethod( Clear::class, '__invoke' );
			$docblock = $method->getDocComment();
			expect( $docblock )->toContain( '## OPTIONS' );
		} );

		it( 'documents --id option', function () {
			$method = new ReflectionMethod( Clear::class, '__invoke' );
			$docblock = $method->getDocComment();
			expect( $docblock )->toContain( '[--id=<id>]' );
		} );

		it( 'documents --url option', function () {
			$method = new ReflectionMethod( Clear::class, '__invoke' );
			$docblock = $method->getDocComment();
			expect( $docblock )->toContain( '[--url=<url>]' );
		} );

		it( 'documents --flag option', function () {
			$method = new ReflectionMethod( Clear::class, '__invoke' );
			$docblock = $method->getDocComment();
			expect( $docblock )->toContain( '[--flag=<flag>]' );
		} );

		it( 'documents --site option', function () {
			$method = new ReflectionMethod( Clear::class, '__invoke' );
			$docblock = $method->getDocComment();
			expect( $docblock )->toContain( '[--site=<site>]' );
		} );

		it( 'documents --network option', function () {
			$method = new ReflectionMethod( Clear::class, '__invoke' );
			$docblock = $method->getDocComment();
			expect( $docblock )->toContain( '[--network=<network>]' );
		} );

		it( 'documents --related option', function () {
			$method = new ReflectionMethod( Clear::class, '__invoke' );
			$docblock = $method->getDocComment();
			expect( $docblock )->toContain( '[--related]' );
		} );

		it( 'documents --expire option', function () {
			$method = new ReflectionMethod( Clear::class, '__invoke' );
			$docblock = $method->getDocComment();
			expect( $docblock )->toContain( '[--expire]' );
		} );

		it( 'has EXAMPLES section in docblock', function () {
			$method = new ReflectionMethod( Clear::class, '__invoke' );
			$docblock = $method->getDocComment();
			expect( $docblock )->toContain( '## EXAMPLES' );
		} );

		it( 'has @when after_wp_load annotation', function () {
			$method = new ReflectionMethod( Clear::class, '__invoke' );
			$docblock = $method->getDocComment();
			expect( $docblock )->toContain( '@when after_wp_load' );
		} );
	} );
} );
