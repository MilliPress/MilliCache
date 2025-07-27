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
	public function enqueue_adminbar_assets() {
		if ( Admin::enqueue_assets( 'adminbar' ) ) {
			$context = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'request_flags' => MilliCache::get_request_flags(),
				'is_network_admin' => is_network_admin(),
			);

			wp_add_inline_script( 'millicache-adminbar', 'const millicache = ' . json_encode( $context ) . ';', 'before' );
		}
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
					'title'  => __( 'Clear current view cache', 'millicache' ),
					'meta'   => array( 'title' => esc_html__( 'Deletes the cache related to the current page', 'millicache' ) ),
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
	public function validate_clear_cache_request(): bool {
		// Sanitize the input.
		$action = $this->get_request_value( '_millicache' );
		$nonce = $this->get_request_value( '_wpnonce' );

		// Validate nonce here as needed.
		if ( ! in_array( $action, array( 'flush', 'flush_current' ), true ) ) {
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
		$success = $this->process_clear_cache( $action, (array) json_decode( $this->get_request_value( '_request_flags' ), true) );

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
	 * @param   array       $flags The Flags to clear.
	 * @return  bool
	 */
	private function process_clear_cache( ?string $action = 'flush', array $flags = array() ): bool {
		if ( 'flush' === $action ) {
			if ( $this->get_request_value( '_is_network_admin' ) === 'true' ) {
				Engine::clear_cache_by_network_id();
				Admin::add_notice( __( 'The network cache has been cleared.', 'millicache' ), 'success' );
			} else {
				Engine::clear_cache_by_site_ids();
				Admin::add_notice( __( 'The site cache has been cleared.', 'millicache' ), 'success' );
			}

			return true;
		} elseif ( 'flush_current' === $action ) {
			if ( ! $flags ) {
				$flags = MilliCache::get_request_flags();
			}

			Engine::clear_cache_by_flags( $flags );

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
