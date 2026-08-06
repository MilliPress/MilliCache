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

if ( ! function_exists( 'is_multisite' ) ) {
	function is_multisite() {
		global $test_is_multisite;
		return $test_is_multisite ?? false;
	}
}

if ( ! class_exists( 'WP_CLI' ) ) {
	class WP_CLI {
		public static $config = array();

		public static function get_config( $key = null ) {
			return self::$config[ $key ] ?? null;
		}
	}
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

	describe( 'should_prefix_flag method', function () {
		$should_prefix = function ( string $flag ): bool {
			$method = new ReflectionMethod( Clear::class, 'should_prefix_flag' );
			$method->setAccessible( true );

			return $method->invoke( new Clear(), $flag );
		};

		beforeEach( function () {
			global $test_is_multisite;
			$test_is_multisite = false;
			WP_CLI::$config    = array();
		} );

		// Later-loading suites read the same globals; leave them clean.
		afterEach( function () {
			global $test_is_multisite;
			$test_is_multisite = false;
			WP_CLI::$config    = array();
		} );

		it( 'prefixes bare flags on single site', function () use ( $should_prefix ) {
			expect( $should_prefix( 'home' ) )->toBeTrue();
			expect( $should_prefix( 'archive:post' ) )->toBeTrue();
			expect( $should_prefix( 'archive:2026:07' ) )->toBeTrue();
		} );

		it( 'prefixes bare flags on multisite when --url scopes to a site', function () use ( $should_prefix ) {
			global $test_is_multisite;
			$test_is_multisite = true;
			WP_CLI::$config    = array( 'url' => 'https://wp.test' );

			expect( $should_prefix( 'home' ) )->toBeTrue();
		} );

		it( 'keeps bare flags raw on multisite without --url (network scope)', function () use ( $should_prefix ) {
			global $test_is_multisite;
			$test_is_multisite = true;

			expect( $should_prefix( 'home' ) )->toBeFalse();
		} );

		it( 'passes site-prefixed flags through raw', function () use ( $should_prefix ) {
			WP_CLI::$config = array( 'url' => 'https://wp.test' );

			expect( $should_prefix( '5:home' ) )->toBeFalse();
			expect( $should_prefix( '2:5:home' ) )->toBeFalse();
		} );

		it( 'passes patterns through raw', function () use ( $should_prefix ) {
			WP_CLI::$config = array( 'url' => 'https://wp.test' );

			expect( $should_prefix( '*:home' ) )->toBeFalse();
			expect( $should_prefix( '5:*' ) )->toBeFalse();
			expect( $should_prefix( 'archive:?' ) )->toBeFalse();
		} );

		it( 'never prefixes url: flags (stored unprefixed even on multisite)', function () use ( $should_prefix ) {
			global $test_is_multisite;
			$test_is_multisite = true;
			WP_CLI::$config    = array( 'url' => 'https://wp.test' );

			expect( $should_prefix( 'url:9cf5dfa8aa42f85b' ) )->toBeFalse();
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

		it( 'documents --uri option', function () {
			$method = new ReflectionMethod( Clear::class, '__invoke' );
			$docblock = $method->getDocComment();
			expect( $docblock )->toContain( '[--uri=<uri>]' );
		} );

		it( 'does not register --url as a subcommand option (conflicts with WP-CLI global)', function () {
			$method = new ReflectionMethod( Clear::class, '__invoke' );
			$docblock = $method->getDocComment();
			expect( $docblock )->not->toContain( '[--url=<url>]' );
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
