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
 * @since      1.6.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin
 */

namespace MilliCache\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	 * @since 1.6.0
	 * @access public
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
	 * @since 1.6.0
	 * @access public
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
	 * @since 1.6.0
	 * @access public
	 *
	 * @param string      $filename   Drop-in filename ('advanced-cache.php').
	 * @param string|null $source_dir Absolute path to the plugin folder containing $filename. Defaults to plugin root.
	 * @param bool        $force      Override the higher-version safeguard.
	 * @return null|string 'symlinked', 'copied', 'unchanged', 'preserved', 'unwritable', or null on failure.
	 */
	public static function install( string $filename = 'advanced-cache.php', ?string $source_dir = null, bool $force = false ): ?string {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable -- Drop-in management needs direct wp-content access; WP_Filesystem is not initialized in activation/CLI contexts.
		if ( ! is_writable( WP_CONTENT_DIR ) ) {
			return 'unwritable';
		}

		$source_dir  = $source_dir ?? dirname( __DIR__, 2 );
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

		// Capture before rename() clobbers the link.
		$old_target = is_link( $destination ) ? (string) readlink( $destination ) : '';
		$temp = $destination . '.millicache.' . uniqid( '', true ) . '.tmp';

		if ( function_exists( 'symlink' ) && @symlink( $source_file, $temp ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename -- Atomic swap: the drop-in loads on every request and must never be half-written; WP_Filesystem::move() is not atomic.
			if ( @rename( $temp, $destination ) ) {
				self::invalidate_opcache( $destination, $old_target );
				return 'symlinked';
			}
			wp_delete_file( $temp );
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

		// phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename -- Atomic swap: the drop-in loads on every request and must never be half-written; WP_Filesystem::move() is not atomic.
		if ( @rename( $temp, $destination ) ) {
			self::invalidate_opcache( $destination, $old_target );
			return 'copied';
		}

		wp_delete_file( $temp );
		return null;
	}

	/**
	 * Remove the drop-in at the destination.
	 *
	 * Symlinks are always safe to remove (they point at the plugin source).
	 * A plain copy with a higher version than the bundled file is preserved
	 * as 'preserved' unless $force is true.
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @param string      $filename   Drop-in filename ('advanced-cache.php').
	 * @param string|null $source_dir Absolute path to the plugin folder containing $filename. Defaults to plugin root.
	 * @param bool        $force      Override the higher-version safeguard.
	 * @return null|string 'removed', 'absent', 'preserved', 'unwritable', or null on failure.
	 */
	public static function remove( string $filename = 'advanced-cache.php', ?string $source_dir = null, bool $force = false ): ?string {
		if ( ! self::exists( $filename ) ) {
			return 'absent';
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable -- Drop-in management needs direct wp-content access; WP_Filesystem is not initialized in activation/CLI contexts.
		if ( ! is_writable( WP_CONTENT_DIR ) ) {
			return 'unwritable';
		}

		$destination = self::destination( $filename );

		if ( ! $force && ! is_link( $destination ) ) {
			$source_dir  = $source_dir ?? dirname( __DIR__, 2 );
			$source_file = $source_dir . '/' . $filename;
			if ( self::destination_is_newer( $destination, $source_file ) ) {
				return 'preserved';
			}
		}

		if ( ! wp_delete_file( $destination ) ) {
			return null;
		}

		self::invalidate_opcache( $destination );
		return 'removed';
	}

	/**
	 * Re-point the drop-in at the loaded plugin copy when it boots another one.
	 *
	 * The loaded copy is the one MILLICACHE_DIR names: plugin main files claim
	 * the constant first-writer-wins, so an active standalone outranks an
	 * extension's bundle, and a stale drop-in never gets a vote. Divergence is
	 * judged by path, not version, so install() runs forced. An absent drop-in
	 * stays absent — healing never installs, so it cannot undo a deactivation.
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @param string      $filename     Drop-in filename ('advanced-cache.php').
	 * @param string|null $expected_dir Plugin folder that should own the drop-in. Defaults to MILLICACHE_DIR.
	 * @return null|string install() result, 'absent', 'unchanged', or null when no expected source is known.
	 */
	public static function heal( string $filename = 'advanced-cache.php', ?string $expected_dir = null ): ?string {
		$expected_dir = $expected_dir ?? ( defined( 'MILLICACHE_DIR' ) ? MILLICACHE_DIR : null );

		if ( null === $expected_dir ) {
			return null;
		}

		if ( ! self::exists( $filename ) ) {
			return 'absent';
		}

		$current = self::current_source_dir( $filename );

		if ( null !== $current && self::same_path( $current, $expected_dir ) ) {
			return 'unchanged';
		}

		$result = self::install( $filename, $expected_dir, true );

		if ( in_array( $result, array( 'symlinked', 'copied' ), true ) ) {
			/**
			 * Fires after a drop-in was re-pointed to the loaded plugin copy.
			 *
			 * @since 1.7.0
			 *
			 * @param string      $filename     Drop-in filename.
			 * @param string      $expected_dir Plugin folder now backing the drop-in.
			 * @param string|null $current      Folder it booted before, if known.
			 */
			do_action( 'millicache_dropin_healed', $filename, $expected_dir, $current );
		}

		return $result;
	}

	/**
	 * Resolve the plugin folder the installed drop-in boots from.
	 *
	 * Symlinks resolve via readlink(); plain copies via the `$engine_path` /
	 * `$plugin_path` literal that install() rewrites. Null when the drop-in
	 * is absent or its source cannot be determined (e.g. a manually copied
	 * file still resolving relative to wp-content).
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @param string $filename Drop-in filename ('advanced-cache.php').
	 * @return string|null Absolute path to the source plugin folder, or null.
	 */
	public static function current_source_dir( string $filename = 'advanced-cache.php' ): ?string {
		$destination = self::destination( $filename );

		if ( is_link( $destination ) ) {
			$target = readlink( $destination );

			return false === $target ? null : dirname( $target );
		}

		if ( ! is_readable( $destination ) ) {
			return null;
		}

		$content = file_get_contents( $destination );
		if ( false === $content || ! preg_match( '/\$(?:engine|plugin)_path\s*=\s*\'([^\']+)\';/', $content, $matches ) ) {
			return null;
		}

		return $matches[1];
	}

	/**
	 * Drop stale OPcache entries for replaced or deleted drop-in files.
	 *
	 * @since 1.7.2
	 * @access private
	 *
	 * @param string ...$paths Absolute paths; empty strings are skipped.
	 */
	private static function invalidate_opcache( string ...$paths ): void {
		foreach ( array_filter( $paths ) as $path ) {
			clearstatcache( true, $path );

			if ( function_exists( 'wp_opcache_invalidate' ) ) {
				wp_opcache_invalidate( $path, true );
			} elseif ( function_exists( 'opcache_invalidate' ) ) {
				@opcache_invalidate( $path, true );
			}
		}
	}

	/**
	 * Whether two paths name the same directory, resolving symlinks.
	 *
	 * Falls back to a string comparison when either side does not resolve
	 * (e.g., a broken symlink target), so a dead path never equals a live one.
	 *
	 * @since 1.7.0
	 * @access private
	 *
	 * @param string $a First path.
	 * @param string $b Second path.
	 */
	private static function same_path( string $a, string $b ): bool {
		$real_a = realpath( $a );
		$real_b = realpath( $b );

		if ( false !== $real_a && false !== $real_b ) {
			return $real_a === $real_b;
		}

		return untrailingslashit( $a ) === untrailingslashit( $b );
	}

	/**
	 * Whether the destination's version is higher than the bundled source.
	 *
	 * Returns false if either version header is missing — only an explicit
	 * higher version triggers the safeguard.
	 *
	 * @since 1.6.0
	 * @access private
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
