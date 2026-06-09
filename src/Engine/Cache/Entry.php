<?php
/**
 * Cache entry value object.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package     MilliCache
 * @subpackage  Engine\Cache
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Engine\Cache;

use MilliCache\Engine\Utilities\Helpers;

! defined( 'ABSPATH' ) && exit;

/**
 * Immutable value object representing a cache entry.
 *
 * Encapsulates all data stored in a single cache entry including
 * output, headers, metadata, and compression settings.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Entry {

	/**
	 * Cached output content.
	 *
	 * @var string
	 */
	public string $output;

	/**
	 * Human-readable URL identifying this cache entry.
	 *
	 * @var string
	 */
	public string $url;

	/**
	 * HTTP headers to send with cached content.
	 *
	 * @var array<string>
	 */
	public array $headers;

	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	public int $status;

	/**
	 * Whether content is gzip compressed.
	 *
	 * @var bool
	 */
	public bool $gzip;

	/**
	 * Unix timestamp when cache was created/updated.
	 *
	 * @var int
	 */
	public int $updated;

	/**
	 * Pre-compression body length in bytes (for gzip + dedup savings reporting).
	 *
	 * @var int
	 */
	public int $size_raw;

	/**
	 * Custom TTL for this specific entry (optional).
	 *
	 * @var int|null
	 */
	public ?int $custom_ttl;

	/**
	 * Custom grace period for this entry (optional).
	 *
	 * @var int|null
	 */
	public ?int $custom_grace;

	/**
	 * Variant dimensions (cookies/vars) that stored this entry separately from
	 * the default variant; null for the default.
	 *
	 * @var array<string,mixed>|null
	 */
	public ?array $variant;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string                       $output        Cached HTML/content.
	 * @param string                       $url           Human-readable URL identifying this entry.
	 * @param array<string>                $headers       HTTP headers.
	 * @param int                          $status        HTTP status code.
	 * @param bool                         $gzip          Is content gzipped.
	 * @param int                          $updated       Timestamp when cached.
	 * @param int|null                     $custom_ttl    Custom TTL override.
	 * @param int|null                     $custom_grace  Custom grace override.
	 * @param array<array-key, mixed>|null $variant       Variant dimensions.
	 * @param int|null                     $size_raw      Pre-compression size; defaults to `strlen($output)` when null.
	 */
	public function __construct(
		string $output,
		string $url,
		array $headers,
		int $status,
		bool $gzip,
		int $updated,
		?int $custom_ttl = null,
		?int $custom_grace = null,
		?array $variant = null,
		?int $size_raw = null
	) {
		$this->output        = $output;
		$this->url           = $url;
		$this->headers       = $headers;
		$this->status        = $status;
		$this->gzip          = $gzip;
		$this->updated       = $updated;
		$this->custom_ttl    = null !== $custom_ttl ? $custom_ttl : null;
		$this->custom_grace  = null !== $custom_grace ? $custom_grace : null;
		$this->variant       = $variant;
		$this->size_raw = null !== $size_raw ? $size_raw : strlen( $output );
	}

	/**
	 * Create from an array (storage format).
	 *
	 * @since 1.0.0
	 *
	 * @param array<string,mixed> $data Cache data from storage.
	 * @return self New CacheEntry instance.
	 */
	public static function from_array( array $data ): self {
		return new self(
			isset( $data['output'] ) && is_string( $data['output'] ) ? $data['output'] : '',
			isset( $data['url'] ) && is_string( $data['url'] ) ? $data['url'] : '',
			Helpers::pluck_string_array( $data, 'headers' ),
			isset( $data['status'] ) && is_numeric( $data['status'] ) ? (int) $data['status'] : 200,
			isset( $data['gzip'] ) && $data['gzip'],
			isset( $data['updated'] ) && is_numeric( $data['updated'] ) ? (int) $data['updated'] : time(),
			isset( $data['custom_ttl'] ) && is_numeric( $data['custom_ttl'] ) ? (int) $data['custom_ttl'] : null,
			isset( $data['custom_grace'] ) && is_numeric( $data['custom_grace'] ) ? (int) $data['custom_grace'] : null,
			isset( $data['variant'] ) && is_array( $data['variant'] ) ? $data['variant'] : null,
			isset( $data['size_raw'] ) && is_numeric( $data['size_raw'] ) ? (int) $data['size_raw'] : null
		);
	}

	/**
	 * Convert to array for storage.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string,mixed> Array representation for Redis storage.
	 */
	public function to_array(): array {
		$data = array(
			'output'        => $this->output,
			'url'           => $this->url,
			'headers'       => $this->headers,
			'status'        => $this->status,
			'gzip'          => $this->gzip,
			'updated'       => $this->updated,
			'size_raw'      => $this->size_raw,
		);

		if ( null !== $this->custom_ttl ) {
			$data['custom_ttl'] = $this->custom_ttl;
		}

		if ( null !== $this->custom_grace ) {
			$data['custom_grace'] = $this->custom_grace;
		}

		if ( null !== $this->variant ) {
			$data['variant'] = $this->variant;
		}

		return $data;
	}
}
