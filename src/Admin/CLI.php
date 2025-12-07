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
	 * [--ids=<ids>]
	 * : Comma separated list of post IDs.
	 *
	 * [--urls=<urls>]
	 * : Comma separated list of URLs.
	 *
	 * [--flags=<flags>]
	 * : Comma separated list of flags.
	 *
	 * [--sites=<sites>]
	 * : Comma separated list of site IDs.
	 *
	 * [--networks=<networks>]
	 * : Comma separated list of network IDs.
	 *
	 * [--expire=<expire>]
	 * : Expire the cache. Default is false.
	 *
	 * ## EXAMPLES
	 *
	 *     wp millicache clear --ids=1,2,3
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
				'ids'       => '',
				'urls'      => '',
				'flags'     => '',
				'sites'     => '',
				'networks'  => '',
				'expire'    => false,
			)
		);

		$expire = (bool) $assoc_args['expire'];

		// Clear the full cache if no arguments are given.
		if ( '' === $assoc_args['ids'] && '' === $assoc_args['urls'] && '' === $assoc_args['flags'] && '' === $assoc_args['sites'] && '' === $assoc_args['networks'] ) {
			$this->engine->clear()->all( $expire )->execute_queue();
			\WP_CLI::success( is_multisite() ? esc_html__( 'Network cache cleared.', 'millicache' ) : esc_html__( 'Site cache cleared.', 'millicache' ) );
			return;
		}

		$clear  = $this->engine->clear();
		$messages = array();

		// Queue network cache clearing.
		if ( '' !== $assoc_args['networks'] ) {
			$network_ids = array_map( 'intval', explode( ',', $assoc_args['networks'] ) );
			foreach ( $network_ids as $network_id ) {
				$clear->network( $network_id, $expire );
			}
			$messages[] = sprintf(
				// translators: %s is a comma-separated list of network IDs.
				esc_html__( 'Network cache cleared for networks: %s', 'millicache' ),
				implode( ', ', $network_ids )
			);
		}

		// Queue site cache clearing.
		if ( '' !== $assoc_args['sites'] ) {
			$site_ids = array_map( 'intval', explode( ',', $assoc_args['sites'] ) );
			foreach ( $site_ids as $site_id ) {
				$clear->sites( $site_id, null, $expire );
			}
			$messages[] = sprintf(
				// translators: %s is a comma-separated list of site IDs.
				esc_html__( 'Site cache cleared for sites: %s', 'millicache' ),
				implode( ', ', $site_ids )
			);
		}

		// Queue cache clearing by post-IDs.
		if ( '' !== $assoc_args['ids'] ) {
			$post_ids = array_map( 'intval', explode( ',', $assoc_args['ids'] ) );
			foreach ( $post_ids as $post_id ) {
				$clear->posts( $post_id, $expire );
			}
			$messages[] = sprintf(
				// translators: %s is a comma-separated list of post IDs.
				esc_html__( 'Post cache cleared for IDs: %s', 'millicache' ),
				implode( ', ', $post_ids )
			);
		}

		// Queue cache clearing by URLs.
		if ( '' !== $assoc_args['urls'] ) {
			$urls = array_map( 'trim', explode( ',', $assoc_args['urls'] ) );
			foreach ( $urls as $url ) {
				$clear->urls( $url, $expire );
			}
			$messages[] = sprintf(
				// translators: %s is a comma-separated list of URLs.
				esc_html__( 'URL cache cleared for: %s', 'millicache' ),
				implode( ', ', $urls )
			);
		}

		// Queue cache clearing by flags.
		if ( '' !== $assoc_args['flags'] ) {
			$flags = array_map( 'trim', explode( ',', $assoc_args['flags'] ) );
			foreach ( $flags as $flag ) {
				$clear->flags( $flag, $expire, false );
			}
			$messages[] = sprintf(
				// translators: %s is a comma-separated list of flags.
				esc_html__( 'Cache cleared for flags: %s', 'millicache' ),
				implode( ', ', $flags )
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
	 * Get the cache size.
	 *
	 * ## OPTIONS
	 *
	 * [--flag=<flag>]
	 * : The flag to search for. Wildcards are allowed.
	 *
	 * ## EXAMPLES
	 *
	 *     wp millicache stats --flag=1:*
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
		$size = Admin::get_cache_size( $flag, true );
		\WP_CLI::line(
			sprintf(
				// translators: %1$s is the MilliCache version, %2$s is the cache size summary, %3$s is the flag.
				__( 'MilliCache (v%1$s): %2$s for flag "%3$s".', 'millicache' ),
				$this->version,
				Admin::get_cache_size_summary_string( $size ),
				$flag
			)
		);
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

		// translators: %1$s is the Redis host, %2$d is the port, %3$d is the database number.
		\WP_CLI::line( sprintf( __( 'Connecting to Redis at %1$s:%2$d (database %3$d)...', 'millicache' ), $host, $port, $db ) );
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
}
