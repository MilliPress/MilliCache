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
final class Adminbar {

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
		if ( ! is_admin_bar_showing() ) {
			return;
		}

		if ( Utils::enqueue_assets( 'adminbar', array( 'wp-api-fetch', 'wp-i18n' ) ) ) {
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
		if ( ! is_admin_bar_showing() || ! current_user_can( MilliCache::get_clear_cache_capability() ) ) {
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

		// State-specific "Clear Current".
		$targets = array();
		$title   = '';

		if ( is_admin() ) {
			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

			if ( $screen && 'post' === $screen->base ) {
				$post = get_post();

				if ( $post && 'publish' === $post->post_status ) {
					$post_type_object = get_post_type_object( $post->post_type );

					if ( $post_type_object && $post_type_object->public ) {
						// Edit screen has no view; clear the post permalink's
						// url: flag (all variants of that page).
						$permalink = get_permalink( $post );
						$targets   = $permalink
							? array( 'url:' . Engine::instance()->request()->get_url_hash( $permalink ) )
							: array();
						$title     = sprintf(
							/* translators: %s: Post type name */
							__( 'Clear %s Cache', 'millicache' ),
							$post_type_object->labels->singular_name
						);
					}
				}
			}
		} else {
			// url: flag is per-URL and variant-agnostic, so this clears all
			// variants of only this view — no global/query-block flags.
			// Related listings: handled by auto edit-time invalidation.
			$targets = array( 'url:' . Engine::instance()->request()->get_url_hash() );
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
							'_millicache' => 'clear_current',
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

		if ( current_user_can( 'manage_options' ) ) {
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
					'id' => 'millicache-settings',
					'href' => is_network_admin()
						? network_admin_url( 'settings.php?page=millicache' )
						: admin_url( 'options-general.php?page=millicache' ),
					'title' => '<span class="millicache-cache-size">' . esc_html( Utils::get_cache_size_summary_string() ) . '</span>',
				)
			);
		}
	}
}
