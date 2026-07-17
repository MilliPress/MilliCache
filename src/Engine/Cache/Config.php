<?php
/**
 * Cache configuration value object.
 *
 * @link        https://www.millipress.com
 * @since       1.0.0
 *
 * @package     MilliCache
 * @subpackage  Engine\Cache
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Engine\Cache;

use MilliCache\Engine\Utilities\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Immutable value object representing cache configuration.
 *
 * Groups related configuration settings together for easier management
 * and type safety. All properties are readonly to ensure immutability.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Config {

	/**
	 * Cache time-to-live in seconds.
	 *
	 * @var int
	 */
	public int $ttl;

	/**
	 * Grace period in seconds (stale cache tolerance).
	 *
	 * @var int
	 */
	public int $grace;

	/**
	 * Whether to use gzip compression.
	 *
	 * @var bool
	 */
	public bool $gzip;

	/**
	 * Whether debug mode is enabled.
	 *
	 * @var bool
	 */
	public bool $debug;

	/**
	 * Paths that should not be cached.
	 *
	 * @var array<string>
	 */
	public array $nocache_paths;

	/**
	 * Cookies that should not be cached.
	 *
	 * @var array<string>
	 */
	public array $nocache_cookies;

	/**
	 * Cookies to ignore in cache hash calculation.
	 *
	 * @var array<string>
	 */
	public array $ignore_cookies;

	/**
	 * Query string keys to ignore.
	 *
	 * @var array<string>
	 */
	public array $ignore_request_keys;

	/**
	 * Variables that make requests unique.
	 *
	 * @var array<string>
	 */
	public array $unique;

	/**
	 * Map of bucket name → (raw value → bucket token).
	 *
	 * Shared lookup tables for resolvers to consume when calling
	 * {@see \MilliCache\Engine\Request\Bucket\Resolver::add_bucket()}.
	 *
	 * @var array<string, array<string, string>>
	 */
	public array $buckets;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param int                                  $ttl                 Cache time-to-live in seconds.
	 * @param int                                  $grace               Grace period in seconds.
	 * @param bool                                 $gzip                Whether to use gzip compression.
	 * @param bool                                 $debug               Whether debug mode is enabled.
	 * @param array<string>                        $nocache_paths       Paths that should not be cached.
	 * @param array<string>                        $nocache_cookies     Cookies that prevent caching.
	 * @param array<string>                        $ignore_cookies      Cookies to ignore in hash.
	 * @param array<string>                        $ignore_request_keys Query keys to ignore.
	 * @param array<string>                        $unique              Variables making requests unique.
	 * @param array<string, array<string, string>> $buckets             Bucket name → (raw value → token) map.
	 */
	public function __construct(
		int $ttl,
		int $grace,
		bool $gzip,
		bool $debug,
		array $nocache_paths,
		array $nocache_cookies,
		array $ignore_cookies,
		array $ignore_request_keys,
		array $unique,
		array $buckets = array()
	) {
		$this->ttl                 = $ttl;
		$this->grace               = $grace;
		$this->gzip                = $gzip;
		$this->debug               = $debug;
		$this->nocache_paths       = $nocache_paths;
		$this->nocache_cookies     = $nocache_cookies;
		$this->ignore_cookies      = $ignore_cookies;
		$this->ignore_request_keys = $ignore_request_keys;
		$this->unique              = $unique;
		$this->buckets             = $buckets;
	}

	/**
	 * Create config from the settings array.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string,mixed> $settings Settings array from Engine.
	 * @return self New CacheConfig instance.
	 */
	public static function from_settings( array $settings ): self {
		return new self(
			isset( $settings['ttl'] ) && is_numeric( $settings['ttl'] ) ? (int) $settings['ttl'] : 86400,
			isset( $settings['grace'] ) && is_numeric( $settings['grace'] ) ? (int) $settings['grace'] : 3600,
			! isset( $settings['gzip'] ) || $settings['gzip'],
			isset( $settings['debug'] ) && $settings['debug'],
			Helpers::pluck_string_array( $settings, 'nocache_paths' ),
			Helpers::pluck_string_array( $settings, 'nocache_cookies' ),
			Helpers::pluck_string_array( $settings, 'ignore_cookies' ),
			Helpers::pluck_string_array( $settings, 'ignore_request_keys' ),
			Helpers::pluck_string_array( $settings, 'unique' ),
			self::resolve_buckets( $settings )
		);
	}

	/**
	 * Resolve the bucket configuration map from settings.
	 *
	 * Returns a nested map of bucket name → (lowercased raw value → token).
	 * Drops invalid pairs silently. Defaults to an empty map.
	 *
	 * @since 1.7.0
	 *
	 * @param array<string,mixed> $settings Settings array.
	 * @return array<string, array<string, string>> Nested bucket map.
	 */
	private static function resolve_buckets( array $settings ): array {
		$configured = isset( $settings['buckets'] ) && is_array( $settings['buckets'] )
			? $settings['buckets']
			: array();

		$normalized = array();
		foreach ( $configured as $bucket_name => $tokens ) {
			if ( ! is_string( $bucket_name ) || ! is_array( $tokens ) ) {
				continue;
			}

			$key   = strtolower( trim( $bucket_name ) );
			$inner = array();
			foreach ( $tokens as $raw => $token ) {
				if ( ! is_string( $raw ) || ! is_string( $token ) || '' === $token ) {
					continue;
				}
				$inner[ strtolower( trim( $raw ) ) ] = $token;
			}

			if ( ! empty( $inner ) ) {
				$normalized[ $key ] = $inner;
			}
		}

		return $normalized;
	}
}
