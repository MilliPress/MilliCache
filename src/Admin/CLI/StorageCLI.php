<?php
/**
 * CLI command for interactive storage CLI.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/CLI
 */

namespace MilliCache\Admin\CLI;

use MilliCache\Core\Connection;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Storage command (interactive Redis CLI).
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/CLI
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class StorageCLI {

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
	 *
	 * @param array<string> $args The list of arguments.
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		// Check if redis-cli is available.
		$redis_cli = trim( (string) shell_exec( 'which redis-cli 2>/dev/null' ) );
		if ( empty( $redis_cli ) ) {
			\WP_CLI::error( __( 'redis-cli is not installed or not in PATH. Please install Redis tools.', 'millicache' ) );
		}

		// Get storage settings and resolve the topology.
		$storage_settings = millicache()->get_settings( 'storage' );
		$topology         = ( new Connection( $storage_settings ) )->describe();
		$mode             = is_string( $topology['mode'] ?? null ) ? $topology['mode'] : 'single';

		// redis-cli targets a single node, so advanced modes need narrowing.
		if ( 'disabled' === $mode ) {
			$reason = is_string( $topology['reason'] ?? null ) && '' !== $topology['reason']
				? $topology['reason']
				: __( 'MC_STORAGE_HOST is misconfigured.', 'millicache' );
			\WP_CLI::error( $reason . ' ' . __( 'The cache is disabled.', 'millicache' ) );
		}

		if ( 'sentinel' === $mode ) {
			\WP_CLI::error( __( 'Interactive CLI is not available for Sentinel topologies. Connect redis-cli to a specific data node directly.', 'millicache' ) );
		}

		if ( 'single' === $mode ) {
			$host = is_string( $topology['host'] ?? null ) ? $topology['host'] : '127.0.0.1';
			$port = is_numeric( $topology['port'] ?? null ) ? (int) $topology['port'] : 6379;
		} else {
			// Replication: describe() lists the master first.
			$nodes  = is_array( $topology['nodes'] ?? null ) ? $topology['nodes'] : array();
			$master = is_array( $nodes[0] ?? null ) ? $nodes[0] : array();
			$host   = is_string( $master['host'] ?? null ) ? $master['host'] : '127.0.0.1';
			$port   = isset( $master['port'] ) && is_numeric( $master['port'] ) ? (int) $master['port'] : 6379;
		}

		$db       = is_int( $storage_settings['db'] ?? null ) ? $storage_settings['db'] : 0;
		$password = is_string( $storage_settings['enc_password'] ?? null ) ? $storage_settings['enc_password'] : '';

		$is_socket = 0 === strpos( $host, '/' );

		\WP_CLI::line(
			sprintf(
				$is_socket
				// translators: %1$d is the database number, %2$s is the socket path.
				? __( 'Connecting to database %1$d at %2$s...', 'millicache' )
				// translators: %1$d is the database number, %2$s is the host, %3$d is the port.
				: __( 'Connecting to database %1$d at %2$s:%3$d...', 'millicache' ),
				$db,
				$host,
				$port
			)
		);

		// Build the redis-cli host/socket flags.
		$host_flags = $is_socket
			? sprintf( '-s %s', escapeshellarg( $host ) )
			: sprintf( '-h %s -p %d', escapeshellarg( $host ), $port );

		// Test connection with timeout before launching an interactive session.
		$test_command = sprintf( 'timeout 5 redis-cli %s PING 2>&1', $host_flags );

		// Add the password to the test command if set.
		if ( '' !== $password ) {
			$test_command = sprintf(
				'timeout 5 redis-cli %s -a %s --no-auth-warning PING 2>&1',
				$host_flags,
				escapeshellarg( $password )
			);
		}

		$test_result = trim( (string) shell_exec( $test_command ) );

		if ( 'PONG' !== $test_result ) {
			$error_msg = '' !== $test_result ? $test_result : __( 'Connection timed out', 'millicache' );
			\WP_CLI::error(
				sprintf(
					// translators: %1$s is the host or socket path, %2$s is the error message.
					__( 'Cannot connect to Redis at %1$s - %2$s', 'millicache' ),
					$is_socket ? $host : "$host:$port",
					$error_msg
				)
			);
		}

		// Build the redis-cli command.
		$command = sprintf( 'redis-cli %s -n %d', $host_flags, $db );

		// Add a password if set.
		if ( '' !== $password ) {
			$command .= sprintf( ' -a %s --no-auth-warning', escapeshellarg( $password ) );
		}

		\WP_CLI::line( __( 'Type "quit" to exit.', 'millicache' ) );
		\WP_CLI::line( '' );

		// Launch the interactive session with the real terminal.
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_proc_open -- Interactive redis-cli session in WP-CLI context only; the command is escapeshellarg-escaped.
		$process = proc_open( $command, array( STDIN, STDOUT, STDERR ), $pipes );

		if ( is_resource( $process ) ) {
			proc_close( $process );
		}
	}
}
