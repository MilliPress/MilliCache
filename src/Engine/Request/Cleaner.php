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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cleans and normalizes request data.
 *
 * Conditional headers are dropped before WordPress loads. Ignored query keys
 * are removed from the superglobals right before rendering, so redirects see
 * the request as sent and the cached HTML never contains them.
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
	 * Drop the conditional request headers.
	 *
	 * @since 1.8.1
	 *
	 * @return void
	 */
	public function clean_conditional_headers(): void {
		unset( $_SERVER['HTTP_IF_NONE_MATCH'], $_SERVER['HTTP_IF_MODIFIED_SINCE'] );
	}

	/**
	 * Remove the ignored query keys from $_SERVER, $_GET and $_REQUEST.
	 *
	 * @since 1.8.1
	 *
	 * @return void
	 */
	public function normalize_superglobals(): void {
		$query = $this->get_server_var( 'QUERY_STRING' );
		if ( '' !== $query ) {
			$_SERVER['QUERY_STRING'] = $this->strip_ignored_keys( $query );
		}

		$request_uri = $this->get_server_var( 'REQUEST_URI' );
		if ( false !== strpos( $request_uri, '?' ) ) {
			list( $path, $query ) = explode( '?', $request_uri, 2 );
			$query                  = $this->strip_ignored_keys( $query );
			$_SERVER['REQUEST_URI'] = '' === $query ? $path : $path . '?' . $query;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only unsets ignored keys.
		foreach ( array_keys( $_GET ) as $key ) {
			if ( $this->is_ignored( (string) $key ) ) {
				unset( $_GET[ $key ], $_REQUEST[ $key ] );
			}
		}
	}

	/**
	 * Read a server variable without the entity encoding of {@see ServerVars::get()}.
	 *
	 * @since 1.8.1
	 *
	 * @param string $key The server variable key.
	 * @return string The value as sent, or an empty string if not set.
	 */
	private function get_server_var( string $key ): string {
		return html_entity_decode( ServerVars::get( $key ), ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Remove the ignored pairs from a query string, keeping order and encoding.
	 *
	 * @since 1.8.1
	 *
	 * @param string $query The query string.
	 * @return string The query string without the ignored pairs.
	 */
	private function strip_ignored_keys( string $query ): string {
		$pairs = array_filter(
			explode( '&', $query ),
			fn( string $pair ): bool => ! $this->is_ignored( explode( '=', $pair, 2 )[0] )
		);

		return implode( '&', $pairs );
	}

	/**
	 * Whether a query key matches one of the ignored patterns.
	 *
	 * @since 1.8.1
	 *
	 * @param string $key The query key.
	 * @return bool True if the key is ignored.
	 */
	private function is_ignored( string $key ): bool {
		foreach ( $this->config->ignore_request_keys as $pattern ) {
			if ( PatternMatcher::match( $key, $pattern ) ) {
				return true;
			}
		}

		return false;
	}
}
