<?php
/**
 * Tests for Updater class.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Core\Loader;
use MilliCache\Core\Updater;

// Define constants if not already defined.
if ( ! defined( 'MILLICACHE_VERSION' ) ) {
	define( 'MILLICACHE_VERSION', '1.0.1' );
}

if ( ! defined( 'MILLICACHE_BASENAME' ) ) {
	define( 'MILLICACHE_BASENAME', 'millicache/millicache.php' );
}

// Mock WordPress functions.
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

/**
 * Helper: build a mock HTTP success response.
 *
 * @param object|array<mixed> $body The response body data.
 * @return array<string, mixed>
 */
function mock_http_response( $body ): array {
	return array(
		'response' => array( 'code' => 200 ),
		'body'     => json_encode( $body ),
	);
}

/**
 * Helper: build a standard remote info object.
 *
 * @param string $version The version string.
 * @return object
 */
function mock_remote_info( string $version = '2.0.0' ): object {
	return (object) array(
		'name'           => 'MilliCache',
		'slug'           => 'millicache',
		'version'        => $version,
		'homepage'       => 'https://www.millipress.com/millicache',
		'author'         => '<a href="https://www.millipress.com/">MilliPress Team</a>',
		'author_profile' => 'https://www.millipress.com/',
		'requires'       => '6.6',
		'tested'         => '6.8',
		'requires_php'   => '7.4',
		'download_url'   => 'https://github.com/millipress/millicache/releases/download/v2.0.0/millicache-v2.0.0.zip',
		'last_updated'   => '2026-01-31',
		'sections'       => (object) array(
			'description' => '<p>Full Page Cache for WordPress.</p>',
			'changelog'   => '<h4>2.0.0</h4><ul><li>Major release</li></ul>',
		),
		'banners'        => (object) array(
			'low'  => 'https://www.millipress.com/assets/millicache-banner-772x250.jpg',
			'high' => 'https://www.millipress.com/assets/millicache-banner-1544x500.jpg',
		),
		'icons'          => (object) array(
			'1x' => 'https://www.millipress.com/assets/millicache-icon-128x128.png',
			'2x' => 'https://www.millipress.com/assets/millicache-icon-256x256.png',
		),
	);
}

uses()->beforeEach( function () {
	global $test_actions, $test_filters, $test_site_transients, $test_wp_remote_get_response, $test_apply_filters_returns;
	$test_actions                = array();
	$test_filters                = array();
	$test_site_transients        = array();
	$test_wp_remote_get_response = null;
	$test_apply_filters_returns  = array();
} );

