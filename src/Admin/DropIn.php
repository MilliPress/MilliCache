<?php
/**
 * Lifecycle of WordPress drop-ins (advanced-cache.php, object-cache.php).
 *
 * Single canonical place where a drop-in is installed (symlink or copy)
 * and located on disk. Activator, Deactivator, and the `wp millicache drop`
 * CLI command all route through this class, so the installation behavior
 * stays identical across paths.
 *
 * Drop-ins installed via copy must use `$engine_path` or `$plugin_path` to
 * resolve their plugin root — install() rewrites that assignment so the copy
 * still bootstraps from the original plugin folder.
 *
 * @link       https://www.millipress.com
 * @since      1.5.3
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin
 */

namespace MilliCache\Admin;

! defined( 'ABSPATH' ) && exit;

/**
 * Lifecycle of WordPress drop-ins (advanced-cache.php).
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin
 * @author Philipp Wellmer <hello@millipress.com>
 */
final class DropIn {

	/**
	 * Absolute path to the drop-in destination in wp-content.
	 *
	 * @param string $filename Drop-in filename (e.g. 'advanced-cache.php', 'object-cache.php').
	 */
	public static function destination( string $filename = 'advanced-cache.php' ): string {
		return WP_CONTENT_DIR . '/' . $filename;
	}

	/**
	 * Whether a drop-in is currently present at the destination.
	 *
	 * Is_link() catches broken symlinks, where file_exists() returns false.
	 *
	 * @param string $filename Drop-in filename ('advanced-cache.php').
	 */
	public static function exists( string $filename = 'advanced-cache.php' ): bool {
		$destination = self::destination( $filename );
		return file_exists( $destination ) || is_link( $destination );
	}

	/**
	 * Install a drop-in pointing at the given source plugin folder.
	 *
	 * A symlink already pointing at the source is reported as 'unchanged'.
	 * A plain-copy destination with a higher version is preserved as
	 * 'preserved' unless $force is true — guards user customizations from
	 * being silently clobbered on activation or `wp millicache drop`.
	 *
	 * @param string      $filename   Drop-in filename ('advanced-cache.php').
	 * @param string|null $source_dir Absolute path to the plugin folder containing $filename. Defaults to MILLICACHE_DIR.
	 * @param bool        $force      Override the higher-version safeguard.
	 * @return null|string 'symlinked', 'copied', 'unchanged', 'preserved', 'unwritable', or null on failure.
	 */
	public static function install( string $filename = 'advanced-cache.php', ?string $source_dir = null, bool $force = false ): ?string {
		if ( ! is_writable( WP_CONTENT_DIR ) ) {
			return 'unwritable';
		}

		$source_dir  = $source_dir ?? MILLICACHE_DIR;
		$source_file = $source_dir . '/' . $filename;
		$destination = self::destination( $filename );

		if ( ! is_readable( $source_file ) ) {
			return null;
		}

		if ( self::exists( $filename ) ) {
			if ( is_link( $destination ) && readlink( $destination ) === $source_file ) {
				return 'unchanged';
			}

			if ( ! $force && ! is_link( $destination )
				&& self::destination_is_newer( $destination, $source_file )
			) {
				return 'preserved';
			}
		}

		$temp = $destination . '.millicache.' . uniqid( '', true ) . '.tmp';

		if ( function_exists( 'symlink' ) && @symlink( $source_file, $temp ) ) {
			if ( @rename( $temp, $destination ) ) {
				return 'symlinked';
			}
			@unlink( $temp );
		}

		$source_content = file_get_contents( $source_file );
		if ( false === $source_content ) {
			return null;
		}

		$rewritten = preg_replace(
			'/(\$(?:engine|plugin)_path\s*=\s*)dirname.*?;/s',
			"$1'" . $source_dir . "';",
			$source_content
		);

		if ( null === $rewritten || false === file_put_contents( $temp, $rewritten, LOCK_EX ) ) {
			return null;
		}

		if ( @rename( $temp, $destination ) ) {
			return 'copied';
		}

		@unlink( $temp );
		return null;
	}

	/**
	 * Remove the drop-in at the destination.
	 *
	 * Symlinks are always safe to remove (they point at the plugin source).
	 * A plain copy with a higher version than the bundled file is preserved
	 * as 'preserved' unless $force is true.
	 *
	 * @param string      $filename   Drop-in filename ('advanced-cache.php').
	 * @param string|null $source_dir Absolute path to the plugin folder containing $filename. Defaults to MILLICACHE_DIR.
	 * @param bool        $force      Override the higher-version safeguard.
	 * @return null|string 'removed', 'absent', 'preserved', 'unwritable', or null on failure.
	 */
	public static function remove( string $filename = 'advanced-cache.php', ?string $source_dir = null, bool $force = false ): ?string {
		if ( ! self::exists( $filename ) ) {
			return 'absent';
		}

		if ( ! is_writable( WP_CONTENT_DIR ) ) {
			return 'unwritable';
		}

		$destination = self::destination( $filename );

		if ( ! $force && ! is_link( $destination ) ) {
			$source_dir  = $source_dir ?? MILLICACHE_DIR;
			$source_file = $source_dir . '/' . $filename;
			if ( self::destination_is_newer( $destination, $source_file ) ) {
				return 'preserved';
			}
		}

		return wp_delete_file( $destination ) ? 'removed' : null;
	}

	/**
	 * Whether the destination's version is higher than the bundled source.
	 *
	 * Returns false if either version header is missing — only an explicit
	 * higher version triggers the safeguard.
	 *
	 * @param string $destination Absolute path to the installed drop-in.
	 * @param string $source      Absolute path to the bundled drop-in source.
	 */
	private static function destination_is_newer( string $destination, string $source ): bool {
		$dest_version   = Utils::get_file_version( $destination );
		$source_version = Utils::get_file_version( $source );

		return $dest_version && $source_version
			&& version_compare( $dest_version, $source_version ) > 0;
	}
}
