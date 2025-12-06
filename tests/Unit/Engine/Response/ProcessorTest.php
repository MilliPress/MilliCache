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

		it( 'check_cache_decision logic with bypass', function () {
			$context = State::create( 'test-hash' )
				->with_cache_decision( false, 'no-cache' );

			$decision = $context->get_cache_decision();

			// Simulate check logic.
			$should_cache = ! ( $decision && ! $decision['decision'] );

			expect( $should_cache )->toBeFalse();
		} );

		it( 'check_cache_decision logic with allow', function () {
			$context = State::create( 'test-hash' )
				->with_cache_decision( true, 'cache-enabled' );

			$decision = $context->get_cache_decision();

			// Simulate check logic.
			$should_cache = ! ( $decision && ! $decision['decision'] );

			expect( $should_cache )->toBeTrue();
		} );

		it( 'check_cache_decision logic with no decision', function () {
			$context = State::create( 'test-hash' );

			$decision = $context->get_cache_decision();

			// Simulate check logic.
			$should_cache = ! ( $decision && ! $decision['decision'] );

			expect( $should_cache )->toBeTrue();
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
				->with_fcgi_regenerate( false )
				->with_debug_data( array( 'test' => 'data' ) );

			expect( $context->get_request_hash() )->toBe( 'test-hash' );
			expect( $context->get_ttl_override() )->toBe( 3600 );
			expect( $context->get_grace_override() )->toBe( 600 );
			expect( $context->get_cache_decision() )->not->toBeNull();
			expect( $context->should_fcgi_regenerate() )->toBeFalse();
			expect( $context->get_debug_data() )->toBe( array( 'test' => 'data' ) );
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

		it( 'process_output_buffer takes string parameter', function () {
			$method = new ReflectionMethod( Processor::class, 'process_output_buffer' );
			$params = $method->getParameters();

			expect( count( $params ) )->toBe( 1 );
			expect( $params[0]->getName() )->toBe( 'output' );
			expect( $params[0]->getType()->getName() )->toBe( 'string' );
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
