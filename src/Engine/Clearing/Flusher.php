<?php
/**
 * Clears cache by flags.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage Engine/Clearing
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Engine\Clearing;

use MilliCache\Core\Storage;
use MilliCache\Engine\Multisite;

! defined( 'ABSPATH' ) && exit;

/**
 * Manages cache clearing operations by flags.
 *
 * Accumulates flags for expiration/deletion and performs batch
 * clearing operations on shutdown.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @subpackage Engine/Clearing
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Flusher {

	/**
	 * Storage instance.
	 *
	 * @var Storage
	 */
	private Storage $storage;

	/**
	 * Multisite helper.
	 *
	 * @var Multisite
	 */
	private Multisite $multisite;

	/**
	 * Default TTL for expiration.
	 *
	 * @var int
	 */
	private int $default_ttl;

	/**
	 * Flags to expire.
	 *
	 * @var array<string>
	 */
	private array $flags_to_expire = array();

	/**
	 * Flags to delete.
	 *
	 * @var array<string>
	 */
	private array $flags_to_delete = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Storage   $storage     Storage instance.
	 * @param Multisite $multisite   Multisite helper.
	 * @param int       $default_ttl Default TTL for expiration.
	 */
	public function __construct( Storage $storage, Multisite $multisite, int $default_ttl = 3600 ) {
		$this->storage     = $storage;
		$this->multisite   = $multisite;
		$this->default_ttl = $default_ttl;
	}

	/**
	 * Add flags to expire.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string> $flags Flags to expire.
	 * @param bool          $add_prefix Whether to add multisite prefix.
	 * @return void
	 */
	public function add_to_expire( array $flags, bool $add_prefix = true ): void {
		if ( $add_prefix ) {
			$flags = $this->prefix_flags( $flags );
		}

		array_push( $this->flags_to_expire, ...$flags );
	}

	/**
	 * Add flags to delete.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string> $flags Flags to delete.
	 * @param bool          $add_prefix Whether to add multisite prefix.
	 * @return void
	 */
	public function add_to_delete( array $flags, bool $add_prefix = true ): void {
		if ( $add_prefix ) {
			$flags = $this->prefix_flags( $flags );
		}

		array_push( $this->flags_to_delete, ...$flags );
	}

	/**
	 * Get flags pending expiration.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string> Flags to expire.
	 */
	public function get_expire_queue(): array {
		return $this->flags_to_expire;
	}

	/**
	 * Get flags pending deletion.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string> Flags to delete.
	 */
	public function get_delete_queue(): array {
		return $this->flags_to_delete;
	}

	/**
	 * Clear queued flags immediately.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if cleared successfully.
	 */
	public function flush(): bool {
		if ( empty( $this->flags_to_expire ) && empty( $this->flags_to_delete ) ) {
			return true;
		}

		$sets = array(
			'mll:expired-flags' => $this->flags_to_expire,
			'mll:deleted-flags' => $this->flags_to_delete,
		);

		// Clear cache by sets.
		$this->storage->clear_cache_by_sets( $sets, $this->default_ttl );

		// Clear queues after flush.
		$this->clear_queues();

		return true;
	}

	/**
	 * Prefix flags with multisite identifiers.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string>   $flags      Flags to prefix.
	 * @param int|string|null $site_id    Site ID (null for current).
	 * @param int|string|null $network_id Network ID (null for current).
	 * @return array<string> Prefixed flags.
	 */
	public function prefix_flags( array $flags, $site_id = null, $network_id = null ): array {
		if ( ! $this->multisite->is_enabled() ) {
			return $flags;
		}

		$prefix = $this->multisite->get_flag_prefix( $site_id, $network_id );

		return array_map(
			function ( $flag ) use ( $prefix ) {
				return $prefix . $flag;
			},
			$flags
		);
	}

	/**
	 * Clear all queued flags.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function clear_queues(): void {
		$this->flags_to_expire = array();
		$this->flags_to_delete = array();
	}

	/**
	 * Get queue sizes.
	 *
	 * @since 1.0.0
	 *
	 * @return array{expire: int, delete: int} Queue sizes.
	 */
	public function get_queue_sizes(): array {
		return array(
			'expire' => count( $this->flags_to_expire ),
			'delete' => count( $this->flags_to_delete ),
		);
	}
}
