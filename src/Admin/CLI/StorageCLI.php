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

! defined( 'ABSPATH' ) && exit;

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

		// Get storage settings.
		$storage_settings = millicache()->get_settings( 'storage' );

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

		$is_socket = str_starts_with( $host, '/' );

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

		// Launch interactive session using passthru for proper TTY handling.
		passthru( $command );
	}
}
