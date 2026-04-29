<?php
/**
 * Tests for DropIn — the drop-in lifecycle policy class.
 *
 * Each scenario builds real files in /tmp/wp-content and /tmp/plugins/millicache
 * because DropIn delegates to symlink()/rename()/file_put_contents() which can't
 * be mocked through the function_exists guard pattern. Fixtures are cleared in
 * afterEach to keep tests isolated.
 *
 * @link       https://www.millipress.com
 * @since      1.5.3
 *
 * @package    MilliCache
 */

use MilliCache\Admin\DropIn;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/' );
}

if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', '/tmp/wp-content' );
}

if ( ! defined( 'MILLICACHE_DIR' ) ) {
	define( 'MILLICACHE_DIR', '/tmp/plugins/millicache' );
}

// wp_delete_file and get_file_data are mocked in tests/bootstrap.php.

/**
 * Write a source plugin file with a Version header and the $engine_path
 * assignment that install()'s copy path rewrites.
 */
function dropin_write_source( string $filename, string $version = '1.0.0' ): string {
	$path = MILLICACHE_DIR . '/' . $filename;
	$body = "<?php\n/**\n * Version: $version\n */\n\$engine_path = dirname( __FILE__ ) . '/src';\n";
	file_put_contents( $path, $body );
	return $path;
}

/** Write a plain file at the destination with a given Version header. */
function dropin_write_destination( string $filename, string $version ): string {
	$path = WP_CONTENT_DIR . '/' . $filename;
	$body = "<?php\n/**\n * Version: $version\n */\n";
	file_put_contents( $path, $body );
	return $path;
}

/** Sweep test files from both fixture dirs. */
function dropin_cleanup(): void {
	@chmod( WP_CONTENT_DIR, 0755 );
	foreach ( (array) glob( WP_CONTENT_DIR . '/*' ) as $f ) {
		if ( is_link( $f ) || is_file( $f ) ) {
			@unlink( $f );
		}
	}
	foreach ( (array) glob( MILLICACHE_DIR . '/*' ) as $f ) {
		if ( is_file( $f ) ) {
			@unlink( $f );
		}
	}
}

uses()->beforeEach( function () {
	if ( ! is_dir( WP_CONTENT_DIR ) ) {
		mkdir( WP_CONTENT_DIR, 0755, true );
	}
	if ( ! is_dir( MILLICACHE_DIR ) ) {
		mkdir( MILLICACHE_DIR, 0755, true );
	}
	@chmod( WP_CONTENT_DIR, 0755 );
	dropin_cleanup();
} )->afterEach( function () {
	dropin_cleanup();
} );

