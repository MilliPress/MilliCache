<?php
/**
 * Tests for the cache metrics Reader.
 *
 * @link       https://www.millipress.com
 * @since      1.7.0
 *
 * @package    MilliCache
 */

use MilliCache\Engine\Metrics\Reader;

describe( 'Reader::from_fields', function () {

	beforeEach( function () {
		// Fixed reference: 2026-05-30 14:25:00 UTC → current hour bucket 2026053014.
		$this->now = gmmktime( 14, 25, 0, 5, 30, 2026 );
	} );

	it( 'returns a null ratio and a zero-filled series when there is no data', function () {
		$result = Reader::from_fields( array(), 3, $this->now );

		expect( $result['hits'] )->toBe( 0 );
		expect( $result['misses'] )->toBe( 0 );
		expect( $result['ratio'] )->toBeNull();
		expect( $result['series'] )->toBe( array(
			array( 't' => '2026053012', 'hits' => 0, 'misses' => 0 ),
			array( 't' => '2026053013', 'hits' => 0, 'misses' => 0 ),
			array( 't' => '2026053014', 'hits' => 0, 'misses' => 0 ),
		) );
	} );

	it( 'sums hits/misses over the window and computes the ratio', function () {
		$result = Reader::from_fields(
			array(
				'2026053013:hit'  => 9,
				'2026053013:miss' => 1,
				'2026053014:hit'  => 5,
			),
			3,
			$this->now
		);

		expect( $result['hits'] )->toBe( 14 );
		expect( $result['misses'] )->toBe( 1 );
		expect( $result['ratio'] )->toBe( 93.3 ); // 14 / 15
	} );

	it( 'builds an ordered, zero-filled series (oldest first)', function () {
		$result = Reader::from_fields(
			array( '2026053013:hit' => 9, '2026053013:miss' => 1, '2026053014:hit' => 5 ),
			3,
			$this->now
		);

		expect( $result['series'] )->toBe( array(
			array( 't' => '2026053012', 'hits' => 0, 'misses' => 0 ),
			array( 't' => '2026053013', 'hits' => 9, 'misses' => 1 ),
			array( 't' => '2026053014', 'hits' => 5, 'misses' => 0 ),
		) );
	} );

	it( 'ignores buckets outside the window', function () {
		$result = Reader::from_fields(
			array(
				'2026053010:hit' => 100, // 4 hours before now — outside a 3-hour window
				'2026053014:hit' => 2,
			),
			3,
			$this->now
		);

		expect( $result['hits'] )->toBe( 2 );
		expect( $result['ratio'] )->toBe( 100.0 );
	} );

	it( 'ignores non-hit/miss fields (bytes, time)', function () {
		$result = Reader::from_fields(
			array(
				'2026053014:hit'       => 4,
				'2026053014:miss'      => 1,
				'2026053014:hit_bytes' => 99999,
				'2026053014:hit_time'  => 1234,
			),
			3,
			$this->now
		);

		expect( $result['hits'] )->toBe( 4 );
		expect( $result['misses'] )->toBe( 1 );
		expect( $result['ratio'] )->toBe( 80.0 );
	} );
} );
