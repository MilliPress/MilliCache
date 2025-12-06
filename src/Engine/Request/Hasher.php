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
	 * Debug data for hash generation.
	 *
	 * @var array<string,mixed>|null
	 */
	private ?array $debug_data = null;

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

		// Store debug data if enabled.
		$this->debug_data = $this->config->debug ? array( 'request_hash' => $request_hash ) : null;

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
	 * Get debug data from hash generation.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string,mixed>|null Debug data, or null if debug disabled.
	 */
	public function get_debug_data(): ?array {
		return $this->debug_data;
	}
}
