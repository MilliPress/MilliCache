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

! defined( 'ABSPATH' ) && exit;

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
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param int           $ttl                 Cache time-to-live in seconds.
	 * @param int           $grace               Grace period in seconds.
	 * @param bool          $gzip                Whether to use gzip compression.
	 * @param bool          $debug               Whether debug mode is enabled.
	 * @param array<string> $nocache_paths       Paths that should not be cached.
	 * @param array<string> $nocache_cookies     Cookies that prevent caching.
	 * @param array<string> $ignore_cookies      Cookies to ignore in hash.
	 * @param array<string> $ignore_request_keys Query keys to ignore.
	 * @param array<string> $unique              Variables making requests unique.
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
		array $unique
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
	}

	/**
	 * Create from settings array.
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
			self::extract_string_array( $settings, 'nocache_paths' ),
			self::extract_string_array( $settings, 'nocache_cookies' ),
			self::extract_string_array( $settings, 'ignore_cookies' ),
			self::extract_string_array( $settings, 'ignore_request_keys' ),
			self::extract_string_array( $settings, 'unique' )
		);
	}

	/**
	 * Safely extract a string array from settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string,mixed> $settings Settings array.
	 * @param string              $key      Key to extract.
	 * @return array<string> String array or empty array.
	 */
	private static function extract_string_array( array $settings, string $key ): array {
		if ( ! isset( $settings[ $key ] ) || ! is_array( $settings[ $key ] ) ) {
			return array();
		}

		// Filter to ensure all values are strings.
		return array_filter(
			array_map( 'strval', $settings[ $key ] ),
			'is_string'
		);
	}

	/**
	 * Create a modified copy with new TTL.
	 *
	 * @since 1.0.0
	 *
	 * @param int $ttl New TTL value.
	 * @return self New instance with modified TTL.
	 */
	public function with_ttl( int $ttl ): self {
		return new self(
			$ttl,
			$this->grace,
			$this->gzip,
			$this->debug,
			$this->nocache_paths,
			$this->nocache_cookies,
			$this->ignore_cookies,
			$this->ignore_request_keys,
			$this->unique
		);
	}

	/**
	 * Create a modified copy with new grace period.
	 *
	 * @since 1.0.0
	 *
	 * @param int $grace New grace value.
	 * @return self New instance with modified grace.
	 */
	public function with_grace( int $grace ): self {
		return new self(
			$this->ttl,
			$grace,
			$this->gzip,
			$this->debug,
			$this->nocache_paths,
			$this->nocache_cookies,
			$this->ignore_cookies,
			$this->ignore_request_keys,
			$this->unique
		);
	}
}
