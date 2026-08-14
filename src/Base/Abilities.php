<?php
/**
 * Domain abilities for the Abilities API.
 *
 * @link       https://www.millipress.com
 * @since      1.8.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Base
 */

namespace MilliCache\Base;

use MilliCache\MilliCache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the `abilities` entries that expose MilliCache operations.
 *
 * @since 1.8.0
 */
final class Abilities {

	/**
	 * Build the cache abilities for one scope.
	 *
	 * @since 1.8.0
	 *
	 * @param callable      $status_provider Returns the full status payload.
	 * @param callable      $clear_handler   Takes a WP_REST_Request, returns a response or error.
	 * @param bool          $is_network      Whether this is the network Manager.
	 * @param callable|null $network_status Returns the network payload on a subsite.
	 * @return array<int, array<string, mixed>>
	 */
	public static function cache( callable $status_provider, callable $clear_handler, bool $is_network, ?callable $network_status = null ): array {
		return array(
			self::status( $status_provider, $is_network, $network_status ),
			self::clear( $clear_handler, $is_network ),
		);
	}

	/**
	 * Build the `cache-status` ability entry.
	 *
	 * Returns a curated subset, not the whole `/status` payload: that one
	 * carries the site's plugin and theme inventory, which is context bloat
	 * and needless disclosure once it reaches a model.
	 *
	 * @since 1.8.0
	 *
	 * @param callable      $status_provider Returns the full status payload.
	 * @param bool          $is_network      Whether this is the network Manager.
	 * @param callable|null $network_status  Returns the network payload on a
	 *                                       subsite, whose own checks omit the
	 *                                       network-owned ones.
	 * @return array<string, mixed>
	 */
	private static function status( callable $status_provider, bool $is_network, ?callable $network_status = null ): array {
		return array(
			'id'            => ( $is_network ? 'network-' : '' ) . 'cache-status',
			'label'         => $is_network
				? __( 'Network Cache Status', 'millicache' )
				: __( 'Cache Status', 'millicache' ),
			'description'   => $is_network
				/* translators: An AI reads this to decide when to call this operation. Keep `issues` and the status words verbatim; they are literal response values the AI checks for. */
				? __( 'Report whether the network cache is working: overall health, storage connectivity, cache size and the list of problems found. Call this before diagnosing why pages are not being cached. The `issues` list only contains checks that need attention; an empty list means everything passed.', 'millicache' )
				/* translators: An AI reads this to decide when to call this operation. Keep `issues` and the status words verbatim; they are literal response values the AI checks for. */
				: __( 'Report whether the cache is working: overall health, storage connectivity, cache size and the list of problems found. Call this before diagnosing why pages are not being cached. The `issues` list only contains checks that need attention; an empty list means everything passed.', 'millicache' ),
			'callback'      => static function () use ( $status_provider, $network_status ): array {
				$payload = $status_provider();
				$network = null !== $network_status ? $network_status() : null;

				return self::summarize(
					is_array( $payload ) ? $payload : array(),
					is_array( $network ) ? $network : array()
				);
			},
			'input_schema'  => array(
				'type'                 => array( 'object', 'null' ),
				'additionalProperties' => false,
			),
			'output_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'health'  => array(
						'type'        => 'string',
						'enum'        => array( 'ok', 'warning', 'error' ),
						'description' => __( 'Overall health: error when caching is broken, warning when something is recommended.', 'millicache' ),
					),
					'storage'   => array(
						'type'                 => 'object',
						'description'          => __( 'Connectivity to the in-memory store. When connected is false, nothing can be cached. `mode` is how that store is reached (single server, sentinel, or cluster) and says nothing about the WordPress installation.', 'millicache' ),
						'additionalProperties' => true,
					),
					'multisite' => array(
						'type'        => 'boolean',
						'description' => __( 'Whether this is a multisite installation. On multisite the storage and several settings are network-wide, and there are separate network-scoped operations.', 'millicache' ),
					),
					'cache'   => array(
						'type'                 => 'object',
						'description'          => __( 'Cached entry count, total size as bytes and as a human-readable string, and the configured lifetime in seconds. Compute with size_bytes, not size.', 'millicache' ),
						'additionalProperties' => true,
					),
					'issues'  => array(
						'type'        => 'array',
						/* translators: An AI reads this. Keep `scope`, `site` and `network` verbatim; they are literal response values. */
						'description' => __( 'Checks that need attention. Empty when everything passed. Each issue carries a `scope`: `site` for something this site controls, `network` for a network-wide setting only a network administrator can change.', 'millicache' ),
						'items'       => array(
							'type'                 => 'object',
							'additionalProperties' => true,
						),
					),
				),
				'required'   => array( 'health', 'storage', 'cache', 'issues' ),
			),
			'meta'          => array(
				'annotations' => array(
					'readonly' => true,
				),
			),
		);
	}

	/**
	 * Reduce the full status payload to the fields worth sending to a model.
	 *
	 * @since 1.8.0
	 *
	 * @param array<string, mixed> $payload The payload from StatusBuilder::build().
	 * @param array<string, mixed> $network The network payload, empty on a single site.
	 * @return array<string, mixed>
	 */
	private static function summarize( array $payload, array $network = array() ): array {
		$debug   = is_array( $payload['debug'] ?? null ) ? $payload['debug'] : array();
		$checks  = is_array( $debug['checks'] ?? null ) ? $debug['checks'] : array();
		$storage = is_array( $payload['storage'] ?? null ) ? $payload['storage'] : array();
		$cache   = is_array( $payload['cache'] ?? null ) ? $payload['cache'] : array();
		$config  = is_array( $storage['config'] ?? null ) ? $storage['config'] : array();

		$network_debug  = is_array( $network['debug'] ?? null ) ? $network['debug'] : array();
		$network_checks = is_array( $network_debug['checks'] ?? null ) ? $network_debug['checks'] : array();

		$network_issues = self::problems( $network_checks, 'network' );
		$issues         = array_merge( self::problems( $checks, 'site' ), $network_issues );

		$mode = self::as_string( $config, 'mode' );

		return array(
			'health'    => self::health(
				is_string( $debug['health'] ?? null ) ? $debug['health'] : 'ok',
				$network_issues
			),
			'multisite' => is_multisite(),
			// Subsites see only connectivity; the connection itself is
			// network-owned, so `mode` is absent rather than empty there.
			'storage' => '' === $mode
				? array( 'connected' => (bool) ( $storage['connected'] ?? false ) )
				: array(
					'connected' => (bool) ( $storage['connected'] ?? false ),
					'mode'      => $mode,
				),
			'cache'   => array(
				'entries'    => self::as_int( $cache, 'index' ),
				'size_bytes' => self::as_int( $cache, 'size' ),
				'size'       => self::as_string( $cache, 'size_human' ),
				'ttl'        => self::as_int( $cache, 'ttl' ),
			),
			'issues'  => $issues,
		);
	}

	/**
	 * Overall health, raised to match the network-owned findings.
	 *
	 * The site's own health covers its own checks only, so leaving it alone
	 * would answer `ok` while listing something that stops caching outright.
	 *
	 * @since 1.8.0
	 *
	 * @param string                           $health The site's own health.
	 * @param array<int, array<string, mixed>> $issues The network-scoped issues.
	 * @return string
	 */
	private static function health( string $health, array $issues ): string {
		$statuses = array_column( $issues, 'status' );

		if ( in_array( 'critical', $statuses, true ) ) {
			return 'error';
		}

		if ( 'ok' === $health && in_array( 'recommended', $statuses, true ) ) {
			return 'warning';
		}

		return $health;
	}

	/**
	 * The checks that need attention, as issues.
	 *
	 * @since 1.8.0
	 *
	 * @param array<int, mixed> $checks The checks a status payload carries.
	 * @param string            $scope  Where they were read: `site` or `network`.
	 * @return array<int, array<string, mixed>>
	 */
	private static function problems( array $checks, string $scope ): array {
		$issues = array();

		foreach ( $checks as $check ) {
			if ( ! is_array( $check ) ) {
				continue;
			}

			$status = self::as_string( $check, 'status' );

			if ( ! in_array( $status, array( 'critical', 'recommended' ), true ) ) {
				continue;
			}

			$issues[] = array(
				'id'          => self::as_string( $check, 'id' ),
				'label'       => self::as_string( $check, 'label' ),
				'status'      => $status,
				'scope'       => $scope,
				'description' => wp_strip_all_tags( self::as_string( $check, 'description' ) ),
			);
		}

		return $issues;
	}

	/**
	 * Read a string field from an untyped payload array.
	 *
	 * Values pass through the `millicache_status_checks` filter, so any of
	 * them can be anything by the time they arrive here.
	 *
	 * @since 1.8.0
	 *
	 * @param array<array-key, mixed> $source The array to read from.
	 * @param string                  $key    The field name.
	 * @return string Empty string when missing or not scalar.
	 */
	private static function as_string( array $source, string $key ): string {
		$value = $source[ $key ] ?? null;

		return is_scalar( $value ) ? (string) $value : '';
	}

	/**
	 * Read an integer field from an untyped payload array.
	 *
	 * @since 1.8.0
	 *
	 * @param array<array-key, mixed> $source The array to read from.
	 * @param string                  $key    The field name.
	 * @return int Zero when missing or not numeric.
	 */
	private static function as_int( array $source, string $key ): int {
		$value = $source[ $key ] ?? null;

		return is_numeric( $value ) ? (int) $value : 0;
	}

	/**
	 * Build the `cache-clear` ability entry.
	 *
	 * @since 1.8.0
	 *
	 * @param callable $clear_handler Takes a WP_REST_Request, returns a response or error.
	 * @param bool     $is_network    Whether this is the network Manager.
	 * @return array<string, mixed>
	 */
	private static function clear( callable $clear_handler, bool $is_network ): array {
		return array(
			'id'            => ( $is_network ? 'network-' : '' ) . 'cache-clear',
			'label'         => $is_network
				? __( 'Clear Network Cache', 'millicache' )
				: __( 'Clear Cache', 'millicache' ),
			'description'   => $is_network
				/* translators: An AI reads this to decide when to call this destructive operation. Keep `scope`, `targets`, `all` and `expire` verbatim; they are literal API field and value names. */
				? __( 'Clear cached pages across every site in the network. Set `scope` to `all` to clear everything, or list cache flags in `targets` to clear only those. Targets combine as a union: everything matching any of them is cleared. Set `expire` to regenerate pages in the background instead of deleting them outright.', 'millicache' )
				/* translators: An AI reads this to decide when to call this destructive operation. Keep `scope`, `targets`, `all` and `expire` verbatim; they are literal API field and value names. */
				: __( 'Clear cached pages for this site. Set `scope` to `all` to clear everything, or list URLs, post IDs or cache flags in `targets` to clear only those. Targets combine as a union: everything matching any of them is cleared. A post ID also clears the feeds, but never the homepage or archives, so list those separately when they need clearing too. Set `expire` to regenerate pages in the background instead of deleting them outright.', 'millicache' ),
			'callback'      => static function ( $input = null ) use ( $clear_handler ) {
				return self::run_clear( $clear_handler, is_array( $input ) ? $input : array() );
			},
			'input_schema'  => array(
				'type'                 => array( 'object', 'null' ),
				'additionalProperties' => false,
				'properties'           => array(
					'scope'   => array(
						'type'        => 'string',
						'enum'        => array( 'targets', 'all' ),
						'default'     => 'targets',
						'description' => __( 'Use all only when the whole cache should go; it is not the fallback for an empty target list.', 'millicache' ),
					),
					'targets' => array(
						'type'        => 'array',
						'minItems'    => 1,
						'items'       => array( 'type' => 'string' ),
						'description' => $is_network
							? __( 'Cache flags to clear, for example post:12 or home.', 'millicache' )
							: __( 'What to clear: a URL on this site (scheme and host must match the site exactly), a path such as /blog/, a post ID, or a cache flag such as home.', 'millicache' ),
					),
					'expire'  => array(
						'type'        => 'boolean',
						'default'     => false,
						'description' => __( 'Mark entries stale and regenerate them in the background instead of deleting them.', 'millicache' ),
					),
				),
			),
			'output_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'success' => array( 'type' => 'boolean' ),
					'cleared' => array(
						'type'        => 'integer',
						'description' => __( 'Number of cache entries removed or expired.', 'millicache' ),
					),
					'message' => array( 'type' => 'string' ),
					'skipped' => array(
						'type'        => 'array',
						'description' => __( 'Targets that were not applied because they belong to another site. Present only when something was skipped; check it before reading cleared: 0 as an empty cache.', 'millicache' ),
						'items'       => array( 'type' => 'string' ),
					),
				),
				'required'   => array( 'success', 'cleared' ),
			),
			'capability'    => $is_network ? 'manage_network_options' : MilliCache::get_clear_cache_capability(),
			'meta'          => array(
				'annotations' => array(
					'destructive' => true,
					'idempotent'  => true,
				),
			),
		);
	}

	/**
	 * Execute a cache clear on behalf of an ability caller.
	 *
	 * An empty target list is refused: the invalidation manager reads no
	 * targets as "clear the whole site" (see
	 * {@see \MilliCache\Engine\Cache\Invalidation\Manager::targets()}), so a
	 * model that found nothing to clear would purge everything. Wiping the
	 * cache stays an explicit `scope: all`.
	 *
	 * @since 1.8.0
	 *
	 * @param callable             $clear_handler Takes a WP_REST_Request, returns a response or error.
	 * @param array<string, mixed> $input         The ability input.
	 * @return array<string, mixed>|\WP_Error
	 */
	private static function run_clear( callable $clear_handler, array $input ) {
		$all     = 'all' === ( $input['scope'] ?? 'targets' );
		$targets = is_array( $input['targets'] ?? null )
			? array_values( array_filter( $input['targets'], 'is_string' ) )
			: array();

		if ( ! $all && array() === $targets ) {
			return new \WP_Error(
				'millicache_no_targets',
				__( 'No targets given. Pass at least one target, or set scope to all to clear the entire cache.', 'millicache' ),
				array( 'status' => 400 )
			);
		}

		// Built here so listeners on millicache_rest_cache_action_performed
		// still receive the request they expect.
		$request = new \WP_REST_Request( 'POST' );
		$request->set_param( 'action', $all ? 'clear' : 'clear_targets' );
		$request->set_param( 'expire', (bool) ( $input['expire'] ?? false ) );

		if ( ! $all ) {
			$request->set_param( 'targets', $targets );
		}

		$result = $clear_handler( $request );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$data = $result->get_data();

		return is_array( $data ) ? $data : array(
			'success' => false,
			'cleared' => 0,
		);
	}
}
