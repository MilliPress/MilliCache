<?php
/**
 * Metrics subsystem: request-scoped writes, reads, and nightly maintenance.
 *
 * @link       https://www.millipress.com
 * @since      1.7.0
 *
 * @package     MilliCache
 * @subpackage  Engine\Metrics
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Engine\Metrics;

use MilliCache\Core\Storage;

! defined( 'ABSPATH' ) && exit;

/**
 * The metrics subsystem: request-scoped writes ({@see self::record()}), reads
 * ({@see self::read()}), and the nightly rollup ({@see self::rollup()}).
 *
 * @since      1.7.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Manager {

	/**
	 * Storage the counters are written, read, and rolled through.
	 *
	 * @var Storage
	 */
	private Storage $storage;

	/**
	 * Current blog's counter prefix (the per-blog read target).
	 *
	 * @var string
	 */
	private string $prefix;

	/**
	 * Whether the write collector records the detailed Pro field set.
	 *
	 * @var bool
	 */
	private bool $detailed;

	/**
	 * Days to keep per resolution on the nightly prune (`RES_*` => days).
	 *
	 * @var array<string, int>
	 */
	private array $retention;

	/**
	 * The request-scoped write collector, built on first {@see self::record()}.
	 *
	 * @var Collector|null
	 */
	private ?Collector $collector = null;

	/**
	 * Constructor.
	 *
	 * @since 1.7.0
	 *
	 * @param Storage            $storage   Storage instance.
	 * @param string             $prefix    Current blog's metrics prefix.
	 * @param bool               $detailed  Record the detailed Pro field set on writes.
	 * @param array<string, int> $retention Days to keep per resolution (`RES_*` => days).
	 */
	public function __construct( Storage $storage, string $prefix, bool $detailed, array $retention = array() ) {
		$this->storage   = $storage;
		$this->prefix    = $prefix;
		$this->detailed  = $detailed;
		$this->retention = $retention;
	}

	/**
	 * The request-scoped write collector; the `metrics.active` Pro module adds
	 * the detailed field set.
	 *
	 * @since 1.7.0
	 *
	 * @return Collector The write collector.
	 */
	public function record(): Collector {
		if ( null === $this->collector ) {
			$this->collector = new Collector( $this->storage, $this->detailed );
		}
		return $this->collector;
	}

	/**
	 * Flush this request's buffered counters (no-op if nothing was recorded).
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function flush(): void {
		if ( null !== $this->collector ) {
			$this->collector->flush();
		}
	}

	/**
	 * Read the hit-ratio summary + trend series. The network view pools every
	 * blog's hits/misses (not an average of averages).
	 *
	 * @since 1.7.0
	 *
	 * @param bool $network Aggregate across all blogs.
	 * @param int  $hours   Window length in hours.
	 * @return array{hits: int, misses: int, ratio: float|null, series: array<int, array{t: string, hits: int, misses: int}>}
	 */
	public function read( bool $network, int $hours = 168 ): array {
		if ( ! $network ) {
			return ( new Reader( new StorageStore( $this->storage, $this->prefix ) ) )->read( $hours );
		}

		$merged = array();
		foreach ( $this->storage->metrics_prefixes() as $prefix ) {
			foreach ( $this->storage->metrics_read( $prefix, Recorder::RES_HOURLY ) as $field => $value ) {
				$merged[ $field ] = ( $merged[ $field ] ?? 0 ) + $value;
			}
		}

		return Reader::from_fields( $merged, $hours );
	}

	/**
	 * Roll up and prune every blog's hourly buckets to daily (nightly).
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function rollup(): void {
		foreach ( $this->storage->metrics_prefixes() as $prefix ) {
			$recorder = new Recorder( new StorageStore( $this->storage, $prefix ), false, $this->retention );
			$recorder->rollup();
		}
	}
}
