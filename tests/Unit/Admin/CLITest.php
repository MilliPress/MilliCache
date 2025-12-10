<?php
/**
 * Tests for CLI.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Admin\CLI;

// Ensure constants are defined.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/' );
}

describe( 'CLI', function () {

	describe( 'class structure', function () {
		it( 'is a final class', function () {
			$reflection = new ReflectionClass( CLI::class );
			expect( $reflection->isFinal() )->toBeTrue();
		} );

		it( 'has constructor', function () {
			$reflection = new ReflectionClass( CLI::class );
			expect( $reflection->hasMethod( '__construct' ) )->toBeTrue();
		} );

		it( 'has is_cli method', function () {
			$reflection = new ReflectionClass( CLI::class );
			expect( $reflection->hasMethod( 'is_cli' ) )->toBeTrue();
		} );

		it( 'has register_commands method', function () {
			$reflection = new ReflectionClass( CLI::class );
			expect( $reflection->hasMethod( 'register_commands' ) )->toBeTrue();
		} );
	} );

	describe( 'properties', function () {
		it( 'has plugin_name property', function () {
			$reflection = new ReflectionClass( CLI::class );
			expect( $reflection->hasProperty( 'plugin_name' ) )->toBeTrue();
		} );

		it( 'plugin_name property is private', function () {
			$property = new ReflectionProperty( CLI::class, 'plugin_name' );
			expect( $property->isPrivate() )->toBeTrue();
		} );
	} );

	describe( 'method signatures', function () {
		it( 'is_cli is public and static', function () {
			$method = new ReflectionMethod( CLI::class, 'is_cli' );
			expect( $method->isPublic() )->toBeTrue();
			expect( $method->isStatic() )->toBeTrue();
		} );

		it( 'is_cli returns bool', function () {
			$method = new ReflectionMethod( CLI::class, 'is_cli' );
			$return_type = $method->getReturnType();
			expect( $return_type )->not->toBeNull();
			expect( $return_type->getName() )->toBe( 'bool' );
		} );

		it( 'register_commands is private', function () {
			$method = new ReflectionMethod( CLI::class, 'register_commands' );
			expect( $method->isPrivate() )->toBeTrue();
		} );

		it( 'register_commands returns void', function () {
			$method = new ReflectionMethod( CLI::class, 'register_commands' );
			$return_type = $method->getReturnType();
			expect( $return_type )->not->toBeNull();
			expect( $return_type->getName() )->toBe( 'void' );
		} );
	} );

	describe( 'constructor signature', function () {
		it( 'constructor takes 1 parameter', function () {
			$method = new ReflectionMethod( CLI::class, '__construct' );
			expect( $method->getNumberOfParameters() )->toBe( 1 );
		} );

		it( 'constructor parameter is correctly typed', function () {
			$method = new ReflectionMethod( CLI::class, '__construct' );
			$params = $method->getParameters();

			// First parameter: plugin_name.
			expect( $params[0]->getName() )->toBe( 'plugin_name' );
			expect( $params[0]->getType()->getName() )->toBe( 'string' );
		} );
	} );

	describe( 'is_cli behavior', function () {
		it( 'returns false when WP_CLI is not defined', function () {
			// In a unit test environment without WP_CLI defined, should return false.
			$result = CLI::is_cli();
			expect( $result )->toBeFalse();
		} );
	} );
} );
