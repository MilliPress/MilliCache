<?php
/**
 * Pest PHP configuration file.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

// WordPress mocks (constants & function stubs).
require_once __DIR__ . '/bootstrap.php';

// Set up autoloading.
require_once __DIR__ . '/../vendor/autoload.php';

// Integration test helpers — depends on autoloader.
require_once __DIR__ . '/Helpers/Rules.php';

/**
 * Create a Config instance with sensible test defaults.
 *
 * Any parameter can be overridden via named arguments (PHP 8).
 *
 * @param int           $ttl                 Cache TTL in seconds.
 * @param int           $grace               Grace period in seconds.
 * @param bool          $gzip                Whether gzip is enabled.
 * @param bool          $debug               Whether debug mode is enabled.
 * @param array<string> $nocache_paths       Paths to exclude from cache.
 * @param array<string> $nocache_cookies     Cookies that prevent caching.
 * @param array<string> $ignore_cookies      Cookies to ignore in hash.
 * @param array<string> $ignore_request_keys Query keys to ignore.
 * @param array<string> $unique              Variables making requests unique.
 * @return \MilliCache\Engine\Cache\Config
 */
function create_test_config(
	int $ttl = 3600,
	int $grace = 600,
	bool $gzip = true,
	bool $debug = false,
	array $nocache_paths = array(),
	array $nocache_cookies = array(),
	array $ignore_cookies = array(),
	array $ignore_request_keys = array(),
	array $unique = array()
): \MilliCache\Engine\Cache\Config {
	return new \MilliCache\Engine\Cache\Config(
		$ttl, $grace, $gzip, $debug,
		$nocache_paths, $nocache_cookies, $ignore_cookies,
		$ignore_request_keys, $unique
	);
}

/**
 * Run a callback with error_log output and PHP warnings suppressed.
 *
 * Useful for tests that intentionally trigger error paths (e.g. Redis
 * connection failures) where error_log() output would cause PHPUnit
 * to mark the test as risky.
 *
 * @param callable(): mixed $fn The callback to execute.
 * @return mixed The callback's return value.
 */
function suppressing_errors( callable $fn ): mixed {
	$prev_log = ini_set( 'error_log', '/dev/null' );
	set_error_handler( fn() => true, E_WARNING );
	try {
		return $fn();
	} finally {
		restore_error_handler();
		ini_set( 'error_log', $prev_log ?: '' );
	}
}

// Close Mockery after each test to prevent memory leaks and test pollution.
uses()
	->afterEach( function () {
		Mockery::close();
	} )
	->in( 'Unit' );

// Integration tests run the real MilliRules engine; reset its global static
// state before each test. Must run serially (no --parallel) because of that
// shared state.
uses()
	->beforeEach( function () {
		reset_rules_state();
	} )
	->afterEach( function () {
		Mockery::close();
	} )
	->in( 'Integration' );
