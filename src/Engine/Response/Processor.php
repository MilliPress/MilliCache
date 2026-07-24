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

use MilliCache\Engine;
use MilliCache\Engine\Cache\Config;
use MilliCache\Engine\Cache\Entry;
use MilliCache\Engine\Cache\Manager as CacheManager;
use MilliCache\Engine\Flags;
use MilliCache\Engine\Request\Processor as RequestManager;
use MilliCache\Engine\Utilities\ServerVars;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
		$flags = $this->collect_flags();

		// Get current HTTP status.
		$status = http_response_code();
		if ( false === $status || true === $status ) {
			$status = 200;
		}

		// Get URL, TTL/grace options and variant data from context.
		$url          = $this->request_manager->get_url() ?? '';
		$custom_ttl   = $this->state->get_ttl_override();
		$custom_grace = $this->state->get_grace_override();
		$variant      = $this->request_manager->get_variant();

		// On regen the live header table is frozen (headers already sent), so
		// reuse the served entry's stored headers instead of headers_list().
		$regen_headers = $this->state->get_regen_headers();
		$headers       = null !== $regen_headers ? $regen_headers : headers_list();

		// Bypass storage entirely for unsupported Vary directives.
		$vary_bypass = $this->should_bypass_for_vary( $headers );
		if ( null !== $vary_bypass ) {
			if ( ! $this->state->should_fcgi_regenerate() ) {
				$this->headers->set_status( 'bypass' );
			}
			$this->headers->set_reason( $vary_bypass );
			return $this->state->should_fcgi_regenerate() ? null : $output;
		}

		// Store the response. Identical bodies across variants are deduplicated
		// automatically by the Storage layer's content-addressable output keyspace.
		$result = $this->cache_manager->cache_output(
			$this->request_manager->get_hasher()->get_hash() ?? '',
			$output,
			$flags,
			$status,
			$headers,
			$custom_ttl,
			$custom_grace,
			$url,
			$variant
		);

		// Set headers based on result.
		if ( ! $result['cached'] && ! $this->state->should_fcgi_regenerate() ) {
			$this->headers->set_status( 'bypass' );
		}

		// Add a reason header if available.
		if ( ! empty( $result['reason'] ) ) {
			$this->headers->set_reason( $result['reason'] );
		}

		// Count a cacheable miss — but not the background regenerate of a
		// stale hit (already counted as a hit), nor uncacheable responses.
		$regenerate = $this->state->should_fcgi_regenerate();
		if ( ! $regenerate && $result['cached'] ) {
			$this->record_miss_metric( strlen( $output ) );
		}

		// Return output (null for background FCGI tasks).
		return $regenerate ? null : $output;
	}

	/**
	 * Decide whether to bypass storage entirely based on Vary directives.
	 *
	 * Allowed tokens are either covered by request keying (Host,
	 * Authorization via the auth bucket, Accept via the optional accept
	 * bucket, Accept-Encoding via gzip storage) or describe request bodies
	 * that cacheable GET/HEAD requests do not carry (Content-Type,
	 * Content-Length). Vary: Cookie bypasses on purpose: caching
	 * per-cookie responses would fragment storage into per-visitor entries.
	 *
	 * @since 1.6.0
	 *
	 * @param array<string> $headers Response headers ("Key: Value" strings).
	 * @return string|null Bypass reason, or null to allow caching.
	 */
	private function should_bypass_for_vary( array $headers ): ?string {
		$vary = implode( ',', $this->extract_header_values( $headers, 'vary' ) );
		if ( '' === $vary ) {
			return null;
		}

		$tokens = $this->parse_vary( $vary );

		if ( in_array( '*', $tokens, true ) ) {
			return 'Vary: * is uncacheable';
		}

		$allowed = array(
			'host',
			'accept',
			'accept-encoding',
			'authorization',
			'content-type',
			'content-length',
		);
		foreach ( $tokens as $token ) {
			if ( ! in_array( $token, $allowed, true ) ) {
				return 'Vary: ' . $token . ' is not supported';
			}
		}

		return null;
	}

	/**
	 * Extract all values of a named header from a "Key: Value" header list.
	 *
	 * Returns the trimmed values of every occurrence of the named header
	 * (matching is case-insensitive), in source order. Multiple occurrences
	 * of a list-valued header combine per RFC 9110 §5.2, so callers must
	 * consider all of them, not just the last.
	 *
	 * @since 1.6.0
	 *
	 * @param array<string> $headers Header lines.
	 * @param string        $name    Lowercase header name.
	 * @return array<string> Header values, empty when the header is missing.
	 */
	private function extract_header_values( array $headers, string $name ): array {
		$values = array();
		foreach ( $headers as $header ) {
			if ( false === strpos( $header, ':' ) ) {
				continue;
			}
			list( $key, $candidate ) = explode( ':', $header, 2 );
			if ( strtolower( trim( $key ) ) === $name ) {
				$values[] = trim( $candidate );
			}
		}
		return $values;
	}

	/**
	 * Parse a Vary header value into a list of lowercased, deduplicated tokens.
	 *
	 * @since 1.6.0
	 *
	 * @param string $vary Vary header value.
	 * @return array<string> Tokens.
	 */
	private function parse_vary( string $vary ): array {
		$tokens = array();
		foreach ( explode( ',', $vary ) as $token ) {
			$token = strtolower( trim( $token ) );
			if ( '' !== $token ) {
				$tokens[] = $token;
			}
		}
		return array_values( array_unique( $tokens ) );
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

		// Update context with a regenerate flag.
		if ( $result['regenerate'] ) {
			$state = $state->with_fcgi_regenerate( true );
		}

		// Get the cache entry (guaranteed to exist if serve is true).
		$entry = $result['entry'];
		assert( $entry instanceof Entry );

		// Stash the served entry's headers to reuse when regenerating.
		if ( $state->should_fcgi_regenerate() ) {
			$state = $state->with_regen_headers( $entry->headers );
		}

		// Set debug headers if enabled.
		$this->set_debug_headers(
			$state,
			$entry,
			$result['result']->flags
		);

		// Set status header.
		$status = $state->should_fcgi_regenerate() ? 'stale' : 'hit';
		$this->headers->set_status( $status );

		// Record the hit before output() flushes and exits (written post-response).
		$this->record_hit_metric(
			$entry,
			$result['result']->flags,
			$state->should_fcgi_regenerate()
		);

		// Output the cache.
		$this->cache_manager->get_reader()->output(
			$entry,
			$state->should_fcgi_regenerate()
		);

		// Mark cache as served.
		return $state->with_cache_served();
	}

	/**
	 * Store a response in the cache.
	 *
	 * Framework-independent entry point for caching a response. Unlike the
	 * internal output buffering path (process_output_buffer), this method
	 * accepts pre-built response data — suitable for middleware integrations
	 * (e.g., Laravel, Acorn) that already have the response in the hand.
	 *
	 * Headers must be in "Key: Value" string format, e.g.:
	 *     ['Content-Type: text/html', 'X-Custom: value']
	 *
	 * @since 1.0.0
	 *
	 * @param string        $output       The response body.
	 * @param array<string> $headers      Response headers in "Key: Value" format.
	 * @param int           $status       HTTP status code.
	 * @param int|null      $custom_ttl   Optional TTL override in seconds.
	 * @param int|null      $custom_grace Optional grace period override in seconds.
	 * @return array{cached: bool, reason: string} Result with cached flag and reason.
	 */
	public function store(
		string $output,
		array $headers,
		int $status,
		?int $custom_ttl = null,
		?int $custom_grace = null
	): array {
		if ( ! $this->request_manager->get_hasher()->get_hash() ) {
			$this->request_manager->process();
		}

		$vary_bypass = $this->should_bypass_for_vary( $headers );
		if ( null !== $vary_bypass ) {
			return array(
				'cached' => false,
				'reason' => $vary_bypass,
			);
		}

		return $this->cache_manager->cache_output(
			$this->request_manager->get_hasher()->get_hash() ?? '',
			$output,
			$this->collect_flags(),
			$status,
			$headers,
			$custom_ttl,
			$custom_grace,
			$this->request_manager->get_url() ?? '',
			$this->request_manager->get_variant()
		);
	}

	/**
	 * Collect and prepare flags for cache storage.
	 *
	 * Gathers all flags accumulated during request processing,
	 * adds the URL hash flag, and ensures a fallback flag exists.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string> Prepared flags for cache storage.
	 */
	private function collect_flags(): array {
		$flags   = $this->flags->get_all();
		$flags[] = 'url:' . $this->request_manager->get_url_hash();
		$flags   = array_unique( $flags );

		if ( count( $flags ) <= 1 ) {
			$flags[] = $this->flags->add_key_prefix( 'flag' );
		}

		return $flags;
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

	/**
	 * Buffer a cache-hit metric (prefix from the entry's flags, pre-WP safe).
	 *
	 * @since 1.7.0
	 *
	 * @param Entry         $entry The entry being served.
	 * @param array<string> $flags The entry's flags (storage prefix stripped).
	 * @param bool          $stale Whether the entry is served stale.
	 * @return void
	 */
	private function record_hit_metric( Entry $entry, array $flags, bool $stale ): void {
		if ( $this->is_internal_request() ) {
			return;
		}

		Engine::instance()->metrics()->record()->hit(
			Flags::detect_prefix( $flags ),
			$entry->size_raw,
			$this->elapsed_ms(),
			$stale
		);
	}

	/**
	 * Buffer a genuine cacheable miss (prefix from {@see Flags::get_prefix()}).
	 * Flushed immediately, not via the Engine shutdown flush — this runs in the
	 * output-buffer callback, which PHP executes *after* shutdown functions.
	 *
	 * @since 1.7.0
	 *
	 * @param int $bytes Bytes of the freshly generated response.
	 * @return void
	 */
	private function record_miss_metric( int $bytes ): void {
		if ( $this->is_internal_request() ) {
			return;
		}

		$record = Engine::instance()->metrics()->record();
		$record->miss(
			$this->flags->get_prefix(),
			$bytes,
			$this->elapsed_ms()
		);
		$record->flush();
	}

	/**
	 * Milliseconds elapsed since the request started.
	 *
	 * @since 1.7.0
	 *
	 * @return int
	 */
	private function elapsed_ms(): int {
		$start = ServerVars::has( 'REQUEST_TIME_FLOAT' )
			? (float) ServerVars::get( 'REQUEST_TIME_FLOAT' )
			: microtime( true );

		return (int) round( ( microtime( true ) - $start ) * 1000 );
	}

	/**
	 * Whether this is MilliCache's own internal request.
	 *
	 * @since 1.7.0
	 *
	 * @return bool
	 */
	private function is_internal_request(): bool {
		return 0 === strpos( ServerVars::get( 'HTTP_USER_AGENT' ), 'MilliCache/' );
	}
}
