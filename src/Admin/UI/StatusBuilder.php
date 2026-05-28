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
			'health'       => $this->compute_health( $payload ),
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
	 * Compute the health-dot indicator from the assembled payload.
	 *
	 * The dropin block uses the existing convention: empty array = missing,
	 * `{type, custom, outdated}` = present. WP_CACHE is read from the
	 * constant directly since it isn't a field in any payload block.
	 *
	 * @since 1.7.0
	 *
	 * @param array<string, mixed> $payload The (in-progress) payload.
	 * @return string One of `'ok'`, `'warning'`, `'error'`.
	 */
	private function compute_health( array $payload ): string {
		$dropin  = is_array( $payload['dropin'] ?? null ) ? $payload['dropin'] : null;
		$storage = is_array( $payload['storage'] ?? null ) ? $payload['storage'] : array();

		$dropin_present = is_array( $dropin ) && ! empty( $dropin );
		$wp_cache_on    = defined( 'WP_CACHE' ) && WP_CACHE;
		$connected      = ! empty( $storage['connected'] );

		// Per-site multisite carries a thin storage block; if drop-in info
		// isn't included (install-wide), trust the connection signal alone.
		$skip_dropin_check = null === $dropin;

		if ( ( ! $skip_dropin_check && ! $dropin_present ) || ! $wp_cache_on || ! $connected ) {
			return 'error';
		}

		$info     = is_array( $storage['info'] ?? null ) ? $storage['info'] : array();
		$memory   = is_array( $info['Memory'] ?? null ) ? $info['Memory'] : array();
		$max_mem  = is_numeric( $memory['maxmemory'] ?? null ) ? (int) $memory['maxmemory'] : 0;
		$policy   = $memory['maxmemory_policy'] ?? null;

		$policy_ok       = ! is_string( $policy ) || '' === $policy || 'allkeys-lru' === $policy;
		$server_warning  = array() !== $info && ( 0 === $max_mem || ! $policy_ok );
		$dropin_warning  = is_array( $dropin ) && ( ! empty( $dropin['outdated'] ) || ! empty( $dropin['custom'] ) );

		return ( $server_warning || $dropin_warning ) ? 'warning' : 'ok';
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
