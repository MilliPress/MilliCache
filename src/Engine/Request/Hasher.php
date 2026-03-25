<?php
/**
 * Request hash generator.
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
 * Generates unique hash for each request.
 *
 * Creates an MD5 hash based on request URI, host, method, cookies,
 * unique variables, and authorization headers to uniquely identify
 * cache entries.
 *
 * @since       1.0.0
 * @package     MilliCache
 * @subpackage  Engine\Request
 * @author      Philipp Wellmer <hello@millipress.com>
 */
final class Hasher {

	/**
	 * Cache configuration.
	 *
	 * @var Config
	 */
	private Config $config;

	/**
	 * Request parser.
	 *
	 * @var Parser
	 */
	private Parser $parser;

	/**
	 * Generated request hash.
	 *
	 * @var string|null
	 */
	private ?string $hash = null;

	/**
	 * Variant dimensions that differentiate this request from the default.
	 *
	 * @var array<string,mixed>|null
	 */
	private ?array $variant = null;

	/**
	 * Human-readable URL for this request (host + path + query).
	 *
	 * @var string|null
	 */
	private ?string $url = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Config $config Cache configuration.
	 * @param Parser $parser Request parser.
	 */
	public function __construct( Config $config, Parser $parser ) {
		$this->config = $config;
		$this->parser = $parser;
	}

	/**
	 * Generate a unique request hash.
	 *
	 * @since 1.0.0
	 *
	 * @return string The generated hash.
	 */
	public function generate(): string {
		$request_hash = array(
			'request' => $this->parser->parse_request_uri(
				ServerVars::get( 'REQUEST_URI' )
			),
			'host'    => ServerVars::get( 'HTTP_HOST' ),
			'https'   => ServerVars::get( 'HTTPS' ),
			'method'  => ServerVars::get( 'REQUEST_METHOD' ),
			'unique'  => $this->config->unique,
			'cookies' => $this->parser->parse_cookies( $_COOKIE ),
		);

		// Make sure requests with Authorization headers are unique.
		$auth = ServerVars::get( 'HTTP_AUTHORIZATION' );
		if ( ! empty( $auth ) ) {
			$request_hash['unique']['mc-auth-header'] = md5( $auth );
		}

		// Build variant dimensions (only non-default parts that differentiate this request).
		$variant = array();
		if ( empty( $request_hash['https'] ) || 'on' !== $request_hash['https'] ) {
			$variant['https'] = false;
		}
		if ( 'GET' !== strtoupper( (string) $request_hash['method'] ) ) {
			$variant['method'] = strtoupper( (string) $request_hash['method'] );
		}
		if ( ! empty( $request_hash['cookies'] ) ) {
			$variant['cookies'] = array_keys( $request_hash['cookies'] );
		}
		if ( ! empty( $request_hash['unique'] ) ) {
			$variant['unique'] = $request_hash['unique'];
		}
		$this->variant = ! empty( $variant ) ? $variant : null;

		// Capture the human-readable URL for this request.
		$scheme    = isset( $variant['https'] ) ? 'http' : 'https';
		$this->url = $scheme . '://' . $request_hash['host'] . $request_hash['request'];

		// Convert to an actual hash.
		$this->hash = md5( serialize( $request_hash ) );

		return $this->hash;
	}

	/**
	 * Get the previously generated hash.
	 *
	 * @since 1.0.0
	 *
	 * @return string|null The hash, or null if not yet generated.
	 */
	public function get_hash(): ?string {
		return $this->hash;
	}

	/**
	 * Get variant dimensions from hash generation.
	 *
	 * Returns the non-default request dimensions (cookie names, unique variables)
	 * that differentiate this cache entry from a vanilla anonymous request.
	 * Null when the request has no variant dimensions.
	 *
	 * @since 1.4.0
	 *
	 * @return array<string,mixed>|null Variant data, or null if no variant dimensions.
	 */
	public function get_variant(): ?array {
		return $this->variant;
	}

	/**
	 * Get the human-readable URL from hash generation.
	 *
	 * Returns the normalized host + request path that identifies this cache entry.
	 * Null before generate() is called.
	 *
	 * @since 1.4.0
	 *
	 * @return string|null The request URL, or null if not yet generated.
	 */
	public function get_url(): ?string {
		return $this->url;
	}
}
