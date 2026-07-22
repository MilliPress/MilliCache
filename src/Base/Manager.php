<?php
/**
 * Abstract base class for MilliCache MilliBase Manager wiring.
 *
 * @link       https://www.millipress.com
 * @since      1.7.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Base
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Base;

use MilliCache\Admin\UI\CacheActions;
use MilliCache\Admin\UI\StatusBuilder;
use MilliCache\Engine\Utilities\Multisite;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Common scaffolding for the per-site and network MilliBase Manager wiring.
 *
 * Each concrete subclass owns its own {@see \MilliBase\Manager} instance and
 * supplies the scope-specific config (UI, CLI, REST, abilities, migrations);
 * this abstract provides the shared UI building blocks (header links,
 * troubleshooting URL, status tab, clear-cache button, cache-action config shape).
 *
 * @package    MilliCache
 * @subpackage MilliCache/Base
 * @author     Philipp Wellmer <hello@millipress.com>
 */
abstract class Manager {

	/**
	 * Plugin slug — shared by the MilliBase Settings and Manager instances
	 * created by each scope subclass.
	 *
	 * @since 1.7.0
	 * @var string
	 */
	protected const SLUG = 'millicache';

	/**
	 * Documentation base URL.
	 *
	 * @since 1.7.0
	 * @var string
	 */
	protected const DOCS_BASE = 'https://millipress.com/docs/millicache';

	/**
	 * Support / issue tracker URL.
	 *
	 * @since 1.7.0
	 * @var string
	 */
	protected const SUPPORT_URL = 'https://github.com/MilliPress/MilliCache/issues';

	/**
	 * The plugin slug.
	 *
	 * @since 1.7.0
	 * @var string
	 */
	protected string $plugin_name;

	/**
	 * The plugin version.
	 *
	 * @since 1.7.0
	 * @var string
	 */
	protected string $version;

	/**
	 * The status payload builder.
	 *
	 * @since 1.7.0
	 * @var StatusBuilder
	 */
	protected StatusBuilder $status_builder;

	/**
	 * The cache action REST handler.
	 *
	 * @since 1.7.0
	 * @var CacheActions
	 */
	protected CacheActions $cache_actions;

	/**
	 * Construct a settings page.
	 *
	 * Calling the constructor registers the MilliBase Manager via the
	 * subclass's {@see self::register()}.
	 *
	 * @since 1.7.0
	 *
	 * @param string        $plugin_name    Plugin slug.
	 * @param string        $version        Plugin version.
	 * @param StatusBuilder $status_builder Status payload builder.
	 * @param CacheActions  $cache_actions  REST action handler.
	 */
	public function __construct(
		string $plugin_name,
		string $version,
		StatusBuilder $status_builder,
		CacheActions $cache_actions
	) {
		$this->plugin_name    = $plugin_name;
		$this->version        = $version;
		$this->status_builder = $status_builder;
		$this->cache_actions  = $cache_actions;

		$this->register();
	}

	/**
	 * Register the MilliBase Manager that backs this page.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	abstract protected function register(): void;

	/**
	 * Build the MilliBase facade configuration for this page.
	 *
	 * Called by the Manager's config closure on `init`, when translations
	 * are safe.
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, mixed>
	 */
	abstract protected function get_config(): array;

	/**
	 * Troubleshooting config block (used by both pages).
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, string>
	 */
	protected function troubleshooting_config(): array {
		return array( 'url' => self::DOCS_BASE . '/09-troubleshooting/01-common-issues' );
	}

	/**
	 * Header `links` for the settings pages.
	 *
	 * Super admins on multisite see a "Network Settings" link from the
	 * per-site page; everyone else sees a generic Support link.
	 *
	 * @since 1.7.0
	 *
	 * @return array<int, array<string, string>>
	 */
	protected function get_header_links(): array {
		$links = array(
			array(
				'label' => __( 'Documentation', 'millicache' ),
				'url'   => self::DOCS_BASE,
			),
		);

		if ( Multisite::is_enabled() && is_super_admin() && ! is_network_admin() ) {
			$links[] = array(
				'label' => __( 'Network Settings', 'millicache' ),
				'url'   => network_admin_url( 'settings.php?page=' . $this->plugin_name ),
			);
		} else {
			$links[] = array(
				'label' => __( 'Support', 'millicache' ),
				'url'   => self::SUPPORT_URL,
			);
		}

		return $links;
	}

	/**
	 * "Clear Cache" button entry for the page header.
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, string>
	 */
	protected function clear_cache_button(): array {
		return array(
			'label'     => __( 'Clear Cache', 'millicache' ),
			'component' => 'MilliCacheClearButton',
		);
	}

	/**
	 * Status tab entry for the tabs' config.
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, string|int>
	 */
	protected function status_tab(): array {
		return array(
			'name'      => 'status',
			'title'     => __( 'Status', 'millicache' ),
			'type'      => 'custom',
			'component' => 'MilliCacheStatus',
			'position'  => 10,
		);
	}

	/**
	 * Build a single `actions` entry for the cache REST endpoint.
	 *
	 * @since 1.7.0
	 *
	 * @param array<int, string> $names      Action names served by this endpoint.
	 * @param string             $capability Required capability.
	 * @param callable           $callback   REST callback.
	 * @return array<string, mixed>
	 */
	protected function cache_action_config( array $names, string $capability, callable $callback ): array {
		return array(
			'name'       => $names,
			'endpoint'   => 'cache',
			'method'     => 'POST',
			'capability' => $capability,
			'callback'   => $callback,
		);
	}

	/**
	 * Resolve the directory where MilliCache's config files live.
	 *
	 * Shared by both scope subclasses when constructing their MilliBase
	 * Settings instances.
	 *
	 * @since 1.7.0
	 *
	 * @return string
	 */
	protected static function config_directory(): string {
		return ( defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : '' ) . '/settings/millicache/';
	}
}
