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

use MilliCache\Base;
use MilliCache\Engine;
use MilliCache\Admin\UI\CacheActions;
use MilliCache\Admin\UI\StatusBuilder;

! defined( 'ABSPATH' ) && exit;

/**
 * Constructs the shared dependencies (StatusBuilder, CacheActions) and
 * the per-site and (on multisite) network MilliBase Managers.
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
	 * are available immediately. Each Manager's config closure defers
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

		if ( $engine->multisite()->is_enabled() ) {
			new Base\Network( $plugin_name, $version, $status_builder, $cache_actions );
		}

		new Base\Site( $plugin_name, $version, $status_builder, $cache_actions );
	}
}
