<?php
/**
 * CLI command for creating and fixing WordPress drop-ins.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/CLI
 */

namespace MilliCache\Admin\CLI;

use MilliCache\Admin\DropIn;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Drop command.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/CLI
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Drop {

	/**
	 * Fix or reinstall the MilliCache drop-ins.
	 *
	 * ## DESCRIPTION
	 *
	 * Removes and recreates the drop-in files in wp-content. Useful for CD
	 * workflows where symlinks may break between deploys. Handles
	 * advanced-cache.php, and object-cache.php when MilliCache Pro is active.
	 *
	 * ## OPTIONS
	 *
	 * [<dropin>]
	 * : Which drop-in to reinstall: advanced-cache or object-cache. Defaults to all.
	 *
	 * [--force]
	 * : Force reinstall even if the current version matches.
	 *
	 * ## EXAMPLES
	 *
	 *     wp millicache drop
	 *     wp millicache drop --force
	 *     wp millicache drop object-cache --force
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
		$force  = isset( $assoc_args['force'] );
		$target = isset( $args[0] ) ? str_replace( '.php', '', $args[0] ) : 'all';

		if ( ! in_array( $target, array( 'all', 'advanced-cache', 'object-cache' ), true ) ) {
			\WP_CLI::error(
				sprintf(
					/* translators: %s: the invalid drop-in name given on the command line. */
					__( 'Unknown drop-in "%s". Use advanced-cache or object-cache.', 'millicache' ),
					$target
				)
			);
		}

		if ( 'all' === $target || 'advanced-cache' === $target ) {
			$this->reinstall_advanced_cache( $force );
		}

		/**
		 * Reinstall drop-ins owned by extensions, e.g. Pro's object-cache.php.
		 *
		 * Handlers should act only when $target is 'all' or their own drop-in
		 * name, and report their result via WP_CLI themselves.
		 *
		 * @since 1.7.3
		 *
		 * @param string $target Requested drop-in: 'all', 'advanced-cache', or 'object-cache'.
		 * @param bool   $force  Whether --force was passed.
		 */
		do_action( 'millicache_drop_dropin', $target, $force );
	}

	/**
	 * Reinstall the advanced-cache.php drop-in and report the outcome.
	 *
	 * @since 1.7.3
	 *
	 * @param bool $force Whether to override the higher-version safeguard.
	 * @return void
	 */
	private function reinstall_advanced_cache( bool $force ): void {
		$result   = DropIn::install( 'advanced-cache.php', null, $force );
		$describe = DropIn::describe( $result, 'advanced-cache.php' );
		$message  = $describe['message'];

		if ( 'preserved' === $result ) {
			$message .= ' ' . __( 'Use --force to overwrite.', 'millicache' );
		}

		switch ( $describe['status'] ) {
			case 'success':
				\WP_CLI::success( $message );
				return;
			case 'warning':
				\WP_CLI::warning( $message );
				return;
			default:
				\WP_CLI::error( $message );
		}
	}
}
