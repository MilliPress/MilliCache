<?php
/**
 * Request bucket resolver.
 *
 * @link        https://www.millipress.com
 * @since       1.7.0
 *
 * @package     MilliCache
 * @subpackage  Engine\Request\Bucket
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Engine\Request\Bucket;

use MilliCache\Engine\Cache\Config;
use MilliCache\Engine\Utilities\ServerVars;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves request signals into a normalized bucket map.
 *
 * Each bucket is a (name, token) pair folded into the request hash so that
 * requests sharing the same intent map to the same cache entry. Built-in
 * resolvers handle Authorization (always on, security primitive) and Accept
 * content negotiation (dormant unless `MC_CACHE_BUCKETS['accept']` is set).
 * Additional dimensions are added via {@see Resolver::add_bucket()} from
 * rule actions or other early-phase code.
 *
 * @since      1.7.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Resolver {

	/**
	 * Cache configuration.
	 *
	 * @var Config
	 */
	private Config $config;

	/**
	 * Buckets injected by the rules engine before hash generation.
	 *
	 * @var array<string, string>
	 */
	private array $extra_buckets = array();

	/**
	 * Memoized resolution result.
	 *
	 * @var array<string, string>|null
	 */
	private ?array $resolved = null;

	/**
	 * Constructor.
	 *
	 * @since 1.7.0
	 *
	 * @param Config $config Cache configuration.
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 * Register a bucket for the current request.
	 *
	 * Empty names or tokens are silently dropped.
	 *
	 * @since 1.7.0
	 *
	 * @param string $name  Bucket name (e.g. `device`, `tenant`, `ab`).
	 * @param string $token Bucket token; folded into the request hash.
	 * @return void
	 */
	public function add( string $name, string $token ): void {
		if ( '' === $name || '' === $token ) {
			return;
		}
		$this->extra_buckets[ $name ] = $token;
		$this->resolved               = null;
	}

	/**
	 * Get the full resolved bucket map.
	 *
	 * Runs built-in resolvers (Authorization always-on, Accept dormant
	 * unless `MC_CACHE_BUCKETS['accept']` is configured), then merges any
	 * buckets registered via {@see Resolver::add()}. Programmatic
	 * additions take precedence when names collide. Memoized after first
	 * call until {@see Resolver::add()} invalidates it.
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, string> Bucket name → token map.
	 */
	public function all(): array {
		if ( null !== $this->resolved ) {
			return $this->resolved;
		}

		$buckets = array();

		$auth = $this->resolve_authorization();
		if ( null !== $auth ) {
			$buckets['auth'] = $auth;
		}

		$accept = $this->resolve_accept();
		if ( null !== $accept ) {
			$buckets['accept'] = $accept;
		}

		foreach ( $this->extra_buckets as $name => $token ) {
			$buckets[ $name ] = $token;
		}

		$this->resolved = $buckets;
		return $buckets;
	}

	/**
	 * Get the resolved token for a single bucket.
	 *
	 * @since 1.7.0
	 *
	 * @param string $name Bucket name (e.g. `accept`, `auth`).
	 * @return string|null Token, or null when the bucket did not resolve.
	 */
	public function get( string $name ): ?string {
		return $this->all()[ $name ] ?? null;
	}

	/**
	 * Resolve the Authorization-header bucket.
	 *
	 * Each unique Authorization header maps to a per-value bucket so
	 * authenticated requests don't share cache entries with each other or
	 * with anonymous requests.
	 *
	 * @since 1.7.0
	 *
	 * @return string|null Per-token bucket value, or null when absent.
	 */
	private function resolve_authorization(): ?string {
		$auth = ServerVars::get( 'HTTP_AUTHORIZATION' );
		if ( empty( $auth ) ) {
			return null;
		}

		return md5( (string) $auth );
	}

	/**
	 * Resolve the Accept-header bucket.
	 *
	 * Parses the request's Accept header (with q-values), takes the
	 * top-preferred MIME type, and looks it up in the configured `accept`
	 * bucket map. Returns null when no map is configured, when the header
	 * is empty/wildcard, or when the top type isn't in the map.
	 *
	 * @since 1.7.0
	 *
	 * @return string|null Bucket token, or null when no match.
	 */
	private function resolve_accept(): ?string {
		$accept_buckets = $this->config->buckets['accept'] ?? array();
		if ( empty( $accept_buckets ) ) {
			return null;
		}

		$accept = strtolower( trim( (string) ServerVars::get( 'HTTP_ACCEPT' ) ) );
		if ( '' === $accept || '*/*' === $accept ) {
			return null;
		}

		$top_type = $this->parse_accept_top_type( $accept );
		if ( null === $top_type ) {
			return null;
		}

		return $accept_buckets[ $top_type ] ?? null;
	}

	/**
	 * Parse an Accept header and return the most-preferred MIME type.
	 *
	 * Honors q-values (defaulting to 1.0 when omitted). Ties resolve in
	 * source order, matching RFC 9110 §12.5.1.
	 *
	 * @since 1.7.0
	 *
	 * @param string $accept Lowercased, trimmed Accept header value.
	 * @return string|null The top-preferred MIME type, or null when none usable.
	 */
	private function parse_accept_top_type( string $accept ): ?string {
		$entries = array();
		foreach ( explode( ',', $accept ) as $position => $segment ) {
			$segment = trim( $segment );
			if ( '' === $segment ) {
				continue;
			}

			$parts = explode( ';', $segment );
			$type  = trim( (string) array_shift( $parts ) );
			if ( '' === $type ) {
				continue;
			}

			$q = 1.0;
			foreach ( $parts as $param ) {
				$param = trim( $param );
				if ( 'q=' === substr( $param, 0, 2 ) ) {
					$candidate = (float) substr( $param, 2 );
					if ( $candidate >= 0.0 && $candidate <= 1.0 ) {
						$q = $candidate;
					}
				}
			}

			$entries[] = array(
				'type'     => $type,
				'q'        => $q,
				'position' => $position,
			);
		}

		if ( empty( $entries ) ) {
			return null;
		}

		usort(
			$entries,
			static function ( array $a, array $b ): int {
				if ( $a['q'] !== $b['q'] ) {
					return $b['q'] <=> $a['q'];
				}
				return $a['position'] <=> $b['position'];
			}
		);

		return $entries[0]['type'];
	}
}
