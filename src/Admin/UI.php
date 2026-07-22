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
use MilliCache\Core\Loader;
use MilliCache\Engine;
use MilliCache\Admin\SiteHealth;
use MilliCache\Admin\UI\CacheActions;
use MilliCache\Admin\UI\StatusBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	 * @param Loader $loader      The hook loader (used by Site Health filter wiring).
	 * @param Engine $engine      The MilliCache engine instance.
	 * @param string $plugin_name The plugin slug.
	 * @param string $version     The plugin version.
	 */
	public function __construct( Loader $loader, Engine $engine, string $plugin_name, string $version ) {
		$status_builder = new StatusBuilder( $engine, $plugin_name, $version );
		$cache_actions  = new CacheActions( $engine );

		new SiteHealth( $loader, $status_builder );

		if ( $engine->multisite()->is_enabled() ) {
			new Base\Network( $plugin_name, $version, $status_builder, $cache_actions );
		}

		new Base\Site( $plugin_name, $version, $status_builder, $cache_actions );
	}
}
