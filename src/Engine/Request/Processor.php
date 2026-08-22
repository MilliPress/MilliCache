<?php
/**
 * Request management orchestrator.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package     MilliCache
 * @subpackage  Engine\Request
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Engine\Request;

use MilliCache\Engine\Cache\Config;
use MilliCache\Engine\Request\Bucket\Resolver as BucketResolver;
use MilliCache\Engine\Utilities\ServerVars;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Orchestrates request parsing, cleaning, and hashing.
 *
 * High-level API for request handling that delegates to specialized
 * components for parsing, cleaning, and hash generation.
 *
 * @since       1.0.0
 * @package     MilliCache
 * @subpackage  Engine\Request
 * @author      Philipp Wellmer <hello@millipress.com>
 */
final class Processor {

	/**
	 * Cache configuration.
	 *
	 * @var Config
	 */
	private Config $config;

	/**
	 * Request parser.
	 *
	 * @var Parser|null
	 */
	private ?Parser $parser = null;

	/**
	 * Request cleaner.
	 *
	 * @var Cleaner|null
	 */
	private ?Cleaner $cleaner = null;

	/**
	 * Request hasher.
	 *
	 * @var Hasher|null
	 */
	private ?Hasher $hasher = null;

	/**
	 * Bucket resolver.
	 *
	 * @var BucketResolver|null
	 */
	private ?BucketResolver $buckets = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Config $config Cache configuration.
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 * Get the request parser.
	 *
	 * @since 1.0.0
	 *
	 * @return Parser The parser instance.
	 */
	public function get_parser(): Parser {
		if ( ! $this->parser ) {
			$this->parser = new Parser( $this->config );
		}
		return $this->parser;
	}

	/**
	 * Get the request cleaner.
	 *
	 * @since 1.0.0
	 *
	 * @return Cleaner The cleaner instance.
	 */
	public function get_cleaner(): Cleaner {
		if ( ! $this->cleaner ) {
			$this->cleaner = new Cleaner( $this->config, $this->get_parser() );
		}
		return $this->cleaner;
	}

	/**
	 * Get the request hasher.
	 *
	 * @since 1.0.0
	 *
	 * @return Hasher The hasher instance.
	 */
	public function get_hasher(): Hasher {
		if ( ! $this->hasher ) {
			$this->hasher = new Hasher( $this->config, $this->get_parser(), $this->buckets() );
		}
		return $this->hasher;
	}

	/**
	 * Get the bucket resolver.
	 *
	 * Used by rule actions and other early-phase code to register additional
	 * buckets via {@see BucketResolver::add_bucket()} before the hash is generated.
	 *
	 * @since 1.7.0
	 *
	 * @return BucketResolver The resolver instance.
	 */
	public function buckets(): BucketResolver {
		if ( ! $this->buckets ) {
			$this->buckets = new BucketResolver( $this->config );
		}
		return $this->buckets;
	}

	/**
	 * Clean request and generate hash.
	 *
	 * Convenience method that cleans the request superglobals and
	 * generates a unique hash in one call.
	 *
	 * @since 1.0.0
	 *
	 * @return string The generated request hash.
	 */
	public function process(): string {
		$this->get_cleaner()->clean_request();
		return $this->get_hasher()->generate();
	}

	/**
	 * Get URL hash for a given URL or current request.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $url The URL to hash, or null for the current request.
	 * @return string The URL hash.
	 */
	public function get_url_hash( ?string $url = null ): string {
		if ( ! $url ) {
			$host = strtolower( ServerVars::get( 'HTTP_HOST' ) );
			$path = $this->get_parser()->parse_request_uri( ServerVars::get( 'REQUEST_URI' ) );
		} else {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- Runs pre-WordPress from advanced-cache.php where wp_parse_url() is not loaded.
			$parsed = parse_url( $url );
			$host   = strtolower( $parsed['host'] ?? '' );
			$path   = ( $parsed['path'] ?? '' ) . ( isset( $parsed['query'] ) ? '?' . $parsed['query'] : '' );

			// HTTP_HOST carries non-default ports; mirror it so both hash paths agree.
			$scheme = strtolower( $parsed['scheme'] ?? '' );
			$port   = $parsed['port'] ?? null;
			if ( $port && ! ( 'http' === $scheme && 80 === $port ) && ! ( 'https' === $scheme && 443 === $port ) ) {
				$host .= ':' . $port;
			}
		}

		return $this->get_parser()->get_url_hash( $host, $path );
	}

	/**
	 * Get variant dimensions from the last hash generation.
	 *
	 * @since 1.4.0
	 *
	 * @return array<string,mixed>|null Variant data, or null if no variant dimensions.
	 */
	public function get_variant(): ?array {
		return $this->get_hasher()->get_variant();
	}

	/**
	 * Get the human-readable URL from the last hash generation.
	 *
	 * @since 1.4.0
	 *
	 * @return string|null The request URL, or null if not yet generated.
	 */
	public function get_url(): ?string {
		return $this->get_hasher()->get_url();
	}
}
