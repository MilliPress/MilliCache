<?php
/**
 * Request data cleaner.
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
use MilliCache\Engine\Utilities\PatternMatcher;
use MilliCache\Engine\Utilities\ServerVars;

! defined( 'ABSPATH' ) && exit;

/**
 * Cleans and normalizes request data.
 *
 * Removes ignored query parameters from superglobals and server variables
 * to ensure consistent cache key generation.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @subpackage Engine\Request
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Cleaner {

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
	 * Clean up the request superglobals and server variables.
	 *
	 * Removes ETags, Last-Modified headers, and filters ignored query parameters.
	 * Modifies $_SERVER, $_GET, and $_REQUEST in place.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function clean_request(): void {
		// Unset the ETag and Last-Modified headers.
		unset( $_SERVER['HTTP_IF_NONE_MATCH'], $_SERVER['HTTP_IF_MODIFIED_SINCE'] );

		// Remove ignored request keys from the query string.
		if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
			$_SERVER['QUERY_STRING'] = $this->parser->remove_query_args(
				(string) filter_var( ServerVars::get( 'QUERY_STRING' ), FILTER_SANITIZE_URL ),
				$this->config->ignore_request_keys
			);
		}

		// Remove ignored request keys from the request uri.
		$request_uri = ServerVars::get( 'REQUEST_URI' );
		if ( $request_uri && strpos( $request_uri, '?' ) !== false ) {
			list($path, $query) = explode( '?', $request_uri, 2 );
			$query = $this->parser->remove_query_args( $query, $this->config->ignore_request_keys );
			$_SERVER['REQUEST_URI'] = $path . ( ! empty( $query ) ? '?' . $query : '' );
		}

		// Remove ignored request keys from the superglobals.
		foreach ( $_GET as $key => $value ) {
			foreach ( $this->config->ignore_request_keys as $pattern ) {
				if ( PatternMatcher::match( $key, $pattern ) ) {
					unset( $_GET[ $key ], $_REQUEST[ $key ] );
					break;
				}
			}
		}
	}
}
