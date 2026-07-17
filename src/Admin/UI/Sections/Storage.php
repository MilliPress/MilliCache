<?php
/**
 * Storage server settings section configuration.
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
 * Provides the `storage` section configuration consumed by MilliBase.
 *
 * On multisite this section lives on the Network Admin page (network-scoped
 * connection); on single-site it lives on the per-site settings page.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/UI/Sections
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Storage {

	/**
	 * Return the storage section configuration.
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, mixed>
	 */
	public static function config(): array {
		$host      = millicache()->get_settings( 'storage' )['host'] ?? null;
		$multinode = is_array( $host );

		$fields = array(
			array(
				'key'      => 'storage.host',
				'type'     => 'text',
				'label'    => __( 'Server Host', 'millicache' ),
				'tooltip'  => __( 'The hostname or IP address of your Redis, Valkey, KeyDB, or other compatible server. Typically "localhost" or "127.0.0.1" for local servers.', 'millicache' ),
				'default'  => '127.0.0.1',
				'preserve' => true,
			),
			array(
				'key'      => 'storage.port',
				'type'     => 'number',
				'hide'     => array( 'storage.host', '/*' ),
				'label'    => __( 'Server Port', 'millicache' ),
				'tooltip'  => __( 'The port your storage server listens on. Default is 6379 for most installations.', 'millicache' ),
				'default'  => 6379,
				'min'      => 1024,
				'max'      => 65535,
				'inline'   => true,
				'width'    => '120px',
				'preserve' => true,
			),
			array(
				'key'      => 'storage.username',
				'type'     => 'text',
				'label'    => __( 'Username', 'millicache' ),
				'tooltip'  => __( 'Username used to authenticate with your Redis, Valkey, or other compatible server. Leave empty if your server does not use ACL with named users.', 'millicache' ),
				'default'  => '',
				'preserve' => true,
			),
			array(
				'key'      => 'storage.enc_password',
				'type'     => 'password',
				'label'    => __( 'Password', 'millicache' ),
				'tooltip'  => __( 'Password used to authenticate with your Redis, Valkey, or other compatible server. Leave empty if your server does not require authentication.', 'millicache' ),
				'default'  => '',
				'inline'   => true,
				'preserve' => true,
			),
			array(
				'key'      => 'storage.db',
				'type'     => 'number',
				'label'    => __( 'Database ID', 'millicache' ),
				'tooltip'  => __( 'The database to use within your storage server (typically 0-15, with 0 being the default).', 'millicache' ),
				'default'  => 0,
				'min'      => 0,
				'max'      => 15,
				'inline'   => true,
				'width'    => '120px',
				'preserve' => true,
			),
			array(
				'key'      => 'storage.persistent',
				'type'     => 'toggle',
				'label'    => __( 'Persistent Storage Connection', 'millicache' ),
				'tooltip'  => __( 'When enabled, maintains a persistent connection to the server instead of creating a new connection for each request. Improves performance but uses more server resources.', 'millicache' ),
				'default'  => true,
				'preserve' => true,
			),
		);

		// A multi-node array supersedes the scalar host/port (which would choke a
		// text field), so drop those two; the shared credentials stay editable.
		if ( $multinode ) {
			$fields = array_values(
				array_filter(
					$fields,
					fn( array $field ) => ! in_array( $field['key'], array( 'storage.host', 'storage.port' ), true )
				)
			);
		}

		$config = array(
			'id'     => 'storage',
			'title'  => __( 'Storage Server', 'millicache' ),

			'open'   => Multisite::is_enabled() ? true : 'error',
			'status' => array(
				'key'   => 'storage.connected',
				'ok'    => true,
				'badge' => array(
					'ok'    => __( 'Connected', 'millicache' ),
					'error' => __( 'Disconnected', 'millicache' ),
				),
			),

			'fields' => $fields,
		);

		if ( $multinode ) {
			$config['intro'] = __( 'This storage connection is configured via a constant. The credentials below apply to every node.', 'millicache' );
		}

		return $config;
	}
}
