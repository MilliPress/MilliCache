<?php
/**
 * Tests for Translator class.
 *
 * @link       https://www.millipress.com
 * @since      1.7.4
 *
 * @package    MilliCache
 */

use MilliCache\Core\Loader;
use MilliCache\Core\Translator;

// Guarded WP-function stubs, behavior-identical to UpdaterTest.php's so the
// file also runs standalone (whichever file loads first defines them).
if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $hook, $value, ...$args ) {
		global $test_apply_filters_returns;
		if ( isset( $test_apply_filters_returns[ $hook ] ) ) {
			return $test_apply_filters_returns[ $hook ];
		}
		return $value;
	}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( $hook, $callable, $priority = 10, $accepted_args = 1 ) {
		global $test_actions;
		$test_actions[] = array(
			'hook'          => $hook,
			'callable'      => $callable,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);
		return true;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $hook, $callable, $priority = 10, $accepted_args = 1 ) {
		global $test_filters;
		$test_filters[] = array(
			'hook'          => $hook,
			'callable'      => $callable,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);
		return true;
	}
}

if ( ! function_exists( 'get_site_transient' ) ) {
	function get_site_transient( $key ) {
		global $test_site_transients;
		return $test_site_transients[ $key ] ?? false;
	}
}

if ( ! function_exists( 'set_site_transient' ) ) {
	function set_site_transient( $key, $value, $expiration = 0 ) {
		global $test_site_transients;
		$test_site_transients[ $key ] = $value;
		return true;
	}
}

if ( ! function_exists( 'delete_site_transient' ) ) {
	function delete_site_transient( $key ) {
		global $test_site_transients;
		unset( $test_site_transients[ $key ] );
		return true;
	}
}

if ( ! function_exists( 'wp_remote_get' ) ) {
	function wp_remote_get( $url, $args = array() ) {
		global $test_wp_remote_get_response;
		return $test_wp_remote_get_response ?? new \WP_Error( 'http_request_failed', 'Mock error' );
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ) {
		return $thing instanceof \WP_Error;
	}
}

if ( ! function_exists( 'wp_remote_retrieve_response_code' ) ) {
	function wp_remote_retrieve_response_code( $response ) {
		return $response['response']['code'] ?? 0;
	}
}

if ( ! function_exists( 'wp_remote_retrieve_body' ) ) {
	function wp_remote_retrieve_body( $response ) {
		return $response['body'] ?? '';
	}
}

if ( ! class_exists( 'WP_Error' ) ) {
	// phpcs:ignore Generic.Files.OneObjectStructurePerFile.MultipleFound
	class WP_Error {
		private string $code;
		private string $message;

		public function __construct( string $code = '', string $message = '' ) {
			$this->code    = $code;
			$this->message = $message;
		}

		public function get_error_message(): string {
			return $this->message;
		}
	}
}

if ( ! function_exists( 'get_available_languages' ) ) {
	function get_available_languages() {
		global $test_available_languages;
		return $test_available_languages ?? array();
	}
}

if ( ! function_exists( 'get_locale' ) ) {
	function get_locale() {
		global $test_locale;
		return $test_locale ?? 'en_US';
	}
}

if ( ! function_exists( 'wp_get_installed_translations' ) ) {
	function wp_get_installed_translations( $type ) {
		global $test_installed_translations;
		return $test_installed_translations ?? array();
	}
}

uses()->beforeEach( function () {
	global $test_actions, $test_filters, $test_site_transients, $test_wp_remote_get_response,
		$test_apply_filters_returns, $test_available_languages, $test_locale, $test_installed_translations;
	$test_actions                 = array();
	$test_filters                 = array();
	$test_site_transients         = array();
	$test_wp_remote_get_response  = null;
	$test_apply_filters_returns   = array();
	$test_available_languages     = array( 'de_DE', 'de_DE_formal' );
	$test_locale                  = 'de_DE';
	$test_installed_translations  = array();
} );

/**
 * Helper: a manifest API body for the given entries.
 *
 * @param array<int, array<string, mixed>> $entries The translations list.
 * @return array<string, mixed>
 */
function mock_manifest_response( array $entries ): array {
	return array(
		'response' => array( 'code' => 200 ),
		'body'     => wp_json_encode_stub( array( 'translations' => $entries ) ),
	);
}

/**
 * Helper: json_encode without depending on WP's wp_json_encode.
 *
 * @param mixed $data The data to encode.
 * @return string
 */
