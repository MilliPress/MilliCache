<?php
/**
 * `wp millicache status` — show MilliCache status with optional formats.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/CLI
 */

namespace MilliCache\Admin\CLI;

use MilliCache\Admin\UI\StatusBuilder;
use MilliCache\Engine;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Single user-facing command that surfaces MilliCache status in three views,
 * all backed by {@see StatusBuilder::build()} so labels and signals stay
 * consistent with the admin-footer Status modal:
 *
 * - `table` (default): a concise key/value summary suitable for quick checks.
 * - `markdown`: the full sanitized payload, ready to paste into a GitHub issue.
 * - `json`: the full payload as JSON for scripted consumers.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/CLI
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Status {

	use OutputTrait;

	/**
	 * Show the MilliCache status snapshot.
	 *
	 * ## DESCRIPTION
	 *
	 * Reports the same sanitized payload as the admin-footer Status indicator —
	 * versions, drop-in state, storage server stats, active plugins/theme, and
	 * cache statistics. Secrets, hosts, and customer paths/cookies are never
	 * serialized.
	 *
	 * Use `--format=markdown` to copy the output straight into a GitHub issue
	 * or chat thread when reporting a problem.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Output format. Default: table.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - markdown
	 *   - json
	 * ---
	 *
	 * [--network]
	 * : Report network-wide cache stats instead of the per-site scope.
	 *
	 * ## EXAMPLES
	 *
	 *     wp millicache status
	 *     wp millicache status --format=markdown
	 *     wp millicache status --format=json
	 *     wp millicache status --network --format=markdown
	 *
	 * @when after_wp_load
	 *
	 * @since 1.0.0
	 *
	 * @param array<string> $args       The list of arguments.
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		unset( $args );

		$format        = $assoc_args['format'] ?? 'table';
		$network_admin = isset( $assoc_args['network'] );

		$engine         = Engine::instance();
		$version        = defined( 'MILLICACHE_VERSION' ) ? MILLICACHE_VERSION : '1.0.0';
		$status_builder = new StatusBuilder( $engine, 'millicache', $version );

		$payload = $status_builder->build( $network_admin );

		if ( 'markdown' === $format ) {
			$debug    = is_array( $payload['debug'] ?? null ) ? $payload['debug'] : array();
			$markdown = is_string( $debug['markdown'] ?? null ) ? $debug['markdown'] : '';
			\WP_CLI::line( $markdown );
			return;
		}

		if ( 'json' === $format ) {
			\WP_CLI::line( (string) wp_json_encode( $payload, JSON_PRETTY_PRINT ) );
			return;
		}

		$rows = $this->extract_table_view( $payload );
		$this->output_items(
			$this->build_rows_from_array( $rows, 'property', 'status' ),
			$rows,
			'table',
			array( 'property', 'status' )
		);
	}

	/**
	 * Extract the headline scalars from the unified payload into a flat
	 * key/value map for the table view.
	 *
	 * Mirrors the field set of the legacy `wp millicache status` table so
	 * scripts that parsed that output continue to find the same keys.
	 *
	 * @since 1.7.0
	 *
	 * @param array<string, mixed> $payload The unified status payload.
	 * @return array<string, string>
	 */
	private function extract_table_view( array $payload ): array {
		$dropin   = is_array( $payload['dropin'] ?? null ) ? $payload['dropin'] : null;
		$storage  = is_array( $payload['storage'] ?? null ) ? $payload['storage'] : array();
		$info     = is_array( $storage['info'] ?? null ) ? $storage['info'] : array();
		$memory   = is_array( $info['Memory'] ?? null ) ? $info['Memory'] : array();
		$server   = is_array( $info['Server'] ?? null ) ? $info['Server'] : array();
		$cache    = is_array( $payload['cache'] ?? null ) ? $payload['cache'] : array();
		$debug    = is_array( $payload['debug'] ?? null ) ? $payload['debug'] : array();
		$plugin   = is_array( $debug['plugin'] ?? null ) ? $debug['plugin'] : array();
		$versions = is_array( $debug['versions'] ?? null ) ? $debug['versions'] : array();
		$health   = is_string( $debug['health'] ?? null ) ? $debug['health'] : 'unknown';

		$rows = array(
			'plugin_version' => is_string( $plugin['version'] ?? null ) ? $plugin['version'] : '',
			'wp_version'     => is_string( $versions['wp'] ?? null ) ? $versions['wp'] : '',
			'php_version'    => is_string( $versions['php'] ?? null ) ? $versions['php'] : '',
			'wp_cache'       => defined( 'WP_CACHE' ) && WP_CACHE ? 'enabled' : 'disabled',
			'advanced_cache' => $this->describe_dropin( $dropin ),
			'storage'        => ! empty( $storage['connected'] ) ? 'connected' : 'disconnected',
			'storage_server' => is_string( $server['version'] ?? null ) ? $server['version'] : 'n/a',
			'memory_used'    => is_string( $memory['used_memory_human'] ?? null ) ? $memory['used_memory_human'] : 'n/a',
			'memory_max'     => is_string( $memory['maxmemory_human'] ?? null ) ? $memory['maxmemory_human'] : 'n/a',
			'cache_entries'  => isset( $cache['index'] ) && is_numeric( $cache['index'] ) ? (string) (int) $cache['index'] : '0',
			'cache_size'     => is_string( $cache['size_human'] ?? null ) ? $cache['size_human'] : 'n/a',
			'health'         => $health,
		);

		if ( isset( $storage['error'] ) && is_string( $storage['error'] ) ) {
			$rows['storage_error'] = $storage['error'];
		}

		return $rows;
	}

	/**
	 * Compose the legacy `advanced_cache` status string (e.g. `symlink`,
	 * `file (outdated)`, `missing`).
	 *
	 * The unified payload uses the historical dropin shape: `null` when
	 * install-wide context is unavailable (per-site multisite), `[]` for
	 * missing, or `{type, custom, outdated}` when present.
	 *
	 * @since 1.7.0
	 *
	 * @param array<mixed>|null $dropin The dropin block from the payload.
	 * @return string
	 */
	private function describe_dropin( ?array $dropin ): string {
		if ( null === $dropin ) {
			return 'n/a';
		}

		if ( empty( $dropin ) ) {
			return 'missing';
		}

		$value = is_string( $dropin['type'] ?? null ) ? $dropin['type'] : 'file';

		if ( ! empty( $dropin['outdated'] ) ) {
			$value .= ' (outdated)';
		}

		if ( ! empty( $dropin['custom'] ) ) {
			$value .= ' (custom)';
		}

		return $value;
	}
}
