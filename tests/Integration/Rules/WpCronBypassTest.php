<?php
/**
 * Integration tests: wp-cron.php must never be served from or stored in the cache.
 *
 * WordPress runs scheduled events by issuing a loopback request to
 * /wp-cron.php?doing_wp_cron=<lock>. A cached response there would replay a
 * stale lock value and silently stop cron from ever spawning site-wide, so the
 * bypass is asserted end-to-end rather than by rule inspection alone:
 * Engine::start() gates BOTH the serve and the store path behind
 * check_cache_decision(), so these tests drive the real engine and assert that
 * storage is never touched in either direction.
 *
 * @package MilliCache
 */

use MilliCache\Core\Storage;
use MilliCache\Engine;
use MilliCache\Rules\Bootstrap as BootstrapRules;
use MilliRules\MilliRules;
use MilliRules\Rules;

/**
 * Storage double that records every cache read and write it is asked for.
 *
 * Extends the real Storage so the Engine's own wiring (Reader/Writer, metrics)
 * stays untouched; only the two methods that reach Redis are intercepted.
 */
final class RecordingStorage extends Storage {

	/**
	 * Hashes passed to get_cache().
	 *
	 * @var list<string>
	 */
	public array $reads = array();

	/**
	 * Hashes passed to perform_cache().
	 *
	 * @var list<string>
	 */
	public array $writes = array();

	/**
	 * Report the backend as usable so Reader/Writer proceed to the real calls.
	 *
	 * @return bool Always true.
	 */
	public static function is_available(): bool {
		return true;
	}

	/**
	 * Record a cache read and always miss.
	 *
	 * @param string $hash The entry hash.
	 * @return array<string, mixed>|null Always null.
	 */
	public function get_cache( string $hash ): ?array {
		$this->reads[] = $hash;
		return null;
	}

	/**
	 * Record a cache write.
	 *
	 * @param string              $hash  The entry hash.
	 * @param array<string,mixed> $data  The entry payload.
	 * @param array<string>       $flags The entry flags.
	 * @param bool                $cache Whether to store the entry.
	 * @return bool Always true.
	 */
	public function perform_cache( string $hash, array $data, array $flags = array(), bool $cache = true ): bool {
		$this->writes[] = $hash;
		return true;
	}
}

/**
 * Seed the request superglobals for a single simulated request.
 *
 * @param string $method HTTP method.
 * @param string $uri    Request URI including any query string.
 * @return void
 */
function simulate_request( string $method, string $uri ): void {
	$_SERVER['REQUEST_METHOD'] = $method;
	$_SERVER['REQUEST_URI']    = $uri;
	$_SERVER['HTTP_HOST']      = 'example.test';
	$_SERVER['SERVER_NAME']    = 'example.test';
	$_GET                      = array();
	$_POST                     = array();
	$_COOKIE                   = array();
}

/**
 * Run the full engine for one request and report what storage saw.
 *
 * Mirrors advanced-cache.php: construct the Engine, call start(), then unwind
 * any output buffer start() opened so it can't leak into the next test.
 *
 * @param string $method HTTP method.
 * @param string $uri    Request URI including any query string.
 * @return array{reads: list<string>, writes: list<string>, buffered: bool, decision: bool}
 */
function run_engine_request( string $method, string $uri ): array {
	simulate_request( $method, $uri );

	$storage = suppressing_errors( fn() => new RecordingStorage( array() ) );
	$engine  = new Engine( $storage, null, create_test_config() );

	$depth_before = ob_get_level();
	$engine->start();
	$buffered = ob_get_level() > $depth_before;

	while ( ob_get_level() > $depth_before ) {
		ob_end_clean();
	}

	return array(
		'reads'    => $storage->reads,
		'writes'   => $storage->writes,
		'buffered' => $buffered,
		'decision' => $engine->check_cache_decision(),
	);
}

it( 'bypasses the cache for every wp-cron.php request shape', function ( string $method, string $uri ) {
	new Engine( null, null, create_test_config() );
	simulate_request( $method, $uri );

	BootstrapRules::register();
	MilliRules::execute_rules( array( 'PHP' ) );

	$decision = millicache()->options()->get_cache_decision();

	expect( $decision )->not->toBeNull( "{$method} {$uri} produced no cache decision" );
	expect( $decision['decision'] )->toBeFalse( "{$method} {$uri} should bypass the cache" );
	expect( $decision['reason'] )->toBe( 'MilliCache: WP-Cron request' );
	expect( millicache()->check_cache_decision() )->toBeFalse();
} )->with( array(
	// The lock value varies per spawn; the bypass must not depend on it.
	array( 'GET', '/wp-cron.php?doing_wp_cron=1755781234.1234567890' ),
	array( 'POST', '/wp-cron.php?doing_wp_cron=1755781234.1234567890' ),
	array( 'HEAD', '/wp-cron.php?doing_wp_cron=1755781234.1234567890' ),
	// External cron (crontab curl/wget) hits the bare path.
	array( 'GET', '/wp-cron.php' ),
	array( 'POST', '/wp-cron.php' ),
	// Subdirectory installs.
	array( 'GET', '/blog/wp-cron.php?doing_wp_cron=1755781234.1234567890' ),
) );

it( 'never reads a wp-cron.php response from storage', function ( string $method ) {
	$result = run_engine_request( $method, '/wp-cron.php?doing_wp_cron=1755781234.1234567890' );

	expect( $result['decision'] )->toBeFalse();
	expect( $result['reads'] )->toBe( array(), 'wp-cron.php must never be looked up in the cache' );
} )->with( array( 'GET', 'POST' ) );

it( 'never stores a wp-cron.php response', function ( string $method ) {
	$result = run_engine_request( $method, '/wp-cron.php?doing_wp_cron=1755781234.1234567890' );

	expect( $result['writes'] )->toBe( array(), 'wp-cron.php must never be written to the cache' );
	// No output buffer means the storing callback is never even installed.
	expect( $result['buffered'] )->toBeFalse( 'wp-cron.php must not start the caching output buffer' );
} )->with( array( 'GET', 'POST' ) );

it( 'cannot be re-enabled by a custom rule at a higher order', function () {
	new Engine( null, null, create_test_config() );
	simulate_request( 'GET', '/wp-cron.php?doing_wp_cron=1755781234.1234567890' );

	BootstrapRules::register();

	// The generic file-request rule is unlocked and loses to this; only the
	// locked wp-cron rule keeps cron safe from an over-broad site rule.
	Rules::create( 'user:force-cache', 'php' )
		->order( 50 )
		->when()
			->request_url( '*wp-cron*' )
		->then()
			->do_cache( true, 'Site rule forced caching' )
		->register();

	MilliRules::execute_rules( array( 'PHP' ) );

	expect( millicache()->check_cache_decision() )->toBeFalse();
} );

it( 'still caches an ordinary page request', function () {
	$result = run_engine_request( 'GET', '/hello-world/' );

	expect( $result['decision'] )->toBeTrue();
	expect( $result['reads'] )->not->toBe( array(), 'a normal GET should look the page up in the cache' );
	expect( $result['buffered'] )->toBeTrue( 'a normal GET should start the caching output buffer' );
} );
