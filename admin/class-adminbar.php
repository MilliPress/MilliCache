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

namespace MilliCache;

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
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private string $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      string    $version    The current version of this plugin.
	 */
	private string $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 * @access public
	 *
	 * @param Loader $loader The loader class.
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( Loader $loader, string $plugin_name, string $version ) {
		$this->loader = $loader;
		$this->plugin_name = $plugin_name;
		$this->version = $version;
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
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_scripts' );

		// Menu items.
		$this->loader->add_action( 'admin_bar_menu', $this, 'add_adminbar_menu', 999 );

		// Clear cache.
		$this->loader->add_action( 'admin_init', $this, 'maybe_clear_cache' );
		$this->loader->add_action( 'template_redirect', $this, 'maybe_clear_cache' );
	}

	/**
	 * Register the stylesheets for the adminbar.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name . '-adminbar', plugin_dir_url( __FILE__ ) . 'css/millicache-adminbar.css', array(), $this->version );
	}

	/**
	 * Register the JavaScript for the adminbar.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function enqueue_scripts() {
		// phpcs:ignore -- While in beta, we don't want to enqueue any scripts.
		// wp_enqueue_script( $this->plugin_name . '-adminbar', plugin_dir_url( __FILE__ ) . 'js/millicache-adminbar.js', array(), $this->version, false );
	}

	/**
	 * Add the cache flush button to the admin bar.
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
			! class_exists( '\MilliCache\Engine' ) ||
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
				Engine::clear_cache_by_site_ids( get_current_blog_id(), get_current_network_id() );
				Admin::add_notice( __( 'The site cache has been cleared.', 'millicache' ), 'success' );
			} elseif ( is_network_admin() ) {
				Engine::clear_cache_by_network_id( get_current_network_id() );
				Admin::add_notice( __( 'The network cache has been cleared.', 'millicache' ), 'success' );
			}
		} elseif ( 'flush_current' === $_GET['_millicache'] ) {
			if ( is_singular() ) {
				Engine::clear_cache_by_post_ids( (int) get_the_ID() );
			} elseif ( is_home() || is_front_page() ) {
				Engine::clear_cache_by_flags( 'home:' . get_current_blog_id() );
			} else {
				Engine::clear_cache_by_flags( 'url:' . Engine::get_url_hash() );
			}
		}

		if ( wp_doing_ajax() ) {
			wp_die();
		}

		global $pagenow;

		if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) ) {
			wp_safe_redirect( remove_query_arg( '_millicache', wp_get_referer() ) );
			exit();
		}
	}
}
