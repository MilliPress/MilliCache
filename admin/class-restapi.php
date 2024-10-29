<?php
/**
 * Handles the custom REST API functionality for MilliCache plugin.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/includes
 */

namespace MilliCache;

! defined( 'ABSPATH' ) && exit;

/**
 * Handles the custom REST API functionality for MilliCache plugin.
 *
 * @package    MilliCache
 * @subpackage MilliCache/includes
 * @author     Philipp Wellmer <hello@millipress.com>
 */
class RestAPI {


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
		$this->loader->add_action( 'rest_api_init', $this, 'register_routes' );
	}

	/**
	 * Register custom REST API routes for MilliCache plugin.
	 *
	 * @return void
	 * @since    1.0.0
	 * @access   public
	 */
	public function register_routes() {
		register_rest_route(
			'millicache/v1',
			'/status',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_status' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	/**
	 * Get the status of MilliCache, including Redis connection status.
	 *
	 * @return \WP_REST_Response
	 * @since    1.0.0
	 * @access   public
	 */
	public function get_status() {
		try {
			$redis = Engine::get_storage();

			return new \WP_REST_Response(
				array(
					'connected' => $redis->is_connected(),
					'plugin_name' => $this->plugin_name,
					'version' => $this->version,
					'size' => $redis->get_cache_size(),
				)
			);
		} catch ( \Exception $e ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => $e->getMessage(),
				),
				500
			);
		}
	}
}
