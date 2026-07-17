<?php
/**
 * Move the `storage` module from the per-site option to the
 * network-scoped option.
 *
 * @link       https://www.millipress.com
 * @since      1.7.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Base/Migrations
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Base\Migrations;

use MilliBase\Manager;
use MilliCache\Base\Site;
use MilliCache\Engine;
use RuntimeException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Migration: copy `storage` from the main site's per-site option to the
 * network-scoped option.
 *
 * The legacy per-site `storage` key is left in place — the schema filter
 * hides it on every read, so it's invisible to consumers.
 *
 * @since      1.7.0
 * @package    MilliCache
 * @subpackage MilliCache/Base/Migrations
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class MoveStorageToNetwork {

	/**
	 * Execute the migration.
	 *
	 * @since 1.7.0
	 *
	 * @param Manager $manager The MilliBase Manager invoking the migration.
	 * @return void
	 * @throws RuntimeException On write or round-trip verification failure.
	 */
	public static function run( Manager $manager ): void {

		if ( ! is_multisite() ) {
			return;
		}

		$main_id = get_main_site_id();

		switch_to_blog( $main_id );
		$per_site = Site::settings()->read_raw();
		restore_current_blog();

		$storage = $per_site['storage'] ?? null;
		if ( ! is_array( $storage ) || empty( $storage ) ) {
			return;
		}

		if ( ! $manager->settings()->update( array( 'storage' => $storage ) ) ) {
			throw new RuntimeException( 'Failed to write storage to network option.' );
		}

		$read_back = $manager->settings()->get( 'storage' );
		if ( ! is_array( $read_back ) || empty( $read_back ) ) {
			throw new RuntimeException( 'Round-trip verification failed; legacy storage left intact.' );
		}

		do_action(
			'millicache_admin_notice',
			sprintf(
				/* translators: %s: URL to the network admin MilliCache settings page. */
				__( 'MilliCache: Multisite detected. Your storage settings have been moved to the network level — <a href="%s">manage them under Network Admin → MilliCache</a>.', 'millicache' ),
				esc_url( network_admin_url( 'settings.php?page=millicache' ) )
			),
			'success'
		);

		Engine::instance()->logger()->debug( sprintf( 'Migration "move_storage_to_network" succeeded for main site %d.', $main_id ) );
	}
}
