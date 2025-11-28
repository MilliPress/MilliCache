<?php
/**
 * Cache writing and storage.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

namespace MilliCache\Engine\Cache;

use MilliCache\Core\Storage;
use MilliCache\Engine\Utilities\PatternMatcher;

! defined( 'ABSPATH' ) && exit;

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
	 * Process response headers and check if caching is allowed.
	 *
	 * Checks for Set-Cookie headers that would prevent caching.
	 *
	 * @since 1.0.0
	 *
	 * @return array{cacheable: bool, reason: string, headers: array<string>} Array with cacheable flag, reason, and filtered headers.
	 */
	public function process_headers(): array {
		$cacheable       = true;
		$reason          = '';
		$filtered_headers = array();

		foreach ( headers_list() as $header ) {
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
			} elseif ( strpos( $key, 'x-millicache' ) === false ) {
				// Ignore our own headers, add all others.
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
	 * Create cache entry from output buffer.
	 *
	 * @since 1.0.0
	 *
	 * @param string                   $output       The output buffer content.
	 * @param array<string>            $headers      Response headers to store.
	 * @param int                      $status       HTTP status code.
	 * @param int|null                 $custom_ttl   Custom TTL override.
	 * @param int|null                 $custom_grace Custom grace override.
	 * @param array<string,mixed>|null $debug        Debug data.
	 * @return Entry The cache entry.
	 */
	public function create_entry(
		string $output,
		array $headers,
		int $status,
		?int $custom_ttl = null,
		?int $custom_grace = null,
		?array $debug = null
	): Entry {
		$should_gzip = $this->config->gzip && function_exists( 'gzcompress' );

		return new Entry(
			$output,
			$headers,
			$status,
			$should_gzip,
			time(),
			$custom_ttl,
			$custom_grace,
			$debug
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
				$entry->headers,
				$entry->status,
				false, // Disable gzip flag.
				$entry->updated,
				$entry->custom_ttl,
				$entry->custom_grace,
				$entry->debug
			);
		}

		return new Entry(
			$compressed,
			$entry->headers,
			$entry->status,
			true,
			$entry->updated,
			$entry->custom_ttl,
			$entry->custom_grace,
			$entry->debug
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
