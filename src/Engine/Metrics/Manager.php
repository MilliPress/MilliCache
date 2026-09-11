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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The metrics subsystem: request-scoped writes ({@see self::record()}), reads
 * ({@see self::read()}), and the nightly rollup ({@see self::rollup()}), which
 * also mirrors the daily counters to the database and restores them after a
 * storage-server restart ({@see self::restore()}).
 *
 * @since      1.7.0
 * @since      1.9.0 Daily counters are mirrored to the database and restored.
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
	 * Database mirror of the daily counters.
	 *
	 * @var Mirror
	 */
	private Mirror $mirror;

	/**
	 * Constructor.
	 *
	 * @since 1.7.0
	 *
	 * @param Storage            $storage   Storage instance.
	 * @param string             $prefix    Current blog's metrics prefix.
	 * @param bool               $detailed  Record the detailed Pro field set on writes.
	 * @param array<string, int> $retention Days to keep per resolution (`RES_*` => days).
	 * @param Mirror|null        $mirror    Database mirror of the daily counters (for testing).
	 */
	public function __construct( Storage $storage, string $prefix, bool $detailed, array $retention = array(), ?Mirror $mirror = null ) {
		$this->storage   = $storage;
		$this->prefix    = $prefix;
		$this->detailed  = $detailed;
		$this->retention = $retention;
		$this->mirror    = $mirror ?? new Mirror();
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
	 * Bring mirrored daily counters back into storage.
	 *
	 * @since 1.9.0
	 *
	 * @param string|null $prefix Site/network prefix; defaults to the current blog.
	 * @param bool        $merge  Fill in missing fields even when storage is not empty.
	 * @return void
	 */
	public function restore( ?string $prefix = null, bool $merge = false ): void {
		$prefix = $prefix ?? $this->prefix;

		if ( ! $merge && $this->storage->metrics_count( $prefix, Recorder::RES_DAILY ) > 0 ) {
			return;
		}

		$mirrored = $this->mirror->read( $prefix );
		if ( empty( $mirrored ) ) {
			return;
		}

		$existing = $merge ? $this->storage->metrics_read( $prefix, Recorder::RES_DAILY ) : array();
		$missing  = array_diff_key( $mirrored, $existing );

		if ( ! empty( $missing ) ) {
			$this->storage->metrics_set( $prefix, Recorder::RES_DAILY, $missing );
		}
	}

	/**
	 * Roll up and prune every blog's hourly buckets to daily (nightly), then
	 * mirror the daily counters to the database. Blogs known only to the
	 * mirror are restored first, so a storage-server restart between two
	 * nightly runs costs no daily history.
	 *
	 * @since 1.7.0
	 * @since 1.9.0 Restores from and writes to the database mirror.
	 *
	 * @return void
	 */
	public function rollup(): void {
		$prefixes = array_unique( array_merge( $this->storage->metrics_prefixes(), $this->mirror->prefixes() ) );

		foreach ( $prefixes as $prefix ) {
			$this->restore( $prefix, true );

			$recorder = new Recorder( new StorageStore( $this->storage, $prefix ), false, $this->retention );
			$recorder->rollup();

			// An unreachable server reads as empty; keep the last good mirror then.
			$daily = $this->storage->metrics_read( $prefix, Recorder::RES_DAILY );
			if ( ! empty( $daily ) ) {
				$this->mirror->write( $prefix, $daily );
			}
		}
	}

	/**
	 * Delete every recorded counter for a blog, in storage, and in the mirror.
	 *
	 * @since 1.9.0
	 *
	 * @param string|null $prefix Site/network prefix; defaults to the current blog.
	 * @return void
	 */
	public function clear( ?string $prefix = null ): void {
		$prefix = $prefix ?? $this->prefix;

		foreach ( array( Recorder::RES_HOURLY, Recorder::RES_DAILY ) as $resolution ) {
			$fields = array_keys( $this->storage->metrics_read( $prefix, $resolution ) );
			if ( ! empty( $fields ) ) {
				$this->storage->metrics_delete( $prefix, $resolution, $fields );
			}
		}

		$this->mirror->delete( $prefix );
	}
}
