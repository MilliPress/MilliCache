<?php
/**
 * The WordPress CLI functionality of the plugin.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin
 */

namespace MilliCache\Admin;

use MilliCache\Core\Loader;
use MilliCache\Core\Settings;
use MilliCache\Engine;

! defined( 'ABSPATH' ) && exit;

/**
 * The WordPress CLI functionality of the plugin.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class CLI {

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
	 * The Engine instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      Engine    $engine    The Engine instance.
	 */
	private Engine $engine;

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
	 * Initialize the class and set its
	 * properties.
	 *
	 * @since   1.0.0
	 * @access public
	 *
	 * @param Loader $loader Maintains and registers all hooks for the plugin.
	 * @param Engine $engine The Engine instance.
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of the plugin.
	 *
	 * @return void
	 */
	public function __construct( Loader $loader, Engine $engine, string $plugin_name, string $version ) {
		$this->loader = $loader;
		$this->engine = $engine;
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		if ( self::is_cli() ) {
			\WP_CLI::add_command( $this->plugin_name, $this );
		}
	}

	/**
	 * Check if the current request is a CLI request.
	 *
	 * @return bool
	 */
	public static function is_cli(): bool {
		return defined( 'WP_CLI' ) && WP_CLI && class_exists( '\WP_CLI' );
	}

	/**
	 * Clear the cache.
	 *
	 * ## OPTIONS
	 *
	 * [--id=<id>]
	 * : Comma separated list of post IDs.
	 *
	 * [--url=<url>]
	 * : Comma separated list of URLs.
	 *
	 * [--flag=<flag>]
	 * : Comma separated list of flags.
	 *
	 * [--site=<site>]
	 * : Comma separated list of site IDs.
	 *
	 * [--network=<network>]
	 * : Comma separated list of network IDs.
	 *
	 * [--expire=<expire>]
	 * : Expire the cache. Default is false.
	 *
	 * ## EXAMPLES
	 *
	 *     wp millicache clear --id=1,2,3
	 *
	 * @when after_wp_load
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array<string> $args The list of arguments.
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	public function clear( array $args, array $assoc_args ): void {
		$assoc_args = wp_parse_args(
			$assoc_args,
			array(
				'id'      => '',
				'url'     => '',
				'flag'    => '',
				'site'    => '',
				'network' => '',
				'expire'  => false,
			)
		);

		$expire = (bool) $assoc_args['expire'];

		// Clear the full cache if no arguments are given.
		if ( '' === $assoc_args['id'] && '' === $assoc_args['url'] && '' === $assoc_args['flag'] && '' === $assoc_args['site'] && '' === $assoc_args['network'] ) {
			$this->engine->clear()->all( $expire )->execute_queue();
			\WP_CLI::success( is_multisite() ? esc_html__( 'Network cache cleared.', 'millicache' ) : esc_html__( 'Site cache cleared.', 'millicache' ) );
			return;
		}

		$clear  = $this->engine->clear();
		$messages = array();

		// Queue network cache clearing.
		if ( '' !== $assoc_args['network'] ) {
			$network_ids = array_map( 'intval', explode( ',', $assoc_args['network'] ) );
			foreach ( $network_ids as $network_id ) {
				$clear->networks( $network_id, $expire );
			}
			$messages[] = sprintf(
				// translators: %s is the number of cleared network IDs.
				esc_html__( 'Cleared cache for %s networks.', 'millicache' ),
				implode( ', ', $network_ids )
			);
		}

		// Queue site cache clearing.
		if ( '' !== $assoc_args['site'] ) {
			$site_ids = array_map( 'intval', explode( ',', $assoc_args['site'] ) );
			foreach ( $site_ids as $site_id ) {
				$clear->sites( $site_id, null, $expire );
			}
			$messages[] = sprintf(
				// translators: %s is the number of cleared site IDs.
				esc_html__( 'Cleared cache for %s sites.', 'millicache' ),
				count( $site_ids )
			);
		}

		// Queue cache clearing by post-IDs.
		if ( '' !== $assoc_args['id'] ) {
			$post_ids = array_map( 'intval', explode( ',', $assoc_args['id'] ) );
			foreach ( $post_ids as $post_id ) {
				$clear->posts( $post_id, $expire );
			}
			$messages[] = sprintf(
				// translators: %s is the number of cleared post-IDs.
				esc_html__( 'Cleared cache for %s posts.', 'millicache' ),
				count( $post_ids )
			);
		}

		// Queue cache clearing by URLs.
		if ( '' !== $assoc_args['url'] ) {
			$urls = array_map( 'trim', explode( ',', $assoc_args['url'] ) );
			foreach ( $urls as $url ) {
				$clear->urls( $url, $expire );
			}
			$messages[] = sprintf(
				// translators: %s is the number of cleared URLs.
				esc_html__( 'Cleared cache for %s URLs.', 'millicache' ),
				count( $urls )
			);
		}

		// Queue cache clearing by flags.
		if ( '' !== $assoc_args['flag'] ) {
			$flags = array_map( 'trim', explode( ',', $assoc_args['flag'] ) );
			foreach ( $flags as $flag ) {
				$clear->flags( $flag, $expire, false );
			}
			$messages[] = sprintf(
				// translators: %s is the number of cleared flags.
				esc_html__( 'Cleared cache for %s flags.', 'millicache' ),
				count( $flags )
			);
		}

		// Execute all queued operations.
		$clear->execute_queue();

		// Output success messages.
		foreach ( $messages as $message ) {
			\WP_CLI::success( $message );
		}
	}

	/**
	 * Get cache statistics.
	 *
	 * ## DESCRIPTION
	 *
	 * Displays cache statistics including entry count, total size, and average size.
	 *
	 * ## OPTIONS
	 *
	 * [--flag=<flag>]
	 * : The flag to search for. Wildcards are allowed. Default: *.
	 *
	 * [--format=<format>]
	 * : Output format. Default: table.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - json
	 *   - yaml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp millicache stats
	 *     wp millicache stats --flag=1:*
	 *     wp millicache stats --format=json
	 *
	 * @when after_wp_load
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array<string> $args The list of arguments.
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	public function stats( array $args, array $assoc_args ): void {
		$flag = $assoc_args['flag'] ?? '*';
		$format = $assoc_args['format'] ?? 'table';
		$size = Admin::get_cache_size( $flag, true );

		// Calculate average size.
		$avg_size = $size['index'] > 0 ? (int) ( $size['size'] / $size['index'] ) : 0;
		$avg_size_human = (string) size_format( $avg_size, $avg_size > 1024 ? 2 : 0 );

		// Build stats data.
		$stats = array(
			'flag'           => $flag,
			'entries'        => $size['index'],
			'size'           => $size['size'],
			'size_human'     => $size['size_human'],
			'avg_size'       => $avg_size,
			'avg_size_human' => $avg_size_human,
		);

		// Output based on format.
		if ( 'json' === $format ) {
			\WP_CLI::line( (string) wp_json_encode( $stats, JSON_PRETTY_PRINT ) );
		} elseif ( 'yaml' === $format ) {
			$yaml = '';
			foreach ( $stats as $key => $value ) {
				$yaml .= sprintf( "%s: %s\n", $key, $value );
			}
			\WP_CLI::line( $yaml );
		} else {
			// Table format.
			$items = array();
			foreach ( $stats as $key => $value ) {
				$items[] = array(
					'property' => $key,
					'value'    => $value,
				);
			}
			\WP_CLI\Utils\format_items( 'table', $items, array( 'property', 'value' ) );
		}
	}

	/**
	 * Open an interactive Redis CLI connection.
	 *
	 * ## DESCRIPTION
	 *
	 * Opens an interactive Redis CLI session using the configured storage settings.
	 * Requires redis-cli to be installed on the system.
	 *
	 * ## EXAMPLES
	 *
	 *     wp millicache cli
	 *
	 * @when after_wp_load
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array<string> $args The list of arguments.
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	public function cli( array $args, array $assoc_args ): void {
		// Check if redis-cli is available.
		$redis_cli = trim( (string) shell_exec( 'which redis-cli 2>/dev/null' ) );
		if ( empty( $redis_cli ) ) {
			\WP_CLI::error( __( 'redis-cli is not installed or not in PATH. Please install Redis tools.', 'millicache' ) );
		}

		// Get storage settings.
		$settings = new Settings();
		$storage_settings = $settings->get_settings( 'storage' );

		// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- Type hint for PHPStan.
		/** @var string $host */
		$host = $storage_settings['host'] ?? '127.0.0.1';
		// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- Type hint for PHPStan.
		/** @var int $port */
		$port = $storage_settings['port'] ?? 6379;
		// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- Type hint for PHPStan.
		/** @var int $db */
		$db = $storage_settings['db'] ?? 0;
		// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- Type hint for PHPStan.
		/** @var string $password */
		$password = $storage_settings['enc_password'] ?? '';

		// translators: %1$s is the Redis host, %2$d is the port, %3$d is the database number.
		\WP_CLI::line( sprintf( __( 'Connecting to Redis at %1$s:%2$d (database %3$d)...', 'millicache' ), $host, $port, $db ) );

		// Test connection with timeout before launching interactive session.
		$test_command = sprintf(
			'timeout 5 redis-cli -h %s -p %d PING 2>&1',
			escapeshellarg( $host ),
			$port
		);

		// Add password to test command if set.
		if ( '' !== $password ) {
			$test_command = sprintf(
				'timeout 5 redis-cli -h %s -p %d -a %s --no-auth-warning PING 2>&1',
				escapeshellarg( $host ),
				$port,
				escapeshellarg( $password )
			);
		}

		$test_result = trim( (string) shell_exec( $test_command ) );

		if ( 'PONG' !== $test_result ) {
			$error_msg = '' !== $test_result ? $test_result : __( 'Connection timed out', 'millicache' );
			\WP_CLI::error(
				sprintf(
					// translators: %1$s is the host, %2$d is the port, %3$s is the error message.
					__( 'Cannot connect to Redis at %1$s:%2$d - %3$s', 'millicache' ),
					$host,
					$port,
					$error_msg
				)
			);
		}

		// Build the redis-cli command.
		$command = sprintf(
			'redis-cli -h %s -p %d -n %d',
			escapeshellarg( $host ),
			$port,
			$db
		);

		// Add password if set.
		if ( '' !== $password ) {
			$command .= sprintf( ' -a %s --no-auth-warning', escapeshellarg( $password ) );
		}

		\WP_CLI::line( __( 'Type "quit" to exit.', 'millicache' ) );
		\WP_CLI::line( '' );

		// Launch interactive session using passthru for proper TTY handling.
		passthru( $command );
	}

	/**
	 * Fix or reinstall the advanced-cache.php drop-in.
	 *
	 * ## DESCRIPTION
	 *
	 * Removes and recreates the advanced-cache.php file in wp-content.
	 * Useful for CD workflows where symlinks may break.
	 *
	 * ## OPTIONS
	 *
	 * [--force]
	 * : Force reinstall even if the current version matches.
	 *
	 * ## EXAMPLES
	 *
	 *     wp millicache fix
	 *     wp millicache fix --force
	 *
	 * @when after_wp_load
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array<string> $args The list of arguments.
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	public function fix( array $args, array $assoc_args ): void {
		$force = isset( $assoc_args['force'] );
		$destination = WP_CONTENT_DIR . '/advanced-cache.php';
		$source = MILLICACHE_DIR . '/advanced-cache.php';

		// Check current status.
		if ( file_exists( $destination ) && ! $force ) {
			$info = Admin::validate_advanced_cache_file();
			if ( ! empty( $info ) && 'symlink' === $info['type'] ) {
				$target = readlink( $destination );
				if ( $target === $source ) {
					\WP_CLI::success( __( 'advanced-cache.php symlink is already correctly configured.', 'millicache' ) );
					return;
				}
			}
		}

		// Check if wp-content is writable.
		if ( ! is_writable( WP_CONTENT_DIR ) ) {
			\WP_CLI::error( __( 'The wp-content directory is not writable.', 'millicache' ) );
		}

		// Remove existing file.
		if ( file_exists( $destination ) || is_link( $destination ) ) {
			if ( ! unlink( $destination ) ) {
				\WP_CLI::error( __( 'Could not remove existing advanced-cache.php file.', 'millicache' ) );
			}
			\WP_CLI::line( __( 'Removed existing advanced-cache.php.', 'millicache' ) );
		}

		// Check source file.
		if ( ! is_readable( $source ) ) {
			\WP_CLI::error( __( 'Source advanced-cache.php file is not readable.', 'millicache' ) );
		}

		// Try to create symlink first.
		if ( @symlink( $source, $destination ) ) {
			\WP_CLI::success( __( 'Created symlink for advanced-cache.php.', 'millicache' ) );
			return;
		}

		// Fallback: copy file with path replacement.
		$source_content = file_get_contents( $source );
		if ( false === $source_content ) {
			\WP_CLI::error( __( 'Could not read source advanced-cache.php file.', 'millicache' ) );
		}

		// Replace the path to the engine file.
		$source_content = preg_replace(
			'/(\$engine_path\s*=\s*)dirname.*?;/s',
			"$1'" . dirname( __DIR__ ) . "';",
			$source_content
		);

		if ( file_put_contents( $destination, $source_content, LOCK_EX ) ) {
			\WP_CLI::success( __( 'Copied advanced-cache.php to wp-content directory.', 'millicache' ) );
		} else {
			\WP_CLI::error( __( 'Could not create advanced-cache.php file.', 'millicache' ) );
		}
	}

	/**
	 * Show the plugin and cache status.
	 *
	 * ## DESCRIPTION
	 *
	 * Displays comprehensive status information including Redis connection,
	 * server info, advanced-cache.php status, and cache statistics.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Output format. Default: table.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - json
	 *   - yaml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp millicache status
	 *     wp millicache status --format=json
	 *
	 * @when after_wp_load
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array<string> $args The list of arguments.
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	public function status( array $args, array $assoc_args ): void {
		$format = $assoc_args['format'] ?? 'table';

		$status = array();

		// Plugin version.
		$status['plugin_version'] = $this->version;

		// WP_CACHE constant.
		$status['wp_cache'] = defined( 'WP_CACHE' ) && WP_CACHE ? 'enabled' : 'disabled';

		// Advanced-cache.php status.
		$dropin_info = Admin::validate_advanced_cache_file();
		if ( empty( $dropin_info ) ) {
			$status['advanced_cache'] = 'missing';
		} else {
			$status['advanced_cache'] = $dropin_info['type'];
			if ( ! empty( $dropin_info['outdated'] ) ) {
				$status['advanced_cache'] .= ' (outdated)';
			}
		}

		// Storage/Redis status.
		$storage = $this->engine->storage();
		$storage_status = $storage->get_status();

		$status['storage_connected'] = $storage_status['connected'] ? 'yes' : 'no';

		if ( ! empty( $storage_status['error'] ) ) {
			$status['storage_error'] = $storage_status['error'];
		}

		if ( $storage_status['connected'] ) {
			// Server version.
			if ( ! empty( $storage_status['info']['Server']['version'] ) ) {
				$status['storage_server'] = $storage_status['info']['Server']['version'];
			}

			// Memory info.
			if ( ! empty( $storage_status['info']['Memory']['used_memory_human'] ) ) {
				$status['storage_memory_used'] = $storage_status['info']['Memory']['used_memory_human'];
			}
			if ( ! empty( $storage_status['info']['Memory']['maxmemory_human'] ) && '0B' !== $storage_status['info']['Memory']['maxmemory_human'] ) {
				$status['storage_memory_max'] = $storage_status['info']['Memory']['maxmemory_human'];
			}
		}

		// Cache statistics.
		$flag = $this->engine->flags()->get_prefix( is_multisite() && is_network_admin() ? '*' : null ) . '*';
		$cache_size = Admin::get_cache_size( $flag, true );
		$status['cache_entries'] = $cache_size['index'];
		$status['cache_size'] = $cache_size['size_human'];

		// Output based on format.
		if ( 'json' === $format ) {
			\WP_CLI::line( (string) wp_json_encode( $status, JSON_PRETTY_PRINT ) );
		} elseif ( 'yaml' === $format ) {
			$yaml = '';
			foreach ( $status as $key => $value ) {
				$yaml .= sprintf( "%s: %s\n", $key, $value );
			}
			\WP_CLI::line( $yaml );
		} else {
			// Table format.
			$items = array();
			foreach ( $status as $key => $value ) {
				$items[] = array(
					'property' => $key,
					'status'   => $value,
				);
			}
			\WP_CLI\Utils\format_items( 'table', $items, array( 'property', 'status' ) );
		}
	}

	/**
	 * Test the Redis connection with diagnostics.
	 *
	 * ## DESCRIPTION
	 *
	 * Performs connection tests including ping, write, read, and delete operations.
	 * Useful for troubleshooting storage server connectivity.
	 *
	 * ## EXAMPLES
	 *
	 *     wp millicache test
	 *
	 * @when after_wp_load
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array<string> $args The list of arguments.
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	public function test( array $args, array $assoc_args ): void {
		\WP_CLI::line( __( 'Testing Redis connection...', 'millicache' ) );
		\WP_CLI::line( '' );

		$settings = new Settings();
		$storage_settings = $settings->get_settings( 'storage' );

		// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- Type hint for PHPStan.
		/** @var string $host */
		$host = $storage_settings['host'] ?? '127.0.0.1';
		// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- Type hint for PHPStan.
		/** @var int $port */
		$port = $storage_settings['port'] ?? 6379;

		// translators: %1$s is the Redis host, %2$d is the port.
		\WP_CLI::line( sprintf( __( 'Server: %1$s:%2$d', 'millicache' ), $host, $port ) );
		\WP_CLI::line( '' );

		$tests = array();
		$all_passed = true;

		// Test 1: Connection.
		$storage = $this->engine->storage();
		$storage_status = $storage->get_status();

		if ( $storage_status['connected'] ) {
			$tests[] = array(
				'test'   => __( 'Connection', 'millicache' ),
				'status' => 'PASS',
				'info'   => $storage_status['info']['Server']['version'] ?? '',
			);
		} else {
			$tests[] = array(
				'test'   => __( 'Connection', 'millicache' ),
				'status' => 'FAIL',
				'info'   => $storage_status['error'] ?? __( 'Unknown error', 'millicache' ),
			);
			$all_passed = false;
		}

		// Only run additional tests if connected.
		if ( $storage_status['connected'] ) {
			// Test 2: Ping.
			$start = microtime( true );
			try {
				$ping_result = $storage->is_connected();
				$latency = round( ( microtime( true ) - $start ) * 1000, 2 );
				$tests[] = array(
					'test'   => __( 'Ping', 'millicache' ),
					'status' => $ping_result ? 'PASS' : 'FAIL',
					// translators: %s is the latency in milliseconds.
					'info'   => $ping_result ? sprintf( __( '%sms', 'millicache' ), $latency ) : '',
				);
				if ( ! $ping_result ) {
					$all_passed = false;
				}
			} catch ( \Exception $e ) {
				$tests[] = array(
					'test'   => __( 'Ping', 'millicache' ),
					'status' => 'FAIL',
					'info'   => $e->getMessage(),
				);
				$all_passed = false;
			}

			// Test 3: Write.
			$test_key = 'millicache_cli_test_' . time();
			$test_data = array(
				'test'    => true,
				'updated' => time(),
			);
			try {
				$write_result = $storage->set_cache( $test_key, $test_data, array( 'cli-test' ) );
				$tests[] = array(
					'test'   => __( 'Write', 'millicache' ),
					'status' => $write_result ? 'PASS' : 'FAIL',
					'info'   => '',
				);
				if ( ! $write_result ) {
					$all_passed = false;
				}
			} catch ( \Exception $e ) {
				$tests[] = array(
					'test'   => __( 'Write', 'millicache' ),
					'status' => 'FAIL',
					'info'   => $e->getMessage(),
				);
				$all_passed = false;
			}

			// Test 4: Read.
			try {
				$read_result = $storage->get_cache( $test_key );
				$read_passed = is_array( $read_result ) && isset( $read_result[0]['test'] ) && true === $read_result[0]['test'];
				$tests[] = array(
					'test'   => __( 'Read', 'millicache' ),
					'status' => $read_passed ? 'PASS' : 'FAIL',
					'info'   => '',
				);
				if ( ! $read_passed ) {
					$all_passed = false;
				}
			} catch ( \Exception $e ) {
				$tests[] = array(
					'test'   => __( 'Read', 'millicache' ),
					'status' => 'FAIL',
					'info'   => $e->getMessage(),
				);
				$all_passed = false;
			}

			// Test 5: Delete (cleanup).
			try {
				$delete_result = $storage->delete_cache( $test_key );
				$tests[] = array(
					'test'   => __( 'Delete', 'millicache' ),
					'status' => $delete_result ? 'PASS' : 'FAIL',
					'info'   => '',
				);
				if ( ! $delete_result ) {
					$all_passed = false;
				}
			} catch ( \Exception $e ) {
				$tests[] = array(
					'test'   => __( 'Delete', 'millicache' ),
					'status' => 'FAIL',
					'info'   => $e->getMessage(),
				);
				$all_passed = false;
			}
		}

		// Output results.
		\WP_CLI\Utils\format_items( 'table', $tests, array( 'test', 'status', 'info' ) );
		\WP_CLI::line( '' );

		if ( $all_passed ) {
			\WP_CLI::success( __( 'All tests passed.', 'millicache' ) );
		} else {
			\WP_CLI::error( __( 'Some tests failed.', 'millicache' ) );
		}
	}

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
	 * @access public
	 *
	 * @param array<string> $args The list of arguments.
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	public function config( array $args, array $assoc_args ): void {
		$subcommand = $args[0] ?? '';

		switch ( $subcommand ) {
			case 'get':
				$this->config_get( array_slice( $args, 1 ), $assoc_args );
				break;
			case 'set':
				$this->config_set( array_slice( $args, 1 ), $assoc_args );
				break;
			case 'reset':
				$this->config_reset( array_slice( $args, 1 ), $assoc_args );
				break;
			case 'restore':
				$this->config_restore( $assoc_args );
				break;
			case 'export':
				$this->config_export( $assoc_args );
				break;
			case 'import':
				$this->config_import( $assoc_args );
				break;
			default:
				\WP_CLI::error( __( 'Invalid subcommand. Use: get, set, reset, restore, export, import.', 'millicache' ) );
		}
	}

	/**
	 * Get configuration values.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array<string> $args The list of arguments.
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	private function config_get( array $args, array $assoc_args ): void {
		$key = $args[0] ?? '';
		$module = $assoc_args['module'] ?? null;
		$format = $assoc_args['format'] ?? 'table';
		$show_source = isset( $assoc_args['show-source'] );

		$settings_obj = new Settings();

		// Get a specific key.
		if ( '' !== $key ) {
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

			$parts = explode( '.', $key );
			$module_key = $parts[0];
			$setting_key = $parts[1] ?? '';

			if ( 'json' === $format ) {
				\WP_CLI::line( (string) wp_json_encode( array( $key => $value ), JSON_PRETTY_PRINT ) );
			} elseif ( 'yaml' === $format ) {
				\WP_CLI::line( sprintf( '%s: %s', $key, $this->format_value( $value ) ) );
			} else {
				$row = array(
					'key'   => $key,
					'value' => $this->format_value( $value ),
				);
				if ( $show_source && '' !== $setting_key ) {
					$row['source'] = $settings_obj->get_setting_source( $module_key, $setting_key );
				}
				$columns = $show_source ? array( 'key', 'value', 'source' ) : array( 'key', 'value' );
				\WP_CLI\Utils\format_items( 'table', array( $row ), $columns );
			}
			return;
		}

		// Get all settings (optionally filtered by module).
		$settings = $settings_obj->get_settings( $module );

		if ( $module ) {
			// Single module - flatten for display.
			$items = array();
			foreach ( $settings as $key_name => $value ) {
				$row = array(
					'key'   => "{$module}.{$key_name}",
					'value' => $this->format_value( $value ),
				);
				if ( $show_source ) {
					$row['source'] = $settings_obj->get_setting_source( $module, $key_name );
				}
				$items[] = $row;
			}
		} else {
			// All modules - flatten nested structure.
			$items = array();
			foreach ( $settings as $module_key => $module_settings ) {
				if ( ! is_array( $module_settings ) ) {
					continue;
				}
				foreach ( $module_settings as $key_name => $value ) {
					$row = array(
						'key'   => "{$module_key}.{$key_name}",
						'value' => $this->format_value( $value ),
					);
					if ( $show_source ) {
						$row['source'] = $settings_obj->get_setting_source( $module_key, $key_name );
					}
					$items[] = $row;
				}
			}
		}

		// Output based on format.
		if ( 'json' === $format ) {
			\WP_CLI::line( (string) wp_json_encode( $settings, JSON_PRETTY_PRINT ) );
		} elseif ( 'yaml' === $format ) {
			$yaml = '';
			foreach ( $items as $item ) {
				$yaml .= sprintf( "%s: %s\n", $item['key'], $item['value'] );
			}
			\WP_CLI::line( $yaml );
		} else {
			$columns = $show_source ? array( 'key', 'value', 'source' ) : array( 'key', 'value' );
			\WP_CLI\Utils\format_items( 'table', $items, $columns );
		}
	}

	/**
	 * Set a configuration value.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array<string> $args The list of arguments.
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	private function config_set( array $args, array $assoc_args ): void {
		if ( count( $args ) < 2 ) {
			\WP_CLI::error( __( 'Usage: wp millicache config set <key> <value>', 'millicache' ) );
		}

		$key = $args[0];
		$value = $args[1];

		$settings_obj = new Settings();

		// Check if key is defined by a constant.
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

		// Set the value (enc_* fields are automatically encrypted by Settings).
		// Check if this is an encrypted field (key contains enc_ after the module prefix).
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
	 * @access private
	 *
	 * @param array<string> $args The list of arguments.
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	private function config_reset( array $args, array $assoc_args ): void {
		$module = $assoc_args['module'] ?? null;
		$yes = isset( $assoc_args['yes'] );

		// Confirm unless --yes flag is provided.
		if ( ! $yes ) {
			$message = $module
				// translators: %s is the module name.
				? sprintf( __( 'Are you sure you want to reset the "%s" settings to defaults?', 'millicache' ), $module )
				: __( 'Are you sure you want to reset ALL settings to defaults?', 'millicache' );

			\WP_CLI::confirm( $message );
		}

		// Create backup first.
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
	 * @access private
	 *
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	private function config_restore( array $assoc_args ): void {
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
	 * @access private
	 *
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	private function config_export( array $assoc_args ): void {
		$format = $assoc_args['format'] ?? 'json';
		$file = $assoc_args['file'] ?? '';

		$settings_obj = new Settings();
		$settings = $settings_obj->export();

		// Format output.
		if ( 'yaml' === $format ) {
			$output = '';
			foreach ( $settings as $module => $module_settings ) {
				$output .= "{$module}:\n";
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
	 * @access private
	 *
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	private function config_import( array $assoc_args ): void {
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

		// Confirm unless --yes flag is provided.
		if ( ! $yes ) {
			\WP_CLI::confirm( __( 'Are you sure you want to import these settings?', 'millicache' ) );
		}

		// Create backup first.
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

	/**
	 * Format a value for display.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param mixed $value The value to format.
	 * @return string The formatted value.
	 */
	private function format_value( $value ): string {
		if ( is_bool( $value ) ) {
			return $value ? 'true' : 'false';
		}

		if ( null === $value ) {
			return 'null';
		}

		if ( is_array( $value ) ) {
			return (string) wp_json_encode( $value );
		}

		if ( is_scalar( $value ) ) {
			return (string) $value;
		}

		return (string) wp_json_encode( $value );
	}
}
