<?php
/**
 * Network MilliBase Manager wiring for MilliCache.
 *
 * @link       https://www.millipress.com
 * @since      1.7.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Base
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Base;

use MilliBase\Settings as BaseSettings;
use MilliCache\Admin\UI\Sections;
use MilliCache\Engine\Utilities\Multisite;

! defined( 'ABSPATH' ) && exit;

/**
 * Wires the network MilliBase Manager (slug `millicache-network`, option
 * `wp_sitemeta['millicache']`) and supplies its full config (UI, CLI,
 * REST, abilities, migrations).
 *
 * Also owns the network-scoped MilliBase {@see BaseSettings} singleton and
 * the defaults that back it. On single-site the network scope doesn't
 * exist, so {@see self::settings()} delegates to {@see Site::settings()} —
 * callers can read `storage` from either without branching.
 *
 * The Manager wiring side is multisite-only — `Admin\UI` skips
 * instantiating this class on single-site installations.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Base
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Network extends Manager {

	/**
	 * The cached network-scoped MilliBase Settings instance (multisite only).
	 *
	 * @since 1.7.0
	 * @var BaseSettings|null
	 */
	private static ?BaseSettings $settings = null;

	/**
	 * Get the network-scoped MilliBase Settings instance.
	 *
	 * On multisite this returns a separate instance backed by site options
	 * (`wp_sitemeta`) and a `_network-<id>.php` config file. On single-site
	 * the network scope doesn't exist, so this delegates to
	 * {@see Site::settings()} — callers can read `storage` from either
	 * without branching.
	 *
	 * @since 1.7.0
	 *
	 * @return BaseSettings
	 */
	public static function settings(): BaseSettings {
		if ( ! Multisite::is_enabled() ) {
			return Site::settings();
		}

		if ( null === self::$settings ) {
			self::$settings = new BaseSettings(
				array(
					'slug'            => self::SLUG,
					'constant_prefix' => 'MC',
					'encryption'      => true,
					'network'         => true,
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
	 * Build the network-scoped default settings array.
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function defaults(): array {
		return array(
			'storage' => array(
				'host'         => '127.0.0.1',
				'port'         => 6379,
				'username'     => '',
				'enc_password' => '',
				'db'           => 0,
				'persistent'   => true,
				'prefix'       => 'mll',
			),
			'rules'   => array(
				'items' => array(),
			),
		);
	}

	/**
	 * Inject a network-scoped Settings instance (for testing).
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
	 * Register the MilliBase Manager that backs the Network Admin page.
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
	 * Build the MilliBase facade configuration for the Network Admin page.
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, mixed>
	 */
	protected function get_config(): array {
		return array(
			'network'         => true,
			'version'         => $this->version,
			'capability'      => 'manage_network_options',
			'migrations'      => $this->migrations(),

			'text_domain'     => 'millicache',
			'page_title'      => __( 'MilliCache', 'millicache' ),
			'menu_title'      => __( 'MilliCache', 'millicache' ),
			'troubleshooting' => $this->troubleshooting_config(),
			'header'          => array(
				'title'   => __( 'MilliCache Network', 'millicache' ),
				'links'   => $this->get_header_links(),
				'buttons' => array( $this->clear_cache_button() ),
			),

			'tabs'            => array(
				$this->status_tab(),
				array(
					'name'     => 'settings',
					'title'    => __( 'Settings', 'millicache' ),
					'sections' => array( Sections\Storage::config() ),
				),
			),

			'actions'         => array(
				$this->cache_action_config(
					array( 'clear', 'clear_targets' ),
					'manage_network_options',
					function ( \WP_REST_Request $request ) {
						return $this->cache_actions->handle_network( $request );
					}
				),
			),

			'abilities' => array(
				'expose' => array( 'settings' ),
			),

			'status'          => array(
				'callback' => function () {
					return $this->status_builder->build( true );
				},
			),
		);
	}

	/**
	 * Migrations registered with this page's MilliBase Manager.
	 *
	 * Each entry is an array consumed by MilliBase's MigrationRunner with
	 * keys `name`, `version`, `scope`, and `callback`. Identity is
	 * `name@version`; bumping `version` re-runs the migration.
	 *
	 * @since 1.7.0
	 *
	 * @return array<int, array{name: string, version: string, scope: string, callback: callable}>
	 */
	private function migrations(): array {
		return array(
			array(
				'name'     => 'move_storage_to_network',
				'version'  => '1.7.0',
				'scope'    => 'network',
				'callback' => array( Migrations\MoveStorageToNetwork::class, 'run' ),
			),
		);
	}
}
