<?php
/**
 * Factory for the shared MilliBase Settings instance.
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

! defined( 'ABSPATH' ) && exit;

/**
 * Provides a cached singleton of the MilliBase Settings instance,
 * configured with MilliCache's defaults, slug, and encryption.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Core
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Settings {

	/**
	 * The cached MilliBase Settings instance.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var BaseSettings|null
	 */
	private static ?BaseSettings $instance = null;

	/**
	 * Get the shared MilliBase Settings instance.
	 *
	 * Creates the instance on the first call with MilliCache's configuration.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return BaseSettings The MilliBase Settings instance.
	 */
	public static function instance(): BaseSettings {
		if ( null === self::$instance ) {
			self::$instance = new BaseSettings(
				array(
					'slug'            => 'millicache',
					'constant_prefix' => 'MC',
					'encryption'      => true,
					'defaults'        => self::defaults(),
					'config_file'     => array(
						'directory' => ( defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : '' ) . '/settings/millicache/',
					),
				)
			);
		}

		return self::$instance;
	}

	/**
	 * Inject a custom MilliBase Settings instance (for testing).
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param BaseSettings $instance The instance to inject.
	 * @return void
	 */
	public static function inject( BaseSettings $instance ): void {
		self::$instance = $instance;
	}

	/**
	 * Build the raw default settings array.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private static function defaults(): array {
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
			'cache'   => array(
				'ttl'                 => 86400,     // 24 hours
				'grace'               => 2592000,   // 30 days
				'unique'              => array(),
				'nocache_paths'       => array(),
				'nocache_cookies'     => array( 'wp-*pass*', 'comment_author_*' ),
				'ignore_cookies'      => array( '_*' ),
				'ignore_request_keys' => array( '_*', 'utm_*' ),
				'debug'               => false,
				'gzip'                => true,
			),
			'rules'   => array(
				'load'  => false,
				'items' => array(),
			),
		);
	}
}
