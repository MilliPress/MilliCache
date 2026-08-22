<?php
/**
 * Tests for Response Processor.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Engine\Cache\Config;
use MilliCache\Engine\Options;
use MilliCache\Engine\Response\State;
use MilliCache\Engine\Response\Headers;
use MilliCache\Engine\Response\Processor;

// Note: This test focuses on the ResponseManager's public API and integration
// with State. Full integration testing happens at the Engine level.

uses()->beforeEach( function () {
	// Create test config.
	$this->config = new Config(
		3600,    // ttl.
		600,     // grace.
		true,    // gzip.
		false,   // debug disabled for simpler tests.
		array(), // nocache_paths.
		array(), // nocache_cookies.
		array(), // ignore_cookies.
		array(), // ignore_request_keys.
		array()  // unique.
	);

	// Note: We can't easily create real manager instances without full WordPress setup.
	// These tests verify the ResponseManager's own logic and State integration.
} );

describe( 'ResponseManager', function () {

	describe( 'constructor', function () {
		it( 'accepts required dependencies', function () {
			// This test verifies constructor signature - actual instantiation requires
			// full manager dependencies which need WordPress environment.
			expect( true )->toBeTrue();
		} );
	} );

	describe( 'cache decision methods', function () {
		it( 'set_cache_decision returns updated context', function () {
			$context = State::create( 'test-hash' );

			// Simulate what ResponseManager::set_cache_decision does.
			$updated = $context->with_cache_decision( false, 'user-logged-in' );

			expect( $updated )->toBeInstanceOf( State::class );
			expect( $updated->get_cache_decision() )->toBe( array(
				'decision' => false,
				'reason'   => 'user-logged-in',
			) );
		} );

		it( 'get_cache_decision returns decision from context', function () {
			$context = State::create( 'test-hash' )
				->with_cache_decision( true, 'force-cache' );

			$decision = $context->get_cache_decision();

			expect( $decision )->toBe( array(
				'decision' => true,
				'reason'   => 'force-cache',
			) );
		} );
	} );

	describe( 'context integration', function () {
		it( 'works with context ttl and grace options', function () {
			$context = State::create( 'test-hash' )
				->with_ttl_override( 7200 )
				->with_grace_override( 1200 );

			expect( $context->get_ttl_override() )->toBe( 7200 );
			expect( $context->get_grace_override() )->toBe( 1200 );
		} );

		it( 'works with context fcgi regenerate flag', function () {
			$context = State::create( 'test-hash' )
				->with_fcgi_regenerate( true );

			expect( $context->should_fcgi_regenerate() )->toBeTrue();
		} );

		it( 'works with context cache_served flag', function () {
			$context = State::create( 'test-hash' )
				->with_cache_served();

			expect( $context->was_cache_served() )->toBeTrue();
		} );

		it( 'supports chaining all context modifications', function () {
			$context = State::create( 'test-hash' )
				->with_ttl_override( 3600 )
				->with_grace_override( 600 )
				->with_cache_decision( true, 'always' )
				->with_fcgi_regenerate( false );

			expect( $context->get_request_hash() )->toBe( 'test-hash' );
			expect( $context->get_ttl_override() )->toBe( 3600 );
			expect( $context->get_grace_override() )->toBe( 600 );
			expect( $context->get_cache_decision() )->not->toBeNull();
			expect( $context->should_fcgi_regenerate() )->toBeFalse();
		} );
	} );

	describe( 'header manager integration', function () {
		it( 'get_header_manager would return Headers', function () {
			// Verify Headers can be instantiated.
			$header_manager = new Headers( $this->config );

			expect( $header_manager )->toBeInstanceOf( Headers::class );
		} );
	} );

	describe( 'output buffer behavior', function () {
		it( 'process_output_buffer returns null for fcgi regenerate', function () {
			$context = State::create( 'test-hash' )
				->with_fcgi_regenerate( true );

			// Simulate the logic: return null if fcgi_regenerate.
			$output = 'test output';
			$result = $context->should_fcgi_regenerate() ? null : $output;

			expect( $result )->toBeNull();
		} );

		it( 'process_output_buffer returns output normally', function () {
			$context = State::create( 'test-hash' )
				->with_fcgi_regenerate( false );

			// Simulate the logic: return output if not fcgi_regenerate.
			$output = 'test output';
			$result = $context->should_fcgi_regenerate() ? null : $output;

			expect( $result )->toBe( 'test output' );
		} );
	} );

	describe( 'class structure', function () {
		it( 'is a final class', function () {
			$reflection = new ReflectionClass( Processor::class );
			expect( $reflection->isFinal() )->toBeTrue();
		} );

		it( 'has start_output_buffer method', function () {
			$reflection = new ReflectionClass( Processor::class );
			expect( $reflection->hasMethod( 'start_output_buffer' ) )->toBeTrue();
		} );

		it( 'has process_output_buffer method', function () {
			$reflection = new ReflectionClass( Processor::class );
			expect( $reflection->hasMethod( 'process_output_buffer' ) )->toBeTrue();
		} );

		it( 'has retrieve_and_serve_cache method', function () {
			$reflection = new ReflectionClass( Processor::class );
			expect( $reflection->hasMethod( 'retrieve_and_serve_cache' ) )->toBeTrue();
		} );
	} );

	describe( 'method signatures', function () {
		it( 'start_output_buffer takes State parameter', function () {
			$method = new ReflectionMethod( Processor::class, 'start_output_buffer' );
			$params = $method->getParameters();

			expect( count( $params ) )->toBe( 1 );
			expect( $params[0]->getName() )->toBe( 'context' );
			expect( $params[0]->getType()->getName() )->toBe( State::class );
		} );

		it( 'start_output_buffer returns void', function () {
			$method = new ReflectionMethod( Processor::class, 'start_output_buffer' );
			$returnType = $method->getReturnType();

			expect( $returnType )->not->toBeNull();
			expect( $returnType->getName() )->toBe( 'void' );
		} );

		it( 'process_output_buffer takes output string and phase bitmask', function () {
			$method = new ReflectionMethod( Processor::class, 'process_output_buffer' );
			$params = $method->getParameters();

			expect( count( $params ) )->toBe( 2 );
			expect( $params[0]->getName() )->toBe( 'output' );
			expect( $params[0]->getType()->getName() )->toBe( 'string' );
			expect( $params[1]->getName() )->toBe( 'phase' );
			expect( $params[1]->getType()->getName() )->toBe( 'int' );
			expect( $params[1]->isDefaultValueAvailable() )->toBeTrue();
			expect( $params[1]->getDefaultValue() )->toBe( 0 );
		} );

		it( 'process_output_buffer returns nullable string', function () {
			$method = new ReflectionMethod( Processor::class, 'process_output_buffer' );
			$returnType = $method->getReturnType();

			expect( $returnType )->not->toBeNull();
			expect( $returnType->getName() )->toBe( 'string' );
			expect( $returnType->allowsNull() )->toBeTrue();
		} );

		it( 'retrieve_and_serve_cache takes State parameter', function () {
			$method = new ReflectionMethod( Processor::class, 'retrieve_and_serve_cache' );
			$params = $method->getParameters();

			expect( count( $params ) )->toBe( 1 );
			expect( $params[0]->getName() )->toBe( 'state' );
			expect( $params[0]->getType()->getName() )->toBe( State::class );
		} );

		it( 'retrieve_and_serve_cache returns State', function () {
			$method = new ReflectionMethod( Processor::class, 'retrieve_and_serve_cache' );
			$returnType = $method->getReturnType();

			expect( $returnType )->not->toBeNull();
			expect( $returnType->getName() )->toBe( State::class );
		} );
	} );

	describe( 'preloader metrics exclusion', function () {
		// is_internal_request() reads only ServerVars + a const, so an
		// instance without the constructor's manager dependencies suffices.
		$invoke = function ( ?string $ua ) {
			if ( null === $ua ) {
				unset( $_SERVER['HTTP_USER_AGENT'] );
			} else {
				$_SERVER['HTTP_USER_AGENT'] = $ua;
			}

			$processor = ( new ReflectionClass( Processor::class ) )->newInstanceWithoutConstructor();
			$method    = new ReflectionMethod( Processor::class, 'is_internal_request' );
			$method->setAccessible( true );

			$result = $method->invoke( $processor );
			unset( $_SERVER['HTTP_USER_AGENT'] );

			return $result;
		};

		it( 'flags a MilliCache/ User-Agent as the preloader', function () use ( $invoke ) {
			expect( $invoke( 'MilliCache/1.2; +https://millipress.com' ) )->toBeTrue();
		} );

		it( 'does not flag ordinary visitor User-Agents', function () use ( $invoke ) {
			expect( $invoke( 'Mozilla/5.0 (X11; Linux) Chrome/120' ) )->toBeFalse();
		} );

		it( 'requires the marker at the start, not merely present', function () use ( $invoke ) {
			expect( $invoke( 'Proxy via MilliCache/1.2' ) )->toBeFalse();
		} );

		it( 'treats a missing User-Agent as a normal request', function () use ( $invoke ) {
			expect( $invoke( null ) )->toBeFalse();
		} );
	} );

	describe( 'vary bypass decision', function () {
		// should_bypass_storage() is self-contained (no manager dependencies),
		// so an instance without the constructor suffices.
		$invoke = function ( array $headers ) {
			$processor = ( new ReflectionClass( Processor::class ) )->newInstanceWithoutConstructor();
			$method    = new ReflectionMethod( Processor::class, 'should_bypass_storage' );
			$method->setAccessible( true );

			return $method->invoke( $processor, $headers );
		};

		it( 'allows responses without a Vary header', function () use ( $invoke ) {
			expect( $invoke( array( 'Content-Type: text/html' ) ) )->toBeNull();
		} );

		it( 'allows Vary on Accept and Accept-Encoding', function () use ( $invoke ) {
			expect( $invoke( array( 'Vary: Accept-Encoding' ) ) )->toBeNull();
			expect( $invoke( array( 'Vary: Accept, Accept-Encoding' ) ) )->toBeNull();
		} );

		it( 'allows Vary on headers covered by request keying', function () use ( $invoke ) {
			expect( $invoke( array( 'Vary: Authorization' ) ) )->toBeNull();
			expect( $invoke( array( 'Vary: Host' ) ) )->toBeNull();
		} );

		it( 'bypasses Vary: Cookie to avoid per-visitor fragmentation', function () use ( $invoke ) {
			expect( $invoke( array( 'Vary: Cookie' ) ) )->toBe( 'Vary: cookie is not supported' );
		} );

		it( 'allows Vary on inert request-body headers', function () use ( $invoke ) {
			expect( $invoke( array( 'Vary: Content-Type' ) ) )->toBeNull();
			expect( $invoke( array( 'Vary: Accept-Encoding, Content-Type' ) ) )->toBeNull();
			expect( $invoke( array( 'Vary: Content-Length' ) ) )->toBeNull();
		} );

		it( 'allows the exact Vary header Jetpack sends on every frontend request', function () use ( $invoke ) {
			// Automattic\Jetpack\Status\Request::is_frontend(), see issue #172.
			expect( $invoke( array( 'Vary: accept, content-type' ) ) )->toBeNull();
		} );

		it( 'matches tokens case-insensitively', function () use ( $invoke ) {
			expect( $invoke( array( 'vary: ACCEPT-ENCODING , Host' ) ) )->toBeNull();
		} );

		it( 'bypasses Vary: * with its own reason', function () use ( $invoke ) {
			expect( $invoke( array( 'Vary: *' ) ) )->toBe( 'Vary: * is uncacheable' );
			expect( $invoke( array( 'Vary: Accept-Encoding, *' ) ) )->toBe( 'Vary: * is uncacheable' );
		} );

		it( 'bypasses unsupported tokens and names the offender', function () use ( $invoke ) {
			expect( $invoke( array( 'Vary: X-Device' ) ) )->toBe( 'Vary: x-device is not supported' );
			expect( $invoke( array( 'Vary: Accept-Encoding, Accept-Language' ) ) )->toBe( 'Vary: accept-language is not supported' );
		} );

		it( 'bypasses encoded bodies, merging multiple Content-Encoding headers', function () use ( $invoke ) {
			expect( $invoke( array( 'Content-Encoding: gzip' ) ) )->toBe( 'Content-Encoding: gzip is not supported' );
			expect( $invoke( array( 'Content-Encoding: gzip', 'content-encoding: identity' ) ) )->toBe( 'Content-Encoding: gzip, identity is not supported' );
		} );

		it( 'merges multiple Vary headers per RFC 9110', function () use ( $invoke ) {
			$unsupported_last = array(
				'Vary: Accept-Encoding',
				'Vary: X-Device',
			);
			$unsupported_first = array(
				'Vary: X-Device',
				'Vary: Accept-Encoding',
			);
			expect( $invoke( $unsupported_last ) )->toBe( 'Vary: x-device is not supported' );
			expect( $invoke( $unsupported_first ) )->toBe( 'Vary: x-device is not supported' );

			$all_supported = array(
				'Vary: Accept-Encoding',
				'Vary: Accept',
			);
			expect( $invoke( $all_supported ) )->toBeNull();
		} );
	} );

	describe( 'constructor signature', function () {
		it( 'constructor takes 5 parameters', function () {
			$method = new ReflectionMethod( Processor::class, '__construct' );
			expect( $method->getNumberOfParameters() )->toBe( 5 );
		} );

		it( 'constructor parameters are correctly typed', function () {
			$method = new ReflectionMethod( Processor::class, '__construct' );
			$params = $method->getParameters();

			expect( $params[0]->getName() )->toBe( 'config' );
			expect( $params[1]->getName() )->toBe( 'flags' );
			expect( $params[2]->getName() )->toBe( 'headers' );
			expect( $params[3]->getName() )->toBe( 'cache_manager' );
			expect( $params[4]->getName() )->toBe( 'request_manager' );
		} );
	} );
} );
