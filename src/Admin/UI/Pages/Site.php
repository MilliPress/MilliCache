<?php
/**
 * Per-site settings page for MilliCache.
 *
 * @link       https://www.millipress.com
 * @since      1.7.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/UI/Pages
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Admin\UI\Pages;

use MilliBase\Manager;
use MilliCache\Admin\UI\Sections;
use MilliCache\Core\Settings;
use MilliCache\Engine\Utilities\Multisite;
use MilliCache\MilliCache;

! defined( 'ABSPATH' ) && exit;

/**
 * Wires the per-site MilliBase Manager (slug `millicache`, option
 * `wp_options['millicache']`) and supplies its UI configuration.
 *
 * On multisite this page exposes only the `cache` section; on single-site
 * it also exposes the `storage` section (which lives in the network
 * Manager on multisite).
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/UI/Pages
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Site extends Base {

	/**
	 * Register the MilliBase Manager that backs the per-site page.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	protected function register(): void {
		new Manager(
			$this->plugin_name,
			function () {
				return $this->get_config();
			},
			Settings::site()
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
			'text_domain'     => 'millicache',
			'page_title'      => __( 'MilliCache', 'millicache' ),
			'menu_title'      => __( 'MilliCache', 'millicache' ),
			'menu_parent'     => 'options-general.php',
			'capability'      => 'manage_options',
			'troubleshooting' => $this->troubleshooting_config(),
			'header'          => array(
				'title'      => __( 'MilliCache', 'millicache' ),
				'links'      => $this->get_header_links(),
				'buttons'    => array( $this->clear_cache_button() ),
				'menu_items' => array(
					array(
						'label' => __( 'Get Help', 'millicache' ),
						'icon'  => 'lifesaver',
						'url'   => self::SUPPORT_URL,
					),
				),
			),
			'tabs'            => array(
				$this->status_tab(),
				array(
					'name'     => 'settings',
					'title'    => __( 'Settings', 'millicache' ),
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
			'status'          => array(
				'callback' => function ( \WP_REST_Request $request ) {
					return $this->status_builder->build( false, $request );
				},
			),
		);
	}
}
