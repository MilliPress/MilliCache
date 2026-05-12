<?php
/**
 * Network Admin settings page for MilliCache.
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

! defined( 'ABSPATH' ) && exit;

/**
 * Wires the network MilliBase Manager (slug `millicache-network`, option
 * `wp_sitemeta['millicache']`) and supplies its UI configuration.
 *
 * Multisite only — `UI\Bootstrap` skips instantiating this page on
 * single-site installations.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/UI/Pages
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Network extends Base {

	/**
	 * Register the MilliBase Manager that backs the Network Admin page.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	protected function register(): void {
		new Manager(
			$this->plugin_name . '-network',
			function () {
				return $this->get_config();
			},
			Settings::network()
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
			'version'         => $this->version,
			'text_domain'     => 'millicache',
			'capability'      => 'manage_network_options',
			'page_title'      => __( 'MilliCache', 'millicache' ),
			'menu_title'      => __( 'MilliCache', 'millicache' ),
			'cli'             => array( 'slug' => 'millicache' ),
			'network'         => true,
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
			'status'          => array(
				'callback' => function () {
					return $this->status_builder->build( true );
				},
			),
		);
	}
}
