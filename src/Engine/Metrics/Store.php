<?php
/**
 * Persistence seam for cache metrics counters.
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
 * Narrow per-blog storage contract the metrics layer writes through, keeping
 * the concrete client behind a testable seam. A resolution selects one bucket
 * hash (`h` hourly / `d` daily); field names are opaque `<bucket>:<metric>`.
 *
 * @since      1.7.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
interface Store {

	/**
	 * Atomically add the given deltas to counter fields (HINCRBY batch).
	 *
	 * @since 1.7.0
	 *
	 * @param string             $resolution Bucket resolution (`h` or `d`).
	 * @param array<string, int> $deltas     Field name => increment.
	 * @return void
	 */
	public function increment( string $resolution, array $deltas ): void;

	/**
	 * Overwrite counter fields with absolute values (HSET batch).
	 *
	 * Used by the rollup so re-running it for the same day is idempotent.
	 *
	 * @since 1.7.0
	 *
	 * @param string             $resolution Bucket resolution (`h` or `d`).
	 * @param array<string, int> $values     Field name => absolute value.
	 * @return void
	 */
	public function set_fields( string $resolution, array $values ): void;

	/**
	 * Read every counter field for a resolution (HGETALL).
	 *
	 * @since 1.7.0
	 *
	 * @param string $resolution Bucket resolution (`h` or `d`).
	 * @return array<string, int> Field name => value.
	 */
	public function read( string $resolution ): array;

	/**
	 * Delete the named counter fields (HDEL).
	 *
	 * @since 1.7.0
	 *
	 * @param string        $resolution Bucket resolution (`h` or `d`).
	 * @param array<string> $fields     Field names to remove.
	 * @return void
	 */
	public function delete_fields( string $resolution, array $fields ): void;
}
