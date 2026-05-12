<?php
/**
 * Entry point for the MilliCache admin UI.
 *
 * @link       https://www.millipress.com
 * @since      1.3.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Admin;

use MilliCache\Admin\UI\CacheActions;
use MilliCache\Admin\UI\Pages;
use MilliCache\Admin\UI\StatusBuilder;
use MilliCache\Engine;

! defined( 'ABSPATH' ) && exit;

/**
 * Constructs the shared dependencies (StatusBuilder, CacheActions) and
 * the per-site and (on multisite) Network Admin pages.
 *
 * Each Page registers its own MilliBase Manager from its constructor.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class UI {

	/**
	 * Initialize the UI and boot the MilliBase facade.
	 *
	 * Managers are created at plugin load time, so schema-derived defaults
	 * are available immediately. Each Page's config closure defers
	 * translation calls to `init`.
	 *
	 * @since 1.3.0
	 *
	 * @param Engine $engine      The MilliCache engine instance.
	 * @param string $plugin_name The plugin slug.
	 * @param string $version     The plugin version.
	 */
	public function __construct( Engine $engine, string $plugin_name, string $version ) {
		$status_builder = new StatusBuilder( $engine, $plugin_name, $version );
		$cache_actions  = new CacheActions( $engine );

		new Pages\Site( $plugin_name, $version, $status_builder, $cache_actions );

		if ( $engine->multisite()->is_enabled() ) {
			new Pages\Network( $plugin_name, $version, $status_builder, $cache_actions );
		}
	}
}
