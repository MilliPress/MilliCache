<?php
/**
 * Self-hosted plugin update checker
 *
 * Hooks into WordPress's native update system to check a remote endpoint
 * for new plugin versions, enabling seamless updates for GitHub-distributed plugins.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage Core
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Core;

! defined( 'ABSPATH' ) && exit;

/**
 * Self-hosted plugin update checker.
 *
 * Checks a remote Laravel endpoint for new versions and injects update data
 * into WordPress's native update transient, providing one-click updates
 * for plugins distributed via GitHub Releases.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @subpackage Core
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Updater {

	/**
	 * Remote endpoint URL for plugin update information.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private const ENDPOINT_URL = 'https://millipress.com/api/plugins/millicache/info';

	/**
	 * Transient key for caching remote update info.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private const TRANSIENT_KEY = 'millicache_update_info';

	/**
	 * Cache duration in seconds (12 hours).
	 *
	 * @since 1.0.0
	 * @var   int
	 */
	private const CACHE_DURATION = 43200;

	/**
	 * Initialize the updater and register hooks.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param Loader $loader The plugin hook loader.
	 */
	public function __construct( Loader $loader ) {
		$loader->add_filter( 'site_transient_update_plugins', $this, 'check_for_update' );
		$loader->add_filter( 'plugins_api', $this, 'plugin_information', 10, 3 );
		$loader->add_action( 'delete_site_transient_update_plugins', $this, 'clear_update_cache' );
	}

	/**
	 * Inject update data into the WordPress update transient.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param  mixed $transient The update_plugins transient data.
	 * @return mixed The potentially modified transient data.
	 */
	public function check_for_update( $transient ) {
		if ( ! $transient instanceof \stdClass ) {
			return $transient;
		}

		if ( ! defined( 'MILLICACHE_VERSION' ) || ! defined( 'MILLICACHE_BASENAME' ) ) {
			return $transient;
		}

		$remote = $this->get_remote_info();

		if ( null === $remote || ! isset( $remote->version ) ) {
			return $transient;
		}

		$update              = new \stdClass();
		$update->slug        = 'millicache';
		$update->plugin      = MILLICACHE_BASENAME;

		if ( version_compare( MILLICACHE_VERSION, $remote->version, '<' ) ) {
			$update->new_version = $remote->version;
			$update->url         = $remote->homepage ?? '';
			$update->package     = $remote->download_url ?? '';
			$update->tested      = $remote->tested ?? '';
			$update->requires    = $remote->requires ?? '';

			if ( isset( $remote->icons ) ) {
				$update->icons = (array) $remote->icons;
			}

			if ( isset( $remote->banners ) ) {
				$update->banners = (array) $remote->banners;
			}

			$transient->response[ MILLICACHE_BASENAME ] = $update;
		} else {
			$update->new_version = MILLICACHE_VERSION;
			$update->url         = $remote->homepage ?? '';
			$update->package     = '';

			$transient->no_update[ MILLICACHE_BASENAME ] = $update;
		}

		return $transient;
	}

	/**
	 * Serve plugin information for the "View Details" modal.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param  false|object|array<mixed> $result The result object or array. Default false.
	 * @param  string                    $action The type of information being requested.
	 * @param  object                    $args   Plugin API arguments.
	 * @return false|object|array<mixed> The plugin info object, or the unmodified result.
	 */
	public function plugin_information( $result, $action, $args ) {
		if ( 'plugin_information' !== $action ) {
			return $result;
		}

		if ( ! isset( $args->slug ) || 'millicache' !== $args->slug ) {
			return $result;
		}

		$remote = $this->get_remote_info();

		if ( null === $remote ) {
			return $result;
		}

		$info               = new \stdClass();
		$info->name         = $remote->name ?? 'MilliCache';
		$info->slug         = 'millicache';
		$info->version      = $remote->version ?? '';
		$info->author       = $remote->author ?? '';
		$info->author_profile = $remote->author_profile ?? '';
		$info->homepage     = $remote->homepage ?? '';
		$info->requires     = $remote->requires ?? '';
		$info->tested       = $remote->tested ?? '';
		$info->requires_php = $remote->requires_php ?? '';
		$info->download_link = $remote->download_url ?? '';
		$info->last_updated = $remote->last_updated ?? '';

		if ( isset( $remote->sections ) ) {
			$info->sections = (array) $remote->sections;
		}

		if ( isset( $remote->banners ) ) {
			$info->banners = (array) $remote->banners;
		}

		if ( isset( $remote->icons ) ) {
			$info->icons = (array) $remote->icons;
		}

		return $info;
	}

	/**
	 * Clear the cached update info when WordPress force-checks.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function clear_update_cache(): void {
		delete_site_transient( self::TRANSIENT_KEY );
	}

	/**
	 * Fetch and cache remote plugin information.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @return object|null The decoded remote info, or null on failure.
	 */
	private function get_remote_info(): ?object {
		/**
		 * Filters whether update checks are enabled.
		 *
		 * Evaluated here, at update-check time, rather than when the Updater is
		 * constructed (plugin-include time), so that a filter added from a
		 * theme's functions.php or another plugin is registered in time to be
		 * honored. Returning false stops the remote request and hides the update notice.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $enabled Whether to enable update checks. Default true.
		 */
		if ( ! apply_filters( 'millicache_updates', true ) ) {
			return null;
		}

		$cached = get_site_transient( self::TRANSIENT_KEY );

		if ( false !== $cached && is_object( $cached ) ) {
			return $cached;
		}

		$url = self::ENDPOINT_URL;

		// Opt in to prerelease builds by defining MC_UPDATE_PRERELEASE as true.
		if ( defined( 'MC_UPDATE_PRERELEASE' ) && MC_UPDATE_PRERELEASE ) {
			$url = add_query_arg( 'prerelease', '1', $url );
		}

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept' => 'application/json',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $code ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body );

		if ( ! is_object( $data ) || ! isset( $data->version ) ) {
			return null;
		}

		set_site_transient( self::TRANSIENT_KEY, $data, self::CACHE_DURATION );

		return $data;
	}
}
