<?php
/**
 * Handles the settings storage for MilliCache.
 *
 * @package    MilliCache
 * @subpackage MilliCache/includes
 * @author     Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache;

! defined( 'ABSPATH' ) && exit;

/**
 * Handles the settings storage for MilliCache.
 *
 * @package    MilliCache
 * @subpackage MilliCache/includes
 * @author     Philipp Wellmer <hello@millipress.com>
 */
class Settings {

	/**
	 * The domain for which the settings are stored.
	 *
	 * @var string
	 */
	private static string $domain = 'no.tld';

	/**
	 * The option name used in the database.
	 *
	 * @var string
	 */
	private static string $option_name = 'millicache';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function __construct() {
		self::$domain = Engine::get_server_var( 'HTTP_HOST' );

		if ( function_exists( 'add_action' ) ) {
			add_action( 'init', array( $this, 'register_settings' ) );
			add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
			add_action( 'update_option_' . self::$option_name, array( $this, 'write_config_file' ), 10, 2 );
		}
	}

	/**
	 * Add the admin menu item for the plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function add_admin_menu(): void {
		add_options_page(
			__( 'MilliCache', 'millicache' ),
			__( 'MilliCache', 'millicache' ),
			'manage_options',
			'millicache',
			function () {
				printf(
					'<div class="wrap" id="millicache-settings">%s</div>',
					esc_html__( 'Loading Settings...', 'millicache' )
				);
			},
		);
	}

	/**
	 * Get the default settings for the plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param string|null $module The settings module to retrieve (e.g., 'caching', 'redis').
	 *
	 * @return   array<mixed> The default settings.
	 */
	public function get_default_settings( ?string $module = null ): array {
		$defaults = apply_filters(
			'millicache_settings_defaults',
			array(
				'redis' => array(
					'host' => '127.0.0.1',
					'port' => 6379,
					'password' => '',
					'db' => 0,
					'persistent' => true,
					'prefix' => 'mll',
				),
				'cache' => array(
					'ttl' => 900,
					'max_ttl' => MONTH_IN_SECONDS,
					'unique' => array(),
					'nocache_cookies' => array( 'comment_author' ),
					'ignore_cookies' => array(),
					'ignore_request_keys' => array( '_millicache', '_wpnonce', 'utm_source', 'utm_medium', 'utm_term', 'utm_content', 'utm_campaign' ),
					'should_cache_callback' => '',
					'debug' => false,
					'gzip' => true,
				),
			)
		);

		if ( $module ) {
			return isset( $defaults[ $module ] ) ? $defaults[ $module ] : array();
		}

		return $defaults;
	}

	/**
	 * Get the schema for the settings object.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   array<mixed> The settings schema.
	 */
	public function get_settings_schema(): array {
		return apply_filters(
			'millicache_settings_schema',
			array(
				'type'       => 'object',
				'properties' => array(
					'redis' => array(
						'type'       => 'object',
						'properties' => array(
							'host' => array(
								'type' => 'string',
							),
							'port' => array(
								'type' => 'integer',
							),
							'password' => array(
								'type' => 'string',
							),
							'db' => array(
								'type' => 'integer',
							),
							'persistent' => array(
								'type' => 'boolean',
							),
							'prefix' => array(
								'type' => 'string',
							),
						),
					),
					'cache' => array(
						'type'       => 'object',
						'properties' => array(
							'ttl' => array(
								'type' => 'integer',
							),
							'max_ttl' => array(
								'type' => 'integer',
							),
							'unique' => array(
								'type' => 'array',
							),
							'nocache_cookies' => array(
								'type' => 'array',
							),
							'ignore_cookies' => array(
								'type' => 'array',
							),
							'ignore_request_keys' => array(
								'type' => 'array',
							),
							'should_cache_callback' => array(
								'type' => 'string',
							),
							'debug' => array(
								'type' => 'boolean',
							),
							'gzip' => array(
								'type' => 'boolean',
							),
						),
					),
				),
			)
		);
	}

	/**
	 * Register the plugin settings.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function register_settings(): void {
		register_setting(
			'options',
			self::$option_name,
			array(
				'type'         => 'object',
				'default'      => $this->get_default_settings(),
				'show_in_rest' => array(
					'schema' => $this->get_settings_schema(),
				),
			)
		);
	}

	/**
	 * Get MilliCache settings with priority from constants, config file, and database.
	 *
	 * @param string|null $module The settings module to retrieve (e.g., 'cache', 'redis').
	 *
	 * @return array<mixed> The settings array.
	 */
	public function get_settings( ?string $module = null ): array {
		// Step 1: Get default settings.
		$settings = $this->get_default_settings( $module );

		// Step 2: Overwrite with values from the MilliCache settings file or DB.
		$config_settings = $this->get_settings_from_file( $module );
		if ( ! empty( $config_settings ) ) {
			$settings = array_merge( $settings, $config_settings );
		} elseif ( function_exists( 'get_option' ) ) {
			$db_settings = $this->get_settings_from_db( $module );
			if ( ! empty( $db_settings ) ) {
				$settings = array_merge( $settings, $db_settings );
			}
		}

		// Step 3: Overwrite with values from constants in wp-config.php.
		return $this->get_settings_from_constants( $settings, $module );
	}

