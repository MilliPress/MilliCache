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
use MilliCache\Engine\Utilities\ServerVars;

! defined( 'ABSPATH' ) && exit;

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
final class Manager {

	/**
	 * Request parser.
	 *
	 * @var Parser
	 */
	private Parser $parser;

	/**
	 * Request cleaner.
	 *
	 * @var Cleaner
	 */
	private Cleaner $cleaner;

	/**
	 * Request hasher.
	 *
	 * @var Hasher
	 */
	private Hasher $hasher;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Config $config Cache configuration.
	 */
	public function __construct( Config $config ) {
		$this->parser  = new Parser( $config );
		$this->cleaner = new Cleaner( $config, $this->parser );
		$this->hasher  = new Hasher( $config, $this->parser );
	}

	/**
	 * Get the request parser.
	 *
	 * @since 1.0.0
	 *
	 * @return Parser The parser instance.
	 */
	public function get_parser(): Parser {
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
		return $this->hasher;
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
		$this->cleaner->clean_request();
		return $this->hasher->generate();
	}

	/**
	 * Get URL hash for a given URL or current request.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $url The URL to hash, or null for current request.
	 * @return string The URL hash.
	 */
	public function get_url_hash( ?string $url = null ): string {
		if ( ! $url ) {
			$host = strtolower( ServerVars::get( 'HTTP_HOST' ) );
			$path = $this->parser->parse_request_uri( ServerVars::get( 'REQUEST_URI' ) );
		} else {
			$parsed = parse_url( $url );
			$host   = strtolower( $parsed['host'] ?? '' );
			$path   = ( $parsed['path'] ?? '' ) . ( isset( $parsed['query'] ) ? '?' . $parsed['query'] : '' );
		}

		return $this->parser->get_url_hash( $host, $path );
	}

	/**
	 * Get debug data from last hash generation.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string,mixed>|null Debug data, or null if debug disabled.
	 */
	public function get_debug_data(): ?array {
		return $this->hasher->get_debug_data();
	}
}
