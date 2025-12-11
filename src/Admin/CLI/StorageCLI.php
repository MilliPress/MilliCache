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
	 *     wp millicache storage
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

		// translators: %1$s is the Storage DB number, %2$d is the host, %3$d is the port.
		\WP_CLI::line( sprintf( __( 'Connecting to database %1$d at %2$s:%3$d...', 'millicache' ), $db, $host, $port ) );

		// Test connection with timeout before launching an interactive session.
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
