<?php
/**
 * Request-scoped metrics collection entry point.
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
 * Routes a request's hit-or-miss outcome to a {@see Recorder} bound to the
 * right per-blog prefix. One instance per request (owned by the Engine); base
 * hit/miss is always recorded (never gated), the detailed flag (Pro) is
 * forwarded. Buffered and flushed once, post-response.
 *
 * @since      1.7.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Collector {

	/**
	 * Storage boundary the counters are written through.
	 *
	 * @var Storage
	 */
	private Storage $storage;

	/**
	 * Whether to record the detailed Pro field set.
	 *
	 * @var bool
	 */
	private bool $detailed;

	/**
	 * The recorder built for this request's prefix, if any.
	 *
	 * @var Recorder|null
	 */
	private ?Recorder $recorder = null;

	/**
	 * Constructor.
	 *
	 * @since 1.7.0
	 *
	 * @param Storage $storage  Storage instance.
	 * @param bool    $detailed Record the detailed Pro field set.
	 */
	public function __construct( Storage $storage, bool $detailed ) {
		$this->storage  = $storage;
		$this->detailed = $detailed;
	}

	/**
	 * Record a cache hit served for this request.
	 *
	 * @since 1.7.0
	 *
	 * @param string $prefix  Site/network prefix ({@see \MilliCache\Engine\Flags::detect_prefix()}).
	 * @param int    $bytes   Bytes served from cache.
	 * @param int    $time_ms Serve time in milliseconds.
	 * @param bool   $stale   Whether the entry was served stale.
	 * @return void
	 */
	public function hit( string $prefix, int $bytes, int $time_ms, bool $stale = false ): void {
		$this->recorder( $prefix )->record_hit( $bytes, $time_ms, $stale );
	}

	/**
	 * Record a cacheable miss for this request.
	 *
	 * @since 1.7.0
	 *
	 * @param string $prefix  Site/network prefix.
	 * @param int    $bytes   Bytes of the freshly generated response.
	 * @param int    $time_ms Generation time in milliseconds.
	 * @return void
	 */
	public function miss( string $prefix, int $bytes = 0, int $time_ms = 0 ): void {
		$this->recorder( $prefix )->record_miss( $bytes, $time_ms );
	}

	/**
	 * Write this request's buffered counters. Safe to call unconditionally.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function flush(): void {
		if ( null !== $this->recorder ) {
			$this->recorder->flush();
		}
	}

	/**
	 * Lazily build the recorder for this request's prefix.
	 *
	 * @since 1.7.0
	 *
	 * @param string $prefix Site/network prefix.
	 * @return Recorder
	 */
	private function recorder( string $prefix ): Recorder {
		if ( null === $this->recorder ) {
			$this->recorder = new Recorder( new StorageStore( $this->storage, $prefix ), $this->detailed );
		}

		return $this->recorder;
	}
}
