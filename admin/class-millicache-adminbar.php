<?php
/**
 * The WordPress Adminbar functionality of the plugin.
 *
 * @link       https://www.milli.press
 * @since      1.0.0
 *
 * @package    Millicache
 * @subpackage Millicache/admin
 */

! defined( 'ABSPATH' ) && exit;

/**
 * The WordPress Adminbar functionality of the plugin.
 *
 * @package    Millicache
 * @subpackage Millicache/admin
 * @author     Philipp Wellmer <hello@milli.press>
 */
class Millicache_Adminbar {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 *
	 * @var      Millicache_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 * @access public
	 *
	 * @param Millicache_Loader $loader Maintains and registers all hooks for the plugin.
	 * @return  void
	 */
	public function __construct( $loader ) {
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
		$this->loader->add_action( 'admin_bar_menu', $this, 'add_adminbar_menu', 999 );
		$this->loader->add_action( 'admin_init', $this, 'maybe_clear_cache' );
		$this->loader->add_action( 'template_redirect', $this, 'maybe_clear_cache' );
	}

	/**
	 * Add the cache flush button to the admin bar.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The admin bar object.
	 * @return void
	 */
	public function add_adminbar_menu( $wp_admin_bar ) {
		if ( ! is_admin_bar_showing() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'millicache',
				'href'   => wp_nonce_url( add_query_arg( '_millicache', 'flush' ), '_millicache__flush_nonce' ),
				'parent' => 'top-secondary',
				'title'  => '<span class="ab-icon dashicons"></span><span class="ab-label">' . __( 'Cache', 'millicache' ) . '</span>',
				'meta'   => array( 'title' => esc_html__( 'Flush the site cache', 'millicache' ) ),
			)
		);

		if ( ! is_admin() ) {
			$wp_admin_bar->add_menu(
				array(
					'id'     => 'millicache_current',
					'href'   => wp_nonce_url( add_query_arg( '_millicache', 'flush_current' ), '_millicache__flush_nonce' ),
					'parent' => 'millicache',
					'title'  => __( 'Clear this page cache', 'millicache' ),
					'meta'   => array( 'title' => esc_html__( 'Deletes the cache of the current page', 'millicache' ) ),
				)
			);

			$wp_admin_bar->add_menu(
				array(
					'id'     => 'millicache_site',
					'href'   => wp_nonce_url( add_query_arg( '_millicache', 'flush' ), '_millicache__flush_nonce' ),
					'parent' => 'millicache',
					'title'  => __( 'Clear full website cache', 'millicache' ),
					'meta'   => array( 'title' => esc_html__( 'Deletes the full website cache', 'millicache' ) ),
				)
			);
		}
	}

	/**
	 * Maybe clear the cache.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function maybe_clear_cache() {
		// Check conditions.
		if (
			! class_exists( 'Millicache_Engine' ) ||
			! is_admin_bar_showing() ||
			! current_user_can( 'manage_options' ) ||
			! isset( $_GET['_millicache'], $_GET['_wpnonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), '_millicache__flush_nonce' )
		) {
			return;
		}

		// Clear cache.
		if ( 'flush' === $_GET['_millicache'] ) {
			if ( ! is_admin() || is_blog_admin() ) {
				Millicache_Engine::clear_cache_by_site_ids( get_current_blog_id(), get_current_network_id() );
				Millicache_Admin::add_notice( __( 'The site cache has been cleared.', 'millicache' ), 'success' );
			} elseif ( is_network_admin() ) {
				Millicache_Engine::clear_cache_by_network_id( get_current_network_id() );
				Millicache_Admin::add_notice( __( 'The network cache has been cleared.', 'millicache' ), 'success' );
			}
		} elseif ( 'flush_current' === $_GET['_millicache'] ) {
			if ( is_singular() ) {
				Millicache_Engine::clear_cache_by_post_ids( get_the_ID() );
			} elseif ( is_home() || is_front_page() ) {
				Millicache_Engine::clear_cache_by_flags( 'home:' . get_current_blog_id() );
			} else {
				Millicache_Engine::clear_cache_by_flags( 'url:' . Millicache_Engine::get_url_hash() );
			}
		}

		global $pagenow;

		if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) ) {
			wp_safe_redirect( remove_query_arg( '_millicache', wp_get_referer() ) );
			exit();
		}
	}
}