describe( 'DropIn::install', function () {

	it( 'returns "unwritable" when wp-content is not writable', function () {
		dropin_write_source( 'advanced-cache.php' );
		chmod( WP_CONTENT_DIR, 0555 );

		expect( DropIn::install( 'advanced-cache.php' ) )->toBe( 'unwritable' );

		chmod( WP_CONTENT_DIR, 0755 );
	} );

	it( 'returns null when the source file is not readable', function () {
		// No source file written — should return null.
		expect( DropIn::install( 'missing.php' ) )->toBeNull();
	} );

	it( 'creates a symlink on a fresh install and reports "symlinked"', function () {
		$source = dropin_write_source( 'advanced-cache.php' );

		$result = DropIn::install( 'advanced-cache.php' );

		expect( $result )->toBe( 'symlinked' );
		$dest = WP_CONTENT_DIR . '/advanced-cache.php';
		expect( is_link( $dest ) )->toBeTrue();
		expect( readlink( $dest ) )->toBe( $source );
	} );

	it( 'returns "unchanged" when destination is already a symlink to the source', function () {
		$source = dropin_write_source( 'advanced-cache.php' );
		symlink( $source, WP_CONTENT_DIR . '/advanced-cache.php' );

		expect( DropIn::install( 'advanced-cache.php' ) )->toBe( 'unchanged' );
	} );

	it( 'returns "preserved" when destination is a plain copy with a higher version', function () {
		dropin_write_source( 'advanced-cache.php', '1.0.0' );
		dropin_write_destination( 'advanced-cache.php', '2.0.0' );

		expect( DropIn::install( 'advanced-cache.php' ) )->toBe( 'preserved' );
		// File untouched.
		expect( file_get_contents( WP_CONTENT_DIR . '/advanced-cache.php' ) )
			->toContain( 'Version: 2.0.0' );
	} );

	it( 'overwrites a higher-version plain copy when $force is true', function () {
		dropin_write_source( 'advanced-cache.php', '1.0.0' );
		dropin_write_destination( 'advanced-cache.php', '2.0.0' );

		$result = DropIn::install( 'advanced-cache.php', null, true );

		// Symlinks succeed in dev environments; the result is 'symlink' or 'copy'
		// depending on FS support — both indicate the higher-version copy was replaced.
		expect( $result )->toBeIn( array( 'symlinked', 'copied' ) );
		expect( file_exists( WP_CONTENT_DIR . '/advanced-cache.php' ) )->toBeTrue();
	} );

	it( 'replaces a symlink that points at a different path', function () {
		$source = dropin_write_source( 'advanced-cache.php' );
		// Point the destination symlink at an unrelated file.
		$other = MILLICACHE_DIR . '/other.php';
		file_put_contents( $other, '<?php // unrelated' );
		symlink( $other, WP_CONTENT_DIR . '/advanced-cache.php' );

		$result = DropIn::install( 'advanced-cache.php' );

		expect( $result )->toBe( 'symlinked' );
		expect( readlink( WP_CONTENT_DIR . '/advanced-cache.php' ) )->toBe( $source );
	} );

	it( 'does not preserve when version headers are missing on both files', function () {
		// Source with no version header.
		file_put_contents( MILLICACHE_DIR . '/advanced-cache.php', "<?php\n\$engine_path = dirname( __FILE__ );\n" );
		// Plain destination, also no version.
		file_put_contents( WP_CONTENT_DIR . '/advanced-cache.php', "<?php\n// pre-existing\n" );

		// destination_is_newer is false → falls through to install (replaces).
		$result = DropIn::install( 'advanced-cache.php' );

		expect( $result )->toBeIn( array( 'symlinked', 'copied' ) );
	} );
} );

describe( 'DropIn::remove', function () {

	it( 'returns "absent" when nothing is at the destination', function () {
		expect( DropIn::remove( 'advanced-cache.php' ) )->toBe( 'absent' );
	} );

	it( 'returns "unwritable" when destination exists but wp-content is read-only', function () {
		dropin_write_source( 'advanced-cache.php' );
		dropin_write_destination( 'advanced-cache.php', '1.0.0' );
		chmod( WP_CONTENT_DIR, 0555 );

		expect( DropIn::remove( 'advanced-cache.php' ) )->toBe( 'unwritable' );

		chmod( WP_CONTENT_DIR, 0755 );
	} );

	it( 'removes a symlink regardless of any version on the symlink target', function () {
		// Higher-version target — symlinks are still safe to remove.
		$source = dropin_write_source( 'advanced-cache.php', '99.0.0' );
		symlink( $source, WP_CONTENT_DIR . '/advanced-cache.php' );

		expect( DropIn::remove( 'advanced-cache.php' ) )->toBe( 'removed' );
		expect( is_link( WP_CONTENT_DIR . '/advanced-cache.php' ) )->toBeFalse();
		expect( file_exists( WP_CONTENT_DIR . '/advanced-cache.php' ) )->toBeFalse();
	} );

	it( 'returns "preserved" for a plain copy with a higher version', function () {
		dropin_write_source( 'advanced-cache.php', '1.0.0' );
		dropin_write_destination( 'advanced-cache.php', '2.0.0' );

		expect( DropIn::remove( 'advanced-cache.php' ) )->toBe( 'preserved' );
		// Still on disk.
		expect( file_exists( WP_CONTENT_DIR . '/advanced-cache.php' ) )->toBeTrue();
	} );

	it( 'removes a higher-version copy when $force is true', function () {
		dropin_write_source( 'advanced-cache.php', '1.0.0' );
		dropin_write_destination( 'advanced-cache.php', '2.0.0' );

		expect( DropIn::remove( 'advanced-cache.php', null, true ) )->toBe( 'removed' );
		expect( file_exists( WP_CONTENT_DIR . '/advanced-cache.php' ) )->toBeFalse();
	} );
} );
