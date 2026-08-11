<?php
/**
 * Per-site MilliBase Manager wiring for MilliCache.
 *
 * @link       https://www.millipress.com
 * @since      1.7.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Base
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Base;

use MilliCache\MilliCache;
use MilliCache\Admin\UI\Sections;
use MilliCache\Engine\Utilities\Multisite;
use MilliBase\Settings as BaseSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wires the per-site MilliBase Manager (slug `millicache`, option
 * `wp_options['millicache']`) and supplies its full config (UI, CLI,
 * REST, abilities, migrations).
 *
 * Also owns the per-site MilliBase {@see BaseSettings} singleton and the
 * defaults that back it — every reader of per-site settings goes through
 * {@see self::settings()}.
 *
 * On multisite this scope exposes only the `cache` section; on single-site
 * it also exposes the `storage` section (which lives on the network
 * Manager on multisite).
 *
 * @package    MilliCache
 * @subpackage MilliCache/Base
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Site extends Manager {

	/**
	 * The cached per-site MilliBase Settings instance.
	 *
	 * @since 1.7.0
	 * @var BaseSettings|null
	 */
	private static ?BaseSettings $settings = null;

	/**
	 * Get the per-site MilliBase Settings instance.
	 *
	 * Holds `cache` and `rules`; on single-site it additionally holds the
	 * network-owned modules (`storage`, `metrics`), since there is no
	 * separate network scope.
	 *
	 * @since 1.7.0
	 *
	 * @return BaseSettings
	 */
	public static function settings(): BaseSettings {
		if ( null === self::$settings ) {
			self::$settings = new BaseSettings(
				array(
					'slug'            => self::SLUG,
					'constant_prefix' => 'MC',
					'encryption'      => true,
					'defaults'        => self::defaults(),
					'config_file'     => array(
						'directory' => self::config_directory(),
					),
				)
			);
		}

		return self::$settings;
	}

	/**
	 * Build the per-site default settings array.
	 *
	 * On single-site the network-owned modules (storage, metrics) are bundled
	 * here too, since there is no separate network scope to host them.
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function defaults(): array {
		$defaults = array(
			'cache' => array(
				'ttl'                 => 86400,
				'grace'               => 2592000,
				'unique'              => array(),
				'nocache_paths'       => array(),
				'nocache_cookies'     => array( 'wp-*pass*', 'comment_author_*' ),
				'ignore_cookies'      => array( '_*' ),
				'ignore_request_keys' => array( '_*', 'utm_*' ),
				'buckets'             => array(),
				'debug'               => false,
				'gzip'                => true,
			),
			'rules' => array(
				'items' => array(),
			),
		);

		if ( ! Multisite::is_enabled() ) {
			$defaults = array_merge( Network::defaults(), $defaults );
		}

		return $defaults;
	}

	/**
	 * Inject a per-site Settings instance (for testing).
	 *
	 * @since 1.7.0
	 *
	 * @param BaseSettings $instance The instance to inject.
	 * @return void
	 */
	public static function inject_settings( BaseSettings $instance ): void {
		self::$settings = $instance;
	}

	/**
	 * Register the MilliBase Manager that backs the per-site page.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	protected function register(): void {
		new \MilliBase\Manager(
			$this->plugin_name,
			function () {
				return $this->get_config();
			},
			self::settings()
		);
	}

	/**
	 * Build the MilliBase facade configuration for the per-site page.
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, mixed>
	 */
	protected function get_config(): array {
		return array(
			'version'         => $this->version,
			'capability'      => 'manage_options',

			'text_domain'     => 'millicache',
			'page_title'      => __( 'MilliCache', 'millicache' ),
			'menu_title'      => __( 'MilliCache', 'millicache' ),
			'troubleshooting' => $this->troubleshooting_config(),
			'header'          => array(
				'title'      => __( 'MilliCache', 'millicache' ),
				'links'      => $this->get_header_links(),
				'buttons'    => array( $this->clear_cache_button() ),
				'menu_items' => array(
					array(
						'label'    => __( 'Get Help', 'millicache' ),
						'icon'     => 'lifesaver',
						'url'      => self::SUPPORT_URL,
						'position' => 110,
					),
				),
			),
			'footer'          => array(
				'left'  => array( 'component' => 'MilliCacheFooterStatus' ),
				'right' => 'MilliCache ' . MILLICACHE_VERSION,
			),

			'tabs'            => array(
				$this->status_tab(),
				array(
					'name'     => 'settings',
					'title'    => __( 'Settings', 'millicache' ),
					'position' => 90,
					'sections' => Multisite::is_enabled()
						? array( Sections\Cache::config() )
						: array( Sections\Storage::config(), Sections\Cache::config() ),
				),
			),

			'actions'         => array(
				$this->cache_action_config(
					array( 'clear', 'clear_targets', 'clear_current' ),
					MilliCache::get_clear_cache_capability(),
					function ( \WP_REST_Request $request ) {
						return $this->cache_actions->handle_site( $request );
					}
				),
			),

			'abilities' => array(
				'expose' => array( 'settings' ),
				'extend' => Abilities::cache(
					function (): array {
						return $this->status_builder->build( false );
					},
					function ( \WP_REST_Request $request ) {
						return $this->cache_actions->handle_site( $request );
					},
					false
				),

				'rest'   => true,
				'mcp'    => array( 'cache-status', 'cache-clear', 'settings-export' ),
			),

			'status'          => array(
				'callback' => function ( \WP_REST_Request $request ) {
					return $this->status_builder->build( false, $request );
				},
			),
		);
	}
}
