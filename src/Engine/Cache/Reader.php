<?php
/**
 * Cache reading and serving.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package     MilliCache
 * @subpackage  Engine\Cache
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Engine\Cache;

use MilliCache\Core\Storage;

! defined( 'ABSPATH' ) && exit;

/**
 * Reads and serves cached content.
 *
 * Handles cache retrieval, validation, decompression, and output
 * with support for stale-while-revalidate pattern.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Reader {

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
	 * Cache validator.
	 *
	 * @var Validator
	 */
	private Validator $validator;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Config    $config    Cache configuration.
	 * @param Storage   $storage   Storage instance.
	 * @param Validator $validator Cache validator.
	 */
	public function __construct( Config $config, Storage $storage, Validator $validator ) {
		$this->config    = $config;
		$this->storage   = $storage;
		$this->validator = $validator;
	}

	/**
	 * Get cache entry by hash.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hash The request hash.
	 * @return Result The cache result (hit, miss, or stale).
	 */
	public function get( string $hash ): Result {
		if ( ! $this->storage->is_available() ) {
			return Result::miss();
		}

		// Look for an existing cache entry by request hash.
		$result = $this->storage->get_cache( $hash );

		// No cache found.
		if ( ! $result ) {
			return Result::miss();
		}

		// Unpack the result.
		list( $cache, $flags, $locked ) = $result;

		// No valid cache data.
		if ( ! is_array( $cache ) || empty( $cache ) ) {
			return Result::miss();
		}

		// Convert to Entry.
		$entry = Entry::from_array( $cache );

		// Convert locked to boolean (storage returns string).
		$is_locked = ! empty( $locked );

		// Return cache result with entry and metadata.
		return Result::hit( $entry, $flags, $is_locked );
	}

	/**
	 * Check if cache entry should be served.
	 *
	 * Handles deletion of too-old entries and validation of stale entries.
	 *
	 * @since 1.0.0
	 *
	 * @param Result $result       The cache result.
	 * @param string $hash         The request hash.
	 * @return array{serve: bool, regenerate: bool} Array with serve and regenerate flags.
	 */
	public function should_serve( Result $result, string $hash ): array {
		if ( $result->is_miss() ) {
			return array(
				'serve'      => false,
				'regenerate' => false,
			);
		}

		$entry = $result->entry;

		// Null entry should not happen after a hit, but guard against it.
		if ( null === $entry ) {
			return array(
				'serve'      => false,
				'regenerate' => false,
			);
		}

		// This entry is too old, delete it.
		if ( $this->validator->is_too_old( $entry ) ) {
			$this->storage->delete_cache( $hash );
			return array(
				'serve'      => false,
				'regenerate' => false,
			);
		}

		// Check if cache is stale.
		$is_stale = $this->validator->is_stale( $entry );

		// Fresh cache - serve it.
		if ( ! $is_stale ) {
			return array(
				'serve'      => true,
				'regenerate' => false,
			);
		}

		// Stale cache - handle based on lock and regeneration capability.
		if ( $result->locked ) {
			// Already locked by another process, don't serve.
			return array(
				'serve'      => false,
				'regenerate' => false,
			);
		}

		// Try to lock for regeneration.
		if ( ! $this->storage->lock( $hash ) ) {
			// Failed to lock, don't serve.
			return array(
				'serve'      => false,
				'regenerate' => false,
			);
		}

		// Successfully locked - check if we can regenerate in background.
		if ( function_exists( 'fastcgi_finish_request' ) ) {
			// Serve stale cache and regenerate in background.
			return array(
				'serve'      => true,
				'regenerate' => true,
			);
		}

		// Can't regenerate in background, must regenerate now.
		return array(
			'serve'      => false,
			'regenerate' => false,
		);
	}

	/**
	 * Decompress cache entry if needed.
	 *
	 * @since 1.0.0
	 *
	 * @param Entry $entry The cache entry.
	 * @return Entry|null The decompressed entry, or null if decompression failed.
	 */
	public function decompress( Entry $entry ): ?Entry {
		// Not compressed, return as-is.
		if ( ! $entry->gzip ) {
			return $entry;
		}

		// Config doesn't support gzip, can't serve.
		if ( ! $this->config->gzip ) {
			return null;
		}

		// Decompress the output. Suppress warnings for invalid data.
		$decompressed = @gzuncompress( $entry->output );

		if ( false === $decompressed ) {
			return null;
		}

		// Return new entry with decompressed output.
		return new Entry(
			$decompressed,
			$entry->headers,
			$entry->status,
			false, // No longer gzipped.
			$entry->updated,
			$entry->custom_ttl,
			$entry->custom_grace,
			$entry->debug
		);
	}

	/**
	 * Output cache entry to browser.
	 *
	 * @since 1.0.0
	 *
	 * @param Entry $entry      The cache entry.
	 * @param bool  $regenerate Whether regenerating in background.
	 * @return void
	 */
	public function output( Entry $entry, bool $regenerate = false ): void {
		// Output cached status code.
		if ( ! empty( $entry->status ) ) {
			http_response_code( $entry->status );
		}

		// Output cached headers.
		if ( ! empty( $entry->headers ) ) {
			foreach ( $entry->headers as $header ) {
				header( $header );
			}
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- We need to output the cache.
		echo $entry->output;

		// If regenerating in background, finish request and continue.
		if ( $regenerate && function_exists( 'fastcgi_finish_request' ) ) {
			fastcgi_finish_request();
		} else {
			exit;
		}
	}
}
