<?php
/**
 * Builds the `/status` REST payload for the MilliCache settings pages.
 *
 * @link       https://www.millipress.com
 * @since      1.7.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/UI
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Admin\UI;

use MilliCache\Admin\Utils;
use MilliCache\Engine;

! defined( 'ABSPATH' ) && exit;

/**
 * Assembles the status payload returned by each settings page's `/status`
 * endpoint.
 *
 * Per-site multisite returns just the connection up/down indicator and
 * per-site cache numbers — connection details, drop-in info, and storage
 * server stats are install-wide and live on the Network Admin Status tab.
 * Single-site and Network Admin get the full payload.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/UI
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class StatusBuilder {

	/**
	 * The MilliCache engine instance.
	 *
	 * @since 1.7.0
	 * @var Engine
	 */
	private Engine $engine;

	/**
	 * The plugin slug.
	 *
	 * @since 1.7.0
	 * @var string
	 */
	private string $plugin_name;

	/**
	 * The plugin version.
	 *
	 * @since 1.7.0
	 * @var string
	 */
	private string $version;

	/**
	 * Construct a StatusBuilder.
	 *
	 * @since 1.7.0
	 *
	 * @param Engine $engine      The cache engine.
	 * @param string $plugin_name Plugin slug.
	 * @param string $version     Plugin version.
	 */
	public function __construct( Engine $engine, string $plugin_name, string $version ) {
		$this->engine      = $engine;
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Build the status payload.
	 *
	 * @since 1.7.0
	 *
	 * @param bool                  $network_admin True when called from the Network Admin status endpoint.
	 * @param \WP_REST_Request|null $request       Per-site request, used only to read the optional `network` query param.
	 * @phpstan-param \WP_REST_Request<array<string, mixed>>|null $request
	 * @return array<string, mixed>
	 */
	public function build( bool $network_admin, ?\WP_REST_Request $request = null ): array {
		$network_cache = $network_admin || ( $request && $request->get_param( 'network' ) === 'true' );

		$payload = array(
			'plugin_name' => $this->plugin_name,
			'version'     => $this->version,
			'cache'       => $this->engine->cache()->get_status( $network_cache ),
		);

		// Per-site multisite: connection up/down only.
		if ( ! $network_admin && $this->engine->multisite()->is_enabled() ) {
			$payload['storage'] = array( 'connected' => $this->engine->storage()->is_connected() );
			return $payload;
		}

		return array_merge(
			$payload,
			array(
				'storage' => $this->engine->storage()->get_status(),
				'dropin'  => Utils::validate_advanced_cache_file(),
			)
		);
	}
}
