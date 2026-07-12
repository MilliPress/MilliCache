<?php
/**
 * Tests for the cache metrics Recorder.
 *
 * @link       https://www.millipress.com
 * @since      1.7.0
 *
 * @package    MilliCache
 */

use MilliCache\Engine\Metrics\Recorder;
use MilliCache\Engine\Metrics\Store;

/**
 * In-memory {@see Store} for assertions — mirrors the Redis hash semantics
 * (one field map per resolution) without touching a backend.
 */
final class FakeMetricsStore implements Store {

	/** @var array<string, array<string, int>> */
	public array $data = array(
		Recorder::RES_HOURLY => array(),
		Recorder::RES_DAILY  => array(),
	);

	/** @var bool When true, increment() throws — to exercise best-effort. */
	public bool $explode = false;

	public function increment( string $resolution, array $deltas ): void {
		if ( $this->explode ) {
			throw new \RuntimeException( 'storage down' );
		}
		foreach ( $deltas as $field => $delta ) {
			$this->data[ $resolution ][ $field ] = ( $this->data[ $resolution ][ $field ] ?? 0 ) + $delta;
		}
	}

	public function set_fields( string $resolution, array $values ): void {
		foreach ( $values as $field => $value ) {
			$this->data[ $resolution ][ $field ] = $value;
		}
	}

	public function read( string $resolution ): array {
		return $this->data[ $resolution ];
	}

	public function delete_fields( string $resolution, array $fields ): void {
		foreach ( $fields as $field ) {
			unset( $this->data[ $resolution ][ $field ] );
		}
	}
}

