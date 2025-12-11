<?php
/**
 * CLI command for testing storage connection.
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
 * Test command.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/CLI
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Test {

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
	 *
	 * @param array<string> $args The list of arguments.
	 * @param array<string> $assoc_args The list of associative arguments.
	 * @return void
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		\WP_CLI::line( __( 'Testing Redis connection...', 'millicache' ) );
		\WP_CLI::line( '' );

		$storage_settings = millicache()->get_settings( 'storage' );

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
		$storage = millicache()->storage();
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
