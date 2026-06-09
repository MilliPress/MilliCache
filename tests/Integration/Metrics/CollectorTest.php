<?php
/**
 * Integration tests for the metrics Collector gating.
 *
 * Verifies the master switch and detailed flag against a real Redis server.
 * Skipped when Redis is unreachable.
 *
 * @link       https://www.millipress.com
 * @since      1.7.0
 *
 * @package    MilliCache
 */

use MilliCache\Core\Storage;
use MilliCache\Engine\Metrics\Collector;

/**
 * Metric names present in a freshly-written hourly hash (drops the bucket).
 *
 * @param array<string, mixed> $hash HGETALL result (`<bucket>:<metric>` keys).
 * @return array<string>
 */
function metric_names( array $hash ): array {
	return array_map(
		static fn( string $field ): string => explode( ':', $field, 2 )[1] ?? '',
		array_keys( $hash )
	);
}

describe( 'Collector (integration)', function () {

	beforeEach( function () {
		$this->storage = new Storage(
			array(
				'host'         => '127.0.0.1',
				'port'         => 6379,
				'enc_password' => '',
				'db'           => 0,
				'persistent'   => false,
				'prefix'       => 'mc_itest',
			)
		);

		if ( ! $this->storage->ping() ) {
			$this->markTestSkipped( 'Redis not reachable on 127.0.0.1:6379' );
		}

		$this->redis = new \Predis\Client( array( 'host' => '127.0.0.1', 'port' => 6379 ) );
		$keys        = $this->redis->keys( 'mc_itest:m:*' );
		if ( ! empty( $keys ) ) {
			$this->redis->del( $keys );
		}
	} );

	afterEach( function () {
		if ( isset( $this->redis ) ) {
			$keys = $this->redis->keys( 'mc_itest:m:*' );
			if ( ! empty( $keys ) ) {
				$this->redis->del( $keys );
			}
		}
	} );

	it( 'records only hit/miss when detailed is off (free)', function () {
		$collector = new Collector( $this->storage, false );
		$collector->hit( '2:', 1000, 5, true );
		$collector->flush();

		$metrics = metric_names( $this->redis->hgetall( 'mc_itest:m:2:h' ) );
		expect( $metrics )->toContain( 'hit' );
		expect( $metrics )->not->toContain( 'hit_bytes' );
		expect( $metrics )->not->toContain( 'hit_time' );
		expect( $metrics )->not->toContain( 'stale' );
	} );

	it( 'records the detailed set when detailed is on (pro)', function () {
		$collector = new Collector( $this->storage, true );
		$collector->hit( '2:', 1000, 5 );
		$collector->flush();

		$metrics = metric_names( $this->redis->hgetall( 'mc_itest:m:2:h' ) );
		expect( $metrics )->toContain( 'hit' );
		expect( $metrics )->toContain( 'hit_bytes' );
		expect( $metrics )->toContain( 'hit_time' );
	} );

	it( 'records a miss into the same per-blog hash', function () {
		$collector = new Collector( $this->storage, false );
		$collector->miss( '2:', 8000, 120 );
		$collector->flush();

		$metrics = metric_names( $this->redis->hgetall( 'mc_itest:m:2:h' ) );
		expect( $metrics )->toContain( 'miss' );
		expect( $metrics )->not->toContain( 'miss_bytes' );
	} );
} );
