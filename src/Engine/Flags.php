<?php
/**
 * Cache flag management.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage Engine
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Engine;

use MilliCache\Engine\Utilities\Multisite;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages cache flags for targeted invalidation.
 *
 * Flags are labels attached to cache entries that allow for efficient
 * cache clearing. For example, all posts tagged with "post:123" can
 * be cleared simultaneously.
 *
 * Supports multisite with automatic site/network prefixing.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @subpackage Engine
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Flags {

	/**
	 * Current request's flags.
	 *
	 * @var array<string>
	 */
	private array $flags = array();

	/**
	 * Multisite helper instance.
	 *
	 * @var Multisite|null
	 */
	private ?Multisite $multisite;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Multisite|null $multisite Multisite helper (optional).
	 */
	public function __construct( ?Multisite $multisite = null ) {
		$this->multisite = $multisite;
	}

	/**
	 * Add a flag to the current request.
	 *
	 * Automatically prefixes with site/network ID if multisite.
	 *
	 * @since 1.0.0
	 *
	 * @param string $flag The flag name (e.g., 'post:123', 'home').
	 * @return void
	 */
	public function add( string $flag ): void {
		if ( empty( $flag ) ) {
			return;
		}

		$prefixed_flag = $this->add_key_prefix( self::normalize( $flag ) );

		if ( ! in_array( $prefixed_flag, $this->flags, true ) ) {
			$this->flags[] = $prefixed_flag;
		}
	}

	/**
	 * Remove a flag from the current request.
	 *
	 * @since 1.0.0
	 *
	 * @param string $flag The flag name to remove.
	 * @return void
	 */
	public function remove( string $flag ): void {
		if ( empty( $flag ) ) {
			return;
		}

		$prefixed_flag = $this->add_key_prefix( self::normalize( $flag ) );
		$key           = array_search( $prefixed_flag, $this->flags, true );

		if ( false !== $key ) {
			unset( $this->flags[ $key ] );
			$this->flags = array_values( $this->flags ); // Re-index array.
		}
	}

	/**
	 * Normalize a user-supplied flag identifier.
	 *
	 * @since 1.7.0
	 *
	 * @param string $flag The raw flag identifier.
	 * @return string Normalized flag identifier (empty if input was blank).
	 */
	public static function normalize( string $flag ): string {
		return strtolower( trim( $flag ) );
	}

	/**
	 * Get all flags for the current request.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string> Array of flag names.
	 */
	public function get_all(): array {
		return $this->flags;
	}

	/**
	 * Clear all flags.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function clear(): void {
		$this->flags = array();
	}

	/**
	 * Add the site/network prefix to a flag key.
	 *
	 * @since 1.0.0
	 *
	 * @param string          $flag       The flag name.
	 * @param int|string|null $site_id    Site ID (null for current).
	 * @param int|string|null $network_id Network ID (null for current).
	 * @return string The prefixed flag key.
	 */
	public function add_key_prefix( string $flag, $site_id = null, $network_id = null ): string {
		return $this->get_prefix( $site_id, $network_id ) . $flag;
	}

	/**
	 * Strip the site/network prefix from a flag key.
	 *
	 * @since 1.4.0
	 *
	 * @param string          $flag       The prefixed flag key.
	 * @param int|string|null $site_id    Site ID (null for current).
	 * @param int|string|null $network_id Network ID (null for current).
	 * @return string The flag key without the site / network prefix.
	 */
	public function strip_key_prefix( string $flag, $site_id = null, $network_id = null ): string {
		$prefix = $this->get_prefix( $site_id, $network_id );

		if ( '' !== $prefix && strpos( $flag, $prefix ) === 0 ) {
			return substr( $flag, strlen( $prefix ) );
		}

		return $flag;
	}

	/**
	 * Derive a stored entry's site/network prefix from its flags.
	 * The pre-WP, read-side counterpart to {@see self::get_prefix()}.
	 *
	 * @since 1.7.0
	 *
	 * @param array<string> $flags Entry flags, storage prefix already stripped.
	 * @return string The prefix (`''`, `'{site}:'`, or `'{network}:{site}:'`).
	 */
	public static function detect_prefix( array $flags ): string {
		$prefix = null;

		foreach ( $flags as $flag ) {
			if ( preg_match( '/^(?:\d+:)+/', $flag, $matches ) ) {
				$run = $matches[0];
				if ( null === $prefix || strlen( $run ) < strlen( $prefix ) ) {
					$prefix = $run;
				}
			}
		}

		return $prefix ?? '';
	}

	/**
	 * Get the prefix for flags (site:network: or empty).
	 *
	 * @since 1.0.0
	 *
	 * @param int|string|null $site_id    Site ID (null for current).
	 * @param int|string|null $network_id Network ID (null for current).
	 * @return string The prefix string.
	 */
	public function get_prefix( $site_id = null, $network_id = null ): string {
		if ( ! $this->multisite || ! $this->multisite->is_enabled() ) {
			return '';
		}

		return $this->multisite->get_flag_prefix( $site_id, $network_id );
	}

	/**
	 * Prefix an array of flags.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string>|string $flags      Flags to prefix.
	 * @param int|string|null      $site_id    Site ID (null for current).
	 * @param int|string|null      $network_id Network ID (null for current).
	 * @return array<string> Prefixed flags.
	 */
	public function prefix( $flags, $site_id = null, $network_id = null ): array {
		if ( is_string( $flags ) ) {
			$flags = array( $flags );
		}

		$prefix = $this->get_prefix( $site_id, $network_id );

		// Return array_map with a prefix.
		return array_map(
			function ( $flag ) use ( $prefix ) {
				return $prefix . $flag;
			},
			$flags
		);
	}
}
