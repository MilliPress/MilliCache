<?php
/**
 * Builds the `/status` REST payload for the MilliCache settings pages.
 *
 * @link       https://www.millipress.com
 * @since      1.7.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/UI
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Admin\UI;

use MilliCache\Admin\DropIn;
use MilliCache\Admin\Utils;
use MilliCache\Engine;

! defined( 'ABSPATH' ) && exit;

/**
 * Assembles the unified status payload returned by each settings page's
 * `/status` REST endpoint.
 *
 * The payload is the single source of truth for three consumers:
 *
 * - the React Status tab (reads the existing `cache`, `storage`, `dropin`
 *   shapes — backward-compatible);
 * - the React footer Status modal (reads `health` + `markdown`);
 * - the `wp millicache status` CLI command (renders table / markdown / json).
 *
 * Redaction lives at the render layer ({@see self::render_markdown()}), not
 * at the transport layer — the admin-only payload itself carries the
 * unredacted storage block the Status tab needs, while the `markdown` field
 * walks only the safe subset that's appropriate for pasting into a public
 * support ticket.
 *
 * Per-site multisite returns a thin storage shape (`{connected: bool}`)
 * because install-wide storage details live on the Network Admin Status
 * tab; the markdown renderer degrades gracefully when those fields aren't
 * present.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/UI
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class StatusBuilder {

	/**
	 * The MilliCache engine instance.
	 *
	 * @since 1.7.0
	 * @var Engine
	 */
	private Engine $engine;

	/**
	 * The plugin slug.
	 *
	 * @since 1.7.0
	 * @var string
	 */
	private string $plugin_name;

	/**
	 * The plugin version.
	 *
	 * @since 1.7.0
	 * @var string
	 */
	private string $version;

	/**
	 * Construct a StatusBuilder.
	 *
	 * @since 1.7.0
	 *
	 * @param Engine $engine      The cache engine.
	 * @param string $plugin_name Plugin slug.
	 * @param string $version     Plugin version.
	 */
	public function __construct( Engine $engine, string $plugin_name, string $version ) {
		$this->engine      = $engine;
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Build the unified status payload.
	 *
	 * @since 1.7.0
	 *
	 * @param bool                  $network_admin True when called from the Network Admin status endpoint.
	 * @param \WP_REST_Request|null $request       Per-site request, used only to read the optional `network` query param.
	 * @phpstan-param \WP_REST_Request<array<string, mixed>>|null $request
	 * @return array<string, mixed>
	 */
	public function build( bool $network_admin, ?\WP_REST_Request $request = null ): array {
		$network_cache = $network_admin || ( $request && $request->get_param( 'network' ) === 'true' );

		$payload = array(
			'plugin_name' => $this->plugin_name,
			'version'     => $this->version,
			'cache'       => $this->engine->cache()->get_status( $network_cache ),
		);

		if ( ! $network_admin && $this->engine->multisite()->is_enabled() ) {
			$payload['storage'] = array( 'connected' => $this->engine->storage()->ping() );
		} else {
			$payload['storage'] = $this->engine->storage()->get_status();
			$payload['dropin']  = Utils::validate_advanced_cache_file();
		}

		$checks = $this->gather_checks( $payload );

		$payload['debug'] = array(
			'plugin'       => array(
				'name'         => $this->plugin_name,
				'version'      => $this->version,
				'install_mode' => $this->engine->install_mode(),
			),
			'versions'     => $this->gather_versions(),
			'multisite'    => $this->gather_multisite(),
			'flags'        => $this->gather_flags(),
			'plugins'      => $this->gather_plugins(),
			'theme'        => $this->gather_theme(),
			'checks'       => $checks,
			'health'       => $this->compute_health( $checks ),
			'generated_at' => gmdate( 'c' ),
		);

		$payload['debug']['markdown'] = $this->render_markdown( $payload );

		return $payload;
	}

	/**
	 * Gather version strings for WordPress, PHP, and bundled composer packages.
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, string|null>
	 */
	private function gather_versions(): array {
		$wp_version = $GLOBALS['wp_version'] ?? null;

		return array(
			'wp'         => is_string( $wp_version ) && '' !== $wp_version ? $wp_version : 'unknown',
			'php'        => PHP_VERSION,
			'predis'     => $this->composer_version( 'predis/predis' ),
			'millibase'  => $this->composer_version( 'millipress/millibase' ) ?? 'dev',
			'millirules' => $this->composer_version( 'millipress/millirules' ) ?? 'dev',
		);
	}

	/**
	 * Resolve a composer package's installed version via the Composer runtime.
	 *
	 * Mirrors MilliBase's own self-version pattern so behavior is consistent
	 * across the project family; returns null if the runtime class isn't
	 * autoloadable or the package isn't installed.
	 *
	 * @since 1.7.0
	 *
	 * @param string $package Composer package name (e.g. `vendor/package`).
	 * @return string|null
	 */
	private function composer_version( string $package ): ?string {
		if ( ! class_exists( '\Composer\InstalledVersions' ) ) {
			return null;
		}

		$version = \Composer\InstalledVersions::getVersion( $package );
		return is_string( $version ) && '' !== $version ? $version : null;
	}

	/**
	 * Gather multisite topology counts.
	 *
	 * @since 1.7.0
	 *
	 * @return array{enabled: bool, site_count: int, network_count: int}
	 */
	private function gather_multisite(): array {
		$multisite = $this->engine->multisite();
		$enabled   = $multisite->is_enabled();

		return array(
			'enabled'       => $enabled,
			'site_count'    => $enabled ? count( $multisite->get_site_ids() ) : 0,
			'network_count' => $enabled ? count( $multisite->get_network_ids() ) : 0,
		);
	}

	/**
	 * Gather flag-registry stats: total registered + a small name sample.
	 *
	 * @since 1.7.0
	 *
	 * @return array{registered_count: int, sample: array<int, string>}
	 */
	private function gather_flags(): array {
		$all = $this->engine->flags()->get_all();

		return array(
			'registered_count' => count( $all ),
			'sample'           => array_slice( array_values( $all ), 0, 10 ),
		);
	}

	/**
	 * Gather name + version for every active plugin (site- and network-scope).
	 *
	 * Network-scope activation takes precedence over per-site activation when
	 * the same plugin file appears in both lists.
	 *
	 * @since 1.7.0
	 *
	 * @return array<int, array{name: string, version: string, network: bool}>
	 */
	private function gather_plugins(): array {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all = get_plugins();

		$active = array();
		foreach ( (array) get_option( 'active_plugins', array() ) as $file ) {
			if ( is_string( $file ) ) {
				$active[ $file ] = false;
			}
		}

		if ( $this->engine->multisite()->is_enabled() ) {
			foreach ( array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) ) as $file ) {
				if ( is_string( $file ) ) {
					$active[ $file ] = true;
				}
			}
		}

		$rows = array();
		foreach ( $active as $file => $is_network ) {
			$meta = $all[ $file ] ?? null;
			if ( ! is_array( $meta ) ) {
				continue;
			}

			$rows[] = array(
				'name'    => is_string( $meta['Name'] ?? null ) && '' !== $meta['Name'] ? $meta['Name'] : $file,
				'version' => is_string( $meta['Version'] ?? null ) ? $meta['Version'] : '',
				'network' => $is_network,
			);
		}

		return $rows;
	}

	/**
	 * Gather active theme name + version + parent name.
	 *
	 * @since 1.7.0
	 *
	 * @return array{name: string, version: string, parent: string|null}
	 */
	private function gather_theme(): array {
		$theme  = wp_get_theme();
		$parent = $theme->parent();

		return array(
			'name'    => (string) $theme->get( 'Name' ),
			'version' => (string) $theme->get( 'Version' ),
			'parent'  => $parent instanceof \WP_Theme ? (string) $parent->get( 'Name' ) : null,
		);
	}

	/**
	 * Enumerate every health check MilliCache runs, with the current result.
	 *
	 * Each check has a stable `id`, a translated `label`, a `status` (one of
	 * `good` / `recommended` / `critical` — matching WordPress Site Health's
	 * vocabulary), a `description` explaining the result, and an optional
	 * `value` carrying the observed signal.
	 *
	 * Checks that depend on install-wide data (the drop-in, storage server
	 * memory) are omitted on per-site multisite — the React modal and Site
	 * Health cards both degrade gracefully.
	 *
	 * @since 1.7.0
	 *
	 * @param array<string, mixed> $payload The (in-progress) payload.
	 * @return array<int, array{id: string, label: string, status: string, description: string, value?: string}>
	 */
	private function gather_checks( array $payload ): array {
		$dropin  = is_array( $payload['dropin'] ?? null ) ? $payload['dropin'] : null;
		$storage = is_array( $payload['storage'] ?? null ) ? $payload['storage'] : array();
		$info    = is_array( $storage['info'] ?? null ) ? $storage['info'] : array();
		$memory  = is_array( $info['Memory'] ?? null ) ? $info['Memory'] : array();

		$checks            = array();
		$is_per_site_ms    = null === $dropin;
		$dropin_is_present = is_array( $dropin ) && ! empty( $dropin );
		$wp_cache_on       = defined( 'WP_CACHE' ) && WP_CACHE;
		$connected         = ! empty( $storage['connected'] );

		// Drop-in presence — install-wide signal, omitted on per-site multisite.
		if ( ! $is_per_site_ms ) {
			$checks[] = array(
				'id'          => 'dropin_present',
				'label'       => __( 'advanced-cache.php drop-in is installed', 'millicache' ),
				'status'      => $dropin_is_present ? 'good' : 'critical',
				'description' => $dropin_is_present
					? __( 'The drop-in is in place — MilliCache can intercept page requests early.', 'millicache' )
					: __( 'The drop-in is missing. Re-install it from MilliCache settings; without it, no pages are cached.', 'millicache' ),
				'value'       => $dropin_is_present
					? ( is_string( $dropin['type'] ?? null ) ? $dropin['type'] : 'file' )
					: __( 'missing', 'millicache' ),
			);

			if ( $dropin_is_present ) {
				$outdated      = ! empty( $dropin['outdated'] );
				$customized    = ! empty( $dropin['custom'] );
				$dropin_status = ( $outdated || $customized ) ? 'recommended' : 'good';

				if ( $outdated ) {
					$dropin_text  = __( 'The installed drop-in is older than the version bundled with the plugin. Re-install it to pick up improvements and bug fixes.', 'millicache' );
					$dropin_value = __( 'outdated', 'millicache' );
				} elseif ( $customized ) {
					$dropin_text  = __( "The drop-in differs from the bundled version. MilliCache won't overwrite your changes, but plugin updates require manual merging.", 'millicache' );
					$dropin_value = __( 'customized', 'millicache' );
				} else {
					$dropin_text  = __( 'The drop-in matches the bundled version.', 'millicache' );
					$dropin_value = __( 'current', 'millicache' );
				}

				$checks[] = array(
					'id'          => 'dropin_current',
					'label'       => __( 'advanced-cache.php drop-in is current', 'millicache' ),
					'status'      => $dropin_status,
					'description' => $dropin_text,
					'value'       => $dropin_value,
				);
			}
		}

		// WP_CACHE constant — always checked.
		$checks[] = array(
			'id'          => 'wp_cache_constant',
			'label'       => __( 'WP_CACHE constant is enabled', 'millicache' ),
			'status'      => $wp_cache_on ? 'good' : 'critical',
			'description' => $wp_cache_on
				? __( 'WP_CACHE is defined and truthy — WordPress will load the advanced-cache.php drop-in.', 'millicache' )
				: __( "WP_CACHE is not defined or false in wp-config.php. WordPress won't load the drop-in, so MilliCache can't intercept page requests.", 'millicache' ),
			'value'       => $wp_cache_on ? 'true' : 'false',
		);

		// Storage connectivity — always checked.
		$checks[] = array(
			'id'          => 'storage_connected',
			'label'       => __( 'Storage backend is reachable', 'millicache' ),
			'status'      => $connected ? 'good' : 'critical',
			'description' => $connected
				? __( 'MilliCache successfully connected to its configured storage server.', 'millicache' )
				: __( 'MilliCache cannot reach the configured storage server. Cache reads and writes are failing — check the host, port, and credentials.', 'millicache' ),
			'value'       => $connected
				? __( 'connected', 'millicache' )
				: __( 'disconnected', 'millicache' ),
		);

		// Storage server limits — install-wide; skip on per-site multisite or
		// when the storage server isn't reachable to begin with.
		if ( ! $is_per_site_ms && $connected && array() !== $info ) {
			$max_mem    = is_numeric( $memory['maxmemory'] ?? null ) ? (int) $memory['maxmemory'] : 0;
			$max_mem_ok = $max_mem > 0;

			$checks[] = array(
				'id'          => 'storage_max_memory',
				'label'       => __( 'Storage backend has a memory limit', 'millicache' ),
				'status'      => $max_mem_ok ? 'good' : 'recommended',
				'description' => $max_mem_ok
					? __( 'A maxmemory limit is configured — the storage server can evict entries when full.', 'millicache' )
					: __( 'No maxmemory limit is set on the storage server. Without one, the cache can grow until it crowds out other workloads on the host.', 'millicache' ),
				'value'       => is_string( $memory['maxmemory_human'] ?? null ) ? $memory['maxmemory_human'] : 'n/a',
			);

			$policy    = $memory['maxmemory_policy'] ?? null;
			$policy_ok = ! is_string( $policy ) || '' === $policy || 'allkeys-lru' === $policy;

			$checks[] = array(
				'id'          => 'storage_eviction_policy',
				'label'       => __( 'Storage eviction policy is allkeys-lru', 'millicache' ),
				'status'      => $policy_ok ? 'good' : 'recommended',
				'description' => $policy_ok
					? __( 'The storage server is configured with allkeys-lru, the recommended policy for a cache workload.', 'millicache' )
					: __( 'For a cache workload, allkeys-lru is recommended so the server can automatically evict least-recently-used entries when full.', 'millicache' ),
				'value'       => is_string( $policy ) ? $policy : 'n/a',
			);
		}

		return $checks;
	}

	/**
	 * Reduce a checks list to a single health verdict.
	 *
	 * `critical` wins over `recommended` wins over `good`. Mirrors how the
	 * footer pill, Site Health cards, and CLI all derive a one-word answer
	 * from the same structured signal.
	 *
	 * @since 1.7.0
	 *
	 * @param array<int, array{status: string}> $checks The checks list from {@see self::gather_checks()}.
	 * @return string One of `'ok'`, `'warning'`, `'error'`.
	 */
	private function compute_health( array $checks ): string {
		$has_warning = false;

		foreach ( $checks as $check ) {
			if ( 'critical' === $check['status'] ) {
				return 'error';
			}
			if ( 'recommended' === $check['status'] ) {
				$has_warning = true;
			}
		}

		return $has_warning ? 'warning' : 'ok';
	}

	/**
	 * Render the assembled payload as Markdown suitable for pasting into a
	 * GitHub issue.
	 *
	 * Walks only the subset of the payload that's safe for public sharing —
	 * storage host/port, credentials, prefix, database index, and the raw
	 * values of `cache.nocache_paths` / `cache.*_cookies` /
	 * `cache.ignore_*` are intentionally omitted in favor of counts.
	 *
	 * @since 1.7.0
	 *
	 * @param array<string, mixed> $payload The assembled payload.
	 * @return string
	 */
	private function render_markdown( array $payload ): string {
		// Top-level legacy blocks (also consumed by the Status tab).
		$dropin  = is_array( $payload['dropin'] ?? null ) ? $payload['dropin'] : null;
		$storage = is_array( $payload['storage'] ?? null ) ? $payload['storage'] : array();
		$info    = is_array( $storage['info'] ?? null ) ? $storage['info'] : array();
		$memory  = is_array( $info['Memory'] ?? null ) ? $info['Memory'] : array();
		$server  = is_array( $info['Server'] ?? null ) ? $info['Server'] : array();
		$cfg     = is_array( $storage['config'] ?? null ) ? $storage['config'] : array();
		$cache   = is_array( $payload['cache'] ?? null ) ? $payload['cache'] : array();

		// Extended debug blocks.
		$debug     = is_array( $payload['debug'] ?? null ) ? $payload['debug'] : array();
		$plugin    = is_array( $debug['plugin'] ?? null ) ? $debug['plugin'] : array();
		$versions  = is_array( $debug['versions'] ?? null ) ? $debug['versions'] : array();
		$multisite = is_array( $debug['multisite'] ?? null ) ? $debug['multisite'] : array();
		$flags     = is_array( $debug['flags'] ?? null ) ? $debug['flags'] : array();
		$plugins   = is_array( $debug['plugins'] ?? null ) ? $debug['plugins'] : array();
		$theme     = is_array( $debug['theme'] ?? null ) ? $debug['theme'] : array();
		$health    = $this->as_string( $debug['health'] ?? null, 'unknown' );

		$plugin_version = $this->as_string( $plugin['version'] ?? null );

		$lines = array();

		$lines[] = '### MilliCache Debug Info';
		$lines[] = '';

		$lines[] = '**Versions**';
		$lines[] = sprintf( '- MilliCache: %s (%s)', $plugin_version, $this->as_string( $plugin['install_mode'] ?? null, 'unknown' ) );
		$lines[] = sprintf( '- MilliBase: %s', $this->as_string( $versions['millibase'] ?? null, 'dev' ) );
		$lines[] = sprintf( '- MilliRules: %s', $this->as_string( $versions['millirules'] ?? null, 'dev' ) );
		$lines[] = sprintf( '- WordPress: %s', $this->as_string( $versions['wp'] ?? null, 'unknown' ) );
		$lines[] = sprintf( '- PHP: %s', $this->as_string( $versions['php'] ?? null, 'unknown' ) );
		$lines[] = sprintf( '- Predis: %s', $this->as_string( $versions['predis'] ?? null, 'not installed' ) );
		$lines[] = '';

		$lines[] = '**Multisite**';
		$lines[] = sprintf( '- Enabled: %s', ! empty( $multisite['enabled'] ) ? 'yes' : 'no' );
		if ( ! empty( $multisite['enabled'] ) ) {
			$lines[] = sprintf( '- Sites: %d', $this->as_int( $multisite['site_count'] ?? null ) );
			$lines[] = sprintf( '- Networks: %d', $this->as_int( $multisite['network_count'] ?? null ) );
		}
		$lines[] = '';

		$lines[] = '**advanced-cache.php**';
		if ( null === $dropin ) {
			$lines[] = '- _Install-wide diagnostic — see the Network Admin Status page._';
		} else {
			$present = ! empty( $dropin );
			$lines[] = sprintf( '- Present: %s', $present ? 'yes' : 'no' );
			if ( $present ) {
				$lines[] = sprintf( '- Type: %s', $this->as_string( $dropin['type'] ?? null, 'n/a' ) );
				$lines[] = sprintf( '- Outdated: %s', ! empty( $dropin['outdated'] ) ? 'yes' : 'no' );
				$lines[] = sprintf( '- Customized: %s', ! empty( $dropin['custom'] ) ? 'yes' : 'no' );
			}
			$lines[] = sprintf( '- WP_CACHE constant: %s', defined( 'WP_CACHE' ) && WP_CACHE ? 'true' : 'false' );
		}
		$lines[] = '';

		$lines[] = '**Storage**';
		$lines[] = sprintf( '- Connected: %s', ! empty( $storage['connected'] ) ? 'yes' : 'no' );
		if ( array() !== $info ) {
			$lines[] = sprintf( '- Server: %s', $this->as_string( $server['version'] ?? null, 'n/a' ) );
			$lines[] = sprintf( '- Used memory: %s', $this->as_string( $memory['used_memory_human'] ?? null, 'n/a' ) );
			$lines[] = sprintf( '- Max memory: %s', $this->as_string( $memory['maxmemory_human'] ?? null, 'n/a' ) );
			$lines[] = sprintf( '- Max-memory policy: %s', $this->as_string( $memory['maxmemory_policy'] ?? null, 'n/a' ) );
			$lines[] = sprintf(
				'- Databases available: %s',
				is_numeric( $cfg['databases'] ?? null )
					? (string) (int) $cfg['databases']
					: 'n/a'
			);
		} else {
			$lines[] = '- _Install-wide diagnostic — see the Network Admin Status page._';
		}
		if ( isset( $storage['error'] ) && is_string( $storage['error'] ) ) {
			$lines[] = sprintf( '- Error: %s', $storage['error'] );
		}
		$lines[] = '';

		$nocache_paths       = is_array( $cache['nocache_paths'] ?? null ) ? $cache['nocache_paths'] : array();
		$nocache_cookies     = is_array( $cache['nocache_cookies'] ?? null ) ? $cache['nocache_cookies'] : array();
		$ignore_cookies      = is_array( $cache['ignore_cookies'] ?? null ) ? $cache['ignore_cookies'] : array();
		$ignore_request_keys = is_array( $cache['ignore_request_keys'] ?? null ) ? $cache['ignore_request_keys'] : array();

		$lines[] = '**Cache config**';
		$lines[] = sprintf( '- TTL: %d', $this->as_int( $cache['ttl'] ?? null ) );
		$lines[] = sprintf( '- Grace: %d', $this->as_int( $cache['grace'] ?? null ) );
		$lines[] = sprintf( '- Gzip: %s', ! empty( $cache['gzip'] ) ? 'true' : 'false' );
		$lines[] = sprintf( '- Debug: %s', ! empty( $cache['debug'] ) ? 'true' : 'false' );
		$lines[] = sprintf( '- nocache_paths entries: %d', count( $nocache_paths ) );
		$lines[] = sprintf( '- nocache_cookies entries: %d', count( $nocache_cookies ) );
		$lines[] = sprintf( '- ignore_cookies entries: %d', count( $ignore_cookies ) );
		$lines[] = sprintf( '- ignore_request_keys entries: %d', count( $ignore_request_keys ) );
		$lines[] = '';

		$lines[] = '**Cache stats**';
		$lines[] = sprintf( '- Index: %d', $this->as_int( $cache['index'] ?? null ) );
		$lines[] = sprintf( '- Size: %s', $this->as_string( $cache['size_human'] ?? null, 'n/a' ) );
		$lines[] = sprintf( '- Unique bodies: %d', $this->as_int( $cache['unique'] ?? null ) );
		$lines[] = sprintf( '- Largest entry: %s', $this->as_string( $cache['largest_human'] ?? null, 'n/a' ) );
		$lines[] = '';

		$sample = is_array( $flags['sample'] ?? null ) ? $flags['sample'] : array();
		$lines[] = '**Flags**';
		$lines[] = sprintf( '- Registered: %d', $this->as_int( $flags['registered_count'] ?? null ) );
		$lines[] = sprintf(
			'- Sample: %s',
			empty( $sample ) ? 'none' : implode( ', ', array_filter( $sample, 'is_string' ) )
		);
		$lines[] = '';

		$lines[] = '**Active plugins**';
		if ( empty( $plugins ) ) {
			$lines[] = '- none';
		} else {
			foreach ( $plugins as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$lines[] = sprintf(
					'- %s %s%s',
					$this->as_string( $row['name'] ?? null ),
					$this->as_string( $row['version'] ?? null ),
					! empty( $row['network'] ) ? ' (network)' : ''
				);
			}
		}
		$lines[] = '';

		$parent  = $this->as_string( $theme['parent'] ?? null );
		$lines[] = '**Theme**';
		$lines[] = sprintf(
			'- %s %s (parent: %s)',
			$this->as_string( $theme['name'] ?? null, 'unknown' ),
			$this->as_string( $theme['version'] ?? null ),
			'' !== $parent ? $parent : '—'
		);
		$lines[] = '';

		$lines[] = sprintf( '**Health**: %s', $health );
		$lines[] = '';
		$lines[] = sprintf( '_Generated by MilliCache %s on %s_', $plugin_version, gmdate( 'Y-m-d' ) );

		return implode( "\n", $lines );
	}

	/**
	 * Narrow a `mixed` value to a string, falling back to `$default` when not a string.
	 *
	 * @since 1.7.0
	 *
	 * @param mixed  $value   The candidate value.
	 * @param string $default Fallback when `$value` isn't a string.
	 * @return string
	 */
	private function as_string( mixed $value, string $default = '' ): string {
		return is_string( $value ) ? $value : $default;
	}

	/**
	 * Narrow a `mixed` value to an int, falling back to `$default` when not numeric.
	 *
	 * @since 1.7.0
	 *
	 * @param mixed $value   The candidate value.
	 * @param int   $default Fallback when `$value` isn't numeric.
	 * @return int
	 */
	private function as_int( mixed $value, int $default = 0 ): int {
		return is_numeric( $value ) ? (int) $value : $default;
	}
}
