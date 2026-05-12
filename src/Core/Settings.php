<?php
/**
 * Factories for MilliCache's MilliBase Settings instances.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage Core
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Core;

use MilliBase\Settings as BaseSettings;
use MilliCache\Engine\Utilities\Multisite;

! defined( 'ABSPATH' ) && exit;

/**
 * Provides cached MilliBase Settings singletons scoped per-site or per-network.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Core
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Settings {

	/**
	 * The cached per-site MilliBase Settings instance.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var BaseSettings|null
	 */
	private static ?BaseSettings $site = null;

	/**
	 * The cached network-scoped MilliBase Settings instance (multisite only).
	 *
	 * @since 1.7.0
	 * @access private
	 *
	 * @var BaseSettings|null
	 */
	private static ?BaseSettings $network = null;

	/**
	 * Get the per-site Settings instance.
	 *
	 * Holds `cache` and `rules` on multisite; on single-site it additionally
	 * holds `storage` (since there is no separate network scope to host it).
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @return BaseSettings
	 */
	public static function site(): BaseSettings {
		if ( null === self::$site ) {
			self::$site = new BaseSettings(
				array(
					'slug'            => 'millicache',
					'constant_prefix' => 'MC',
					'encryption'      => true,
					'defaults'        => self::site_defaults(),
					'config_file'     => array(
						'directory' => self::config_directory(),
					),
				)
			);
		}

		return self::$site;
	}

	/**
	 * Get the network-scoped Settings instance.
	 *
	 * On multisite this returns a separate instance backed by site options
	 * (`wp_sitemeta`) and a `_network-<id>.php` config file. On single-site
	 * the network scope doesn't exist, so this delegates to {@see self::site()}
	 * — callers can read `storage` from either without branching.
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @return BaseSettings
	 */
	public static function network(): BaseSettings {
		if ( ! Multisite::is_enabled() ) {
			return self::site();
		}

		if ( null === self::$network ) {
			self::$network = new BaseSettings(
				array(
					'slug'            => 'millicache',
					'constant_prefix' => 'MC',
					'encryption'      => true,
					'network'         => true,
					'defaults'        => self::network_defaults(),
					'config_file'     => array(
						'directory' => self::config_directory(),
					),
				)
			);
		}

		return self::$network;
	}

	/**
	 * Build the per-site default settings array.
	 *
	 * On single-site the storage module is bundled here too, since there is
	 * no separate network scope to host it.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private static function site_defaults(): array {
		$defaults = array(
			'cache' => array(
				'ttl'                 => 86400,
				'grace'               => 2592000,
				'unique'              => array(),
				'nocache_paths'       => array(),
				'nocache_cookies'     => array( 'wp-*pass*', 'comment_author_*' ),
				'ignore_cookies'      => array( '_*' ),
				'ignore_request_keys' => array( '_*', 'utm_*' ),
				'debug'               => false,
				'gzip'                => true,
			),
			'rules' => array(
				'load'  => false,
				'items' => array(),
			),
		);

		if ( ! Multisite::is_enabled() ) {
			$defaults = array_merge( self::network_defaults(), $defaults );
		}

		return $defaults;
	}

	/**
	 * Build the network-scoped default settings array.
	 *
	 * @since 1.7.0
	 * @access private
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private static function network_defaults(): array {
		return array(
			'storage' => array(
				'host'         => '127.0.0.1',
				'port'         => 6379,
				'username'     => '',
				'enc_password' => '',
				'db'           => 0,
				'persistent'   => true,
				'prefix'       => 'mll',
			),
		);
	}

	/**
	 * Inject a per-site Settings instance (for testing).
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @param BaseSettings $instance The instance to inject.
	 * @return void
	 */
	public static function inject_site( BaseSettings $instance ): void {
		self::$site = $instance;
	}

	/**
	 * Inject a network-scoped Settings instance (for testing).
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @param BaseSettings $instance The instance to inject.
	 * @return void
	 */
	public static function inject_network( BaseSettings $instance ): void {
		self::$network = $instance;
	}

	/**
	 * Resolve the directory where MilliCache's config files live.
	 *
	 * @since 1.7.0
	 * @access private
	 *
	 * @return string
	 */
	private static function config_directory(): string {
		return ( defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : '' ) . '/settings/millicache/';
	}
}
