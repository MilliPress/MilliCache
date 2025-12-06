<?php
/**
 * Tests for Options (formerly OverrideManager).
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Engine\Options;
use MilliCache\Engine\Response\State;

describe( 'Options', function () {

	describe( 'initialization', function () {
		it( 'starts with no options set', function () {
			$options = new Options();

			expect( $options->get_ttl() )->toBeNull();
			expect( $options->get_grace() )->toBeNull();
			expect( $options->get_cache_decision() )->toBeNull();
			expect( $options->has_any() )->toBeFalse();
		} );
	} );

	describe( 'set_ttl', function () {
		it( 'sets TTL override when value is positive', function () {
			$options = new Options();
			$options->set_ttl( 3600 );

			expect( $options->get_ttl() )->toBe( 3600 );
			expect( $options->has_any() )->toBeTrue();
		} );

		it( 'ignores zero TTL', function () {
			$options = new Options();
			$options->set_ttl( 0 );

			expect( $options->get_ttl() )->toBeNull();
			expect( $options->has_any() )->toBeFalse();
		} );

		it( 'ignores negative TTL', function () {
			$options = new Options();
			$options->set_ttl( -100 );

			expect( $options->get_ttl() )->toBeNull();
			expect( $options->has_any() )->toBeFalse();
		} );

		it( 'can update TTL to a new value', function () {
			$options = new Options();
			$options->set_ttl( 1800 );
			expect( $options->get_ttl() )->toBe( 1800 );

			$options->set_ttl( 3600 );
			expect( $options->get_ttl() )->toBe( 3600 );
		} );
	} );

	describe( 'set_grace', function () {
		it( 'sets grace override when value is positive', function () {
			$options = new Options();
			$options->set_grace( 600 );

			expect( $options->get_grace() )->toBe( 600 );
			expect( $options->has_any() )->toBeTrue();
		} );

		it( 'accepts zero grace', function () {
			$options = new Options();
			$options->set_grace( 0 );

			expect( $options->get_grace() )->toBe( 0 );
			expect( $options->has_any() )->toBeTrue();
		} );

		it( 'ignores negative grace', function () {
			$options = new Options();
			$options->set_grace( -100 );

			expect( $options->get_grace() )->toBeNull();
			expect( $options->has_any() )->toBeFalse();
		} );

		it( 'can update grace to a new value', function () {
			$options = new Options();
			$options->set_grace( 300 );
			expect( $options->get_grace() )->toBe( 300 );

			$options->set_grace( 600 );
			expect( $options->get_grace() )->toBe( 600 );
		} );
	} );

	describe( 'set_cache_decision', function () {
		it( 'sets cache decision with reason', function () {
			$options = new Options();
			$options->set_cache_decision( false, 'user-logged-in' );

			expect( $options->get_cache_decision() )->toBe( array(
				'decision' => false,
				'reason'   => 'user-logged-in',
			) );
			expect( $options->has_any() )->toBeTrue();
		} );

		it( 'sets cache decision without reason', function () {
			$options = new Options();
			$options->set_cache_decision( true );

			expect( $options->get_cache_decision() )->toBe( array(
				'decision' => true,
				'reason'   => '',
			) );
			expect( $options->has_any() )->toBeTrue();
		} );

		it( 'can update cache decision', function () {
			$options = new Options();
			$options->set_cache_decision( true, 'first reason' );
			expect( $options->get_cache_decision()['decision'] )->toBeTrue();

			$options->set_cache_decision( false, 'second reason' );
			expect( $options->get_cache_decision() )->toBe( array(
				'decision' => false,
				'reason'   => 'second reason',
			) );
		} );
	} );

	describe( 'multiple options', function () {
		it( 'can set multiple options together', function () {
			$options = new Options();
			$options->set_ttl( 7200 );
			$options->set_grace( 1800 );
			$options->set_cache_decision( true, 'custom rule' );

			expect( $options->get_ttl() )->toBe( 7200 );
			expect( $options->get_grace() )->toBe( 1800 );
			expect( $options->get_cache_decision() )->toBe( array(
				'decision' => true,
				'reason'   => 'custom rule',
			) );
			expect( $options->has_any() )->toBeTrue();
		} );

		it( 'has_any returns true with only TTL set', function () {
			$options = new Options();
			$options->set_ttl( 3600 );

			expect( $options->has_any() )->toBeTrue();
		} );

		it( 'has_any returns true with only grace set', function () {
			$options = new Options();
			$options->set_grace( 600 );

			expect( $options->has_any() )->toBeTrue();
		} );

		it( 'has_any returns true with only cache decision set', function () {
			$options = new Options();
			$options->set_cache_decision( false, 'test' );

			expect( $options->has_any() )->toBeTrue();
		} );
	} );

	describe( 'apply_to_state', function () {
		it( 'applies TTL override to state', function () {
			$options = new Options();
			$options->set_ttl( 3600 );

			$state = State::create( 'test-hash' );
			$updated = $options->apply_to_state( $state );

			expect( $updated->get_ttl_override() )->toBe( 3600 );
			expect( $updated->get_grace_override() )->toBeNull();
			expect( $updated->get_cache_decision() )->toBeNull();
		} );

		it( 'applies grace override to state', function () {
			$options = new Options();
			$options->set_grace( 600 );

			$state = State::create( 'test-hash' );
			$updated = $options->apply_to_state( $state );

			expect( $updated->get_ttl_override() )->toBeNull();
			expect( $updated->get_grace_override() )->toBe( 600 );
			expect( $updated->get_cache_decision() )->toBeNull();
		} );

		it( 'applies cache decision to state', function () {
			$options = new Options();
			$options->set_cache_decision( false, 'test-reason' );

			$state = State::create( 'test-hash' );
			$updated = $options->apply_to_state( $state );

			expect( $updated->get_ttl_override() )->toBeNull();
			expect( $updated->get_grace_override() )->toBeNull();
			expect( $updated->get_cache_decision() )->toBe( array(
				'decision' => false,
				'reason'   => 'test-reason',
			) );
		} );

		it( 'applies all options to state', function () {
			$options = new Options();
			$options->set_ttl( 7200 );
			$options->set_grace( 1800 );
			$options->set_cache_decision( true, 'all-set' );

			$state = State::create( 'test-hash' );
			$updated = $options->apply_to_state( $state );

			expect( $updated->get_ttl_override() )->toBe( 7200 );
			expect( $updated->get_grace_override() )->toBe( 1800 );
			expect( $updated->get_cache_decision() )->toBe( array(
				'decision' => true,
				'reason'   => 'all-set',
			) );
		} );

		it( 'returns unchanged state when no options set', function () {
			$options = new Options();

			$state = State::create( 'test-hash' );
			$updated = $options->apply_to_state( $state );

			expect( $updated->get_ttl_override() )->toBeNull();
			expect( $updated->get_grace_override() )->toBeNull();
			expect( $updated->get_cache_decision() )->toBeNull();
			expect( $updated->get_request_hash() )->toBe( 'test-hash' );
		} );

		it( 'preserves existing state values', function () {
			$options = new Options();
			$options->set_ttl( 3600 );

			$state = State::create( 'original-hash' )
				->with_grace_override( 900 )
				->with_fcgi_regenerate( true );

			$updated = $options->apply_to_state( $state );

			expect( $updated->get_request_hash() )->toBe( 'original-hash' );
			expect( $updated->get_ttl_override() )->toBe( 3600 );
			expect( $updated->get_grace_override() )->toBe( 900 );
			expect( $updated->should_fcgi_regenerate() )->toBeTrue();
		} );
	} );

	describe( 'reset', function () {
		it( 'clears all options', function () {
			$options = new Options();
			$options->set_ttl( 3600 );
			$options->set_grace( 600 );
			$options->set_cache_decision( false, 'test' );

			expect( $options->has_any() )->toBeTrue();

			$options->reset();

			expect( $options->get_ttl() )->toBeNull();
			expect( $options->get_grace() )->toBeNull();
			expect( $options->get_cache_decision() )->toBeNull();
			expect( $options->has_any() )->toBeFalse();
		} );

		it( 'can be reused after reset', function () {
			$options = new Options();
			$options->set_ttl( 3600 );
			$options->reset();

			$options->set_ttl( 7200 );
			expect( $options->get_ttl() )->toBe( 7200 );
			expect( $options->has_any() )->toBeTrue();
		} );
	} );
} );
