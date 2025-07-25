<?php
/**
 * Handles the settings storage for MilliCache.
 *
 * @package    MilliCache
 * @subpackage MilliCache/includes
 * @author     Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Core;

use MilliCache\Engine;

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
	private static string $domain;

	/**
	 * The option name used in the database.
	 *
	 * @var string
	 */
	public static string $option_name = 'millicache';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function __construct() {
		self::$domain = (string) preg_replace( '/[^a-zA-Z0-9_\-]/', '_', Engine::get_server_var( 'HTTP_HOST' ) );

		if ( function_exists( 'add_action' ) ) {
			add_action( 'init', array( $this, 'register_settings' ) );
			add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

			// Settings storage.
			add_filter( 'option_' . self::$option_name, array( $this, 'filter_settings_by_constants' ) );
			add_filter( 'default_option_' . self::$option_name, array( $this, 'filter_settings_by_constants' ) );
			add_action( 'add_option_' . self::$option_name, array( $this, 'add_config_file' ), 10, 2 );
			add_action( 'update_option_' . self::$option_name, array( $this, 'update_config_file' ), 10, 2 );
			add_action( 'delete_option', array( $this, 'delete_config_file' ) );

			// Encrypt and decrypt sensitive settings data.
			add_filter( 'pre_update_option_' . self::$option_name, array( $this, 'encrypt_sensitive_settings_data' ), 0 );
			add_filter( 'option_' . self::$option_name, array( $this, 'decrypt_sensitive_settings_data' ), 0 );
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
				echo '<div class="wrap" id="millicache-settings"></div>';
			},
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
		$default_settings = $this->get_default_settings();

		register_setting(
			'options',
			self::$option_name,
			array(
				'type'         => 'object',
				'default'      => $default_settings,
				'show_in_rest' => array(
					'schema' => $this->get_settings_schema( $default_settings ),
				),
			)
		);
	}

	/**
	 * Get the default settings for the plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param string|null $module The settings module to retrieve (e.g., 'cache', 'storage').
	 *
	 * @return array<array<bool|float|int|string|null|array<mixed>>> The default settings.
	 */
	public function get_default_settings( ?string $module = null ): array {
		$defaults = apply_filters(
			'millicache_settings_defaults',
			array(
				'storage' => array(
					'host' => '127.0.0.1',
					'port' => 6379,
					'enc_password' => '',
					'db' => 0,
					'persistent' => true,
					'prefix' => 'mll',
				),
				'cache' => array(
					'ttl' => DAY_IN_SECONDS,
					'max_ttl' => MONTH_IN_SECONDS,
					'unique' => array(),
					'nocache_cookies' => array( 'comment_author_*' ),
					'ignore_cookies' => array( '_*' ),
					'ignore_request_keys' => array( '_*', 'utm_*' ),
					'skip_millicache_callback' => '',
					'debug' => false,
					'gzip' => true,
				),
			)
		);

		if ( $module ) {
			return isset( $defaults[ $module ] ) ? $defaults[ $module ] : array();
		}

		$defaults['host'] = array(
			'domain' => self::$domain,
		);

		return $defaults;
	}

	/**
	 * Get the generated schema for the settings object based on default settings.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param array<array<mixed>> $settings The settings to generate the schema for.
	 *
	 * @return   array<mixed> The settings schema.
	 */
	public function get_settings_schema( array $settings ): array {
		$schema = array(
			'type'       => 'object',
			'properties' => array(),
		);

		foreach ( $settings as $module_key => $module_settings ) {
			$module_schema = array(
				'type'       => 'object',
				'properties' => array(),
			);

			foreach ( $module_settings as $key => $value ) {
				$module_schema['properties'][ $key ] = array( 'type' => gettype( $value ) );
			}

			$schema['properties'][ $module_key ] = $module_schema;
		}

		return $schema;
	}

	/**
	 * Get MilliCache settings with priority from constants, config file, and database.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param string|null $module The settings module to retrieve (e.g., 'cache', 'storage').
	 * @param bool $skip_constants Whether to skip constants defined in wp-config.php.
	 *
	 * @return array<array<mixed>> The settings array.
	 */
	public function get_settings( ?string $module = null, bool $skip_constants = false ): array {
		// Step 1: Get default settings.
		$settings = $this->get_default_settings( $module );

		// Step 2: Overwrite with values from the (synced) MilliCache settings file or DB.
		$file_settings = $this->get_settings_from_file( $module );
		$config_settings = $file_settings ? $file_settings : $this->get_settings_from_db( $module );
		foreach ( $config_settings as $module_key => $module_settings ) {
			foreach ( $module_settings as $key => $value ) {
				$settings[ $module_key ][ $key ] = $value;
			}
		}

		// Step 3: Overwrite with values from constants in wp-config.php.
		if ( ! $skip_constants ) {
			$constant_settings = $this->get_settings_from_constants($module);
			foreach ($constant_settings as $module_key => $module_settings) {
				foreach ($module_settings as $key => $value) {
					$settings[$module_key][$key] = $value;
				}
			}
		}

		return $settings;
	}

	/**
	 * Get settings from wp-config.php constants if they exist.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @param string|null $module The settings module to retrieve (e.g., 'cache', 'storage').
	 *
	 * @return array<array<mixed>> The updated settings.
	 */
	private function get_settings_from_constants( ?string $module = null ): array {
		$settings = $this->get_default_settings();

		if ( $module ) {
			// If a specific module is specified.
			foreach ( $settings as $key => $value ) {
				$constant = strtoupper( "MC_{$module}_{$key}" );

				if ( defined( $constant ) ) {
					$settings[ $key ] = constant( $constant );
				} else {
					unset( $settings[ $key ] );
				}
			}
		} else {
			// If no specific module is specified, loop through all settings.
			foreach ( $settings as $module_key => $module_settings ) {
				if ( is_array( $module_settings ) ) {
					foreach ( $module_settings as $key => $value ) {
						$constant = strtoupper( "MC_{$module_key}_{$key}" );

						if ( defined( $constant ) ) {
							$settings[ $module_key ][ $key ] = constant( $constant );
						} else {
							unset( $settings[ $module_key ][ $key ] );
						}
					}
				}
			}
		}

		return array_filter( $settings );
	}

	/**
	 * Get settings from the MilliCache configuration file.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @param string|null $module The settings module to retrieve (e.g., 'cache', 'storage').
	 *
	 * @return array<array<mixed>> The settings from the config file.
	 */
	private function get_settings_from_file( ?string $module = null ): array {
		$config_directory = WP_CONTENT_DIR . '/settings/millicache/';
		$config_file = $config_directory . self::$domain . '.php';

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
	 * @since    1.0.0
	 * @access   private
	 *
	 * @param string|null $module The settings module to retrieve (e.g., 'cache', 'storage').
	 *
	 * @return array<array<mixed>> The settings from the database.
	 */
	private function get_settings_from_db( ?string $module = null ): array {
		if ( ! function_exists( 'get_option' ) ) {
			return array();
		}

		$db_settings = (array) get_option( self::$option_name, array() );
		if ( $module ) {
			return isset( $db_settings[ $module ] ) ? array( (array) $db_settings[ $module ] ) : array();
		}

		return array_map(
			function ( $setting ) {
				return (array) $setting;
			},
			$db_settings
		);
	}

	/**
	 * Filter settings by constants defined in wp-config.php.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param false|array<array<mixed>> $settings The settings to filter.
	 *
	 * @return array<array<mixed>> The filtered settings.
	 */
	public function filter_settings_by_constants( $settings ): array {
		if ( ! is_array( $settings ) ) {
			return array();
		}

		// Do not save settings that are defined as constants in wp-config.php.
		$constant_settings = $this->get_settings_from_constants();
		if ( ! empty( $constant_settings ) ) {
			foreach ( $constant_settings as $module => $module_settings ) {
				foreach ( $module_settings as $key => $value ) {
					unset( $settings[ $module ][ $key ] );
				}
			}
		}

		// When constants are removed, we need to add the default settings back in.
		$default_settings = $this->get_default_settings();
		foreach ( $default_settings as $module => $module_settings ) {
			foreach ( $module_settings as $key => $value ) {
				if ( ! isset( $settings[ $module ][ $key ] ) && ! isset( $constant_settings[ $module ][ $key ] ) ) {
					$settings[ $module ][ $key ] = $value;
				}
			}
		}

		return $settings;
	}

	/**
	 * Back up the current settings to a transient.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param string|null $module The settings module to back up (e.g., 'cache', 'storage').
	 *
	 * @return void
	 */
	public static function backup( string $module = null ): void {
		$settings = new self();
		$current_settings = $settings->get_settings( $module );

		if ( $current_settings ) {
			set_transient( 'millicache_settings_backup', $current_settings, 12 * HOUR_IN_SECONDS );
		}
	}

	/**
	 * Check if a backup of the settings exists in a transient.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return bool True if a backup exists, false otherwise.
	 */
	public static function has_backup(): bool {
		return (bool) get_transient( 'millicache_settings_backup' );
	}

	/**
	 * Restore the settings from the backup stored in a transient.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param string|null $module The settings module to restore (e.g., 'cache', 'storage').
	 *
	 * @return bool True if the settings were restored, false otherwise.
	 */
	public static function restore_backup( string $module = null ): bool {
		$backup = get_transient( 'millicache_settings_backup' );

		if ( ! $backup ) {
			return false;
		}

		update_option( 'millicache', $backup );
		delete_transient( 'millicache_settings_backup' );

		return true;
	}

	/**
	 * Check if the settings are the default settings.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param string|null $module The settings module to check (e.g., 'cache', 'storage').
	 *
	 * @return bool True if the settings are the default settings, false otherwise.
	 */
	public static function has_default_settings( string $module = null ): bool {
		$settings = new self();
		return $settings->get_settings( $module, true ) === $settings->get_default_settings( $module );
	}

	/**
	 * Add the configuration file for the current site.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param string       $option The option name.
	 * @param array<mixed> $settings The settings to write.
	 *
	 * @return void
	 */
	public function add_config_file( string $option, array $settings ): void {
		$this->write_config_file( $settings );
	}

	/**
	 * Update the configuration file for the current site.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param array<mixed> $old_settings The old settings.
	 * @param array<mixed> $settings The new settings.
	 *
	 * @return void
	 */
	public function update_config_file( array $old_settings, array $settings ): void {
		$this->write_config_file( $settings );
	}

	/**
	 * Write settings to the configuration file.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param array<mixed> $settings The settings to write.
	 *
	 * @return void
	 */
	private function write_config_file( array $settings ): void {
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

		// After saving, invalidate OPcache for this file.
		if ( function_exists( 'opcache_invalidate' ) ) {
			opcache_invalidate( $config_file, true );
		}
	}

	/**
	 * Delete the configuration file for the current site.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @param string $option The option name.
	 *
	 * @return void
	 */
	public function delete_config_file( string $option ): void {
		if ( $option !== self::$option_name ) {
			return;
		}

		$settings = get_option( self::$option_name, array() );

		if ( is_array( $settings ) && isset( $settings['host']['domain'] ) ) {
			$config_directory = WP_CONTENT_DIR . '/settings/millicache/';
			$config_file = $config_directory . sanitize_file_name( $settings['host']['domain'] ) . '.php';

			if ( file_exists( $config_file ) ) {
				unlink( $config_file );
			}
		}
	}

	/**
	 * Encrypt sensitive settings data.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array<array<mixed>> $settings The plugin settings before saving.
	 *
	 * @return array<array<mixed>>
	 *
	 * @throws \Exception If random bytes cannot be generated.
	 * @throws \SodiumException If the encryption fails.
	 */
	public function encrypt_sensitive_settings_data( array $settings ): array {
		foreach ( $settings as $module => $module_settings ) {
			foreach ( $module_settings as $key => $value ) {
				if ( strpos( $key, 'enc_' ) === 0 ) {
					if ( is_string( $value ) ) {
						$settings[ $module ][ $key ] = $this->encrypt_value( $value );
					}
				}
			}
		}

		return $settings;
	}

	/**
	 * Decrypt sensitive settings data.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array<array<mixed>> $settings The stored plugin settings.
	 *
	 * @return array<array<mixed>>
	 *
	 * @throws \SodiumException If the decryption fails.
	 */
	public function decrypt_sensitive_settings_data( array $settings ): array {
		foreach ( $settings as $module => $module_settings ) {
			foreach ( $module_settings as $key => $value ) {
				if ( strpos( $key, 'enc_' ) === 0 ) {
					if ( is_string( $value ) ) {
						$settings[ $module ][ $key ] = $this->decrypt_value( $value );
					}
				}
			}
		}

		return $settings;
	}

	/**
	 * Encrypt the value using sodium.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $value The value to encrypt.
	 *
	 * @return string
	 *
	 * @throws \Exception If random bytes cannot be generated.
	 * @throws \SodiumException If the encryption fails.
	 */
	private function encrypt_value( string $value ): string {
		if ( empty( $value ) ) {
			return $value;
		}

		$nonce = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
		$key = sodium_crypto_generichash( AUTH_KEY . SECURE_AUTH_KEY, '', SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES ); // @phpstan-ignore-line

		$encrypted = sodium_crypto_secretbox( $value, $nonce, $key );
		return 'ENC:' . base64_encode( $nonce . $encrypted );
	}

	/**
	 * Decrypt the value using sodium.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $encrypted_value The encrypted value to decrypt.
	 *
	 * @return string|bool The decrypted value, or false if the value is not encrypted.
	 *
	 * @throws \SodiumException If the decryption fails.
	 */
	public static function decrypt_value( string $encrypted_value ) {
		if ( ! function_exists( 'sodium_crypto_secretbox_open' ) ) {
			require_once ABSPATH . 'wp-includes/sodium-compat/autoload.php';
		}

		// Check if the value is already decrypted.
		if ( strpos( $encrypted_value, 'ENC:' ) !== 0 ) {
			return $encrypted_value;
		}

		$encrypted_value = substr( $encrypted_value, 4 );
		$key = sodium_crypto_generichash( AUTH_KEY . SECURE_AUTH_KEY, '', SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES ); // @phpstan-ignore-line
		$decoded = base64_decode( $encrypted_value );

		$nonce = mb_substr( $decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit' );
		$ciphertext = mb_substr( $decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit' );

		return sodium_crypto_secretbox_open( $ciphertext, $nonce, $key );
	}
}
