<?php
/**
 * Request URI and cookie parser.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

namespace MilliCache\Engine\Request;

use MilliCache\Engine\Cache\Config;
use MilliCache\Engine\Utilities\PatternMatcher;

! defined( 'ABSPATH' ) && exit;

/**
 * Parses and normalizes request URIs and cookies.
 *
 * Handles URI normalization, query string filtering, and cookie parsing
 * according to cache configuration rules.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Parser {

	/**
	 * Cache configuration.
	 *
	 * @var Config
	 */
	private Config $config;

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
	 * Parse and normalize a request URI.
	 *
	 * Removes ignored query parameters and normalizes the path.
	 *
	 * @since 1.0.0
	 *
	 * @param string $request_uri The request URI to parse.
	 * @return string The normalized request URI.
	 */
	public function parse_request_uri( string $request_uri ): string {
		// Fix for requests with no host.
		$parsed = parse_url( 'http://null' . $request_uri );

		// Set the path and lowercase it for normalization.
		$path = strtolower( $parsed['path'] ?? '' );

		// Get and clean the query string.
		$query = $parsed['query'] ?? '';
		$query = $this->remove_query_args( $query, $this->config->ignore_request_keys );

		// Return the cleaned request uri.
		return $query ? $path . '?' . $query : $path;
	}

	/**
	 * Remove query arguments from a query string.
	 *
	 * @since 1.0.0
	 *
	 * @param string        $query_string The input query string, such as foo=bar&baz=qux.
	 * @param array<string> $args         An array of keys to remove.
	 * @return string The resulting query string.
	 */
	public function remove_query_args( string $query_string, array $args ): string {
		// Empty query string.
		if ( empty( $query_string ) ) {
			return '';
		}

		// Decode HTML entities to convert &amp; to &.
		$query_string = html_entity_decode( $query_string );

		// Split the query string into an array.
		$query = explode( '&', $query_string );

		// Remove the query arguments.
		$query = array_filter(
			$query,
			function ( $value ) use ( $args ) {
				// Extract parameter name (everything before = or the entire string if no =).
				$param_name = strpos( $value, '=' ) !== false ?
					substr( $value, 0, strpos( $value, '=' ) ) :
					$value;

				foreach ( $args as $pattern ) {
					if ( PatternMatcher::match( $param_name, $pattern ) ) {
						return false;
					}
				}

				return true;
			}
		);

		// Sort the query arguments to avoid cache duplication.
		sort( $query );

		// Return the resulting query string.
		return implode( '&', $query );
	}

	/**
	 * Parse cookies and remove ignored cookies.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string,string> $cookies The input cookies.
	 * @return array<string,string> The filtered cookies.
	 */
	public function parse_cookies( array $cookies ): array {
		return array_filter(
			$cookies,
			function ( $key ) {
				// Check if cookie matches any pattern in the ignore list.
				foreach ( $this->config->ignore_cookies as $pattern ) {
					if ( PatternMatcher::match( strtolower( $key ), $pattern ) ) {
						return false;
					}
				}

				return true;
			},
			ARRAY_FILTER_USE_KEY
		);
	}

	/**
	 * Get a URL hash for domain.com/path?query.
	 *
	 * @since 1.0.0
	 *
	 * @param string $host The hostname.
	 * @param string $path The request path with optional query string.
	 * @return string The URL hash.
	 */
	public function get_url_hash( string $host, string $path ): string {
		$normalized_host = strtolower( $host );
		$normalized_path = $this->parse_request_uri( $path );

		return md5( $normalized_host . $normalized_path );
	}
}
