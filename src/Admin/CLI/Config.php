<?php
/**
 * CLI command for managing configuration.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/CLI
 */

namespace MilliCache\Admin\CLI;

use MilliCache\Core\Settings;

! defined( 'ABSPATH' ) && exit;

/**
 * Config command.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/CLI
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Config {

	use OutputTrait;

	/**
	 * Manage MilliCache configuration.
	 *
	 * ## DESCRIPTION
	 *
	 * Get, set, reset, restore, export, or import configuration values.
	 * Settings use a priority hierarchy: constants > file > database > defaults.
	 *
	 * ## OPTIONS
	 *
	 * <subcommand>
	 * : The operation to perform.
	 * ---
	 * options:
	 *   - get
	 *   - set
	 *   - reset
	 *   - restore
	 *   - export
	 *   - import
	 * ---
	 *
	 * [<key>]
	 * : The setting key in dot notation (e.g., 'cache.ttl', 'storage.host').
	 *
	 * [<value>]
	 * : The value to set (for 'set' subcommand).
	 *
	 * [--module=<module>]
	 * : Filter by module (storage, cache, rules).
	 *
	 * [--format=<format>]
	 * : Output format for get/export.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - json
	 *   - yaml
	 * ---
	 *
	 * [--show-source]
	 * : Show where each value comes from (constant, file, db, default).
	 *
	 * [--file=<path>]
	 * : File path for export/import operations.
	 *
	 * [--yes]
	 * : Skip confirmation for destructive operations.
	 *
	 * ## EXAMPLES
	 *
	 *     # Get all settings
	 *     wp millicache config get
	 *
	 *     # Get a specific value
	 *     wp millicache config get cache.ttl
	 *
	 *     # Get settings with source info
	 *     wp millicache config get --show-source
	 *
	 *     # Set a value
	 *     wp millicache config set cache.ttl 3600
	 *
	 *     # Set a sensitive value (enc_* fields are automatically encrypted)
	 *     wp millicache config set storage.enc_password mysecret
	 *
	 *     # Reset all settings
	 *     wp millicache config reset
	 *
	 *     # Reset specific module
	 *     wp millicache config reset --module=cache --yes
	 *
	 *     # Restore from backup
	 *     wp millicache config restore
	 *
	 *     # Export settings
	 *     wp millicache config export --format=json
	 *
	 *     # Export to file
	 *     wp millicache config export --file=config.json
	 *
	 *     # Import settings
	 *     wp millicache config import --file=config.json
	 *
	 * @when after_wp_load
	 *
	 * @since 1.0.0
	 *
	 * @param array<string> $args The list of arguments.
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		$subcommand = $args[0] ?? '';

		switch ( $subcommand ) {
			case 'get':
				$this->get( array_slice( $args, 1 ), $assoc_args );
				break;
			case 'set':
				$this->set( array_slice( $args, 1 ), $assoc_args );
				break;
			case 'reset':
				$this->reset( array_slice( $args, 1 ), $assoc_args );
				break;
			case 'restore':
				$this->restore( $assoc_args );
				break;
			case 'export':
				$this->export( $assoc_args );
				break;
			case 'import':
				$this->import( $assoc_args );
				break;
			default:
				\WP_CLI::error( __( 'Invalid subcommand. Use: get, set, reset, restore, export, import.', 'millicache' ) );
		}
	}

	/**
	 * Get configuration values.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string> $args The list of arguments.
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	private function get( array $args, array $assoc_args ): void {
		$key         = $args[0] ?? '';
		$module      = $assoc_args['module'] ?? null;
		$format      = $assoc_args['format'] ?? 'table';
		$show_source = isset( $assoc_args['show-source'] );

		$settings_obj = new Settings();
		$raw_settings = null;
		$items        = array();

		if ( '' !== $key ) {
			// Get a specific key.
			$value = $settings_obj->get( $key );

			if ( null === $value ) {
				\WP_CLI::error(
					sprintf(
						// translators: %s is the setting key.
						__( 'Setting "%s" not found.', 'millicache' ),
						$key
					)
				);
			}

			$raw_settings = array( $key => $value );
			$has_sub_key  = strpos( $key, '.' ) !== false;

			if ( is_array( $value ) && ! $has_sub_key ) {
				// Module-level key (e.g., "storage") - flatten to individual settings.
				$items = $this->flatten_settings( array( $key => $value ), $settings_obj, $show_source );
			} else {
				// Single scalar setting.
				$items[] = $this->build_row( $key, $value, $settings_obj, $show_source );
			}
		} else {
			// Get all settings (optionally filtered by module).
			$raw_settings = $settings_obj->get_settings( $module );

			if ( $module ) {
				// Module filter - wrap for consistent flattening.
				$items = $this->flatten_settings( array( $module => $raw_settings ), $settings_obj, $show_source );
			} else {
				$items = $this->flatten_settings( $raw_settings, $settings_obj, $show_source );
			}
		}

		$columns = $show_source ? array( 'key', 'value', 'source' ) : array( 'key', 'value' );
		$this->output_items( $items, $raw_settings, $format, $columns );
	}

	/**
	 * Build a single row for display.
	 *
	 * @since 1.0.0
	 *
	 * @param string   $full_key     The full dot-notation key.
	 * @param mixed    $value        The setting value.
	 * @param Settings $settings_obj The settings instance.
	 * @param bool     $show_source  Whether to include source info.
	 * @return array<string, string> The row data.
	 */
	private function build_row( string $full_key, $value, Settings $settings_obj, bool $show_source ): array {
		$row = array(
			'key'   => $full_key,
			'value' => $this->format_value( $value ),
		);

		if ( $show_source ) {
			$parts = explode( '.', $full_key );
			if ( count( $parts ) >= 2 ) {
				$row['source'] = $settings_obj->get_setting_source( $parts[0], $parts[1] );
			}
		}

		return $row;
	}

	/**
	 * Flatten nested settings into rows.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $settings     The nested settings array.
	 * @param Settings             $settings_obj The settings instance.
	 * @param bool                 $show_source  Whether to include source info.
	 * @return array<int, array<string, string>> The flattened rows.
	 */
	private function flatten_settings( array $settings, Settings $settings_obj, bool $show_source ): array {
		$items = array();

		foreach ( $settings as $module_key => $module_settings ) {
			if ( ! is_array( $module_settings ) ) {
				continue;
			}
			foreach ( $module_settings as $key_name => $value ) {
				$items[] = $this->build_row( "$module_key.$key_name", $value, $settings_obj, $show_source );
			}
		}

		return $items;
	}

	/**
	 * Set a configuration value.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string> $args The list of arguments.
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	private function set( array $args, array $assoc_args ): void {
		if ( count( $args ) < 2 ) {
			\WP_CLI::error( __( 'Usage: wp millicache config set <key> <value>', 'millicache' ) );
		}

		$key = $args[0];
		$value = $args[1];

		$settings_obj = new Settings();

		// Check if the key is defined by a constant.
		$parts = explode( '.', $key );
		if ( count( $parts ) >= 2 ) {
			$module = $parts[0];
			$setting_key = $parts[1];
			$source = $settings_obj->get_setting_source( $module, $setting_key );

			if ( 'constant' === $source ) {
				\WP_CLI::error(
					sprintf(
						// translators: %s is the setting key.
						__( 'Cannot set "%s" because it is defined as a constant.', 'millicache' ),
						$key
					)
				);
			}
		}

		// Coerce value type.
		$typed_value = Settings::coerce_value( $value );

		// Set the value (Settings automatically encrypt enc_* fields).
		// Check if this is an encrypted field (the key contains enc_ after the module prefix).
		$is_encrypted_field = isset( $setting_key ) && strpos( $setting_key, 'enc_' ) === 0;

		if ( $settings_obj->set( $key, $typed_value ) ) {
			\WP_CLI::success(
				sprintf(
					// translators: %1$s is the key, %2$s is the value.
					__( 'Set "%1$s" to "%2$s".', 'millicache' ),
					$key,
					$is_encrypted_field ? '***' : $this->format_value( $typed_value )
				)
			);
		} else {
			\WP_CLI::error(
				sprintf(
					// translators: %s is the setting key.
					__( 'Failed to set "%s".', 'millicache' ),
					$key
				)
			);
		}
	}

	/**
	 * Reset configuration to defaults.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string> $args The list of arguments.
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	private function reset( array $args, array $assoc_args ): void {
		$module = $assoc_args['module'] ?? null;
		$yes = isset( $assoc_args['yes'] );

		// Confirm unless a "--yes"-flag is provided.
		if ( ! $yes ) {
			$message = $module
				// translators: %s is the module name.
				? sprintf( __( 'Are you sure you want to reset the "%s" settings to defaults?', 'millicache' ), $module )
				: __( 'Are you sure you want to reset ALL settings to defaults?', 'millicache' );

			\WP_CLI::confirm( $message );
		}

		// Create a backup first.
		Settings::backup( $module );
		\WP_CLI::line( __( 'Created settings backup.', 'millicache' ) );

		// Reset settings.
		$settings_obj = new Settings();
		if ( $settings_obj->reset( $module ) ) {
			if ( $module ) {
				\WP_CLI::success(
					sprintf(
						// translators: %s is the module name.
						__( 'Reset "%s" settings to defaults.', 'millicache' ),
						$module
					)
				);
			} else {
				\WP_CLI::success( __( 'Reset all settings to defaults.', 'millicache' ) );
			}
		} else {
			\WP_CLI::error( __( 'Failed to reset settings.', 'millicache' ) );
		}
	}

	/**
	 * Restore configuration from backup.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	private function restore( array $assoc_args ): void {
		if ( ! Settings::has_backup() ) {
			\WP_CLI::error( __( 'No backup found. Backups are created when using "config reset".', 'millicache' ) );
		}

		if ( Settings::restore_backup() ) {
			\WP_CLI::success( __( 'Settings restored from backup.', 'millicache' ) );
		} else {
			\WP_CLI::error( __( 'Failed to restore settings from backup.', 'millicache' ) );
		}
	}

	/**
	 * Export configuration.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	private function export( array $assoc_args ): void {
		$format = $assoc_args['format'] ?? 'json';
		$file = $assoc_args['file'] ?? '';

		$settings_obj = new Settings();
		$settings = $settings_obj->export();

		// Format output.
		if ( 'yaml' === $format ) {
			$output = '';
			foreach ( $settings as $module => $module_settings ) {
				$output .= "$module:\n";
				if ( is_array( $module_settings ) ) {
					foreach ( $module_settings as $key => $value ) {
						$output .= sprintf( "  %s: %s\n", $key, $this->format_value( $value ) );
					}
				}
			}
		} elseif ( 'php' === $format ) {
			$output = "<?php\nreturn " . var_export( $settings, true ) . ";\n";
		} else {
			// JSON (default).
			$output = (string) wp_json_encode( $settings, JSON_PRETTY_PRINT );
		}

		// Write to file or stdout.
		if ( '' !== $file ) {
			if ( file_put_contents( $file, $output ) ) {
				\WP_CLI::success(
					sprintf(
						// translators: %s is the file path.
						__( 'Settings exported to "%s".', 'millicache' ),
						$file
					)
				);
			} else {
				\WP_CLI::error(
					sprintf(
						// translators: %s is the file path.
						__( 'Failed to write to "%s".', 'millicache' ),
						$file
					)
				);
			}
		} else {
			\WP_CLI::line( $output );
		}
	}

	/**
	 * Import configuration from file.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	private function import( array $assoc_args ): void {
		$file = $assoc_args['file'] ?? '';
		$yes = isset( $assoc_args['yes'] );

		if ( '' === $file ) {
			\WP_CLI::error( __( 'Usage: wp millicache config import --file=<path>', 'millicache' ) );
		}

		if ( ! file_exists( $file ) ) {
			\WP_CLI::error(
				sprintf(
					// translators: %s is the file path.
					__( 'File not found: "%s".', 'millicache' ),
					$file
				)
			);
		}

		// Detect format and parse.
		$extension = pathinfo( $file, PATHINFO_EXTENSION );
		$content = file_get_contents( $file );

		if ( false === $content ) {
			\WP_CLI::error(
				sprintf(
					// translators: %s is the file path.
					__( 'Failed to read file: "%s".', 'millicache' ),
					$file
				)
			);
		}

		$settings = array();

		if ( 'php' === $extension ) {
			$settings = include $file;
		} elseif ( 'json' === $extension ) {
			$settings = json_decode( $content, true );
			if ( null === $settings ) {
				\WP_CLI::error( __( 'Invalid JSON file.', 'millicache' ) );
			}
		} elseif ( 'yaml' === $extension || 'yml' === $extension ) {
			\WP_CLI::error( __( 'YAML import requires the symfony/yaml package. Use JSON or PHP format.', 'millicache' ) );
		} else {
			// Try JSON first, then PHP.
			$settings = json_decode( $content, true );
			if ( null === $settings ) {
				\WP_CLI::error( __( 'Could not parse file. Use JSON or PHP format.', 'millicache' ) );
			}
		}

		if ( ! is_array( $settings ) || empty( $settings ) ) {
			\WP_CLI::error( __( 'No valid settings found in file.', 'millicache' ) );
		}

		// Confirm unless --yes a flag is provided.
		if ( ! $yes ) {
			\WP_CLI::confirm( __( 'Are you sure you want to import these settings?', 'millicache' ) );
		}

		// Create a backup first.
		Settings::backup();
		\WP_CLI::line( __( 'Created settings backup.', 'millicache' ) );

		// Import settings.
		$settings_obj = new Settings();
		if ( $settings_obj->import( $settings ) ) {
			\WP_CLI::success( __( 'Settings imported successfully.', 'millicache' ) );
		} else {
			\WP_CLI::error( __( 'Failed to import settings.', 'millicache' ) );
		}
	}
}
