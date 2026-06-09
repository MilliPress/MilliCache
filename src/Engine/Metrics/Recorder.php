<?php
/**
 * Cache hit/miss metrics recorder.
 *
 * @link       https://www.millipress.com
 * @since      1.7.0
 *
 * @package     MilliCache
 * @subpackage  Engine\Metrics
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Engine\Metrics;

! defined( 'ABSPATH' ) && exit;

/**
 * Records the plugin's own page-cache hit/miss counters into time buckets —
 * hourly (`YmdH`, GMT) rolled up nightly to daily (`Ymd`), fixed-width so
 * lexicographic order equals chronological. Scoped per blog via {@see Store};
 * accumulated in memory and flushed once, best-effort, post-response. Free
 * records `hit`/`miss`; detailed mode (Pro) adds the parallel `hit_*`/`miss_*`
 * set (bytes, time, stale).
 *
 * @since      1.7.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Recorder {

	/**
	 * Hourly bucket resolution key.
	 *
	 * @var string
	 */
	public const RES_HOURLY = 'h';

	/**
	 * Daily bucket resolution key.
	 *
	 * @var string
	 */
	public const RES_DAILY = 'd';

	/**
	 * How many days of hourly buckets to keep. Covers the 7-day view plus the
	 * "vs. previous period" delta (needs twice the window).
	 *
	 * @var int
	 */
	public const RETAIN_HOURLY_DAYS = 14;

	/**
	 * How many days of daily buckets to keep.
	 *
	 * @var int
	 */
	public const RETAIN_DAILY_DAYS = 365;

	/**
	 * Storage seam the counters are written through.
	 *
	 * @var Store
	 */
	private Store $store;

	/**
	 * Whether to record the detailed Pro field set in addition to hit/miss.
	 *
	 * @var bool
	 */
	private bool $detailed;

	/**
	 * Pending counter deltas for the current request (metric => delta).
	 *
	 * @var array<string, int>
	 */
	private array $pending = array();

	/**
	 * Constructor.
	 *
	 * @since 1.7.0
	 *
	 * @param Store $store    Counter persistence seam.
	 * @param bool  $detailed Record the detailed Pro field set (default: hit/miss only).
	 */
	public function __construct( Store $store, bool $detailed = false ) {
		$this->store    = $store;
		$this->detailed = $detailed;
	}

	/**
	 * Record a cache hit served for this request (detailed mode adds Pro fields).
	 *
	 * @since 1.7.0
	 *
	 * @param int  $bytes   Bytes served from cache (bandwidth, detailed only).
	 * @param int  $time_ms Serve time in milliseconds (detailed only).
	 * @param bool $stale   Whether the entry was served stale (detailed only).
	 * @return void
	 */
	public function record_hit( int $bytes, int $time_ms, bool $stale = false ): void {
		$this->bump( 'hit' );

		if ( ! $this->detailed ) {
			return;
		}

		$this->bump( 'hit_bytes', max( 0, $bytes ) );
		$this->bump( 'hit_time', max( 0, $time_ms ) );

		if ( $stale ) {
			$this->bump( 'stale' );
		}
	}

	/**
	 * Record a cacheable miss for this request (detailed mode adds Pro fields).
	 *
	 * @since 1.7.0
	 *
	 * @param int $bytes   Bytes of the freshly generated response (detailed only).
	 * @param int $time_ms Generation time in milliseconds (detailed only).
	 * @return void
	 */
	public function record_miss( int $bytes = 0, int $time_ms = 0 ): void {
		$this->bump( 'miss' );

		if ( ! $this->detailed ) {
			return;
		}

		$this->bump( 'miss_bytes', max( 0, $bytes ) );
		$this->bump( 'miss_time', max( 0, $time_ms ) );
	}

	/**
	 * Write accumulated counters to the current hourly bucket, then reset.
	 * Best-effort (storage errors swallowed); no-op when nothing was recorded.
	 *
	 * @since 1.7.0
	 *
	 * @param int|null $timestamp Bucket time; defaults to now.
	 */
	public function flush( ?int $timestamp = null ): void {
		if ( empty( $this->pending ) ) {
			return;
		}

		$bucket = self::bucket_key( $timestamp ?? time(), self::RES_HOURLY );
		$deltas = array();

		foreach ( $this->pending as $metric => $delta ) {
			$deltas[ self::field( $bucket, $metric ) ] = $delta;
		}

		try {
			$this->store->increment( self::RES_HOURLY, $deltas );
		} catch ( \Throwable $e ) {
			unset( $e ); // Metrics are best-effort; never surface to the request.
		}

		$this->pending = array();
	}

	/**
	 * Roll completed days' hourly buckets up into daily, then prune. Idempotent:
	 * daily fields are overwritten from the still-present hourly source.
	 *
	 * @since 1.7.0
	 *
	 * @param int|null $now Reference time; defaults to now.
	 */
	public function rollup( ?int $now = null ): void {
		$now   = $now ?? time();
		$today = self::bucket_key( $now, self::RES_DAILY );

		$daily = array();
		foreach ( $this->store->read( self::RES_HOURLY ) as $field => $value ) {
			list( $bucket, $metric ) = self::parse_field( $field );
			$day                     = substr( $bucket, 0, 8 );

			if ( '' === $metric || $day >= $today ) {
				continue; // Skip the current, still-incomplete day.
			}

			$key           = self::field( $day, $metric );
			$daily[ $key ] = ( $daily[ $key ] ?? 0 ) + (int) $value;
		}

		if ( ! empty( $daily ) ) {
			$this->store->set_fields( self::RES_DAILY, $daily );
		}

		$this->prune( $now );
	}

	/**
	 * Delete bucket fields older than their resolution's retention window.
	 *
	 * @since 1.7.0
	 *
	 * @param int|null $now Reference time; defaults to now.
	 * @return void
	 */
	public function prune( ?int $now = null ): void {
		$now = $now ?? time();

		foreach ( array( self::RES_HOURLY, self::RES_DAILY ) as $resolution ) {
			$fields  = array_keys( $this->store->read( $resolution ) );
			$expired = self::expired_fields( $fields, $resolution, $now );

			if ( ! empty( $expired ) ) {
				$this->store->delete_fields( $resolution, $expired );
			}
		}
	}

	/**
	 * Currently accumulated, not-yet-flushed deltas (metric => delta).
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, int>
	 */
	public function pending(): array {
		return $this->pending;
	}

	/**
	 * Add a delta to a pending metric.
	 *
	 * @since 1.7.0
	 *
	 * @param string $metric Metric name.
	 * @param int    $delta  Amount to add (defaults to 1).
	 * @return void
	 */
	private function bump( string $metric, int $delta = 1 ): void {
		$this->pending[ $metric ] = ( $this->pending[ $metric ] ?? 0 ) + $delta;
	}

	/**
	 * Bucket key for a timestamp at a resolution (GMT, fixed width).
	 *
	 * @since 1.7.0
	 *
	 * @param int    $timestamp  Unix time.
	 * @param string $resolution Resolution key.
	 * @return string `Ymd` for daily, `YmdH` for hourly.
	 */
	public static function bucket_key( int $timestamp, string $resolution ): string {
		return self::RES_DAILY === $resolution
			? gmdate( 'Ymd', $timestamp )
			: gmdate( 'YmdH', $timestamp );
	}

	/**
	 * Compose a hash field name from a bucket and metric.
	 *
	 * @since 1.7.0
	 *
	 * @param string $bucket Bucket key.
	 * @param string $metric Metric name.
	 * @return string `<bucket>:<metric>`.
	 */
	public static function field( string $bucket, string $metric ): string {
		return $bucket . ':' . $metric;
	}

	/**
	 * Split a hash field name back into its bucket and metric.
	 *
	 * @since 1.7.0
	 *
	 * @param string $field Field name.
	 * @return array{0: string, 1: string} `[bucket, metric]` (metric empty if malformed).
	 */
	public static function parse_field( string $field ): array {
		$parts = explode( ':', $field, 2 );
		return array( $parts[0], $parts[1] ?? '' );
	}

	/**
	 * Filter a field list down to those whose bucket is past retention.
	 *
	 * @since 1.7.0
	 *
	 * @param array<string> $fields     Field names to test.
	 * @param string        $resolution Resolution key.
	 * @param int           $now        Reference time.
	 * @return array<string> Fields whose bucket is older than the window.
	 */
	public static function expired_fields( array $fields, string $resolution, int $now ): array {
		$days   = self::RES_DAILY === $resolution ? self::RETAIN_DAILY_DAYS : self::RETAIN_HOURLY_DAYS;
		$cutoff = self::bucket_key( $now - ( $days * DAY_IN_SECONDS ), $resolution );

		$expired = array();
		foreach ( $fields as $field ) {
			list( $bucket ) = self::parse_field( $field );
			if ( '' !== $bucket && $bucket < $cutoff ) {
				$expired[] = $field;
			}
		}

		return $expired;
	}
}
