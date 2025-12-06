<?php
/**
 * Response manager orchestrating cache retrieval and output buffering.
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
use MilliCache\Engine\Cache\Manager as CacheManager;
use MilliCache\Engine\Flags;
use MilliCache\Engine\Request\Processor as RequestManager;

! defined( 'ABSPATH' ) && exit;

/**
 * Processor class for HTTP response and caching orchestration.
 *
 * Coordinates cache retrieval, output buffering, header management,
 * and cache decision logic. Acts as the main orchestrator for the
 * response phase of the cache lifecycle.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Processor {

	/**
	 * Cache configuration.
	 *
	 * @var Config
	 */
	private Config $config;

	/**
	 * Current response state.
	 *
	 * @var State|null
	 */
	private ?State $state = null;

	/**
	 * Flag manager.
	 *
	 * @var Flags
	 */
	private Flags $flags;

	/**
	 * Header manager.
	 *
	 * @var Headers
	 */
	private Headers $headers;

	/**
	 * Cache manager.
	 *
	 * @var CacheManager
	 */
	private CacheManager $cache_manager;

	/**
	 * Request manager.
	 *
	 * @var RequestManager
	 */
	private RequestManager $request_manager;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Config         $config          Cache configuration.
	 * @param Flags          $flags           Flag manager instance.
	 * @param Headers        $headers Header manager instance.
	 * @param CacheManager   $cache_manager   Cache manager instance.
	 * @param RequestManager $request_manager Request manager instance.
	 */
	public function __construct(
		Config $config,
		Flags $flags,
		Headers $headers,
		CacheManager $cache_manager,
		RequestManager $request_manager
	) {
		$this->config          = $config;
		$this->cache_manager   = $cache_manager;
		$this->flags    = $flags;
		$this->request_manager = $request_manager;
		$this->headers  = $headers;
	}

	/**
	 * Start output buffering for this request.
	 *
	 * @since 1.0.0
	 *
	 * @param State $context Request context.
	 * @return void
	 */
	public function start_output_buffer( State $context ): void {
		$this->state = $context;
		ob_start( array( $this, 'process_output_buffer' ) );
	}

	/**
	 * Process output buffer callback.
	 *
	 * Caches the output if appropriate, sets headers, and returns the output.
	 *
	 * @since 1.0.0
	 *
	 * @param string $output The buffered output.
	 * @return string|null Output to send (null for background FCGI tasks).
	 */
	public function process_output_buffer( string $output ): ?string {
		if ( ! $this->state ) {
			return $output;
		}

		// Get all flags for this request.
		$flags = $this->flags->get_all();
		$flags[] = 'url:' . $this->request_manager->get_url_hash();
		$flags = array_unique( $flags );

		// If no flags set, use fallback site flag.
		if ( count( $flags ) <= 1 ) {
			$flags[] = $this->flags->get_key( 'flag' );
		}

		// Get TTL/grace options and debug data from context.
		$custom_ttl   = $this->state->get_ttl_override();
		$custom_grace = $this->state->get_grace_override();
		$debug        = $this->config->debug ? $this->state->get_debug_data() : null;

		// Cache the output.
		$result = $this->cache_manager->cache_output(
			$this->state->get_request_hash(),
			$output,
			$flags,
			$custom_ttl,
			$custom_grace,
			$debug
		);

		// Set headers based on result.
		if ( ! $result['cached'] && ! $this->state->should_fcgi_regenerate() ) {
			$this->headers->set_status( 'bypass' );
		}

		// Add reason header if available.
		if ( ! empty( $result['reason'] ) ) {
			$this->headers->set_reason( $result['reason'] );
		}

		// Return output (null for background FCGI tasks).
		return $this->state->should_fcgi_regenerate() ? null : $output;
	}

	/**
	 * Retrieve and serve cached content if available.
	 *
	 * @since 1.0.0
	 *
	 * @param State $state Request state.
	 * @return State Updated context with cache_served flag and fcgi_regenerate.
	 */
	public function retrieve_and_serve_cache( State $state ): State {
		// Get and validate cache.
		$result = $this->cache_manager->get_and_validate(
			$state->get_request_hash(),
		);

		// No cache to serve.
		if ( ! $result['serve'] ) {
			if ( $result['regenerate'] ) {
				return $state->with_fcgi_regenerate( true );
			}
			return $state;
		}

		// Update context with regenerate flag.
		if ( $result['regenerate'] ) {
			$state = $state->with_fcgi_regenerate( true );
		}

		// Get the cache entry (guaranteed to exist if serve is true).
		$entry = $result['entry'];
		assert( $entry instanceof Entry );

		// Set debug headers if enabled.
		$this->set_debug_headers(
			$state,
			$entry,
			$result['result']->flags
		);

		// Set status header.
		$status = $state->should_fcgi_regenerate() ? 'stale' : 'hit';
		$this->headers->set_status( $status );

		// Output the cache.
		$this->cache_manager->get_reader()->output(
			$entry,
			$state->should_fcgi_regenerate()
		);

		// Mark cache as served.
		return $state->with_cache_served();
	}

	/**
	 * Set all debug headers for a cache hit.
	 *
	 * Orchestrates setting multiple debug headers by delegating to the
	 * header manager's set() method. Only sets headers when debug mode
	 * is enabled in configuration.
	 *
	 * @since 1.0.0
	 *
	 * @param State         $state   Request state with hash and debug data.
	 * @param Entry         $entry   Cache entry being served.
	 * @param array<string> $flags   Cache flags associated with this entry.
	 * @return void
	 */
	private function set_debug_headers(
		State $state,
		Entry $entry,
		array $flags
	): void {
		if ( ! $this->config->debug ) {
			return;
		}

		// Set request hash key.
		$hash = $state->get_request_hash();
		if ( ! empty( $hash ) ) {
			$this->headers->set( 'Key', $hash );
		}

		// Set cache update time.
		$this->headers->set( 'Time', gmdate( 'D, d M Y H:i:s \G\M\T', $entry->updated ) );

		// Set cache flags.
		$this->headers->set( 'Flags', implode( ' ', $flags ) );

		// Set gzip compression indicator.
		if ( $entry->gzip ) {
			$this->headers->set( 'Gzip', 'true' );
		}

		// Set time until expiry.
		$validator = $this->cache_manager->get_validator();
		$time_left = $validator->time_to_expiry( $entry );
		$this->headers->set( 'Expires', $validator->format_time_remaining( $time_left ) );
	}
}
