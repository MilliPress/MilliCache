<?php
/**
 * Admin UI configuration for MilliCache.
 *
 * Boots the MilliBase facade for the admin page, REST routes,
 * and WordPress settings registration.
 *
 * @link       https://www.millipress.com
 * @since      1.3.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Admin;

use MilliBase\Manager;
use MilliCache\Core\Settings;
use MilliCache\Engine;
use MilliCache\MilliCache;

! defined( 'ABSPATH' ) && exit;

/**
 * Admin UI configuration for MilliCache.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class UI {

	/**
	 * The plugin slug.
	 *
	 * @var string
	 */
	private string $plugin_name;

	/**
	 * The plugin version.
	 *
	 * @var string
	 */
	private string $version;

	/**
	 * The MilliCache engine instance.
	 *
	 * @var Engine
	 */
	private Engine $engine;

	/**
	 * Initialize the UI and boot the MilliBase Manager.
	 *
	 * Manager is created at plugin load time, so schema-derived defaults
	 * are available immediately. The config closure defers translation
	 * calls to `init`.
	 *
	 * @since    1.3.0
	 * @access   public
	 *
	 * @param Engine $engine      The MilliCache engine instance.
	 * @param string $plugin_name The plugin slug.
	 * @param string $version     The plugin version.
	 */
	public function __construct( Engine $engine, string $plugin_name, string $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->engine      = $engine;

		$this->boot();
	}

	/**
	 * Boot the MilliBase facade.
	 *
	 * @since    1.3.1
	 * @access   public
	 *
	 * @return   void
	 */
	public function boot(): void {
		new Manager(
			$this->plugin_name,
			function () {
				return $this->get_ui_config();
			},
			Settings::instance()
		);
	}

	/**
	 * Build the full MilliBase facade configuration array.
	 *
	 * Called by Manager's config closure on `init`, when translations are safe.
	 *
	 * @since 1.3.0
	 *
	 * @return array<string, mixed>
	 */
	private function get_ui_config(): array {
		return array(
			'version'        => $this->version,
			'text_domain'    => 'millicache',
			'page_title'     => __( 'MilliCache', 'millicache' ),
			'menu_title'     => __( 'MilliCache', 'millicache' ),
			'menu_parent'    => 'options-general.php',
			'capability'     => 'manage_options',

			'troubleshooting' => array(
				'url'   => 'https://millipress.com/docs/millicache/09-troubleshooting/01-common-issues',
			),

			'header' => array(
				'title' => __( 'MilliCache', 'millicache' ),
				'links' => array(
					array(
						'label' => __( 'Documentation', 'millicache' ),
						'url'   => 'https://millipress.com/docs/millicache',
					),
					array(
						'label' => __( 'Support', 'millicache' ),
						'url'   => 'https://github.com/MilliPress/MilliCache/issues',
					),
				),
				'buttons' => array(
					array(
						'label'     => __( 'Clear Cache', 'millicache' ),
						'component' => 'MilliCacheClearButton',
					),
				),
				'menu_items' => array(
					array(
						'label' => __( 'Get Help', 'millicache' ),
						'icon'  => 'lifesaver',
						'url'   => 'https://github.com/MilliPress/MilliCache/issues',
					),
				),
			),

			'tabs' => array(
				array(
					'name'      => 'status',
					'title'     => __( 'Status', 'millicache' ),
					'type'      => 'custom',
					'component' => 'MilliCacheStatus',
				),
				array(
					'name'     => 'settings',
					'title'    => __( 'Settings', 'millicache' ),
					'sections' => array(
						$this->get_storage_section(),
						$this->get_cache_section(),
					),
				),
			),

			'actions' => array(
				array(
					'name'       => array( 'clear', 'clear_targets', 'clear_current' ),
					'endpoint'   => 'cache',
					'method'     => 'POST',
					'capability' => MilliCache::get_clear_cache_capability(),
					'callback'   => function ( \WP_REST_Request $request ) {
						return $this->handle_cache_action( $request );
					},
				),
			),

			'status' => array(
				'callback' => function ( \WP_REST_Request $request ) {
					return array(
						'plugin_name' => $this->plugin_name,
						'version'     => $this->version,
						'cache'       => $this->engine->cache()->get_status( $request->get_param( 'network' ) === 'true' ),
						'storage'     => $this->engine->storage()->get_status(),
						'dropin'      => Utils::validate_advanced_cache_file(),
					);
				},
			),
		);
	}

	/**
	 * Handle cache clear actions from the REST API.
	 *
	 * @param \WP_REST_Request $request The REST API request object.
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	private function handle_cache_action( \WP_REST_Request $request ) {
		$action = $request->get_param( 'action' );

		/**
		 * Filters allowed REST cache actions.
		 *
		 * This filter lets you modify the list of permitted cache actions
		 * for the MilliCache REST API endpoints.
		 *
		 * Default actions:
		 *  - 'clear' Clear all cache.
		 *  - 'clear_current' Clear the current view cache.
		 *  - 'clear_targets' Clear by targets (post IDs, URLs, flags).
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $allowed_actions Array of allowed REST cache action slugs.
		 * @return string[] Modified array of allowed REST cache actions.
		 */
		$allowed_actions = apply_filters(
			'millicache_rest_cache_allowed_actions',
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
						$this->engine->clear()->networks();
						$message = __( 'The network cache has been cleared.', 'millicache' );
					} else {
						$this->engine->clear()->sites();
						$message = __( 'The site cache has been cleared.', 'millicache' );
					}

					break;

				case 'clear_current':
					$flags         = array();
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

					$this->engine->clear()->flags( $flags );

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

					$this->engine->clear()->targets( $targets );

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
			do_action( 'millicache_rest_cache_action_performed', $action, $request->get_params(), $request );

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
	 * Get the storage server UI section configuration.
	 *
	 * @return array<string, mixed>
	 */
	private function get_storage_section(): array {
		return array(
			'id'           => 'storage',
			'title'        => __( 'Storage Server', 'millicache' ),

			'open'         => 'error',
			'status'       => array(
				'key'   => 'storage.connected',
				'ok'    => true,
				'badge' => array(
					'ok' => __( 'Connected', 'millicache' ),
					'error' => __( 'Disconnected', 'millicache' ),
				),
			),

			'fields'       => array(
				array(
					'key'     => 'storage.host',
					'type'    => 'text',
					'label'   => __( 'Server Host', 'millicache' ),
					'tooltip' => __( 'The hostname or IP address of your Redis, Valkey, KeyDB, or other compatible server. Typically "localhost" or "127.0.0.1" for local servers.', 'millicache' ),
					'default' => '127.0.0.1',
				),
				array(
					'key'     => 'storage.port',
					'type'    => 'number',
					'hide'    => array( 'storage.host', '/*' ),
					'label'   => __( 'Server Port', 'millicache' ),
					'tooltip' => __( 'The port your storage server listens on. Default is 6379 for most installations.', 'millicache' ),
					'default' => 6379,
					'min'     => 1024,
					'max'     => 65535,
					'inline'  => true,
					'width'   => '120px',
				),
				array(
					'key'     => 'storage.enc_password',
					'type'    => 'password',
					'label'   => __( 'Authentication Password', 'millicache' ),
					'tooltip' => __( 'Password used to authenticate with your Redis, Valkey, or other compatible server. Leave empty if your server does not require authentication.', 'millicache' ),
					'default' => '',
				),
				array(
					'key'     => 'storage.db',
					'type'    => 'number',
					'label'   => __( 'Database ID', 'millicache' ),
					'tooltip' => __( 'The database to use within your storage server (typically 0-15, with 0 being the default).', 'millicache' ),
					'default' => 0,
					'min'     => 0,
					'max'     => 15,
					'inline'  => true,
					'width'   => '120px',
				),
				array(
					'key'     => 'storage.persistent',
					'type'    => 'toggle',
					'label'   => __( 'Persistent Storage Connection', 'millicache' ),
					'tooltip' => __( 'When enabled, maintains a persistent connection to the server instead of creating a new connection for each request. Improves performance but uses more server resources.', 'millicache' ),
					'default' => true,
				),
			),
		);
	}

	/**
	 * Get the cache settings UI section configuration.
	 *
	 * @return array<string, mixed>
	 */
	private function get_cache_section(): array {
		return array(
			'id'           => 'cache',
			'title'        => __( 'Cache Settings', 'millicache' ),

			'open'         => 'ok',
			'status'       => array(
				'key' => 'storage.connected',
				'ok'  => true,
			),

			'fields'       => array(
				array(
					'key'      => 'cache.ttl',
					'type'     => 'unit',
					'label'    => __( 'TTL (Cache Expiry)', 'millicache' ),
					'tooltip'  => __( 'Time To Live - How long cache is considered fresh before becoming stale. Fresh cache is served directly without regeneration.', 'millicache' ),
					'default'  => 86400,
					'min'      => 1,
					'save'     => 'seconds',
					'units'    => array(
						array(
							'value' => 's',
							'label' => __( 'Seconds', 'millicache' ),
						),
						array(
							'value' => 'm',
							'label' => __( 'Minutes', 'millicache' ),
						),
						array(
							'value' => 'h',
							'label' => __( 'Hours', 'millicache' ),
						),
						array(
							'value' => 'd',
							'label' => __( 'Days', 'millicache' ),
						),
					),
				),
				array(
					'key'      => 'cache.grace',
					'type'     => 'unit',
					'label'    => __( 'Grace Period', 'millicache' ),
					'tooltip'  => __( 'Time after TTL expiration when stale cache can still be served while new content is generated in the background. Helps reduce user wait times. 0 = disable.', 'millicache' ),
					'default'  => 2592000,
					'save'     => 'seconds',
					'inline'   => true,
					'units'    => array(
						array(
							'value' => 'h',
							'label' => __( 'Hours', 'millicache' ),
						),
						array(
							'value' => 'd',
							'label' => __( 'Days', 'millicache' ),
						),
						array(
							'value' => 'w',
							'label' => __( 'Weeks', 'millicache' ),
						),
						array(
							'value' => 'M',
							'label' => __( 'Months', 'millicache' ),
						),
					),
				),
				array(
					'key'     => 'cache.gzip',
					'type'    => 'toggle',
					'label'   => __( 'Enable Gzip Compression', 'millicache' ),
					'tooltip' => __( 'Compresses cached data to reduce storage space usage. Slightly increases CPU usage but significantly reduces memory consumption.', 'millicache' ),
					'default' => true,
				),
				array(
					'key'     => 'cache.debug',
					'type'    => 'toggle',
					'label'   => __( 'Enable Debugging', 'millicache' ),
					'tooltip' => __( 'Adds detailed debug information to response headers such as the cache flags and times.', 'millicache' ),
					'default' => false,
				),
				array(
					'key'         => 'cache.nocache_paths',
					'type'        => 'token-list',
					'label'       => __( 'No-Cache Paths', 'millicache' ),
					'tooltip'     => __( 'URL paths that are not cached. You can use * wildcards (e.g. "/shop/*") or regular expressions enclosed in / characters.', 'millicache' ),
					'placeholder' => __( 'Add path or pattern (e.g. "/shop/", "/blog/*")', 'millicache' ),
					'default'     => array(),
				),
				array(
					'key'         => 'cache.nocache_cookies',
					'type'        => 'token-list',
					'label'       => __( 'No-Cache Cookies', 'millicache' ),
					'tooltip'     => __( 'Cookies that prevent caching. Example: "session_*" will skip caching if a cookie starting with "session_" is set. * = wildcard.', 'millicache' ),
					'placeholder' => __( 'Add cookie name or pattern (e.g. "session_*")', 'millicache' ),
					'default'     => array( 'wp-*pass*', 'comment_author_*' ),
				),
				array(
					'key'         => 'cache.ignore_cookies',
					'type'        => 'token-list',
					'label'       => __( 'Ignored Cookies', 'millicache' ),
					'tooltip'     => __( 'Cookies that are ignored when creating cache keys. Example: "dark_mode" means all users share the same cache regardless of the preference. * = wildcard.', 'millicache' ),
					'placeholder' => __( 'Add cookie name or pattern (e.g. "dark_mode")', 'millicache' ),
					'default'     => array( '_*' ),
				),
				array(
					'key'         => 'cache.ignore_request_keys',
					'type'        => 'token-list',
					'label'       => __( 'Ignored Request Keys', 'millicache' ),
					'tooltip'     => __( 'URL parameters that are ignored when creating cache keys. Example: "utm_*" means analytics parameters are ignored. * = wildcard.', 'millicache' ),
					'placeholder' => __( 'Add parameter name or pattern (e.g. "utm_*")', 'millicache' ),
					'default'     => array( '_*', 'utm_*' ),
				),
			),
		);
	}
}
