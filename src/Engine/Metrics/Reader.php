<?php
/**
 * Cache metrics read/aggregation.
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
 * Read-side counterpart to {@see Recorder}: turns the hourly bucket hash into
 * totals, the ratio, and a zero-filled per-hour series. The pure
 * {@see Reader::from_fields()} core also serves the merged network view.
 *
 * @since      1.7.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Reader {

	/**
	 * Storage seam the counters are read from.
	 *
	 * @var Store
	 */
	private Store $store;

	/**
	 * Constructor.
	 *
	 * @since 1.7.0
	 *
	 * @param Store $store Counter persistence seam.
	 */
	public function __construct( Store $store ) {
		$this->store = $store;
	}

	/**
	 * Summarise the last $hours hourly buckets for this store's prefix.
	 *
	 * @since 1.7.0
	 *
	 * @param int      $hours Window length in hours.
	 * @param int|null $now   Reference time; defaults to now.
	 * @return array{hits: int, misses: int, ratio: float|null, series: array<int, array{t: string, hits: int, misses: int}>}
	 */
	public function read( int $hours = 168, ?int $now = null ): array {
		return self::from_fields( $this->store->read( Recorder::RES_HOURLY ), $hours, $now );
	}

	/**
	 * Build a summary + trend series from a raw hourly field map.
	 *
	 * Pure: the network view passes a map merged across several blogs. The
	 * series is ordered oldest-first and zero-filled so gaps render flat.
	 *
	 * @since 1.7.0
	 *
	 * @param array<string, int> $fields Hourly fields (`<bucket>:<metric>` => value).
	 * @param int                $hours  Window length in hours.
	 * @param int|null           $now    Reference time; defaults to now.
	 * @return array{hits: int, misses: int, ratio: float|null, series: array<int, array{t: string, hits: int, misses: int}>}
	 */
	public static function from_fields( array $fields, int $hours, ?int $now = null ): array {
		$now = $now ?? time();

		$by_bucket = array();
		foreach ( $fields as $field => $value ) {
			list( $bucket, $metric ) = Recorder::parse_field( (string) $field );
			if ( 'hit' === $metric ) {
				$by_bucket[ $bucket ]['hits'] = (int) $value;
			} elseif ( 'miss' === $metric ) {
				$by_bucket[ $bucket ]['misses'] = (int) $value;
			}
		}

		$series = array();
		$hits   = 0;
		$misses = 0;

		for ( $i = $hours - 1; $i >= 0; $i-- ) {
			$bucket  = Recorder::bucket_key( $now - ( $i * HOUR_IN_SECONDS ), Recorder::RES_HOURLY );
			$h       = $by_bucket[ $bucket ]['hits'] ?? 0;
			$m       = $by_bucket[ $bucket ]['misses'] ?? 0;
			$hits   += $h;
			$misses += $m;

			$series[] = array(
				't'      => $bucket,
				'hits'   => $h,
				'misses' => $m,
			);
		}

		$total = $hits + $misses;

		return array(
			'hits'   => $hits,
			'misses' => $misses,
			'ratio'  => $total > 0 ? round( $hits / $total * 100, 1 ) : null,
			'series' => $series,
		);
	}
}
