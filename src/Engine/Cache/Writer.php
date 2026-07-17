<?php
/**
 * Cache writing and storage.
 *
 * @link        https://www.millipress.com
 * @since       1.0.0
 *
 * @package     MilliCache
 * @subpackage  Engine\Cache
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Engine\Cache;

use MilliCache\Core\Storage;
use MilliCache\Engine\Utilities\PatternMatcher;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Writes and stores cache entries.
 *
 * Handles output buffering, compression, header validation, and
 * cache storage with flag management.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Writer {

	/**
	 * Maximum raw (pre-compression) entry size in bytes (5MB).
	 *
	 * Redis executes commands on a single thread, so multi-megabyte values
	 * stall all other clients while they are serialized onto the wire.
	 * Capping the raw buffer at 5MB keeps gzipped HTML entries near the
	 * ~1MB value size Redis handles comfortably and rejects runaway
	 * responses (e.g. binary exports) before they can fill the instance
	 * and evict legitimate pages.
	 *
	 * @since 1.7.0
	 * @var int
	 */
	public const MAX_ENTRY_SIZE = 5242880;

	/**
	 * Cache configuration.
	 *
	 * @var Config
	 */
	private Config $config;

	/**
	 * Storage instance.
	 *
	 * @var Storage
	 */
	private Storage $storage;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Config  $config  Cache configuration.
	 * @param Storage $storage Storage instance.
	 */
	public function __construct( Config $config, Storage $storage ) {
		$this->config  = $config;
		$this->storage = $storage;
	}

	/**
	 * Check if response should be cached.
	 *
	 * @since 1.0.0
	 *
	 * @param int $status_code HTTP status code.
	 * @return array{cacheable: bool, reason: string} Array with cacheable flag and reason.
	 */
	public function should_cache( int $status_code ): array {
		// Don't cache 5xx errors.
		if ( $status_code >= 500 ) {
			return array(
				'cacheable' => false,
				'reason'    => 'Server error response',
			);
		}

		return array(
			'cacheable' => true,
			'reason'    => '',
		);
	}

	/**
	 * Check if the response size allows caching.
	 *
	 * @since 1.7.0
	 *
	 * @param int $size Raw (pre-compression) response size in bytes.
	 * @return array{cacheable: bool, reason: string} Array with cacheable flag and reason.
	 */
	public function validate_size( int $size ): array {
		if ( $size > self::MAX_ENTRY_SIZE ) {
			return array(
				'cacheable' => false,
				'reason'    => sprintf(
					'Response size %.1fMB exceeds the %dMB cache limit',
					$size / 1048576,
					self::MAX_ENTRY_SIZE / 1048576
				),
			);
		}

		return array(
			'cacheable' => true,
			'reason'    => '',
		);
	}

	/**
	 * Validate provided headers and check if caching is allowed.
	 *
	 * Checks for Set-Cookie headers that would prevent caching and
	 * filters out MilliCache internal headers. Each header must be
	 * in "Key: Value" format (as returned by headers_list()).
	 *
	 * @since 1.0.0
	 *
	 * @param array<string> $headers Raw headers in "Key: Value" format.
	 * @return array{cacheable: bool, reason: string, headers: array<string>} Array with cacheable flag, reason, and filtered headers.
	 */
	public function validate_headers( array $headers ): array {
		$cacheable        = true;
		$reason           = '';
		$filtered_headers = array();

		foreach ( $headers as $header ) {
			list($key, $value) = explode( ':', $header, 2 );
			$key   = strtolower( $key );
			$value = trim( $value );

			// Check for cookies being set.
			if ( 'set-cookie' === $key ) {
				$cookie = explode( ';', $value, 2 );
				$cookie = trim( $cookie[0] );
				$cookie = wp_parse_args( $cookie );

				// If there is a cookie that is not in the ignore list, disable caching.
				foreach ( $cookie as $cookie_key => $cookie_value ) {
					$cookie_key = strtolower( $cookie_key );
					$is_ignored = false;

					foreach ( $this->config->ignore_cookies as $pattern ) {
						if ( PatternMatcher::match( $cookie_key, $pattern ) ) {
							$is_ignored = true;
							break;
						}
					}

					if ( ! $is_ignored ) {
						$cacheable = false;
						$reason    = "Setting cookie: $cookie_key";
						break 2;
					}
				}
			} elseif (
				false === strpos( $key, 'x-millicache' )
				&& 'age' !== $key
				&& ! ( 'cache-control' === $key && 'no-cache' === strtolower( $value ) )
			) {
				$filtered_headers[] = $header;
			}
		}

		return array(
			'cacheable' => $cacheable,
			'reason'    => $reason,
			'headers'   => $filtered_headers,
		);
	}

	/**
	 * Create a cache entry from the output buffer.
	 *
	 * @since 1.0.0
	 *
	 * @param string                   $output       The output buffer content.
	 * @param array<string>            $headers      Response headers to store.
	 * @param int                      $status       HTTP status code.
	 * @param int|null                 $custom_ttl   Custom TTL override.
	 * @param int|null                 $custom_grace Custom grace override.
	 * @param string                   $url          Human-readable URL.
	 * @param array<string,mixed>|null $variant      Variant dimensions.
	 * @return Entry The cache entry.
	 */
	public function create_entry(
		string $output,
		array $headers,
		int $status,
		?int $custom_ttl = null,
		?int $custom_grace = null,
		string $url = '',
		?array $variant = null
	): Entry {
		$should_gzip = $this->config->gzip && function_exists( 'gzcompress' );

		return new Entry(
			$output,
			$url,
			$headers,
			$status,
			$should_gzip,
			time(),
			$custom_ttl,
			$custom_grace,
			$variant
		);
	}

	/**
	 * Compress cache entry if configured.
	 *
	 * @since 1.0.0
	 *
	 * @param Entry $entry The cache entry.
	 * @return Entry The entry with compressed output (if gzip enabled).
	 */
	public function compress( Entry $entry ): Entry {
		if ( ! $entry->gzip ) {
			return $entry;
		}

		$compressed = gzcompress( $entry->output );

		if ( false === $compressed ) {
			// Compression failed, return uncompressed.
			return new Entry(
				$entry->output,
				$entry->url,
				$entry->headers,
				$entry->status,
				false, // Disable gzip flag.
				$entry->updated,
				$entry->custom_ttl,
				$entry->custom_grace,
				$entry->variant,
				$entry->size_raw
			);
		}

		return new Entry(
			$compressed,
			$entry->url,
			$entry->headers,
			$entry->status,
			true,
			$entry->updated,
			$entry->custom_ttl,
			$entry->custom_grace,
			$entry->variant,
			$entry->size_raw
		);
	}

	/**
	 * Store cache entry to storage.
	 *
	 * @since 1.0.0
	 *
	 * @param string        $hash  The request hash.
	 * @param Entry         $entry The cache entry.
	 * @param array<string> $flags Flags to associate with cache.
	 * @param bool          $cacheable Whether the entry is cacheable (used by Storage).
	 * @return bool True if stored successfully.
	 */
	public function store( string $hash, Entry $entry, array $flags, bool $cacheable = true ): bool {
		if ( ! $this->storage->is_available() ) {
			return false;
		}

		// Convert entry to array for storage.
		$data = $entry->to_array();

		// Store the cache.
		return $this->storage->perform_cache( $hash, $data, $flags, $cacheable );
	}
}
