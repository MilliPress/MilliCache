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
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_styles_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_styles_scripts' );

		// Menu items.
		$this->loader->add_action( 'admin_bar_menu', $this, 'add_adminbar_menu', 999 );

		// Clear cache.
		$this->loader->add_action( 'admin_init', $this, 'process_clear_cache_request' );
		$this->loader->add_action( 'template_redirect', $this, 'process_clear_cache_request' );
		$this->loader->add_action( 'wp_ajax_millicache_adminbar_clear_cache', $this, 'ajax_process_clear_cache_request' );
	}

	/**
	 * Register the stylesheets & JavaScript for the adminbar.
	 *
	 * @return   void
	 * @since    1.0.0
	 * @access   public
	 */
	public function enqueue_styles_scripts() {
		$asset_file = dirname( __DIR__ ) . '/build/adminbar.asset.php';
		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = include $asset_file;

		// Enqueue the adminbar script.
		wp_enqueue_script(
			$this->plugin_name . '-adminbar',
			plugins_url( 'build/adminbar.js', MILLICACHE_FILE ),
			$asset['dependencies'],
			$asset['version'],
			array(
				'in_footer' => true,
			)
		);

		// Attach inline script.
		$inline_script = 'const millicache = ' . json_encode(
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		) . ';';

		wp_add_inline_script( $this->plugin_name . '-adminbar', $inline_script, 'before' );

		// Enqueue the adminbar styles.
		wp_enqueue_style(
			$this->plugin_name . '-adminbar',
			plugins_url( 'build/adminbar.css', MILLICACHE_FILE ),
			$asset['dependencies'],
			$asset['version'],
		);
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
		$inline_script = 'const millicache = ' . json_encode(
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		) . ';';

		wp_enqueue_script( $this->plugin_name . '-adminbar', plugin_dir_url( __FILE__ ) . 'js/millicache-adminbar.js', array(), $this->version, true );
		wp_add_inline_script( $this->plugin_name . '-adminbar', $inline_script, 'before' );
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

		// todo: Add buttons to the admin bar, when on a post edit screen of public post types.
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
	 * Validate a clear cache request.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   bool
	 */
	public function validate_clear_cache_request() {
		// Sanitize the input.
		$action = $this->get_request_value( '_millicache' );
		$nonce = $this->get_request_value( '_wpnonce' );

		// Validate nonce here as needed.
		if ( ! $action || ! in_array( $action, array( 'flush', 'flush_current' ), true ) ) {
			return false;
		}

		if ( ! $nonce || ! wp_verify_nonce( sanitize_key( $nonce ), '_millicache__flush_nonce' ) ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Process a clear cache request.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function process_clear_cache_request() {
		// Validate request.
		if ( ! $this->validate_clear_cache_request() || wp_doing_ajax() ) {
			return;
		}

		// Clear cache.
		$this->process_clear_cache( $this->get_request_value( '_millicache' ) );

		// Reload page.
		wp_safe_redirect( remove_query_arg( '_millicache', wp_get_referer() ) );

		exit();
	}

	/**
	 * Process a clear cache AJAX requests.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function ajax_process_clear_cache_request() {
		// Validate request.
		if ( ! $this->validate_clear_cache_request() || ! wp_doing_ajax() ) {
			return;
		}

		// Clear the cache.
		$action = $this->get_request_value( '_millicache' );
		$success = $this->process_clear_cache( $action, $this->get_request_value( '_url' ) );

		// Send response.
		if ( $success ) {
			wp_send_json_success(
				sprintf(
					/* translators: %s: The type of cache being flushed ('full site' or 'current page'). */
					__( 'Cache for %s flushed successfully.', 'millicache' ),
					'flush' === $action ? __( 'full site', 'millicache' ) : __( 'current page', 'millicache' )
				)
			);
		} else {
			wp_send_json_error( __( 'Cache could not be flushed.', 'millicache' ) );
		}
	}

	/**
	 * Process cache clearing by context.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @param   string|null $action The action to perform.
	 * @param   string|null $url The URL to clear the cache for.
	 * @return  bool
	 */
	private function process_clear_cache( $action = 'flush', $url = '' ) {
		if ( 'flush' === $action ) {
			if ( is_network_admin() ) {
				Engine::clear_cache_by_network_id();
				Admin::add_notice( __( 'The network cache has been cleared.', 'millicache' ), 'success' );
			} else {
				Engine::clear_cache_by_site_ids();
				Admin::add_notice( __( 'The site cache has been cleared.', 'millicache' ), 'success' );
			}

			return true;
		} elseif ( 'flush_current' === $action ) {
			if ( is_singular() ) {
				Engine::clear_cache_by_post_ids( (int) get_the_ID() );
			} elseif ( is_home() || is_front_page() ) {
				Engine::clear_cache_by_flags( 'home:' . get_current_blog_id() );
			} else {
				Engine::clear_cache_by_flags( 'url:' . Engine::get_url_hash( $url ) );
			}

			return true;
		}

		return false;
	}

	/**
	 * Retrieves a value from either $_GET or $_POST superglobals with a fallback to null.
	 *
	 * This function ensures the value is sanitized using `sanitize_text_field` and `wp_unslash`.
	 * It checks both $_POST and $_GET for the specified key.
	 *
	 * @param string $key The key for the value to be retrieved.
	 * @return string|null The sanitized value if found, or null otherwise.
	 */
	public static function get_request_value( $key ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled earlier.
		return sanitize_text_field( wp_unslash( $_POST[ $key ] ?? $_GET[ $key ] ?? null ) );
	}
}
