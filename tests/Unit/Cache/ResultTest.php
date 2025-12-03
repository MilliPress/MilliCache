<?php
/**
 * Tests for CacheResult value object.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Engine\Cache\Result;
use MilliCache\Engine\Cache\Entry;

describe( 'CacheResult', function () {

	describe( 'factory methods', function () {
		it( 'creates cache miss', function () {
			$result = Result::miss();

			expect( $result->entry )->toBeNull();
			expect( $result->flags )->toBeArray()->toBeEmpty();
			expect( $result->locked )->toBeFalse();
			expect( $result->is_miss() )->toBeTrue();
			expect( $result->is_hit() )->toBeFalse();
		} );

		it( 'creates cache hit', function () {
			$entry = new Entry(
				output: 'Test',
				headers: array(),
				status: 200,
				gzip: false,
				updated: time()
			);

			$result = Result::hit( $entry, array( 'post:123', 'home' ), false );

			expect( $result->entry )->toBe( $entry );
			expect( $result->flags )->toBe( array( 'post:123', 'home' ) );
			expect( $result->locked )->toBeFalse();
			expect( $result->is_hit() )->toBeTrue();
			expect( $result->is_miss() )->toBeFalse();
		} );

		it( 'creates locked cache hit', function () {
			$entry = new Entry(
				output: 'Test',
				headers: array(),
				status: 200,
				gzip: false,
				updated: time()
			);

			$result = Result::hit( $entry, array(), true );

			expect( $result->locked )->toBeTrue();
		} );
	} );
} );
