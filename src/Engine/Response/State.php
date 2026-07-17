<?php
/**
 * Request/Response context value object.
 *
 * @link        https://www.millipress.com
 * @since       1.0.0
 *
 * @package     MilliCache
 * @subpackage  Engine\Response
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Engine\Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Immutable value object representing request-specific state.
 *
 * Encapsulates all request-specific state previously scattered
 * across the Engine class properties. All properties are readonly to
 * ensure immutability - modifications return new instances.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class State {

	/**
	 * The request hash identifying this request.
	 *
	 * @var string
	 */
	public string $request_hash;

	/**
	 * Custom TTL override for this request.
	 *
	 * @var int|null
	 */
	public ?int $ttl_override;

	/**
	 * Custom grace period override for this request.
	 *
	 * @var int|null
	 */
	public ?int $grace_override;

	/**
	 * Cache decision override from rules.
	 *
	 * @var array{decision: bool, reason: string}|null
	 */
	public ?array $cache_decision;

	/**
	 * Whether FastCGI background regeneration can be used.
	 *
	 * @var bool
	 */
	public bool $fcgi_regenerate;

	/**
	 * Whether cached content was served for this request.
	 *
	 * @var bool
	 */
	private bool $cache_served = false;

	/**
	 * Served entry's stored headers, stashed for reuse when regenerating.
	 *
	 * @var array<string>|null
	 */
	private ?array $regen_headers = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string                                     $request_hash    Request hash.
	 * @param int|null                                   $ttl_override    Custom TTL override.
	 * @param int|null                                   $grace_override  Custom grace override.
	 * @param array{decision: bool, reason: string}|null $cache_decision  Cache decision override.
	 * @param bool                                       $fcgi_regenerate Whether FCGI regeneration is enabled.
	 */
	public function __construct(
		string $request_hash,
		?int $ttl_override = null,
		?int $grace_override = null,
		?array $cache_decision = null,
		bool $fcgi_regenerate = false
	) {
		$this->request_hash    = $request_hash;
		$this->ttl_override    = $ttl_override;
		$this->grace_override  = $grace_override;
		$this->cache_decision  = $cache_decision;
		$this->fcgi_regenerate = $fcgi_regenerate;
	}

	/**
	 * Create a new context with the given request hash.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hash Request hash.
	 * @return self New context instance.
	 */
	public static function create( string $hash ): self {
		return new self( $hash );
	}

	/**
	 * Create a default context with an empty hash.
	 *
	 * Useful for testing or initialization.
	 *
	 * @since 1.0.0
	 *
	 * @return self New context instance with empty hash.
	 */
	public static function default(): self {
		return new self( '' );
	}

	/**
	 * Create a modified copy with a new TTL override.
	 *
	 * @since 1.0.0
	 *
	 * @param int $ttl TTL value in seconds.
	 * @return self New instance with modified TTL.
	 */
	public function with_ttl_override( int $ttl ): self {
		$copy                = clone $this;
		$copy->ttl_override  = $ttl;
		return $copy;
	}

	/**
	 * Create a modified copy with new grace override.
	 *
	 * @since 1.0.0
	 *
	 * @param int $grace Grace value in seconds.
	 * @return self New instance with modified grace.
	 */
	public function with_grace_override( int $grace ): self {
		$copy                  = clone $this;
		$copy->grace_override  = $grace;
		return $copy;
	}

	/**
	 * Create a modified copy with cache decision override.
	 *
	 * @since 1.0.0
	 *
	 * @param bool   $decision Whether to cache.
	 * @param string $reason   Reason for the decision.
	 * @return self New instance with cache decision.
	 */
	public function with_cache_decision( bool $decision, string $reason = '' ): self {
		$copy                   = clone $this;
		$copy->cache_decision   = array(
			'decision' => $decision,
			'reason'   => $reason,
		);
		return $copy;
	}

	/**
	 * Create a modified copy with FCGI regenerate flag.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $regenerate Whether FCGI regeneration is enabled.
	 * @return self New instance with FCGI flag.
	 */
	public function with_fcgi_regenerate( bool $regenerate ): self {
		$copy                    = clone $this;
		$copy->fcgi_regenerate   = $regenerate;
		return $copy;
	}

	/**
	 * Create a modified copy marking cache as served.
	 *
	 * @since 1.0.0
	 *
	 * @return self New instance with cache_served = true.
	 */
	public function with_cache_served(): self {
		$copy                = clone $this;
		$copy->cache_served  = true;
		return $copy;
	}

	/**
	 * Check if cached content was served.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if cache was served.
	 */
	public function was_cache_served(): bool {
		return $this->cache_served;
	}

	/**
	 * Create a modified copy carrying the served entry's stored headers, reused
	 * as the header base when regenerating (the live table is frozen by then).
	 *
	 * @since 1.7.0
	 *
	 * @param array<string> $headers Stored response headers ("Key: Value").
	 * @return self New instance carrying the regeneration header base.
	 */
	public function with_regen_headers( array $headers ): self {
		$copy                = clone $this;
		$copy->regen_headers = $headers;
		return $copy;
	}

	/**
	 * Get the stashed regeneration header base, if any.
	 *
	 * @since 1.7.0
	 *
	 * @return array<string>|null Stored headers to reuse, or null when not regenerating.
	 */
	public function get_regen_headers(): ?array {
		return $this->regen_headers;
	}

	/**
	 * Get the request hash.
	 *
	 * @since 1.0.0
	 *
	 * @return string Request hash.
	 */
	public function get_request_hash(): string {
		return $this->request_hash;
	}

	/**
	 * Get TTL override value.
	 *
	 * @since 1.0.0
	 *
	 * @return int|null TTL override or null.
	 */
	public function get_ttl_override(): ?int {
		return $this->ttl_override;
	}

	/**
	 * Get grace override value.
	 *
	 * @since 1.0.0
	 *
	 * @return int|null Grace override or null.
	 */
	public function get_grace_override(): ?int {
		return $this->grace_override;
	}

	/**
	 * Get cache decision.
	 *
	 * @since 1.0.0
	 *
	 * @return array{decision: bool, reason: string}|null Cache decision or null.
	 */
	public function get_cache_decision(): ?array {
		return $this->cache_decision;
	}

	/**
	 * Check if FCGI regeneration should be used.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if FCGI regeneration is enabled.
	 */
	public function should_fcgi_regenerate(): bool {
		return $this->fcgi_regenerate;
	}
}
