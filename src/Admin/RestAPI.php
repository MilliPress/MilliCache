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

namespace MilliCache\Admin;

use MilliCache\Core\Loader;
use MilliCache\Core\Settings;
use MilliCache\Engine;

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
	 * @var      Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected Loader $loader;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private string $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      string $version The current version of this plugin.
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
	 * Register all the hooks related to the REST API functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private function register_hooks() {
		// Register REST API routes.
		$this->loader->add_action( 'rest_api_init', $this, 'register_routes' );

		// Enforce nonce verification for all our endpoints.
		$this->loader->add_filter( 'rest_authentication_errors', $this, 'verify_rest_nonce' );
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
			'/cache',
			array(
				'methods' => \WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'perform_cache_action' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		register_rest_route(
			'millicache/v1',
			'/settings',
			array(
				'methods' => \WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'perform_settings_action' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		register_rest_route(
			'millicache/v1',
			'/status',
			array(
				'methods' => \WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_status' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	/**
	 * Unified handler for all cache actions.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param \WP_REST_Request $request The REST API request object.
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function perform_cache_action( \WP_REST_Request $request ) {
		$action = $request->get_param( 'action' );
		$allowed_actions = apply_filters(
			'millicache_rest_allowed_cache_actions',
			array(
				'clear',          // Clear all cache.
				'clear_current',  // Clear the current view cache.
				'clear_targets',  // Clear by targets (post IDs, URLs, flags).
			)
		);

		if ( ! is_string( $action ) || ! in_array( $action, $allowed_actions, true ) ) {
			return new \WP_Error(
				'invalid_action',
				__( 'Invalid cache action.', 'millicache' ),
				array( 'status' => 400 )
			);
		}

		try {
			switch ( $action ) {
				case 'clear':
					$is_network_admin = (bool) $request->get_param( 'is_network_admin' );

					if ( $is_network_admin ) {
						Engine::clear_cache_by_network_id();
						$message = __( 'The network cache has been cleared.', 'millicache' );
					} else {
						Engine::clear_cache_by_site_ids();
						$message = __( 'The site cache has been cleared.', 'millicache' );
					}

					break;

				case 'clear_current':
					$flags = array();
					$request_flags = $request->get_param( 'request_flags' );

					if ( null !== $request_flags ) {
						if ( is_string( $request_flags ) ) {
							$flags = array_values(
								array_filter(
									(array) json_decode( $request_flags, true ),
									'is_string'
								)
							);
						} elseif ( is_array( $request_flags ) ) {
							$flags = array_values(
								array_filter(
									$request_flags,
									'is_string'
								)
							);
						}
					}

					if ( empty( $flags ) ) {
						return new \WP_Error(
							'no_flags',
							__( 'No flags provided to clear cache.', 'millicache' ),
							array( 'status' => 400 )
						);
					}

					Engine::clear_cache_by_flags( $flags );

					$message = __( 'The current page cache has been cleared.', 'millicache' );

					break;

				case 'clear_targets':
					$targets = $request->get_param( 'targets' );

					if ( ! is_string( $targets ) && ! is_array( $targets ) ) {
						return new \WP_Error(
							'invalid_targets',
							__( 'Invalid targets parameter. Must be a string or an array.', 'millicache' ),
							array( 'status' => 400 )
						);
					}

					Engine::clear_cache_by_targets( $targets );

					$message = empty( $targets ) ?
						__( 'The site cache has been cleared.', 'millicache' ) :
						__( 'Cache for the targets has been cleared.', 'millicache' );

					break;
			}

			/**
			 * Fires after a MilliCache cache action has been processed.
			 *
			 * @since 1.0.0
			 *
			 * @param string $action The action that was processed.
			 * @param array  $params The parameters passed to the action.
			 * @param \WP_REST_Request $request The REST API request object.
			 */
			do_action( 'millicache_rest_perform_cache_action', $action, $request->get_params(), $request );

			return rest_ensure_response(
				array(
					'success'   => true,
					'message'   => $message ?? '',
					'action'    => $action,
					'timestamp' => time(),
				)
			);
		} catch ( \Exception $e ) {
			return new \WP_Error(
				'cache_action_failed',
				__( 'Failed to perform cache action: ', 'millicache' ) . $e->getMessage(),
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Perform custom REST API actions for MilliCache plugin.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param \WP_REST_Request $request The REST API request object.
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function perform_settings_action( \WP_REST_Request $request ) {
		$action = $request->get_param( 'action' );
		$supported_actions = apply_filters(
			'millicache_rest_allowed_settings_actions',
			array(
				'reset',
				'restore',
			)
		);

		if ( ! is_string( $action ) || ! in_array( $action, $supported_actions, true ) ) {
			return new \WP_Error( 'invalid_settings_action', __( 'Invalid settings action.', 'millicache' ), array( 'status' => 400 ) );
		}

		try {
			switch ( $action ) {
				case 'reset':
					// Backup before reset.
					Settings::backup();

					// Reset settings.
					delete_option( 'millicache' );

					$message = __( 'Settings reset successfully.', 'millicache' );
					break;

				case 'restore':
					$backup_settings = Settings::restore_backup();
					if ( ! $backup_settings ) {
						return new \WP_REST_Response(
							array(
								'success' => false,
								'message' => __( 'No backup of settings found or backup has expired.', 'millicache' ),
							),
							400
						);
					}
					$message = __( 'Settings successfully restored from backup.', 'millicache' );
					break;
			}
		} catch ( \Exception $e ) {
			return new \WP_Error( 'settings_action_failed', __( 'Failed to perform settings action: ', 'millicache' ) . $e->getMessage(), array( 'status' => 500 ) );
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
		do_action( 'millicache_rest_perform_settings_action', $action, $request->get_params(), $request );

		return rest_ensure_response(
			array(
				'success'   => true,
				'message'   => $message ?? '',
				'action'    => $action,
				'timestamp' => time(),
			)
		);
	}

	/**
	 * Get the status of MilliCache, including storage server connection status.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param \WP_REST_Request $request The REST API request object.
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 *
	 * @return \WP_REST_Response
	 */
	public function get_status( \WP_REST_Request $request ): \WP_REST_Response {
		try {
			return new \WP_REST_Response(
				apply_filters(
					'millicache_rest_status',
					array(
						'plugin_name' => $this->plugin_name,
						'version' => $this->version,
						'cache' => Engine::get_status( $request->get_param( 'network' ) === 'true' ),
						'storage' => Engine::get_storage()->get_status(),
						'dropin' => Admin::validate_advanced_cache_file(),
						'settings' => array(
							'has_defaults' => Settings::has_default_settings(),
							'has_backup' => Settings::has_backup(),
						),
					)
				),
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
	 * Verify the REST API nonce for our endpoints.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param \WP_Error|null|true $result The authentication result.
	 * @return \WP_Error|null|true The authentication result.
	 */
	public function verify_rest_nonce( $result ) {
		// If authentication has already failed, return the error.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Only verify nonce for our plugin's endpoints.
		$request_uri = Engine::get_server_var( 'REQUEST_URI' );
		if ( ! empty( $request_uri ) && str_contains( $request_uri, '/millicache/v1/' ) ) {

			// Skip nonce check for non-request methods.
			$method = Engine::get_server_var( 'REQUEST_METHOD' );
			if ( 'GET' === $method || 'HEAD' === $method || 'OPTIONS' === $method ) {
				return $result;
			}

			// Verify the nonce.
			$nonce = Engine::get_server_var( 'HTTP_X_WP_NONCE' );
			if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
				return new \WP_Error(
					'invalid_nonce',
					__( 'Invalid nonce.', 'millicache' ),
					array( 'status' => 403 )
				);
			}
		}

		return $result;
	}
}
