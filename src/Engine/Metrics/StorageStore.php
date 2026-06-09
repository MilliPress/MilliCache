<?php
/**
 * Storage-backed implementation of the metrics counter store.
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
 * Binds a site/network prefix to {@see Storage}'s metrics hash operations, so
 * each instance writes one blog's `<storage-prefix>:m:<prefix><res>` buckets.
 *
 * @since      1.7.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class StorageStore implements Store {

	/**
	 * The storage boundary the counters are written through.
	 *
	 * @var Storage
	 */
	private Storage $storage;

	/**
	 * Site/network prefix this store is bound to.
	 *
	 * @var string
	 */
	private string $prefix;

	/**
	 * Constructor.
	 *
	 * @since 1.7.0
	 *
	 * @param Storage $storage Storage instance.
	 * @param string  $prefix  Site/network prefix (`''`, `'1:'`, `'1:2:'`).
	 */
	public function __construct( Storage $storage, string $prefix ) {
		$this->storage = $storage;
		$this->prefix  = $prefix;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param string             $resolution Bucket resolution (`h` or `d`).
	 * @param array<string, int> $deltas     Field name => increment.
	 * @return void
	 */
	public function increment( string $resolution, array $deltas ): void {
		$this->storage->metrics_increment( $this->prefix, $resolution, $deltas );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param string             $resolution Bucket resolution (`h` or `d`).
	 * @param array<string, int> $values     Field name => absolute value.
	 * @return void
	 */
	public function set_fields( string $resolution, array $values ): void {
		$this->storage->metrics_set( $this->prefix, $resolution, $values );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param string $resolution Bucket resolution (`h` or `d`).
	 * @return array<string, int> Field name => value.
	 */
	public function read( string $resolution ): array {
		return $this->storage->metrics_read( $this->prefix, $resolution );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param string        $resolution Bucket resolution (`h` or `d`).
	 * @param array<string> $fields     Field names to remove.
	 * @return void
	 */
	public function delete_fields( string $resolution, array $fields ): void {
		$this->storage->metrics_delete( $this->prefix, $resolution, $fields );
	}
}