function wp_json_encode_stub( $data ): string {
	return (string) json_encode( $data ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
}

/**
 * Helper: one manifest entry in the API's shape.
 *
 * @param array<string, mixed> $overrides Field overrides.
 * @return array<string, mixed>
 */
function manifest_entry( array $overrides = array() ): array {
	return array_merge(
		array(
			'type'       => 'plugin',
			'slug'       => 'millicache',
			'language'   => 'de_DE',
			'version'    => '1.8.0',
			'updated'    => '2026-07-21 21:34:00',
			'package'    => 'https://www.millipress.com/api/plugins/millicache/languages/de_DE',
			'autoupdate' => true,
		),
		$overrides
	);
}

/**
 * Helper: run the injector against a fresh transient object.
 *
 * @param Translator $updates The injector under test.
 * @return stdClass
 */
function run_injector( Translator $updates ): stdClass {
	return $updates->inject_translations( new stdClass() );
}

it( 'injects packs for site locales into the transient', function () {
	global $test_wp_remote_get_response;
	$test_wp_remote_get_response = mock_manifest_response( array(
		manifest_entry(),
		manifest_entry( array( 'language' => 'de_DE_formal', 'package' => 'https://www.millipress.com/api/plugins/millicache/languages/de_DE_formal' ) ),
	) );

	$transient = run_injector( new Translator( new Loader() ) );

	// Both configured products (millicache + millibase) receive the same
	// mocked body, so each contributes both locales.
	expect( $transient->translations )->toHaveCount( 4 );
	expect( $transient->translations[0]['slug'] )->toBe( 'millicache' );
	expect( $transient->translations[0]['language'] )->toBe( 'de_DE' );
	expect( $transient->translations[0]['type'] )->toBe( 'plugin' );
	$slugs = array_unique( array_column( $transient->translations, 'slug' ) );
	sort( $slugs );
	expect( $slugs )->toBe( array( 'millibase', 'millicache' ) );
} );

it( 'skips locales the site does not use', function () {
	global $test_wp_remote_get_response, $test_available_languages, $test_locale;
	$test_available_languages    = array();
	$test_locale                 = 'de_DE';
	$test_wp_remote_get_response = mock_manifest_response( array(
		manifest_entry(),
		manifest_entry( array( 'language' => 'fr_FR' ) ),
	) );

	$transient = run_injector( new Translator( new Loader() ) );

	expect( array_column( $transient->translations, 'language' ) )->toBe( array( 'de_DE', 'de_DE' ) );
} );

it( 'does not reoffer a pack the site already has (equal PO-Revision-Date)', function () {
	global $test_wp_remote_get_response, $test_installed_translations;
	$test_wp_remote_get_response  = mock_manifest_response( array( manifest_entry() ) );
	$test_installed_translations  = array(
		'millicache' => array(
			'de_DE' => array( 'PO-Revision-Date' => '2026-07-21 21:34:00' ),
		),
	);

	$transient = run_injector( new Translator( new Loader() ) );

	// millicache/de_DE is up to date; millibase (same body) has nothing installed.
	expect( array_column( $transient->translations, 'slug' ) )->toBe( array( 'millibase' ) );
} );

it( 'offers a pack newer than the installed translation', function () {
	global $test_wp_remote_get_response, $test_installed_translations;
	$test_wp_remote_get_response  = mock_manifest_response( array( manifest_entry() ) );
	$test_installed_translations  = array(
		'millicache' => array(
			'de_DE' => array( 'PO-Revision-Date' => '2026-07-01 10:00:00' ),
		),
	);

	$transient = run_injector( new Translator( new Loader() ) );

	expect( array_column( $transient->translations, 'slug' ) )->toContain( 'millicache' );
} );

it( 'does not duplicate entries already listed in the transient', function () {
	global $test_wp_remote_get_response;
	$test_wp_remote_get_response = mock_manifest_response( array( manifest_entry() ) );

	$updates                 = new Translator( new Loader() );
	$transient               = new stdClass();
	$transient->translations = array( manifest_entry() );

	$result = $updates->inject_translations( $transient );

	// The pre-existing millicache/de_DE entry stays single; millibase adds one.
	expect( array_count_values( array_column( $result->translations, 'slug' ) ) )
		->toBe( array( 'millicache' => 1, 'millibase' => 1 ) );
} );

it( 'caches a failed fetch and stays quiet', function () {
	global $test_wp_remote_get_response;
	$test_wp_remote_get_response = null; // WP_Error from the stub.

	$updates   = new Translator( new Loader() );
	$transient = run_injector( $updates );

	expect( $transient->translations )->toBe( array() );

	// A later request cycle would now find data — but the failure is cached,
	// so nothing is refetched until the transient expires.
	$test_wp_remote_get_response = mock_manifest_response( array( manifest_entry() ) );

	expect( run_injector( $updates )->translations )->toBe( array() );
} );

it( 'serves from the cached manifest bundle without refetching', function () {
	global $test_site_transients, $test_wp_remote_get_response;
	$test_wp_remote_get_response = null; // Any HTTP hit would yield nothing.
	$test_site_transients['millicache_translation_manifests'] = array(
		'millicache' => array( manifest_entry() ),
		'millibase'  => array(),
	);

	$transient = run_injector( new Translator( new Loader() ) );

	expect( array_column( $transient->translations, 'slug' ) )->toBe( array( 'millicache' ) );
} );

it( 'lets a bundling host add its own product via filter', function () {
	global $test_wp_remote_get_response, $test_apply_filters_returns, $test_filters;
	// Both stub conventions: tests/bootstrap.php's apply_filters reads
	// $test_filters[$hook]; the per-file stubs read $test_apply_filters_returns.
	$products = array( 'millicache', 'millibase', 'millicache-pro' );
	$test_apply_filters_returns['millicache_translation_products'] = $products;
	$test_filters['millicache_translation_products']               = $products;
	$test_wp_remote_get_response = mock_manifest_response( array( manifest_entry() ) );

	$transient = run_injector( new Translator( new Loader() ) );

	$slugs = array_unique( array_column( $transient->translations, 'slug' ) );
	sort( $slugs );
	expect( $slugs )->toBe( array( 'millibase', 'millicache', 'millicache-pro' ) );
} );

it( 'leaves a non-object transient untouched', function () {
	$updates = new Translator( new Loader() );

	expect( $updates->inject_translations( false ) )->toBeFalse();
} );

it( 'registers the transient filter and the force-check cache clear', function () {
	global $test_filters, $test_actions;

	$loader = new Loader();
	new Translator( $loader );
	$loader->run();

	expect( array_column( $test_filters, 'hook' ) )->toContain( 'site_transient_update_plugins' );
	expect( array_column( $test_actions, 'hook' ) )->toContain( 'delete_site_transient_update_plugins' );
} );
