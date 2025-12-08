<?php
/**
 * Tests for Response State value object.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Engine\Response\State;

describe( 'State', function () {

	describe( 'create', function () {
		it( 'creates a new context with the given hash', function () {
			$context = State::create( 'test-hash-123' );

			expect( $context->get_request_hash() )->toBe( 'test-hash-123' );
			expect( $context->get_ttl_override() )->toBeNull();
			expect( $context->get_grace_override() )->toBeNull();
			expect( $context->get_cache_decision() )->toBeNull();
			expect( $context->should_fcgi_regenerate() )->toBeFalse();
			expect( $context->get_debug_data() )->toBeNull();
		} );
	} );

	describe( 'default', function () {
		it( 'creates a default context with empty hash', function () {
			$context = State::default();

			expect( $context->get_request_hash() )->toBe( '' );
			expect( $context->get_ttl_override() )->toBeNull();
			expect( $context->get_grace_override() )->toBeNull();
			expect( $context->get_cache_decision() )->toBeNull();
			expect( $context->should_fcgi_regenerate() )->toBeFalse();
			expect( $context->get_debug_data() )->toBeNull();
		} );
	} );

	describe( 'with_ttl_override', function () {
		it( 'returns a new instance with modified TTL', function () {
			$original = State::create( 'test-hash' );
			$modified = $original->with_ttl_override( 3600 );

			// Original unchanged.
			expect( $original->get_ttl_override() )->toBeNull();

			// New instance has TTL.
			expect( $modified->get_ttl_override() )->toBe( 3600 );
			expect( $modified->get_request_hash() )->toBe( 'test-hash' );
		} );
	} );

	describe( 'with_grace_override', function () {
		it( 'returns a new instance with modified grace', function () {
			$original = State::create( 'test-hash' );
			$modified = $original->with_grace_override( 600 );

			// Original unchanged.
			expect( $original->get_grace_override() )->toBeNull();

			// New instance has grace.
			expect( $modified->get_grace_override() )->toBe( 600 );
			expect( $modified->get_request_hash() )->toBe( 'test-hash' );
		} );
	} );

	describe( 'with_cache_decision', function () {
		it( 'returns a new instance with cache decision', function () {
			$original = State::create( 'test-hash' );
			$modified = $original->with_cache_decision( false, 'user-logged-in' );

			// Original unchanged.
			expect( $original->get_cache_decision() )->toBeNull();

			// New instance has decision.
			expect( $modified->get_cache_decision() )->toBe( array(
				'decision' => false,
				'reason'   => 'user-logged-in',
			) );
		} );

		it( 'handles empty reason', function () {
			$context = State::create( 'test-hash' )
				->with_cache_decision( true );

			expect( $context->get_cache_decision() )->toBe( array(
				'decision' => true,
				'reason'   => '',
			) );
		} );
	} );

	describe( 'with_fcgi_regenerate', function () {
		it( 'returns a new instance with FCGI flag', function () {
			$original = State::create( 'test-hash' );
			$modified = $original->with_fcgi_regenerate( true );

			// Original unchanged.
			expect( $original->should_fcgi_regenerate() )->toBeFalse();

			// New instance has flag.
			expect( $modified->should_fcgi_regenerate() )->toBeTrue();
		} );
	} );

	describe( 'with_debug_data', function () {
		it( 'returns a new instance with debug data', function () {
			$original = State::create( 'test-hash' );
			$debug_data = array(
				'cache_hit'  => true,
				'ttl'        => 3600,
				'timestamps' => array( 'start' => 1234567890 ),
			);
			$modified = $original->with_debug_data( $debug_data );

			// Original unchanged.
			expect( $original->get_debug_data() )->toBeNull();

			// New instance has debug data.
			expect( $modified->get_debug_data() )->toBe( $debug_data );
		} );
	} );

	describe( 'with_cache_served', function () {
		it( 'returns a new instance marking cache as served', function () {
			$original = State::create( 'test-hash' );
			$modified = $original->with_cache_served();

			// Original unchanged.
			expect( $original->was_cache_served() )->toBeFalse();

			// New instance has flag.
			expect( $modified->was_cache_served() )->toBeTrue();
		} );
	} );

	describe( 'immutability', function () {
		it( 'ensures all with methods return new instances', function () {
			$context = State::create( 'test-hash' );

			$with_ttl = $context->with_ttl_override( 100 );
			$with_grace = $context->with_grace_override( 200 );
			$with_decision = $context->with_cache_decision( true );
			$with_fcgi = $context->with_fcgi_regenerate( true );
			$with_debug = $context->with_debug_data( array( 'test' => 'data' ) );
			$with_served = $context->with_cache_served();

			// All should be different instances.
			expect( spl_object_hash( $context ) )->not->toBe( spl_object_hash( $with_ttl ) );
			expect( spl_object_hash( $context ) )->not->toBe( spl_object_hash( $with_grace ) );
			expect( spl_object_hash( $context ) )->not->toBe( spl_object_hash( $with_decision ) );
			expect( spl_object_hash( $context ) )->not->toBe( spl_object_hash( $with_fcgi ) );
			expect( spl_object_hash( $context ) )->not->toBe( spl_object_hash( $with_debug ) );
			expect( spl_object_hash( $context ) )->not->toBe( spl_object_hash( $with_served ) );
		} );
	} );

	describe( 'chaining', function () {
		it( 'allows chaining multiple modifications', function () {
			$context = State::create( 'test-hash' )
				->with_ttl_override( 3600 )
				->with_grace_override( 600 )
				->with_cache_decision( true, 'always-cache' )
				->with_fcgi_regenerate( true )
				->with_debug_data( array( 'test' => 'data' ) )
				->with_cache_served();

			expect( $context->get_request_hash() )->toBe( 'test-hash' );
			expect( $context->get_ttl_override() )->toBe( 3600 );
			expect( $context->get_grace_override() )->toBe( 600 );
			expect( $context->get_cache_decision() )->toBe( array(
				'decision' => true,
				'reason'   => 'always-cache',
			) );
			expect( $context->should_fcgi_regenerate() )->toBeTrue();
			expect( $context->get_debug_data() )->toBe( array( 'test' => 'data' ) );
			expect( $context->was_cache_served() )->toBeTrue();
		} );
	} );
} );
