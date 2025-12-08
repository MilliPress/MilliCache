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

		it( 'has clear method', function () {
			$reflection = new ReflectionClass( CLI::class );
			expect( $reflection->hasMethod( 'clear' ) )->toBeTrue();
		} );

		it( 'has stats method', function () {
			$reflection = new ReflectionClass( CLI::class );
			expect( $reflection->hasMethod( 'stats' ) )->toBeTrue();
		} );

		it( 'has cli method', function () {
			$reflection = new ReflectionClass( CLI::class );
			expect( $reflection->hasMethod( 'cli' ) )->toBeTrue();
		} );

		it( 'has fix method', function () {
			$reflection = new ReflectionClass( CLI::class );
			expect( $reflection->hasMethod( 'fix' ) )->toBeTrue();
		} );

		it( 'has status method', function () {
			$reflection = new ReflectionClass( CLI::class );
			expect( $reflection->hasMethod( 'status' ) )->toBeTrue();
		} );

		it( 'has test method', function () {
			$reflection = new ReflectionClass( CLI::class );
			expect( $reflection->hasMethod( 'test' ) )->toBeTrue();
		} );

		it( 'has config method', function () {
			$reflection = new ReflectionClass( CLI::class );
			expect( $reflection->hasMethod( 'config' ) )->toBeTrue();
		} );
	} );

	describe( 'properties', function () {
		it( 'has loader property', function () {
			$reflection = new ReflectionClass( CLI::class );
			expect( $reflection->hasProperty( 'loader' ) )->toBeTrue();
		} );

		it( 'has engine property', function () {
			$reflection = new ReflectionClass( CLI::class );
			expect( $reflection->hasProperty( 'engine' ) )->toBeTrue();
		} );

		it( 'has plugin_name property', function () {
			$reflection = new ReflectionClass( CLI::class );
			expect( $reflection->hasProperty( 'plugin_name' ) )->toBeTrue();
		} );

		it( 'has version property', function () {
			$reflection = new ReflectionClass( CLI::class );
			expect( $reflection->hasProperty( 'version' ) )->toBeTrue();
		} );

		it( 'loader property is protected', function () {
			$property = new ReflectionProperty( CLI::class, 'loader' );
			expect( $property->isProtected() )->toBeTrue();
		} );

		it( 'engine property is private', function () {
			$property = new ReflectionProperty( CLI::class, 'engine' );
			expect( $property->isPrivate() )->toBeTrue();
		} );

		it( 'plugin_name property is private', function () {
			$property = new ReflectionProperty( CLI::class, 'plugin_name' );
			expect( $property->isPrivate() )->toBeTrue();
		} );

		it( 'version property is private', function () {
			$property = new ReflectionProperty( CLI::class, 'version' );
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

		it( 'clear is public', function () {
			$method = new ReflectionMethod( CLI::class, 'clear' );
			expect( $method->isPublic() )->toBeTrue();
		} );

		it( 'clear returns void', function () {
			$method = new ReflectionMethod( CLI::class, 'clear' );
			$return_type = $method->getReturnType();
			expect( $return_type )->not->toBeNull();
			expect( $return_type->getName() )->toBe( 'void' );
		} );

		it( 'clear takes two array parameters', function () {
			$method = new ReflectionMethod( CLI::class, 'clear' );
			expect( $method->getNumberOfParameters() )->toBe( 2 );

			$params = $method->getParameters();
			expect( $params[0]->getName() )->toBe( 'args' );
			expect( $params[1]->getName() )->toBe( 'assoc_args' );
		} );

		it( 'stats is public', function () {
			$method = new ReflectionMethod( CLI::class, 'stats' );
			expect( $method->isPublic() )->toBeTrue();
		} );

		it( 'stats returns void', function () {
			$method = new ReflectionMethod( CLI::class, 'stats' );
			$return_type = $method->getReturnType();
			expect( $return_type )->not->toBeNull();
			expect( $return_type->getName() )->toBe( 'void' );
		} );

		it( 'stats takes two array parameters', function () {
			$method = new ReflectionMethod( CLI::class, 'stats' );
			expect( $method->getNumberOfParameters() )->toBe( 2 );

			$params = $method->getParameters();
			expect( $params[0]->getName() )->toBe( 'args' );
			expect( $params[1]->getName() )->toBe( 'assoc_args' );
		} );

		it( 'cli is public', function () {
			$method = new ReflectionMethod( CLI::class, 'cli' );
			expect( $method->isPublic() )->toBeTrue();
		} );

		it( 'cli returns void', function () {
			$method = new ReflectionMethod( CLI::class, 'cli' );
			$return_type = $method->getReturnType();
			expect( $return_type )->not->toBeNull();
			expect( $return_type->getName() )->toBe( 'void' );
		} );

		it( 'cli takes two array parameters', function () {
			$method = new ReflectionMethod( CLI::class, 'cli' );
			expect( $method->getNumberOfParameters() )->toBe( 2 );

			$params = $method->getParameters();
			expect( $params[0]->getName() )->toBe( 'args' );
			expect( $params[1]->getName() )->toBe( 'assoc_args' );
		} );

		it( 'fix is public', function () {
			$method = new ReflectionMethod( CLI::class, 'fix' );
			expect( $method->isPublic() )->toBeTrue();
		} );

		it( 'fix returns void', function () {
			$method = new ReflectionMethod( CLI::class, 'fix' );
			$return_type = $method->getReturnType();
			expect( $return_type )->not->toBeNull();
			expect( $return_type->getName() )->toBe( 'void' );
		} );

		it( 'fix takes two array parameters', function () {
			$method = new ReflectionMethod( CLI::class, 'fix' );
			expect( $method->getNumberOfParameters() )->toBe( 2 );

			$params = $method->getParameters();
			expect( $params[0]->getName() )->toBe( 'args' );
			expect( $params[1]->getName() )->toBe( 'assoc_args' );
		} );

		it( 'status is public', function () {
			$method = new ReflectionMethod( CLI::class, 'status' );
			expect( $method->isPublic() )->toBeTrue();
		} );

		it( 'status returns void', function () {
			$method = new ReflectionMethod( CLI::class, 'status' );
			$return_type = $method->getReturnType();
			expect( $return_type )->not->toBeNull();
			expect( $return_type->getName() )->toBe( 'void' );
		} );

		it( 'status takes two array parameters', function () {
			$method = new ReflectionMethod( CLI::class, 'status' );
			expect( $method->getNumberOfParameters() )->toBe( 2 );

			$params = $method->getParameters();
			expect( $params[0]->getName() )->toBe( 'args' );
			expect( $params[1]->getName() )->toBe( 'assoc_args' );
		} );

		it( 'test is public', function () {
			$method = new ReflectionMethod( CLI::class, 'test' );
			expect( $method->isPublic() )->toBeTrue();
		} );

		it( 'test returns void', function () {
			$method = new ReflectionMethod( CLI::class, 'test' );
			$return_type = $method->getReturnType();
			expect( $return_type )->not->toBeNull();
			expect( $return_type->getName() )->toBe( 'void' );
		} );

		it( 'test takes two array parameters', function () {
			$method = new ReflectionMethod( CLI::class, 'test' );
			expect( $method->getNumberOfParameters() )->toBe( 2 );

			$params = $method->getParameters();
			expect( $params[0]->getName() )->toBe( 'args' );
			expect( $params[1]->getName() )->toBe( 'assoc_args' );
		} );

		it( 'config is public', function () {
			$method = new ReflectionMethod( CLI::class, 'config' );
			expect( $method->isPublic() )->toBeTrue();
		} );

		it( 'config returns void', function () {
			$method = new ReflectionMethod( CLI::class, 'config' );
			$return_type = $method->getReturnType();
			expect( $return_type )->not->toBeNull();
			expect( $return_type->getName() )->toBe( 'void' );
		} );

		it( 'config takes two array parameters', function () {
			$method = new ReflectionMethod( CLI::class, 'config' );
			expect( $method->getNumberOfParameters() )->toBe( 2 );

			$params = $method->getParameters();
			expect( $params[0]->getName() )->toBe( 'args' );
			expect( $params[1]->getName() )->toBe( 'assoc_args' );
		} );
	} );

	describe( 'constructor signature', function () {
		it( 'constructor takes 4 parameters', function () {
			$method = new ReflectionMethod( CLI::class, '__construct' );
			expect( $method->getNumberOfParameters() )->toBe( 4 );
		} );

		it( 'constructor parameters are correctly typed', function () {
			$method = new ReflectionMethod( CLI::class, '__construct' );
			$params = $method->getParameters();

			// First parameter: Loader.
			expect( $params[0]->getName() )->toBe( 'loader' );
			expect( $params[0]->getType()->getName() )->toBe( 'MilliCache\Core\Loader' );

			// Second parameter: Engine.
			expect( $params[1]->getName() )->toBe( 'engine' );
			expect( $params[1]->getType()->getName() )->toBe( 'MilliCache\Engine' );

			// Third parameter: plugin_name.
			expect( $params[2]->getName() )->toBe( 'plugin_name' );
			expect( $params[2]->getType()->getName() )->toBe( 'string' );

			// Fourth parameter: version.
			expect( $params[3]->getName() )->toBe( 'version' );
			expect( $params[3]->getType()->getName() )->toBe( 'string' );
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
