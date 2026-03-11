<?php
/**
 * Static admin utility methods for MilliCache.
 *
 * Provides asset enqueueing, cache-size helpers,
 * and advanced-cache.php validation.
 *
 * @link       https://www.millipress.com
 * @since      1.3.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Admin;

use MilliCache\Engine;

! defined( 'ABSPATH' ) && exit;

/**
 * Static admin utility methods for MilliCache.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Utils {

	/**
	 * Helper method to enqueue assets.
	 *
	 * @since    1.0.0
	 * @access   public static
	 *
	 * @param string        $asset_name  The asset name without extension.
	 * @param array<string> $js_deps     An array of JavaScript dependencies to include.
	 * @param array<string> $css_deps    An array of CSS dependencies to include.
	 * @param string        $basename    Plugin basename for asset resolution.
	 *                                   Defaults to MILLICACHE_BASENAME.
	 *
	 * @return bool True if assets were successfully enqueued, false otherwise.
	 */
	public static function enqueue_assets( string $asset_name, array $js_deps = array(), array $css_deps = array(), string $basename = '' ): bool {
		if ( ! $basename && ! defined( 'MILLICACHE_BASENAME' ) ) {
			return false;
		}

		if ( ! $basename ) {
			$basename = MILLICACHE_BASENAME;
		}

		$asset_file = plugin_dir_path( WP_PLUGIN_DIR . '/' . $basename ) . '/build/' . $asset_name . '.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return false;
		}

		$asset = include $asset_file;

		wp_enqueue_style(
			"millicache-{$asset_name}",
			plugins_url( 'build/' . $asset_name . '.css', $basename ),
			$css_deps,
			$asset['version']
		);

		wp_enqueue_script(
			"millicache-{$asset_name}",
			plugins_url( 'build/' . $asset_name . '.js', $basename ),
			array_merge( $asset['dependencies'], $js_deps ),
			$asset['version'],
			array( 'in_footer' => true )
		);

		return true;
	}

	/**
	 * Get the version of a file.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param string $file_path The path to the file.
	 * @return null|string The version of the file.
	 */
	public static function get_file_version( string $file_path ): ?string {
		$version = get_file_data( $file_path, array( 'Version' => 'Version' ) );
		return $version['Version'] ?? null;
	}

	/**
	 * Validate the advanced-cache.php file.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @return array<string, bool|string> The validation information or empty array if the file doesn't exist.
	 */
	public static function validate_advanced_cache_file(): array {
		$info = array(
			'type'     => 'file',
			'custom'   => false,
			'outdated' => false,
		);

		$destination = WP_CONTENT_DIR . '/advanced-cache.php';

		if ( is_link( $destination ) ) {
			$info['type'] = 'symlink';
			$destination  = readlink( $destination );
		}

		if ( ! file_exists( (string) $destination ) ) {
			return array();
		}

		if ( 'symlink' !== $info['type'] ) {
			$source = dirname( plugin_dir_path( __FILE__ ) ) . '/advanced-cache.php';

			// Compare the file with the plugin version.
			$source_version      = self::get_file_version( $source );
			$destination_version = self::get_file_version( (string) $destination );

			// Compare versions.
			if ( $source_version && $destination_version ) {
				if ( version_compare( $source_version, $destination_version ) > 0 ) {
					$info['outdated'] = true;
				}
			}

			// Compare file content.
			$source_content      = file_get_contents( $source );
			$destination_content = file_get_contents( (string) $destination );

			if ( $source_content && $destination_content ) {
				$info['custom'] = $source_content !== $destination_content;
			}
		}

		return $info;
	}

	/**
	 * Get the size of the cache.
	 *
	 * @since   1.0.0
	 * @access  public static
	 *
	 * @param string $flag The flag to search for. Wildcards are allowed.
	 * @param bool   $reload Whether to reload the cache size from the storage server.
	 * @return array{index: int, size: int, size_human: string} The index and memory size of the cache.
	 */
	public static function get_cache_size( string $flag = '', bool $reload = false ): array {
		$size = get_site_transient( "millicache_size_$flag" );

		if ( ! is_array( $size ) || $reload ) {
			$storage = Engine::instance()->storage();
			$size    = $storage->get_cache_size( $flag );

			if ( $size ) {
				set_site_transient( "millicache_size_$flag", $size, 12 * HOUR_IN_SECONDS );
			}
		}

		// Get size in bytes.
		$bytes = $size['size'] ?? 0;

		return array(
			'index'      => $size['index'] ?? 0,
			'size'       => $bytes,
			'size_human' => (string) size_format(
				$bytes,
				$bytes > 1048576 ? 2 : 0
			),
		);
	}

	/**
	 * Get a summary string for the cache size.
	 *
	 * @since   1.0.0
	 * @access  public static
	 *
	 * @param ?array{index: int, size: int, size_human: string} $size The size of the cache.
	 * @return string The summary string.
	 */
	public static function get_cache_size_summary_string( ?array $size = null ): string {
		if ( ! $size ) {
			$size = self::get_cache_size( Engine::instance()->flags()->get_prefix( is_network_admin() ? '*' : null ) . '*' );
		}

		if ( $size['size'] > 0 ) {
			return sprintf(
				// translators: %1$s is the number of pages, %2$s is singular or plural "page", %3$s is the cache size.
				__( '%1$s %2$s (%3$s)', 'millicache' ),
				number_format_i18n( $size['index'] ),
				_n( 'page', 'pages', $size['index'], 'millicache' ),
				$size['size_human']
			);
		}

		return __( 'No cached pages', 'millicache' );
	}
}