	/**
	 * Get settings from wp-config.php constants if they exist.
	 *
	 * @param array<mixed> $settings The default settings.
	 * @param string|null  $module The settings module to retrieve (e.g., 'caching', 'redis').
	 *
	 * @return array<mixed> The updated settings.
	 */
	private function get_settings_from_constants( array $settings, ?string $module = null ): array {
		if ( $module ) {
			// If a specific module is specified.
			foreach ( $settings as $key => $value ) {
				$constant = strtoupper( "MC_{$module}_{$key}" );

				if ( defined( $constant ) ) {
					$settings[ $key ] = constant( $constant );
				}
			}
		} else {
			// If no specific module is specified, loop through all settings.
			foreach ( $settings as $module_key => $module_settings ) {
				if ( is_array( $module_settings ) ) {
					foreach ( $module_settings as $key => $value ) {
						$constant = strtoupper( "MC_{$module_key}_{$key}" );

						if ( defined( $constant ) ) {
							if ( is_array( $settings[ $module_key ] ) ) {
								$settings[ $module_key ][ $key ] = constant( $constant );
							}
						}
					}
				}
			}
		}

		return $settings;
	}

	/**
	 * Get settings from the MilliCache configuration file.
	 *
	 * @param string|null $module The settings module to retrieve (e.g., 'caching', 'redis').
	 *
	 * @return array<string, mixed> The settings from the config file.
	 */
	private function get_settings_from_file( ?string $module = null ): array {
		$config_directory = WP_CONTENT_DIR . '/settings/millicache/';
		$config_file = $config_directory . sanitize_file_name( self::$domain ) . '.php';

		if ( file_exists( $config_file ) ) {
			$config_settings = include $config_file;
			if ( $module ) {
				return isset( $config_settings[ $module ] ) ? (array) $config_settings[ $module ] : array();
			}
			return $config_settings;
		}

		return array();
	}

	/**
	 * Get settings from the database.
	 *
	 * @param string|null $module The settings module to retrieve (e.g., 'caching', 'redis').
	 *
	 * @return array<mixed> The settings from the database.
	 */
	private function get_settings_from_db( ?string $module = null ): array {
		$db_settings = (array) get_option( self::$option_name, array() );
		if ( $module ) {
			return isset( $db_settings[ $module ] ) ? (array) $db_settings[ $module ] : array();
		}

		return $db_settings;
	}

	/**
	 * Delete MilliCache settings.
	 *
	 * @return bool Whether the deletion was successful.
	 */
	public static function delete_settings(): bool {
		// Delete settings from the database.
		$deleted = delete_option( self::$option_name );

		// Delete the corresponding configuration file.
		self::delete_config_file();

		return $deleted;
	}

	/**
	 * Write settings to the configuration file.
	 *
	 * @param array<mixed> $old_settings The old settings.
	 * @param array<mixed> $settings The settings to write.
	 *
	 * @return void
	 */
	public function write_config_file( array $old_settings, array $settings ): void {
		$config_directory = WP_CONTENT_DIR . '/settings/millicache/';

		// Ensure the directory exists.
		if ( ! is_dir( $config_directory ) ) {
			wp_mkdir_p( $config_directory );
		}

		// Define the filename for the configuration.
		$config_file = $config_directory . sanitize_file_name( self::$domain ) . '.php';

		// Generate the content for the configuration file.
		$config_content = "<?php\n";
		$config_content .= "// Auto-generated configuration for MilliCache plugin\n";
		$config_content .= 'return ' . var_export( $settings, true ) . ";\n";

		// Write the content to the configuration file.
		file_put_contents( $config_file, $config_content );
	}

	/**
	 * Delete the configuration file for the current site.
	 *
	 * @return void
	 */
	private static function delete_config_file(): void {
		$config_directory = WP_CONTENT_DIR . '/settings/millicache/';
		$config_file = $config_directory . sanitize_file_name( self::$domain ) . '.php';

		if ( file_exists( $config_file ) ) {
			unlink( $config_file );
		}
	}
}
