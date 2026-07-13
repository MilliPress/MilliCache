<?php
/**
 * Regression for the Engine's construction-time re-entrancy invariant:
 * `Engine::instance()` must never build a second Engine while the first is
 * still being constructed.
 *
 * The guarded cycle is:
 *
 *   Engine::instance() -> new Engine -> __construct() -> load_settings()
 *   -> Site::settings()->get() -> ConfigFile::read() -> resolve_domain()
 *   -> home_url() -> get_option() -> wp_cache_get()
 *
 * Any persistent object-cache drop-in that serves that `wp_cache_get()` by
 * reading through to storage can call back into `Engine::instance()`. This
 * is not a Pro-only or MilliCache-owned scenario: the common trigger is a
 * third-party drop-in such as the Redis Object Cache plugin, which any free
 * install may run. Before the fix the singleton was still `null` at that
 * point, so a second `new Engine` was constructed, re-entered the same
 * path, and recursed until the stack (or memory) was exhausted.
 *
 * The fix lives in this repo (Engine::__construct publishes the singleton
 * up front), so the guard belongs here too. The test owns no object cache;
 * it simulates the read-through with a home_url() probe.
 *
 * @package MilliCache
 */

use MilliBase\Settings as BaseSettings;
use MilliCache\Base\Site;
use MilliCache\Engine;

if ( ! function_exists( 'is_multisite' ) ) {
	function is_multisite() {
		global $test_is_multisite;
		return $test_is_multisite ?? false;
	}
}

if ( ! function_exists( 'wp_parse_url' ) ) {
	function wp_parse_url( $url, $component = -1 ) {
		return parse_url( (string) $url, $component );
	}
}

// Simulated home URL. When $millicache_reentry_probe holds a callable,
// invoking home_url() fires it once, standing in for an object-cache
// read-through that re-enters Engine::instance(). Inert (returns a stable
// host, no re-entry) whenever the probe is unset, so other suites that
// happen to reach resolve_domain() are unaffected.
if ( ! function_exists( 'home_url' ) ) {
	function home_url( $path = '', $scheme = null ) {
		global $millicache_reentry_probe;

		if ( is_callable( $millicache_reentry_probe ) ) {
			$probe = $millicache_reentry_probe;
			// Fire at most once: caps recursion so a regressed fix fails the
			// assertions below instead of crashing the whole test run.
			$millicache_reentry_probe = null;
			$probe();
		}

		return 'http://millicache.test' . ( is_string( $path ) ? $path : '' );
	}
}

/**
 * Null out a private static property so each test starts from a clean slate.
 *
 * @param class-string $class    Fully-qualified class name.
 * @param string       $property Private static property name.
 */
function reset_static_property( string $class, string $property ): void {
	$ref = new ReflectionProperty( $class, $property );
	$ref->setAccessible( true );
	$ref->setValue( null, null );
}

beforeEach( function () {
	global $millicache_reentry_probe, $test_is_multisite;

	$millicache_reentry_probe = null;
	$test_is_multisite        = false;

	reset_static_property( Engine::class, 'instance' );

	// A Settings instance backed by a config file, so resolve_domain() (and
	// therefore home_url()) runs on the first cold read during construction.
	Site::inject_settings(
		new BaseSettings(
			array(
				'slug'        => 'millicache',
				'defaults'    => array( 'cache' => array( 'ttl' => 3600 ) ),
				'config_file' => array(
					'directory' => sys_get_temp_dir() . '/millicache-reentry/',
				),
			)
		)
	);
} );

afterEach( function () {
	global $millicache_reentry_probe;

	$millicache_reentry_probe = null;

	// Restore the lazily-built real Settings and a clean singleton for the
	// rest of the suite.
	reset_static_property( Site::class, 'settings' );
	reset_static_property( Engine::class, 'instance' );
} );

it( 'returns the in-flight instance when an object cache re-enters during construction', function () {
	global $millicache_reentry_probe;

	$reentrant  = null;
	$fire_count = 0;

	$millicache_reentry_probe = function () use ( &$reentrant, &$fire_count ) {
		++$fire_count;
		$reentrant = Engine::instance();
	};

	// Reaching this line at all proves there was no unbounded recursion.
	$engine = new Engine();

	// The re-entrant Engine::instance() saw the singleton mid-construction
	// and handed back the very Engine being built, not a fresh one.
	expect( $fire_count )->toBe( 1 );
	expect( $reentrant )->toBe( $engine );
	expect( Engine::instance() )->toBe( $engine );
} );
