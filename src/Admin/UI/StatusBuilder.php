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

use MilliCache\Admin\Utils;
use MilliCache\Engine;

! defined( 'ABSPATH' ) && exit;

/**
 * Assembles the unified `/status` REST payload — the single source of truth for
 * the React Status tab, the footer Status modal (`health` + `markdown`), and
 * the `wp millicache status` CLI. The payload is unredacted; redaction lives in
 * {@see self::render_markdown()}. Per-site multisite returns a thin
 * `{connected}` storage shape.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/UI
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class StatusBuilder {

	/**
	 * Documentation base URL for each check's `url` (mirrors
	 * {@see \MilliCache\Base\Manager::DOCS_BASE}).
	 *
	 * @since 1.7.0
	 * @var string
	 */
	private const DOCS_BASE = 'https://millipress.com/docs/millicache';

	/**
	 * MilliCache Pro upgrade URL.
	 *
	 * @since 1.7.0
	 * @var string
	 */
	private const UPGRADE_URL = 'https://millipress.com/millicache-pro';


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

		// MilliCache's own per-blog page-cache hit ratio (subsites get their own).
		$payload['metrics'] = $this->engine->metrics()->read( $network_cache );

		$checks = $this->gather_checks( $payload );

		/**
		 * Filter the status checks. Each entry (see {@see StatusBuilder::gather_checks()}):
		 *
		 *   - `id`          (string)   Stable identifier.
		 *   - `label`       (string)   Short, translated headline.
		 *   - `status`      (string)   `good`, `recommended`, or `critical`.
		 *   - `description` (string)   Translated explanation of the result.
		 *   - `value`       (string)   Optional observed value.
		 *   - `url`         (string)   Optional "Learn more" URL.
		 *
		 * @since 1.7.0
		 *
		 * @param array<int, array<string, mixed>> $checks     Default check list.
		 * @param array<string, mixed>             $payload    The in-progress status payload.
		 * @param bool                             $is_network Whether the firing `/status` endpoint is network-scoped.
		 */
		$checks = apply_filters( 'millicache_status_checks', $checks, $payload, $network_admin );

		$debug = array(
			'plugin'       => array(
				'name'         => $this->plugin_name,
				'version'      => $this->version,
				'install_mode' => $this->engine->install_mode(),
			),
			'versions'     => $this->gather_versions(),
			'multisite'    => $this->gather_multisite(),
			'flags'        => $this->gather_flags(),
			'rules'        => $this->gather_rules(),
			'plugins'      => $this->gather_plugins(),
			'theme'        => $this->gather_theme(),
			'checks'       => $checks,
			'health'       => $this->compute_health( $checks ),
			'generated_at' => gmdate( 'c' ),
		);

		/**
		 * Filter the debug block before markdown rendering — extensions add
		 * diagnostic sub-blocks. Leave the reserved keys (`checks`, `health`,
		 * `generated_at`, `markdown`) intact.
		 *
		 * @since 1.7.0
		 *
		 * @param array<string, mixed> $debug      Default debug block.
		 * @param array<string, mixed> $payload    The full status payload.
		 * @param bool                 $is_network Whether the firing `/status` endpoint is network-scoped.
		 */
		$payload['debug'] = apply_filters( 'millicache_status_debug', $debug, $payload, $network_admin );

		$payload['panels'] = $this->gather_panels( $payload );

		$payload['debug']['markdown'] = $this->render_markdown( $payload, $network_admin );

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
	 * Resolve a composer package's installed version (null if not installed).
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
	 * Gather MilliRules registry stats (total + per-package + custom count);
	 * zeros when MilliRules isn't loaded.
	 *
	 * @since 1.7.0
	 *
	 * @return array{registered_count: int, custom_count: int, packages: array<string, int>}
	 */
	private function gather_rules(): array {
		if ( ! class_exists( '\\MilliRules\\Packages\\PackageManager' ) ) {
			return array(
				'registered_count' => 0,
				'custom_count'     => 0,
				'packages'         => array(),
			);
		}

		$all      = \MilliRules\Packages\PackageManager::get_all_rules();
		$packages = array();

		foreach ( $all as $rule ) {
			$pkg = is_string( $rule['_package'] ?? null ) ? $rule['_package'] : 'unknown';

			$packages[ $pkg ] = ( $packages[ $pkg ] ?? 0 ) + 1;
		}

		ksort( $packages );

		return array(
			'registered_count' => count( $all ),
			'custom_count'     => $this->count_custom_rules(),
			'packages'         => $packages,
		);
	}

	/**
	 * Sum the enabled, registerable rule entries across site (and network)
	 * settings — mirrors {@see \MilliCache\Rules\Custom::register()}.
	 *
	 * @since 1.7.0
	 *
	 * @return int
	 */
	private function count_custom_rules(): int {
		$count = $this->count_rule_items( \MilliCache\Base\Site::settings()->get( 'rules.items', array() ) );

		if ( $this->engine->multisite()->is_enabled() ) {
			$count += $this->count_rule_items( \MilliCache\Base\Network::settings()->get( 'rules.items', array() ) );
		}

		return $count;
	}

	/**
	 * Count rule entries that would actually register: each entry must be an
	 * array with a non-empty `id`, and `enabled` must not be explicitly false.
	 *
	 * @since 1.7.0
	 *
	 * @param mixed $items The raw `rules.items` value from a settings store.
	 * @return int
	 */
	private function count_rule_items( $items ): int {
		if ( ! is_array( $items ) ) {
			return 0;
		}

		$count = 0;
		foreach ( $items as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			if ( array_key_exists( 'enabled', $item ) && ! $item['enabled'] ) {
				continue;
			}
			if ( '' === (string) ( $item['id'] ?? '' ) ) {
				continue;
			}
			++$count;
		}

		return $count;
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
	 * Enumerate every health check with its current result (`status` uses Site
	 * Health's `good`/`recommended`/`critical`). Install-wide checks (drop-in,
	 * storage memory) are omitted on per-site multisite.
	 *
	 * @since 1.7.0
	 *
	 * @param array<string, mixed> $payload The (in-progress) payload.
	 * @return array<int, array{id: string, label: string, status: string, description: string, value?: string, url?: string}>
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

		$dropin_docs   = self::DOCS_BASE . '/01-getting-started/20-installation#advanced-cache-php-issues';
		$wp_cache_docs = self::DOCS_BASE . '/01-getting-started/20-installation#wp-cache-constant';
		$conn_docs     = self::DOCS_BASE . '/08-storage-backends/01-overview#basic-connection';
		$mem_docs      = self::DOCS_BASE . '/08-storage-backends/01-overview#memory-sizing';
		$policy_docs   = self::DOCS_BASE . '/08-storage-backends/01-overview#recommended-server-configuration';

		// Drop-in presence — install-wide signal, omitted on per-site multisite.
		if ( ! $is_per_site_ms ) {
			$checks[] = array(
				'id'          => 'dropin_present',
				'label'       => __( 'advanced-cache.php drop-in is installed', 'millicache' ),
				'status'      => $dropin_is_present ? 'good' : 'critical',
				'description' => $dropin_is_present
					? __( 'The drop-in is in place. MilliCache can intercept page requests early.', 'millicache' )
					: __( 'The drop-in is missing. Re-install it from MilliCache settings; without it, no pages are cached.', 'millicache' ),
				'value'       => $dropin_is_present
					? ( is_string( $dropin['type'] ?? null ) ? $dropin['type'] : 'file' )
					: __( 'missing', 'millicache' ),
				'url'         => $dropin_docs,
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
					'url'         => $dropin_docs,
				);
			}
		}

		// WP_CACHE constant — always checked.
		$checks[] = array(
			'id'          => 'wp_cache_constant',
			'label'       => __( 'WP_CACHE constant is enabled', 'millicache' ),
			'status'      => $wp_cache_on ? 'good' : 'critical',
			'description' => $wp_cache_on
				? __( 'WP_CACHE is defined and truthy. WordPress will load the advanced-cache.php drop-in.', 'millicache' )
				: __( "WP_CACHE is not defined or false in wp-config.php. WordPress won't load the drop-in, so MilliCache can't intercept page requests.", 'millicache' ),
			'value'       => $wp_cache_on ? 'true' : 'false',
			'url'         => $wp_cache_docs,
		);

		// Storage connectivity — always checked.
		$checks[] = array(
			'id'          => 'storage_connected',
			'label'       => __( 'Storage backend is reachable', 'millicache' ),
			'status'      => $connected ? 'good' : 'critical',
			'description' => $connected
				? __( 'MilliCache successfully connected to its configured storage server.', 'millicache' )
				: __( 'MilliCache cannot reach the configured storage server. Cache reads and writes are failing. Check the host, port, and credentials.', 'millicache' ),
			'value'       => $connected
				? __( 'connected', 'millicache' )
				: __( 'disconnected', 'millicache' ),
			'url'         => $conn_docs,
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
					? __( 'A maxmemory limit is configured. The storage server can evict entries when full.', 'millicache' )
					: __( 'No maxmemory limit is set on the storage server. Without one, the cache can grow until it crowds out other workloads on the host.', 'millicache' ),
				'value'       => is_string( $memory['maxmemory_human'] ?? null ) ? $memory['maxmemory_human'] : 'n/a',
				'url'         => $mem_docs,
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
				'url'         => $policy_docs,
			);
		}

		return $checks;
	}

	/**
	 * Reduce a checks list to a single verdict (`critical` > `recommended` >
	 * `good`; a missing `status` counts as `good`).
	 *
	 * @since 1.7.0
	 *
	 * @param array<int, array<string, mixed>> $checks The (possibly filtered) checks list.
	 * @return string One of `'ok'`, `'warning'`, `'error'`.
	 */
	private function compute_health( array $checks ): string {
		$has_warning = false;

		foreach ( $checks as $check ) {
			$status = $check['status'] ?? 'good';
			if ( 'critical' === $status ) {
				return 'error';
			}
			if ( 'recommended' === $status ) {
				$has_warning = true;
			}
		}

		return $has_warning ? 'warning' : 'ok';
	}

	/**
	 * Assemble the Status-tab panels (flat descriptors; the React side maps
	 * `type` → component).
	 *
	 * @since 1.7.0
	 *
	 * @param array<string, mixed> $payload The (in-progress) payload.
	 * @return array<int, array<string, mixed>>
	 */
	private function gather_panels( array $payload ): array {
		$cache = is_array( $payload['cache'] ?? null ) ? $payload['cache'] : array();

		$index       = $this->as_int( $cache['index'] ?? null );
		$size_bytes  = $this->as_int( $cache['size'] ?? null );
		$raw_bytes   = $this->as_int( $cache['raw'] ?? null );
		$total_saved = max( 0, $raw_bytes - $size_bytes );
		$saved_pct   = ( $raw_bytes > 0 && $total_saved > 0 ) ? (int) round( ( $total_saved / $raw_bytes ) * 100 ) : 0;

		$metrics   = is_array( $payload['metrics'] ?? null ) ? $payload['metrics'] : array();
		$hits      = $this->as_int( $metrics['hits'] ?? null );
		$misses    = $this->as_int( $metrics['misses'] ?? null );
		$total     = $hits + $misses;
		$ratio_pct = ( $total > 0 && is_numeric( $metrics['ratio'] ?? null ) ) ? (float) $metrics['ratio'] : 0.0;
		$series    = is_array( $metrics['series'] ?? null ) ? $metrics['series'] : array();

		// Secondary context lines for the KPI tiles.
		$avg_bytes         = $index > 0 ? $size_bytes / $index : 0;
		$unique            = $this->as_int( $cache['unique'] ?? null );
		$total_saved_human = (string) size_format( $total_saved, $total_saved > 1048576 ? 2 : 0 );

		$panels = array();

		$panels[] = array(
			'id'     => 'kpi_entries',
			'type'   => 'kpi',
			'label'  => __( 'Entries', 'millicache' ),
			'value'  => number_format_i18n( $index ),
			'detail' => $unique > 0
				? sprintf(
					/* translators: %s: number of distinct cached bodies, formatted. */
					_n( '%s unique page', '%s unique pages', $unique, 'millicache' ),
					number_format_i18n( $unique )
				)
				: '',
			'info'   => array(
				'title'       => __( 'Cached entries', 'millicache' ),
				'description' => __( 'Number of distinct cached responses currently held in storage. Each URL variant (including unique query strings) counts as a separate entry.', 'millicache' ),
			),
			'weight' => 10,
		);
		$panels[] = array(
			'id'     => 'kpi_size',
			'type'   => 'kpi',
			'label'  => __( 'Cache size', 'millicache' ),
			'value'  => $this->as_string( $cache['size_human'] ?? null, '0 B' ),
			'detail' => $index > 0
				? sprintf(
					/* translators: %s: average entry size in KB, formatted. */
					__( 'Ø %s KB per entry', 'millicache' ),
					number_format_i18n( $avg_bytes / 1024, 1 )
				)
				: '',
			'info'   => array(
				'title'       => __( 'Cache size', 'millicache' ),
				'description' => __( 'Total bytes stored in the storage backend right now, after compression and deduplication.', 'millicache' ),
			),
			'weight' => 20,
		);
		$panels[] = array(
			'id'     => 'kpi_saved',
			'type'   => 'kpi',
			'label'  => __( 'Saved storage', 'millicache' ),
			'value'  => $saved_pct . '%',
			'detail' => $total_saved > 0
				? sprintf(
					/* translators: %s: human-readable bytes saved, e.g. "1.31 MB". */
					__( '%s saved', 'millicache' ),
					$total_saved_human
				)
				: '',
			'info'   => array(
				'title'       => __( 'Saved storage', 'millicache' ),
				'description' => __( 'How much smaller the stored cache is than the raw response output, thanks to compression and body deduplication. MilliCache stores identical page bodies only once.', 'millicache' ),
			),
			'weight' => 30,
		);
		$panels[] = array(
			'id'     => 'kpi_hit_ratio',
			'type'   => 'kpi',
			'label'  => __( 'Hit ratio', 'millicache' ),
			'value'  => $total > 0
				? number_format_i18n( $ratio_pct, 1 ) . '%'
				: '—',
			'series' => $series,
			'info'   => array(
				'title'       => __( 'Cache hit ratio', 'millicache' ),
				'description' => __( 'Share of cacheable front-end requests this site served straight from the page cache over the last 7 days. Misses are requests that had to be generated; uncacheable requests (excluded URLs, custom rules) are not counted.', 'millicache' ),
			),
			'weight' => 40,
		);

		// Rotating Pro teaser — one random line per Status load. `%PRO%` is a
		// non-printf token (not `%s`) the React side splices with the linked
		// label, so no line needs its own translators comment. Hidden when Pro
		// is active so owners aren't upsold what they already have.
		if ( ! defined( 'MILLICACHE_PRO_VERSION' ) ) {
			$teasers = array(
				// Value / performance.
				__( 'Fast already. Faster with %PRO%.', 'millicache' ),
				__( 'More hits. Fewer misses. Meet %PRO%.', 'millicache' ),
				__( 'Turn good cache stats into great ones with %PRO%.', 'millicache' ),
				__( 'Unlock the full potential of your cache with %PRO%.', 'millicache' ),

				// Entries Browser.
				__( 'See every cached URL at a glance with %PRO%.', 'millicache' ),
				__( 'Browse, inspect and purge cached URLs with %PRO%.', 'millicache' ),
				__( 'Find and clear the exact cache entry with %PRO%.', 'millicache' ),
				__( 'No guessing: inspect your cache with %PRO%.', 'millicache' ),

				// Rules.
				__( 'Custom TTL for specific pages? Easy with %PRO%.', 'millicache' ),
				__( 'HTML for humans, Markdown for AIs? Easy with %PRO%.', 'millicache' ),
				__( 'Cache by URL, cookie, query string or user with %PRO%.', 'millicache' ),
				__( 'Short TTL for news, long TTL for docs? Easy with %PRO%.', 'millicache' ),
				__( 'Cache landing pages longer than blog posts? Easy with %PRO%.', 'millicache' ),
				__( 'Always cache this, never cache that? Easy with %PRO%.', 'millicache' ),
				__( 'Different cache behavior for bots and visitors? Easy with %PRO%.', 'millicache' ),

				// Prefetching.
				__( 'Warm pages before visitors arrive with %PRO%.', 'millicache' ),
				__( 'Serve warmed pages from the first click with %PRO%.', 'millicache' ),
				__( 'Keep key pages ready with %PRO%.', 'millicache' ),
				__( 'Prefetch important URLs automatically with %PRO%.', 'millicache' ),

				// CDN / edge.
				__( 'Take your cache global with %PRO%.', 'millicache' ),
				__( 'Serve pages closer to every visitor with %PRO%.', 'millicache' ),
				__( 'Push cached pages to the edge with %PRO%.', 'millicache' ),
				__( 'Edge delivery for Cloudflare, Bunny.net and more with %PRO%.', 'millicache' ),

				// Smart invalidation.
				__( 'Edit a synced pattern. %PRO% clears the right cache entries.', 'millicache' ),
				__( 'Even smarter cache invalidation, handled by %PRO%.', 'millicache' ),

				// Dynamic sites / WooCommerce.
				__( 'Better caching for dynamic WordPress sites with %PRO%.', 'millicache' ),
				__( 'Cache smarter on busy WooCommerce sites with %PRO%.', 'millicache' ),

				// Debugging / confidence.
				__( "Know exactly what's in your cache with %PRO%.", 'millicache' ),
				__( 'Debug cache behavior faster with %PRO%.', 'millicache' ),
				__( 'Less cache mystery. More control with %PRO%.', 'millicache' ),

				// Support / upgrade.
				__( 'MilliCache already flies. %PRO% adds the afterburner.', 'millicache' ),
				__( 'Love MilliCache? Power its future with %PRO%.', 'millicache' ),
			);

			$panels[] = array(
				'id'        => 'pro_teaser',
				'type'      => 'pro',
				'text'      => $teasers[ array_rand( $teasers ) ],
				'cta_label' => __( 'MilliCache Pro', 'millicache' ),
				'cta_url'   => self::UPGRADE_URL,
				'weight'    => 300,
			);
		}

		// Breakdown cards.
		if ( $total > 0 ) {
			$panels[] = array(
				'id'          => 'breakdown_hit_ratio',
				'type'        => 'breakdown',
				'label'       => __( 'Requests served · last 7 days', 'millicache' ),
				'series'      => $series,
				'buckets'     => array(
					array(
						'label'   => __( 'Total requests', 'millicache' ),
						'value'   => $total,
						'display' => number_format_i18n( $total ),
						'tone'    => 'total',
					),
					array(
						'label'   => __( 'Cached requests', 'millicache' ),
						'value'   => $hits,
						'display' => number_format_i18n( $hits ),
						'tone'    => 'cached',
					),
					array(
						'label'   => __( 'Uncached requests', 'millicache' ),
						'value'   => $misses,
						'display' => number_format_i18n( $misses ),
						'tone'    => 'uncached',
					),
				),
				// Leads the breakdowns; React widens any `series` breakdown to full width.
				'weight'      => 190,
			);
		}

		return $panels;
	}

	/**
	 * Render the payload as support-safe Markdown. Hosts, credentials, and raw
	 * path/cookie values are omitted in favor of counts.
	 *
	 * @since 1.7.0
	 *
	 * @param array<string, mixed> $payload    The assembled payload.
	 * @param bool                 $is_network Whether the firing `/status` endpoint is network-scoped.
	 * @return string
	 */
	private function render_markdown( array $payload, bool $is_network = false ): string {
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
		$rules     = is_array( $debug['rules'] ?? null ) ? $debug['rules'] : array();
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
			$lines[] = '- _Install-wide diagnostic. See the Network Admin Status page._';
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
			$lines[] = '- _Install-wide diagnostic. See the Network Admin Status page._';
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

		$rule_packages = is_array( $rules['packages'] ?? null ) ? $rules['packages'] : array();
		$package_parts = array();
		foreach ( $rule_packages as $pkg_name => $pkg_count ) {
			if ( is_string( $pkg_name ) && is_numeric( $pkg_count ) ) {
				$package_parts[] = sprintf( '%s: %d', $pkg_name, (int) $pkg_count );
			}
		}
		$lines[] = '**Rules**';
		$lines[] = sprintf( '- Registered: %d', $this->as_int( $rules['registered_count'] ?? null ) );
		$lines[] = sprintf( '- Custom (from settings): %d', $this->as_int( $rules['custom_count'] ?? null ) );
		$lines[] = sprintf(
			'- By package: %s',
			empty( $package_parts ) ? 'none' : implode( ', ', $package_parts )
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

		/**
		 * Filter extra Markdown sections appended to the support snapshot. Each
		 * is an independent block with its own bold heading; keep it
		 * support-safe (no hosts, credentials, or customer paths).
		 *
		 * @since 1.7.0
		 *
		 * @param array<int, string>   $sections   Default empty list of sections.
		 * @param array<string, mixed> $payload    The full status payload.
		 * @param bool                 $is_network Whether the firing `/status` endpoint is network-scoped.
		 */
		$extra_sections = apply_filters( 'millicache_status_markdown_sections', array(), $payload, $is_network );
		foreach ( $extra_sections as $section ) {
			if ( '' === trim( $section ) ) {
				continue;
			}
			$lines[] = $section;
			$lines[] = '';
		}

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
	private function as_string( $value, string $default = '' ): string {
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
	private function as_int( $value, int $default = 0 ): int {
		return is_numeric( $value ) ? (int) $value : $default;
	}
}
