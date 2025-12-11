<?php
/**
 * HTTP header manager for cache responses.
 *
 * @link        https://www.millipress.com
 * @since       1.0.0
 *
 * @package     MilliCache
 * @subpackage  Engine\Response
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Engine\Response;

use MilliCache\Engine\Cache\Config;
use MilliCache\Engine\Cache\Entry;
use MilliCache\Engine\Cache\Validator;

! defined( 'ABSPATH' ) && exit;

/**
 * Worker class for managing HTTP cache headers.
 *
 * Handles setting X-MilliCache-* headers for debugging and monitoring
 * cache behavior. All headers are prefixed with X-MilliCache-.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Headers {

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
	 * Set a cache header.
	 *
	 * Sets an X-MilliCache-{$key} header if headers haven't been sent yet.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   Header key (without "X-MilliCache-" prefix).
	 * @param string $value Header value.
	 * @return void
	 */
	public function set( string $key, string $value ): void {
		if ( ! headers_sent() ) {
			header( "X-MilliCache-$key: $value" );
		}
	}

	/**
	 * Set the cache status header.
	 *
	 * Common status values: 'miss', 'hit', 'stale', 'bypass'.
	 *
	 * @since 1.0.0
	 *
	 * @param string $status Cache status.
	 * @return void
	 */
	public function set_status( string $status ): void {
		$this->set( 'Status', $status );
	}

	/**
	 * Set the request hash header.
	 *
	 * Only sets header if debug mode is enabled.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hash Request hash.
	 * @return void
	 */
	public function set_key( string $hash ): void {
		if ( $this->config->debug && ! empty( $hash ) ) {
			$this->set( 'Key', $hash );
		}
	}

	/**
	 * Set the cache decision reason header.
	 *
	 * Only sets the header if debug mode is enabled and value is not empty.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value Reason for cache decision.
	 * @return void
	 */
	public function set_reason( string $value ): void {
		if ( $this->config->debug && ! empty( $value ) ) {
			$this->set( 'Reason', $value );
		}
	}
}
