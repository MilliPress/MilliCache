<?php
/**
 * Request-scoped cache behavior options.
 *
 * @link        https://www.millipress.com
 * @since       1.0.0
 *
 * @package     MilliCache
 * @subpackage  Engine\Response
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Engine;

use MilliCache\Engine\Response\State;

! defined( 'ABSPATH' ) && exit;

/**
 * Manages request-specific cache behavior options.
 *
 * Holds mutable option values (TTL, grace period, cache decision) that can be
 * set by rules or API during request processing. These values serve as a staging
 * area before being applied to the immutable Response\State object for use during
 * response handling.
 *
 * Example usage:
 *     Engine::instance()->options()->set_ttl(7200);
 *     Engine::instance()->options()->set_grace(600);
 *     Engine::instance()->options()->set_cache_decision(false, 'User is admin');
 *
 * @since      1.0.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Options {

	/**
	 * TTL override in seconds.
	 *
	 * @var int|null
	 */
	private ?int $ttl = null;

	/**
	 * Grace period override in seconds.
	 *
	 * @var int|null
	 */
	private ?int $grace = null;

	/**
	 * Cache decision override.
	 *
	 * @var array{decision: bool, reason: string}|null
	 */
	private ?array $cache_decision = null;

	/**
	 * Set TTL override.
	 *
	 * @since 1.0.0
	 *
	 * @param int $ttl TTL in seconds (must be positive).
	 * @return void
	 */
	public function set_ttl( int $ttl ): void {
		if ( $ttl > 0 ) {
			$this->ttl = $ttl;
		}
	}

	/**
	 * Set grace period override.
	 *
	 * @since 1.0.0
	 *
	 * @param int $grace Grace period in seconds (must be non-negative).
	 * @return void
	 */
	public function set_grace( int $grace ): void {
		if ( $grace >= 0 ) {
			$this->grace = $grace;
		}
	}

	/**
	 * Set cache decision override.
	 *
	 * @since 1.0.0
	 *
	 * @param bool   $decision Whether to cache this request.
	 * @param string $reason   Reason for the decision.
	 * @return void
	 */
	public function set_cache_decision( bool $decision, string $reason = '' ): void {
		$this->cache_decision = array(
			'decision' => $decision,
			'reason'   => $reason,
		);
	}

	/**
	 * Get TTL override.
	 *
	 * @since 1.0.0
	 *
	 * @return int|null TTL in seconds or null if not set.
	 */
	public function get_ttl(): ?int {
		return $this->ttl;
	}

	/**
	 * Get grace period override.
	 *
	 * @since 1.0.0
	 *
	 * @return int|null Grace period in seconds or null if not set.
	 */
	public function get_grace(): ?int {
		return $this->grace;
	}

	/**
	 * Get cache decision override.
	 *
	 * @since 1.0.0
	 *
	 * @return array{decision: bool, reason: string}|null Cache decision or null if not set.
	 */
	public function get_cache_decision(): ?array {
		return $this->cache_decision;
	}

	/**
	 * Check if any options are set.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if any options are set.
	 */
	public function has_any(): bool {
		return null !== $this->ttl
			|| null !== $this->grace
			|| null !== $this->cache_decision;
	}

	/**
	 * Apply all options to the State instance.
	 *
	 * Transfers all set options to the given State using its
	 * immutable with_* methods.
	 *
	 * @since 1.0.0
	 *
	 * @param State $state The state to apply options to.
	 * @return State New state with all options applied.
	 */
	public function apply_to_state( State $state ): State {
		if ( null !== $this->ttl ) {
			$state = $state->with_ttl_override( $this->ttl );
		}

		if ( null !== $this->grace ) {
			$state = $state->with_grace_override( $this->grace );
		}

		if ( null !== $this->cache_decision ) {
			$state = $state->with_cache_decision(
				$this->cache_decision['decision'],
				$this->cache_decision['reason']
			);
		}

		return $state;
	}

	/**
	 * Reset all options.
	 *
	 * Clears all set options. Primarily useful for testing.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function reset(): void {
		$this->ttl            = null;
		$this->grace          = null;
		$this->cache_decision = null;
	}
}
