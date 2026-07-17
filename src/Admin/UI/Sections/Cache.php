<?php
/**
 * Cache settings section configuration.
 *
 * @link       https://www.millipress.com
 * @since      1.7.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/UI/Sections
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Admin\UI\Sections;

use MilliCache\Engine\Utilities\Multisite;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides the `cache` section configuration consumed by MilliBase.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/UI/Sections
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Cache {

	/**
	 * Return the cache section configuration.
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, mixed>
	 */
	public static function config(): array {
		return array(
			'id'     => 'cache',
			'title'  => __( 'Cache Settings', 'millicache' ),

			'open'   => Multisite::is_enabled() ? true : 'ok',
			'status' => array(
				'key' => 'storage.connected',
				'ok'  => true,
			),

			'fields' => array(
				array(
					'key'     => 'cache.ttl',
					'type'    => 'unit',
					'label'   => __( 'TTL (Cache Expiry)', 'millicache' ),
					'tooltip' => __( 'Time To Live - How long cache is considered fresh before becoming stale. Fresh cache is served directly without regeneration.', 'millicache' ),
					'default' => 86400,
					'min'     => 1,
					'save'    => 'seconds',
					'units'   => array(
						array(
							'value' => 's',
							'label' => __( 'Seconds', 'millicache' ),
						),
						array(
							'value' => 'm',
							'label' => __( 'Minutes', 'millicache' ),
						),
						array(
							'value' => 'h',
							'label' => __( 'Hours', 'millicache' ),
						),
						array(
							'value' => 'd',
							'label' => __( 'Days', 'millicache' ),
						),
					),
				),
				array(
					'key'     => 'cache.grace',
					'type'    => 'unit',
					'label'   => __( 'Grace Period', 'millicache' ),
					'tooltip' => __( 'Time after TTL expiration when stale cache can still be served while new content is generated in the background. Helps reduce user wait times. 0 = disable.', 'millicache' ),
					'default' => 2592000,
					'save'    => 'seconds',
					'inline'  => true,
					'units'   => array(
						array(
							'value' => 'h',
							'label' => __( 'Hours', 'millicache' ),
						),
						array(
							'value' => 'd',
							'label' => __( 'Days', 'millicache' ),
						),
						array(
							'value' => 'w',
							'label' => __( 'Weeks', 'millicache' ),
						),
						array(
							'value' => 'mo',
							'label' => __( 'Months', 'millicache' ),
						),
					),
				),
				array(
					'key'     => 'cache.gzip',
					'type'    => 'toggle',
					'label'   => __( 'Enable Gzip Compression', 'millicache' ),
					'tooltip' => __( 'Compresses cached data to reduce storage space usage. Slightly increases CPU usage but significantly reduces memory consumption.', 'millicache' ),
					'default' => true,
				),
				array(
					'key'     => 'cache.debug',
					'type'    => 'toggle',
					'label'   => __( 'Enable Debugging', 'millicache' ),
					'tooltip' => __( 'Adds detailed debug information to response headers such as the cache flags and times.', 'millicache' ),
					'default' => false,
				),
				array(
					'key'         => 'cache.nocache_paths',
					'type'        => 'token-list',
					'label'       => __( 'No-Cache Paths', 'millicache' ),
					'tooltip'     => __( 'URL paths that are not cached. You can use * wildcards (e.g. "/shop/*") or regular expressions enclosed in / characters.', 'millicache' ),
					'placeholder' => __( 'Add path or pattern (e.g. "/shop/", "/blog/*")', 'millicache' ),
					'default'     => array(),
				),
				array(
					'key'         => 'cache.nocache_cookies',
					'type'        => 'token-list',
					'label'       => __( 'No-Cache Cookies', 'millicache' ),
					'tooltip'     => __( 'Cookies that prevent caching. Example: "session_*" will skip caching if a cookie starting with "session_" is set. * = wildcard.', 'millicache' ),
					'placeholder' => __( 'Add cookie name or pattern (e.g. "session_*")', 'millicache' ),
					'default'     => array( 'wp-*pass*', 'comment_author_*' ),
				),
				array(
					'key'         => 'cache.ignore_cookies',
					'type'        => 'token-list',
					'label'       => __( 'Ignored Cookies', 'millicache' ),
					'tooltip'     => __( 'Cookies that are ignored when creating cache keys. Example: "dark_mode" means all users share the same cache regardless of the preference. * = wildcard.', 'millicache' ),
					'placeholder' => __( 'Add cookie name or pattern (e.g. "dark_mode")', 'millicache' ),
					'default'     => array( '_*' ),
				),
				array(
					'key'         => 'cache.ignore_request_keys',
					'type'        => 'token-list',
					'label'       => __( 'Ignored Request Keys', 'millicache' ),
					'tooltip'     => __( 'URL parameters that are ignored when creating cache keys. Example: "utm_*" means analytics parameters are ignored. * = wildcard.', 'millicache' ),
					'placeholder' => __( 'Add parameter name or pattern (e.g. "utm_*")', 'millicache' ),
					'default'     => array( '_*', 'utm_*' ),
				),
			),
		);
	}
}
