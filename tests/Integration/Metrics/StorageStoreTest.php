<?php
/**
 * Integration tests for the Redis-backed metrics store.
 *
 * Exercises the full write stack (Recorder → StorageStore → Storage → Redis)
 * against a real server. Skipped when Redis is unreachable.
 *
 * @link       https://www.millipress.com
 * @since      1.7.0
 *
 * @package    MilliCache
 */

use MilliCache\Core\Storage;
use MilliCache\Engine\Metrics\Recorder;
use MilliCache\Engine\Metrics\StorageStore;

describe( 'StorageStore (integration)', function () {

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

		// Independent raw client for verification + cleanup.
		$this->redis = new \Predis\Client( array( 'host' => '127.0.0.1', 'port' => 6379 ) );
		$keys        = $this->redis->keys( 'mc_itest:m:*' );
		if ( ! empty( $keys ) ) {
			$this->redis->del( $keys );
		}

		// A fixed, mid-hour reference time: 2026-05-30 14:25:00 UTC.
		$this->ts = gmmktime( 14, 25, 0, 5, 30, 2026 );
	} );

	afterEach( function () {
		if ( isset( $this->redis ) ) {
			$keys = $this->redis->keys( 'mc_itest:m:*' );
			if ( ! empty( $keys ) ) {
				$this->redis->del( $keys );
			}
		}
	} );

	it( 'writes the detailed hit set into the per-blog hourly hash', function () {
		$recorder = new Recorder( new StorageStore( $this->storage, '2:' ), true );
		$recorder->record_hit( 1000, 5 );
		$recorder->flush( $this->ts );

		$hash = $this->redis->hgetall( 'mc_itest:m:2:h' );
		expect( $hash['2026053014:hit'] )->toBe( '1' );        // Redis returns strings.
		expect( $hash['2026053014:hit_bytes'] )->toBe( '1000' );
		expect( $hash['2026053014:hit_time'] )->toBe( '5' );
	} );

	it( 'keeps single-site and per-blog counters in separate keys', function () {
		( new StorageStore( $this->storage, '' ) )->increment( 'h', array( 'x:hit' => 1 ) );
		( new StorageStore( $this->storage, '2:' ) )->increment( 'h', array( 'x:hit' => 1 ) );

		expect( $this->redis->exists( 'mc_itest:m:h' ) )->toBe( 1 );    // single-site
		expect( $this->redis->exists( 'mc_itest:m:2:h' ) )->toBe( 1 );  // blog 2
	} );

	it( 'round-trips through read() with integer casting', function () {
		$store = new StorageStore( $this->storage, '2:' );
		$store->increment( 'h', array( 'a:hit' => 3, 'a:miss' => 2 ) );

		$read = $store->read( 'h' );
		expect( $read['a:hit'] )->toBe( 3 );
		expect( $read['a:miss'] )->toBe( 2 );
	} );

	it( 'overwrites fields with set_fields and removes them with delete_fields', function () {
		$store = new StorageStore( $this->storage, '' );

		$store->increment( 'd', array( '20260530:hit' => 5 ) );
		$store->set_fields( 'd', array( '20260530:hit' => 5 ) ); // idempotent overwrite, not +5
		expect( $store->read( 'd' )['20260530:hit'] )->toBe( 5 );

		$store->delete_fields( 'd', array( '20260530:hit' ) );
		expect( $store->read( 'd' ) )->toBe( array() );
	} );

	it( 'rolls a completed day up into the daily hash end-to-end', function () {
		$store    = new StorageStore( $this->storage, '' );
		$recorder = new Recorder( $store, true );

		$store->increment( 'h', array( '2026052910:hit' => 4, '2026052910:miss' => 1 ) );
		$recorder->rollup( $this->ts ); // 2026-05-30 → 05-29 is a completed day

		expect( $store->read( 'd' )['20260529:hit'] )->toBe( 4 );
		expect( $store->read( 'd' )['20260529:miss'] )->toBe( 1 );
	} );

	it( 'enumerates the distinct site prefixes that have metrics', function () {
		( new StorageStore( $this->storage, '' ) )->increment( 'h', array( 'x:hit' => 1 ) );
		( new StorageStore( $this->storage, '1:' ) )->increment( 'h', array( 'x:hit' => 1 ) );
		( new StorageStore( $this->storage, '1:' ) )->increment( 'd', array( 'x:hit' => 1 ) ); // same prefix, other resolution
		( new StorageStore( $this->storage, '2:' ) )->increment( 'h', array( 'x:hit' => 1 ) );

		$prefixes = $this->storage->metrics_prefixes();
		sort( $prefixes );

		expect( $prefixes )->toBe( array( '', '1:', '2:' ) );
	} );

	it( 'rolls up every prefix when looped (the nightly cron shape)', function () {
		( new StorageStore( $this->storage, '1:' ) )->increment( 'h', array( '2026052910:hit' => 4 ) );
		( new StorageStore( $this->storage, '2:' ) )->increment( 'h', array( '2026052911:hit' => 7 ) );

		// Mirror Metrics\Manager::rollup(): one Recorder per prefix.
		foreach ( $this->storage->metrics_prefixes() as $prefix ) {
			( new Recorder( new StorageStore( $this->storage, $prefix ) ) )->rollup( $this->ts );
		}

		expect( ( new StorageStore( $this->storage, '1:' ) )->read( 'd' )['20260529:hit'] )->toBe( 4 );
		expect( ( new StorageStore( $this->storage, '2:' ) )->read( 'd' )['20260529:hit'] )->toBe( 7 );
	} );
} );
