<?php
/**
 * The WordPress Adminbar functionality of the plugin.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/admin
 */

namespace MilliCache\Admin;

use MilliCache\Core\Loader;
use MilliCache\Engine;
use MilliCache\MilliCache;

! defined( 'ABSPATH' ) && exit;

/**
 * The WordPress Adminbar functionality of the plugin.
 *
 * @package    MilliCache
 * @subpackage MilliCache/admin
 * @author     Philipp Wellmer <hello@millipress.com>
 */
class Adminbar {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 *
	 * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected Loader $loader;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 * @access public
	 *
	 * @param Loader $loader The loader class.
	 */
	public function __construct( Loader $loader ) {
		$this->loader = $loader;

		$this->register_hooks();
	}

	/**
	 * Register all the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private function register_hooks() {
		// Scripts & Styles.
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_adminbar_assets' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_adminbar_assets' );

		// Menu items.
		$this->loader->add_action( 'admin_bar_menu', $this, 'add_adminbar_menu', 999 );
	}

	/**
	 * Register the stylesheets & JavaScript for the adminbar.
	 *
	 * @return   void
	 * @since    1.0.0
	 * @access   public
	 */
	public function enqueue_adminbar_assets() {
		if ( Admin::enqueue_assets( 'adminbar', array( 'wp-api-fetch' ) ) ) {
			$context = array(
				'rest_url' => esc_url_raw( rest_url( 'millicache/v1/cache' ) ),
				'is_network_admin' => is_network_admin(),
			);

			wp_add_inline_script( 'millicache-adminbar', 'const millicache = ' . json_encode( $context ) . ';', 'before' );
		}
	}

	/**
	 * Add the clear cache menu to the admin bar.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar The admin bar object.
	 * @return void
	 */
	public function add_adminbar_menu( \WP_Admin_Bar $wp_admin_bar ) {
		if ( ! is_admin_bar_showing() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Root Menu.
		$wp_admin_bar->add_menu(
			array(
				'id'     => 'millicache',
				'href'   => add_query_arg( '_millicache', 'clear' ),
				'parent' => 'top-secondary',
				'title'  => '<span class="ab-icon dashicons"></span><span class="ab-label">' . __( 'Cache', 'millicache' ) . '</span>',
				'meta'   => array( 'title' => esc_html__( 'Clear Website Cache', 'millicache' ) ),
			)
		);

		// Context-specific "Clear Current".
		$targets = array();
		$title   = '';

		if ( is_admin() ) {
			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

			if ( $screen && 'post' === $screen->base ) {
				$post = get_post();

				if ( $post && 'publish' === $post->post_status ) {
					$post_type_object = get_post_type_object( $post->post_type );

					if ( $post_type_object && $post_type_object->public ) {
						$targets = MilliCache::get_post_related_flags( $post );
						$title   = sprintf(
							/* translators: %s: Post type name */
							__( 'Clear %s Cache', 'millicache' ),
							$post_type_object->labels->singular_name
						);
					}
				}
			}
		} else {
			$targets = MilliCache::get_request_flags();
			$title   = __( 'Clear Current View Cache', 'millicache' );
		}

		// Add the menu item if we have targets.
		if ( ! empty( $targets ) ) {
			$wp_admin_bar->add_menu(
				array(
					'parent' => 'millicache',
					'id'     => 'millicache_clear_current',
					'href'   => add_query_arg(
						array(
							'_millicache' => 'clear_targets',
							'_targets'    => implode( ',', $targets ),
						)
					),
					'title'  => $title,
				)
			);
		}

		// Always add site/network clear.
		$wp_admin_bar->add_menu(
			array(
				'parent' => 'millicache',
				'id'     => 'millicache-clear',
				'href'   => add_query_arg( '_millicache', 'clear' ),
				'title'  => sprintf(
				 /* translators: %s: "Network" or "Website" */
					__( 'Clear %s Cache', 'millicache' ),
					is_network_admin() ? __( 'Network', 'millicache' ) : __( 'Website', 'millicache' )
				),
			)
		);

		// Add a secondary group.
		$wp_admin_bar->add_group(
			array(
				'parent' => 'millicache',
				'id'     => 'millicache-secondary',
				'meta'   => array(
					'class' => 'ab-sub-secondary',
				),
			)
		);

		// Add the "Settings" menu with cache size.
		$wp_admin_bar->add_menu(
			array(
				'parent' => 'millicache-secondary',
				'id'     => 'millicache-settings',
				'href'   => admin_url( 'options-general.php?page=millicache' ),
				'title'  => Admin::get_cache_size_summary_string(),
			)
		);
	}
}
