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
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_status' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		register_rest_route(
			'millicache/v1',
			'/action',
			array(
				'methods'  => \WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'perform_action' ),
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
	public function get_status(): \WP_REST_Response {
		try {
			return new \WP_REST_Response(
				array(
					'plugin_name' => $this->plugin_name,
					'version' => $this->version,
					'cache' => Engine::get_status(),
					'redis' => Engine::get_storage()->get_status(),
					'dropin' => Admin::validate_advanced_cache_file(),
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

	/**
	 * Perform custom REST API actions for MilliCache plugin.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param \WP_REST_Request<array<string, mixed>> $request The REST API request object.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function perform_action( \WP_REST_Request $request ) {
		$action = $request->get_param( 'action' );
		$params = $request->get_params();

		$supported_actions = apply_filters(
			'millicache_rest_supported_actions',
			array(
				'clear_cache_by_targets',
				'reset_settings',
			)
		);

		if ( ! is_string($action) || ! in_array( $action, $supported_actions, true ) ) {
			return new \WP_Error( 'invalid_action', __( 'Invalid cache action.', 'millicache' ), array( 'status' => 400 ) );
		}

		try {
			switch ( $action ) {
				case 'clear_cache_by_targets':
					$targets = $request->get_param( 'targets' );

					if ( ! is_string( $targets ) && ! is_array( $targets ) ) {
						return new \WP_REST_Response(
							array(
								'success' => false,
								'message' => 'Missing targets parameter to clear cache by targets.',
							),
							400
						);
					}

					Engine::clear_cache_by_targets( $targets );
					$message = __( 'Cache cleared.', 'millicache' );
					break;
				case 'reset_settings':
					delete_option( 'millicache' );
					$message = __( 'Settings reset.', 'millicache' );
					break;
			}
		} catch ( \Exception $e ) {
			return new \WP_Error( 'cache_clear_failed', __( 'Failed to clear cache: ', 'millicache' ) . $e->getMessage(), array( 'status' => 500 ) );
		}

		/**
		 * Fires after a MilliCache REST API action has been processed.
		 *
		 * @since 1.0.0
		 *
		 * @param string $action The action that was processed.
		 * @param array  $params The parameters passed to the action.
		 * @param \WP_REST_Request $request The REST API request object.
		 */
		do_action( 'millicache_rest_perform_action', $action, $params, $request );

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => $message ?? '',
				'action'  => $action,
			)
		);
	}
}
