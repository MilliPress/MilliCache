<?php
/**
 * Integration tests: ignored query keys stay in the request until rendering.
 *
 * Before 1.8.1 the superglobals were rewritten from advanced-cache.php, so
 * redirects built from REQUEST_URI (redirect_canonical, Polylang, WooCommerce)
 * lost the tracking parameters. The rewrite now runs at the end of
 * template_redirect; the cache key is unaffected either way.
 *
 * @package MilliCache
 */

use MilliCache\Core\Storage;
use MilliCache\Engine;

/**
 * Storage double that records reads and never hits.
 */
final class NormalizationRecordingStorage extends Storage {

	/**
	 * Hashes passed to get_cache().
	 *
	 * @var list<string>
	 */
	public array $reads = array();

	/**
	 * Report the backend as usable so the Reader proceeds to get_cache().
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
	 * Never store anything.
	 *
	 * @param string              $hash  The entry hash.
	 * @param array<string,mixed> $data  The entry payload.
	 * @param array<string>       $flags The entry flags.
	 * @param bool                $cache Whether to store the entry.
	 * @return bool Always true.
	 */
	public function perform_cache( string $hash, array $data, array $flags = array(), bool $cache = true ): bool {
		return true;
	}
}

/**
 * Seed the superglobals for one anonymous GET request.
 *
 * @param string               $uri Request URI including any query string.
 * @param array<string,string> $get Parsed query parameters.
 * @return void
 */
function seed_anonymous_get( string $uri, array $get ): void {
	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_SERVER['REQUEST_URI']    = $uri;
	$_SERVER['QUERY_STRING']   = (string) parse_url( $uri, PHP_URL_QUERY );
	$_SERVER['HTTP_HOST']      = 'example.test';
	$_SERVER['SERVER_NAME']    = 'example.test';
	$_SERVER['HTTPS']          = 'on';
	$_GET                      = $get;
	$_REQUEST                  = $get;
	$_POST                     = array();
	$_COOKIE                   = array();
}

/**
 * Run the engine for one request, as advanced-cache.php would.
 *
 * @param string               $uri Request URI including any query string.
 * @param array<string,string> $get Parsed query parameters.
 * @return array{engine: Engine, reads: list<string>, normalizer: callable|null, priority: int|null}
 */
function start_engine_for( string $uri, array $get = array() ): array {
	global $test_actions;
	$test_actions = array();

	seed_anonymous_get( $uri, $get );

	$storage = suppressing_errors( fn() => new NormalizationRecordingStorage( array() ) );
	$engine  = new Engine(
		$storage,
		null,
		create_test_config( ignore_request_keys: array( '_*', 'utm_*', 'gclid' ) )
	);

	$depth_before = ob_get_level();
	$engine->start();
	while ( ob_get_level() > $depth_before ) {
		ob_end_clean();
	}

	// The engine's last template_redirect callback: normalization + storable sentinel.
	$normalizer = null;
	$priority   = null;
	foreach ( $test_actions as $action ) {
		if ( 'template_redirect' === $action['hook'] && ( null === $priority || $action['priority'] > $priority ) ) {
			$normalizer = $action['callable'];
			$priority   = $action['priority'];
		}
	}

	return array(
		'engine'     => $engine,
		'reads'      => $storage->reads,
		'normalizer' => $normalizer,
		'priority'   => $priority,
	);
}

beforeEach( function () {
	$this->original_server  = $_SERVER;
	$this->original_get     = $_GET;
	$this->original_request = $_REQUEST;
	$this->original_cookie  = $_COOKIE;
} );

afterEach( function () {
	$_SERVER  = $this->original_server;
	$_GET     = $this->original_get;
	$_REQUEST = $this->original_request;
	$_COOKIE  = $this->original_cookie;
} );

it( 'leaves the request untouched after start() so redirects see the real URL', function () {
	$result = start_engine_for( '/?gclid=qa-123&utm_source=qa', array( 'gclid' => 'qa-123', 'utm_source' => 'qa' ) );

	expect( $result['engine']->check_cache_decision() )->toBeTrue();
	expect( $result['reads'] )->not->toBe( array(), 'an anonymous GET must be looked up in the cache' );

	expect( $_SERVER['REQUEST_URI'] )->toBe( '/?gclid=qa-123&utm_source=qa' );
	expect( $_SERVER['QUERY_STRING'] )->toBe( 'gclid=qa-123&utm_source=qa' );
	expect( $_GET )->toBe( array( 'gclid' => 'qa-123', 'utm_source' => 'qa' ) );
	expect( $_REQUEST )->toBe( array( 'gclid' => 'qa-123', 'utm_source' => 'qa' ) );
} );

it( 'looks up the same cache entry with and without the ignored keys', function () {
	$with    = start_engine_for( '/?gclid=qa-123&utm_source=qa', array( 'gclid' => 'qa-123', 'utm_source' => 'qa' ) );
	$without = start_engine_for( '/' );

	expect( $with['reads'] )->toBe( $without['reads'] );
} );

it( 'defers the superglobal rewrite to the end of template_redirect', function () {
	$result = start_engine_for( '/?gclid=qa-123&utm_source=qa', array( 'gclid' => 'qa-123', 'utm_source' => 'qa' ) );

	expect( $result['normalizer'] )->not->toBeNull( 'the engine must hook template_redirect' );
	expect( $result['priority'] )->toBeGreaterThan( 1000, 'must follow every core redirect (wp_redirect_admin_locations runs at 1000)' );

	// Simulate WordPress reaching the render phase.
	call_user_func( $result['normalizer'] );

	expect( $_SERVER['REQUEST_URI'] )->toBe( '/' );
	expect( $_SERVER['QUERY_STRING'] )->toBe( '' );
	expect( $_GET )->toBe( array() );
	expect( $_REQUEST )->toBe( array() );
} );

it( 'rewrites faithfully: surviving keys keep their order and encoding', function () {
	$result = start_engine_for(
		"/it's/?paged=2&gclid=x&orderby=price&q=a+b%20c",
		array( 'paged' => '2', 'gclid' => 'x', 'orderby' => 'price', 'q' => 'a b c' )
	);

	call_user_func( $result['normalizer'] );

	expect( $_SERVER['REQUEST_URI'] )->toBe( "/it's/?paged=2&orderby=price&q=a+b%20c" );
	expect( $_SERVER['QUERY_STRING'] )->toBe( 'paged=2&orderby=price&q=a+b%20c' );
	expect( $_GET )->toBe( array( 'paged' => '2', 'orderby' => 'price', 'q' => 'a b c' ) );
} );

it( 'drops the conditional headers before WordPress loads', function () {
	seed_anonymous_get( '/', array() );
	$_SERVER['HTTP_IF_NONE_MATCH']     = '"etag"';
	$_SERVER['HTTP_IF_MODIFIED_SINCE'] = 'Thu, 01 Jan 2026 00:00:00 GMT';

	$storage = suppressing_errors( fn() => new NormalizationRecordingStorage( array() ) );
	$engine  = new Engine( $storage, null, create_test_config() );

	$depth_before = ob_get_level();
	$engine->start();
	while ( ob_get_level() > $depth_before ) {
		ob_end_clean();
	}

	expect( $_SERVER )->not->toHaveKey( 'HTTP_IF_NONE_MATCH' );
	expect( $_SERVER )->not->toHaveKey( 'HTTP_IF_MODIFIED_SINCE' );
} );
