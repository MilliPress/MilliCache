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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	 * @param string        $text_domain Text domain used to load the script's
	 *                                   translations. Defaults to 'millicache'.
	 *
	 * @return bool True if assets were successfully enqueued, false otherwise.
	 */
	public static function enqueue_assets( string $asset_name, array $js_deps = array(), array $css_deps = array(), string $basename = '', string $text_domain = 'millicache' ): bool {
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

		$js_dependencies = array_merge( $asset['dependencies'], $js_deps );

		wp_enqueue_script(
			"millicache-{$asset_name}",
			plugins_url( 'build/' . $asset_name . '.js', $basename ),
			$js_dependencies,
			$asset['version'],
			array( 'in_footer' => true )
		);

		// Load the JSON translations for scripts that use @wordpress/i18n.
		if ( in_array( 'wp-i18n', $js_dependencies, true ) ) {
			wp_set_script_translations( "millicache-{$asset_name}", $text_domain );
		}

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
	 * @return array{index: int, size: int, size_human: string, gross: int, gross_human: string, raw: int, raw_human: string, saved: int, saved_human: string, unique: int, largest: int, largest_human: string} The cache index, net size, gross (pre-dedup) size, pre-dedup uncompressed size, dedup-only savings, unique-body count, and largest body.
	 */
	public static function get_cache_size( string $flag = '', bool $reload = false ): array {
		$size = get_site_transient( "millicache_sizes_$flag" );

		if ( ! is_array( $size ) || $reload ) {
			$storage = Engine::instance()->storage();
			$size    = $storage->get_cache_size( $flag );

			if ( $size ) {
				set_site_transient( "millicache_sizes_$flag", $size, 12 * HOUR_IN_SECONDS );
			}
		}

		$bytes     = isset( $size['size'] ) && is_numeric( $size['size'] ) ? (int) $size['size'] : 0;
		$gross     = isset( $size['gross'] ) && is_numeric( $size['gross'] ) ? (int) $size['gross'] : $bytes;
		$raw       = isset( $size['raw'] ) && is_numeric( $size['raw'] ) ? (int) $size['raw'] : $gross;
		$largest   = isset( $size['largest'] ) && is_numeric( $size['largest'] ) ? (int) $size['largest'] : 0;
		$saved     = max( 0, $gross - $bytes );

		return array(
			'index'         => isset( $size['index'] ) && is_numeric( $size['index'] ) ? (int) $size['index'] : 0,
			'size'          => $bytes,
			'size_human'    => (string) size_format( $bytes, $bytes > 1048576 ? 2 : 0 ),
			'gross'         => $gross,
			'gross_human'   => (string) size_format( $gross, $gross > 1048576 ? 2 : 0 ),
			'raw'           => $raw,
			'raw_human'     => (string) size_format( $raw, $raw > 1048576 ? 2 : 0 ),
			'saved'         => $saved,
			'saved_human'   => (string) size_format( $saved, $saved > 1048576 ? 2 : 0 ),
			'unique'        => isset( $size['unique'] ) && is_numeric( $size['unique'] ) ? (int) $size['unique'] : 0,
			'largest'       => $largest,
			'largest_human' => (string) size_format( $largest, $largest > 1048576 ? 2 : 0 ),
		);
	}

	/**
	 * Get a summary string for the cache size.
	 *
	 * @since   1.0.0
	 * @access  public static
	 *
	 * @param ?array{index: int, size: int, size_human: string, gross?: int, gross_human?: string, saved?: int, saved_human?: string, unique?: int, largest?: int, largest_human?: string} $size The size of the cache.
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
