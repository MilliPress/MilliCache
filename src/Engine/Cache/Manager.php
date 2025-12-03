<?php
/**
 * Cache management orchestrator.
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
 * Orchestrates cache reading, writing, and validation.
 *
 * High-level API for cache operations that delegates to specialized
 * components for reading, writing, and validation.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Manager {

	/**
	 * Cache validator.
	 *
	 * @var Validator
	 */
	private Validator $validator;

	/**
	 * Cache reader.
	 *
	 * @var Reader
	 */
	private Reader $reader;

	/**
	 * Cache writer.
	 *
	 * @var Writer
	 */
	private Writer $writer;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Config  $config  Cache configuration.
	 * @param Storage $storage Storage instance.
	 */
	public function __construct( Config $config, Storage $storage ) {
		$this->validator = new Validator( $config );
		$this->reader    = new Reader( $config, $storage, $this->validator );
		$this->writer    = new Writer( $config, $storage );
	}

	/**
	 * Get the cache validator.
	 *
	 * @since 1.0.0
	 *
	 * @return Validator The validator instance.
	 */
	public function get_validator(): Validator {
		return $this->validator;
	}

	/**
	 * Get the cache reader.
	 *
	 * @since 1.0.0
	 *
	 * @return Reader The reader instance.
	 */
	public function get_reader(): Reader {
		return $this->reader;
	}

	/**
	 * Get the cache writer.
	 *
	 * @since 1.0.0
	 *
	 * @return Writer The writer instance.
	 */
	public function get_writer(): Writer {
		return $this->writer;
	}

	/**
	 * Get cache entry and determine if it should be served.
	 *
	 * Convenience method that combines get, should_serve, and decompress.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hash           The request hash.
	 * @param bool   $can_regenerate Whether FastCGI regeneration is possible.
	 * @return array{result: Result, serve: bool, regenerate: bool, entry: Entry|null} Cache result with serving decision.
	 */
	public function get_and_validate( string $hash, bool $can_regenerate ): array {
		$result = $this->reader->get( $hash );

		if ( $result->is_miss() ) {
			return array(
				'result'     => $result,
				'serve'      => false,
				'regenerate' => false,
				'entry'      => null,
			);
		}

		$decision = $this->reader->should_serve( $result, $hash, $can_regenerate );

		// If we shouldn't serve, return early.
		if ( ! $decision['serve'] ) {
			return array(
				'result'     => $result,
				'serve'      => false,
				'regenerate' => $decision['regenerate'],
				'entry'      => null,
			);
		}

		// Decompress if needed.
		if ( null === $result->entry ) {
			// No entry to decompress.
			return array(
				'result'     => $result,
				'serve'      => false,
				'regenerate' => false,
				'entry'      => null,
			);
		}

		$entry = $this->reader->decompress( $result->entry );

		if ( ! $entry ) {
			// Decompression failed.
			return array(
				'result'     => $result,
				'serve'      => false,
				'regenerate' => false,
				'entry'      => null,
			);
		}

		return array(
			'result'     => $result,
			'serve'      => true,
			'regenerate' => $decision['regenerate'],
			'entry'      => $entry,
		);
	}

	/**
	 * Create and store cache entry from output buffer.
	 *
	 * Convenience method that combines create_entry, compress, and store.
	 *
	 * @since 1.0.0
	 *
	 * @param string                   $hash         The request hash.
	 * @param string                   $output       The output buffer content.
	 * @param array<string>            $flags        Flags to associate with cache.
	 * @param int|null                 $custom_ttl   Custom TTL override.
	 * @param int|null                 $custom_grace Custom grace override.
	 * @param array<string,mixed>|null $debug        Debug data.
	 * @return array{cached: bool, reason: string} Result with cached flag and reason.
	 */
	public function cache_output(
		string $hash,
		string $output,
		array $flags,
		?int $custom_ttl = null,
		?int $custom_grace = null,
		?array $debug = null
	): array {
		// Get current HTTP status.
		$status = http_response_code();

		// Ensure status is an int (http_response_code returns int|false, but also true in some edge cases).
		if ( false === $status || true === $status ) {
			$status = 200;
		}

		// Check if we should cache based on status.
		$status_check = $this->writer->should_cache( $status );
		if ( ! $status_check['cacheable'] ) {
			return array(
				'cached' => false,
				'reason' => $status_check['reason'],
			);
		}

		// Process response headers.
		$header_check = $this->writer->process_headers();
		if ( ! $header_check['cacheable'] ) {
			return array(
				'cached' => false,
				'reason' => $header_check['reason'],
			);
		}

		// Create cache entry.
		$entry = $this->writer->create_entry(
			$output,
			$header_check['headers'],
			$status,
			$custom_ttl,
			$custom_grace,
			$debug
		);

		// Compress if enabled.
		$entry = $this->writer->compress( $entry );

		// Store to cache.
		$stored = $this->writer->store( $hash, $entry, $flags, true );

		return array(
			'cached' => $stored,
			'reason' => $stored ? '' : 'Storage failed',
		);
	}
}