describe( 'Recorder', function () {

	beforeEach( function () {
		$this->store    = new FakeMetricsStore();
		$this->recorder = new Recorder( $this->store, true ); // detailed (Pro) by default in tests.
		// A fixed, mid-hour reference time: 2026-05-30 14:25:00 UTC.
		$this->ts = gmmktime( 14, 25, 0, 5, 30, 2026 );
	} );

	describe( 'accumulation (detailed)', function () {
		it( 'records a hit as hit + hit_bytes + hit_time', function () {
			$this->recorder->record_hit( 13402, 5 );
			expect( $this->recorder->pending() )->toBe( array(
				'hit'       => 1,
				'hit_bytes' => 13402,
				'hit_time'  => 5,
			) );
		} );

		it( 'marks a stale hit as a subset of hits', function () {
			$this->recorder->record_hit( 100, 5, true );
			$pending = $this->recorder->pending();
			expect( $pending['hit'] )->toBe( 1 );
			expect( $pending['stale'] )->toBe( 1 );
		} );

		it( 'records a cacheable miss as miss + miss_bytes + miss_time', function () {
			$this->recorder->record_miss( 8000, 120 );
			expect( $this->recorder->pending() )->toBe( array(
				'miss'       => 1,
				'miss_bytes' => 8000,
				'miss_time'  => 120,
			) );
		} );

		it( 'clamps negative byte and time counts to zero', function () {
			$this->recorder->record_hit( -5, -3 );
			expect( $this->recorder->pending()['hit_bytes'] )->toBe( 0 );
			expect( $this->recorder->pending()['hit_time'] )->toBe( 0 );
		} );

		it( 'sums repeated events before a flush', function () {
			$this->recorder->record_hit( 100, 4 );
			$this->recorder->record_hit( 50, 6 );
			$pending = $this->recorder->pending();
			expect( $pending['hit'] )->toBe( 2 );
			expect( $pending['hit_bytes'] )->toBe( 150 );
			expect( $pending['hit_time'] )->toBe( 10 );
		} );
	} );

	describe( 'free mode (hit/miss only)', function () {
		beforeEach( function () {
			$this->free = new Recorder( $this->store, false );
		} );

		it( 'records a hit as the count only — no detailed fields', function () {
			$this->free->record_hit( 13402, 5, true );
			expect( $this->free->pending() )->toBe( array( 'hit' => 1 ) );
		} );

		it( 'records a miss as the count only — no detailed fields', function () {
			$this->free->record_miss( 8000, 120 );
			expect( $this->free->pending() )->toBe( array( 'miss' => 1 ) );
		} );
	} );

	describe( 'bucket_key', function () {
		it( 'is fixed-width per resolution in GMT', function () {
			expect( Recorder::bucket_key( $this->ts, Recorder::RES_HOURLY ) )->toBe( '2026053014' );
			expect( Recorder::bucket_key( $this->ts, Recorder::RES_DAILY ) )->toBe( '20260530' );
		} );
	} );

	describe( 'flush', function () {
		it( 'writes pending counters to the current hourly bucket and resets', function () {
			$this->recorder->record_hit( 13402, 5 );
			$this->recorder->flush( $this->ts );

			expect( $this->store->read( Recorder::RES_HOURLY ) )->toBe( array(
				'2026053014:hit'       => 1,
				'2026053014:hit_bytes' => 13402,
				'2026053014:hit_time'  => 5,
			) );
			expect( $this->recorder->pending() )->toBe( array() );
		} );

		it( 'is a no-op when nothing was recorded', function () {
			$this->recorder->flush( $this->ts );
			expect( $this->store->read( Recorder::RES_HOURLY ) )->toBe( array() );
		} );

		it( 'accumulates across flushes within the same hour', function () {
			$this->recorder->record_hit( 100, 5 );
			$this->recorder->flush( $this->ts );
			$this->recorder->record_hit( 100, 5 );
			$this->recorder->flush( $this->ts + 600 ); // +10 min, same hour

			expect( $this->store->read( Recorder::RES_HOURLY )['2026053014:hit'] )->toBe( 2 );
		} );

		it( 'separates buckets across different hours', function () {
			$this->recorder->record_hit( 100, 5 );
			$this->recorder->flush( $this->ts );
			$this->recorder->record_hit( 100, 5 );
			$this->recorder->flush( $this->ts + 3600 ); // next hour

			$hourly = $this->store->read( Recorder::RES_HOURLY );
			expect( $hourly['2026053014:hit'] )->toBe( 1 );
			expect( $hourly['2026053015:hit'] )->toBe( 1 );
		} );

		it( 'swallows storage failures and still resets (best-effort)', function () {
			$this->store->explode = true;
			$this->recorder->record_hit( 100, 5 );

			$this->recorder->flush( $this->ts ); // must not throw

			expect( $this->recorder->pending() )->toBe( array() );
			expect( $this->store->read( Recorder::RES_HOURLY ) )->toBe( array() );
		} );
	} );

	describe( 'rollup', function () {
		it( 'sums a completed day of hourly buckets into one daily bucket', function () {
			// Two hours on 2026-05-29 (a past day relative to $this->ts on 05-30).
			$this->store->increment( Recorder::RES_HOURLY, array(
				'2026052910:hit'  => 5,
				'2026052910:miss' => 1,
				'2026052911:hit'  => 3,
			) );

			$this->recorder->rollup( $this->ts );

			$daily = $this->store->read( Recorder::RES_DAILY );
			expect( $daily['20260529:hit'] )->toBe( 8 );
			expect( $daily['20260529:miss'] )->toBe( 1 );
		} );

		it( 'leaves the current, incomplete day un-rolled', function () {
			$this->store->increment( Recorder::RES_HOURLY, array( '2026053009:hit' => 4 ) );

			$this->recorder->rollup( $this->ts );

			expect( $this->store->read( Recorder::RES_DAILY ) )->toBe( array() );
		} );

		it( 'is idempotent — re-running yields the same daily totals', function () {
			$this->store->increment( Recorder::RES_HOURLY, array( '2026052910:hit' => 5 ) );

			$this->recorder->rollup( $this->ts );
			$this->recorder->rollup( $this->ts );

			expect( $this->store->read( Recorder::RES_DAILY )['20260529:hit'] )->toBe( 5 );
		} );
	} );

	describe( 'prune', function () {
		it( 'removes hourly buckets older than the retention window, keeps recent', function () {
			$old    = gmdate( 'YmdH', $this->ts - 20 * DAY_IN_SECONDS ); // > 7 days
			$recent = gmdate( 'YmdH', $this->ts - 2 * DAY_IN_SECONDS );  // within window
			$this->store->increment( Recorder::RES_HOURLY, array(
				"$old:hit"    => 1,
				"$recent:hit" => 1,
			) );

			$this->recorder->prune( $this->ts );

			$hourly = $this->store->read( Recorder::RES_HOURLY );
			expect( $hourly )->toHaveKey( "$recent:hit" );
			expect( $hourly )->not->toHaveKey( "$old:hit" );
		} );

		it( 'removes daily buckets older than the retention window', function () {
			$old    = gmdate( 'Ymd', $this->ts - 40 * DAY_IN_SECONDS ); // > 30 days
			$recent = gmdate( 'Ymd', $this->ts - 10 * DAY_IN_SECONDS ); // within window
			$this->store->set_fields( Recorder::RES_DAILY, array(
				"$old:hit"    => 1,
				"$recent:hit" => 1,
			) );

			$this->recorder->prune( $this->ts );

			$daily = $this->store->read( Recorder::RES_DAILY );
			expect( $daily )->toHaveKey( "$recent:hit" );
			expect( $daily )->not->toHaveKey( "$old:hit" );
		} );

		it( 'honours custom retention windows', function () {
			// 2-day hourly / 7-day daily — both fixtures survive the defaults.
			$recorder = new Recorder( $this->store, false, array(
				Recorder::RES_HOURLY => 2,
				Recorder::RES_DAILY  => 7,
			) );

			$hourly_old  = gmdate( 'YmdH', $this->ts - 3 * DAY_IN_SECONDS );  // > 2, < 7 days
			$hourly_kept = gmdate( 'YmdH', $this->ts - DAY_IN_SECONDS );
			$daily_old   = gmdate( 'Ymd', $this->ts - 10 * DAY_IN_SECONDS );  // > 7, < 30 days
			$daily_kept  = gmdate( 'Ymd', $this->ts - 2 * DAY_IN_SECONDS );
			$this->store->increment( Recorder::RES_HOURLY, array(
				"$hourly_old:hit"  => 1,
				"$hourly_kept:hit" => 1,
			) );
			$this->store->set_fields( Recorder::RES_DAILY, array(
				"$daily_old:hit"  => 1,
				"$daily_kept:hit" => 1,
			) );

			$recorder->prune( $this->ts );

			expect( $this->store->read( Recorder::RES_HOURLY ) )->toHaveKey( "$hourly_kept:hit" );
			expect( $this->store->read( Recorder::RES_HOURLY ) )->not->toHaveKey( "$hourly_old:hit" );
			expect( $this->store->read( Recorder::RES_DAILY ) )->toHaveKey( "$daily_kept:hit" );
			expect( $this->store->read( Recorder::RES_DAILY ) )->not->toHaveKey( "$daily_old:hit" );
		} );
	} );

	describe( 'expired_fields', function () {
		it( 'selects fields strictly older than the cutoff (lexicographic = chronological)', function () {
			$fields = array( '2026052914:hit', '2026053014:hit' );
			// A 14-day cutoff before 2026-06-13 14:00 lands inside the gap.
			$expired = Recorder::expired_fields( $fields, Recorder::RES_HOURLY, 14, gmmktime( 14, 0, 0, 6, 13, 2026 ) );
			expect( $expired )->toBe( array( '2026052914:hit' ) );
		} );
	} );
} );