describe( 'Updater', function () {

	describe( 'constructor', function () {

		it( 'registers hooks with the loader', function () {
			$loader  = new Loader();
			$updater = new Updater( $loader, MILLICACHE_BASENAME );

			$loader->run();

			global $test_filters, $test_actions;

			$filter_hooks = array_column( $test_filters, 'hook' );
			$action_hooks = array_column( $test_actions, 'hook' );

			expect( $filter_hooks )->toContain( 'site_transient_update_plugins' );
			expect( $filter_hooks )->toContain( 'plugins_api' );
			expect( $action_hooks )->toContain( 'delete_site_transient_update_plugins' );
		} );

		it( 'registers hooks regardless of the millicache_updates filter', function () {
			global $test_filters, $test_actions;

			// The filter is evaluated at update-check time, not construction time,
			// so hooks are always registered even when updates are disabled.
			$test_filters['millicache_updates'] = false;

			$loader  = new Loader();
			$updater = new Updater( $loader, MILLICACHE_BASENAME );

			$loader->run();

			$filter_hooks = array_column( $test_filters, 'hook' );
			$action_hooks = array_column( $test_actions, 'hook' );

			expect( $filter_hooks )->toContain( 'site_transient_update_plugins' );
			expect( $filter_hooks )->toContain( 'plugins_api' );
			expect( $action_hooks )->toContain( 'delete_site_transient_update_plugins' );
		} );

		it( 'registers no hooks when this copy does not own the managed basename', function () {
			global $test_filters, $test_actions;

			// Bundled as a dependency: the managed basename points at a host plugin.
			$loader  = new Loader();
			$updater = new Updater( $loader, 'millicache-pro/millicache-pro.php' );

			$loader->run();

			$filter_hooks = array_column( $test_filters, 'hook' );
			$action_hooks = array_column( $test_actions, 'hook' );

			expect( $filter_hooks )->not->toContain( 'site_transient_update_plugins' );
			expect( $filter_hooks )->not->toContain( 'plugins_api' );
			expect( $action_hooks )->not->toContain( 'delete_site_transient_update_plugins' );
		} );

		it( 'registers no hooks when millicache_self_update_enabled is false', function () {
			global $test_filters, $test_actions;

			$test_filters['millicache_self_update_enabled'] = false;

			$loader  = new Loader();
			$updater = new Updater( $loader, MILLICACHE_BASENAME );

			$loader->run();

			$filter_hooks = array_column( $test_filters, 'hook' );
			$action_hooks = array_column( $test_actions, 'hook' );

			expect( $filter_hooks )->not->toContain( 'site_transient_update_plugins' );
			expect( $filter_hooks )->not->toContain( 'plugins_api' );
			expect( $action_hooks )->not->toContain( 'delete_site_transient_update_plugins' );
		} );
	} );

	describe( 'millicache_updates filter', function () {

		it( 'injects no update when the filter disables update checks', function () {
			global $test_wp_remote_get_response, $test_filters;
			$test_wp_remote_get_response         = mock_http_response( mock_remote_info( '2.0.0' ) );
			$test_filters['millicache_updates'] = false;

			$loader  = new Loader();
			$updater = new Updater( $loader, MILLICACHE_BASENAME );

			$transient            = new stdClass();
			$transient->response  = array();
			$transient->no_update = array();

			$result = $updater->check_for_update( $transient );

			expect( $result->response )->not->toHaveKey( MILLICACHE_BASENAME );
			expect( $result->no_update )->not->toHaveKey( MILLICACHE_BASENAME );
		} );
	} );

	describe( 'check_for_update', function () {

		it( 'injects update when remote version is greater', function () {
			global $test_wp_remote_get_response;
			$test_wp_remote_get_response = mock_http_response( mock_remote_info( '2.0.0' ) );

			$loader  = new Loader();
			$updater = new Updater( $loader, MILLICACHE_BASENAME );

			$transient           = new stdClass();
			$transient->response = array();
			$transient->no_update = array();

			$result = $updater->check_for_update( $transient );

			expect( $result->response )->toHaveKey( MILLICACHE_BASENAME );
			expect( $result->response[ MILLICACHE_BASENAME ]->new_version )->toBe( '2.0.0' );
			expect( $result->response[ MILLICACHE_BASENAME ]->slug )->toBe( 'millicache' );
		} );

		it( 'adds to no_update when versions are equal', function () {
			global $test_wp_remote_get_response;
			$test_wp_remote_get_response = mock_http_response( mock_remote_info( MILLICACHE_VERSION ) );

			$loader  = new Loader();
			$updater = new Updater( $loader, MILLICACHE_BASENAME );

			$transient           = new stdClass();
			$transient->response = array();
			$transient->no_update = array();

			$result = $updater->check_for_update( $transient );

			expect( $result->response )->not->toHaveKey( MILLICACHE_BASENAME );
			expect( $result->no_update )->toHaveKey( MILLICACHE_BASENAME );
			expect( $result->no_update[ MILLICACHE_BASENAME ]->new_version )->toBe( MILLICACHE_VERSION );
		} );

		it( 'adds to no_update when remote version is lower', function () {
			global $test_wp_remote_get_response;
			$test_wp_remote_get_response = mock_http_response( mock_remote_info( '0.9.0' ) );

			$loader  = new Loader();
			$updater = new Updater( $loader, MILLICACHE_BASENAME );

			$transient           = new stdClass();
			$transient->response = array();
			$transient->no_update = array();

			$result = $updater->check_for_update( $transient );

			expect( $result->response )->not->toHaveKey( MILLICACHE_BASENAME );
			expect( $result->no_update )->toHaveKey( MILLICACHE_BASENAME );
		} );

		it( 'returns transient unmodified on network failure', function () {
			global $test_wp_remote_get_response;
			$test_wp_remote_get_response = new WP_Error( 'http_request_failed', 'Connection timeout' );

			$loader  = new Loader();
			$updater = new Updater( $loader, MILLICACHE_BASENAME );

			$transient           = new stdClass();
			$transient->response = array();
			$transient->no_update = array();

			$result = $updater->check_for_update( $transient );

			expect( $result->response )->toBeEmpty();
			expect( $result->no_update )->toBeEmpty();
		} );

		it( 'returns transient unmodified on non-200 response', function () {
			global $test_wp_remote_get_response;
			$test_wp_remote_get_response = array(
				'response' => array( 'code' => 500 ),
				'body'     => '',
			);

			$loader  = new Loader();
			$updater = new Updater( $loader, MILLICACHE_BASENAME );

			$transient           = new stdClass();
			$transient->response = array();
			$transient->no_update = array();

			$result = $updater->check_for_update( $transient );

			expect( $result->response )->toBeEmpty();
			expect( $result->no_update )->toBeEmpty();
		} );

		it( 'returns transient unmodified when response has no version field', function () {
			global $test_wp_remote_get_response;
			$test_wp_remote_get_response = mock_http_response( (object) array( 'name' => 'MilliCache' ) );

			$loader  = new Loader();
			$updater = new Updater( $loader, MILLICACHE_BASENAME );

			$transient           = new stdClass();
			$transient->response = array();
			$transient->no_update = array();

			$result = $updater->check_for_update( $transient );

			expect( $result->response )->toBeEmpty();
			expect( $result->no_update )->toBeEmpty();
		} );

		it( 'uses cached transient instead of making HTTP call', function () {
			global $test_site_transients;
			$test_site_transients['millicache_update_info'] = mock_remote_info( '3.0.0' );

			// Set remote response to a different version to prove cache is used.
			global $test_wp_remote_get_response;
			$test_wp_remote_get_response = mock_http_response( mock_remote_info( '4.0.0' ) );

			$loader  = new Loader();
			$updater = new Updater( $loader, MILLICACHE_BASENAME );

			$transient           = new stdClass();
			$transient->response = array();
			$transient->no_update = array();

			$result = $updater->check_for_update( $transient );

			// Should use cached 3.0.0, not the HTTP 4.0.0.
			expect( $result->response[ MILLICACHE_BASENAME ]->new_version )->toBe( '3.0.0' );
		} );

		it( 'returns non-object transient unmodified', function () {
			$loader  = new Loader();
			$updater = new Updater( $loader, MILLICACHE_BASENAME );

			$result = $updater->check_for_update( false );
			expect( $result )->toBeFalse();
		} );

		it( 'includes icons and banners in update data', function () {
			global $test_wp_remote_get_response;
			$test_wp_remote_get_response = mock_http_response( mock_remote_info( '2.0.0' ) );

			$loader  = new Loader();
			$updater = new Updater( $loader, MILLICACHE_BASENAME );

			$transient           = new stdClass();
			$transient->response = array();
			$transient->no_update = array();

			$result = $updater->check_for_update( $transient );

			$update = $result->response[ MILLICACHE_BASENAME ];
			expect( $update->icons )->toBeArray();
			expect( $update->banners )->toBeArray();
			expect( $update->icons['1x'] )->toContain( 'icon-128x128' );
		} );
	} );

	describe( 'plugin_information', function () {

		it( 'returns plugin info for correct slug', function () {
			global $test_wp_remote_get_response;
			$test_wp_remote_get_response = mock_http_response( mock_remote_info( '2.0.0' ) );

			$loader  = new Loader();
			$updater = new Updater( $loader, MILLICACHE_BASENAME );

			$args       = new stdClass();
			$args->slug = 'millicache';

			$result = $updater->plugin_information( false, 'plugin_information', $args );

			expect( $result )->toBeObject();
			expect( $result->name )->toBe( 'MilliCache' );
			expect( $result->version )->toBe( '2.0.0' );
			expect( $result->slug )->toBe( 'millicache' );
			expect( $result->sections )->toBeArray();
			expect( $result->sections )->toHaveKey( 'description' );
			expect( $result->sections )->toHaveKey( 'changelog' );
		} );

		it( 'returns false for wrong slug', function () {
			$loader  = new Loader();
			$updater = new Updater( $loader, MILLICACHE_BASENAME );

			$args       = new stdClass();
			$args->slug = 'some-other-plugin';

			$result = $updater->plugin_information( false, 'plugin_information', $args );
			expect( $result )->toBeFalse();
		} );

		it( 'returns false for wrong action', function () {
			$loader  = new Loader();
			$updater = new Updater( $loader, MILLICACHE_BASENAME );

			$args       = new stdClass();
			$args->slug = 'millicache';

			$result = $updater->plugin_information( false, 'query_plugins', $args );
			expect( $result )->toBeFalse();
		} );

		it( 'returns false when remote info is unavailable', function () {
			global $test_wp_remote_get_response;
			$test_wp_remote_get_response = new WP_Error( 'http_request_failed', 'Timeout' );

			$loader  = new Loader();
			$updater = new Updater( $loader, MILLICACHE_BASENAME );

			$args       = new stdClass();
			$args->slug = 'millicache';

			$result = $updater->plugin_information( false, 'plugin_information', $args );
			expect( $result )->toBeFalse();
		} );

		it( 'includes banners and icons in plugin info', function () {
			global $test_wp_remote_get_response;
			$test_wp_remote_get_response = mock_http_response( mock_remote_info( '2.0.0' ) );

			$loader  = new Loader();
			$updater = new Updater( $loader, MILLICACHE_BASENAME );

			$args       = new stdClass();
			$args->slug = 'millicache';

			$result = $updater->plugin_information( false, 'plugin_information', $args );

			expect( $result->banners )->toBeArray();
			expect( $result->icons )->toBeArray();
			expect( $result->banners )->toHaveKey( 'low' );
			expect( $result->banners )->toHaveKey( 'high' );
			expect( $result->icons )->toHaveKey( '1x' );
			expect( $result->icons )->toHaveKey( '2x' );
		} );
	} );

	describe( 'clear_update_cache', function () {

		it( 'deletes the cached transient', function () {
			global $test_site_transients;
			$test_site_transients['millicache_update_info'] = mock_remote_info( '2.0.0' );

			$loader  = new Loader();
			$updater = new Updater( $loader, MILLICACHE_BASENAME );

			$updater->clear_update_cache();

			expect( $test_site_transients )->not->toHaveKey( 'millicache_update_info' );
		} );
	} );

	describe( 'caching behavior', function () {

		it( 'stores remote info in site transient after successful fetch', function () {
			global $test_wp_remote_get_response, $test_site_transients;
			$test_wp_remote_get_response = mock_http_response( mock_remote_info( '2.0.0' ) );

			$loader  = new Loader();
			$updater = new Updater( $loader, MILLICACHE_BASENAME );

			$transient           = new stdClass();
			$transient->response = array();
			$transient->no_update = array();

			$updater->check_for_update( $transient );

			expect( $test_site_transients )->toHaveKey( 'millicache_update_info' );
			expect( $test_site_transients['millicache_update_info']->version )->toBe( '2.0.0' );
		} );

		it( 'does not cache on network failure', function () {
			global $test_wp_remote_get_response, $test_site_transients;
			$test_wp_remote_get_response = new WP_Error( 'http_request_failed', 'Timeout' );

			$loader  = new Loader();
			$updater = new Updater( $loader, MILLICACHE_BASENAME );

			$transient           = new stdClass();
			$transient->response = array();
			$transient->no_update = array();

			$updater->check_for_update( $transient );

			expect( $test_site_transients )->not->toHaveKey( 'millicache_update_info' );
		} );
	} );
} );
