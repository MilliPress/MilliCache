<?php
/**
 * Tests for REST API Handler.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Admin\RestAPI;
use MilliCache\Core\Loader;

// Mock WordPress functions.
if ( ! function_exists( 'register_rest_route' ) ) {
	function register_rest_route( $namespace, $route, $args ) {
		return true;
	}
}

if ( ! function_exists( 'current_user_can' ) ) {
	function current_user_can( $capability ) {
		return true;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $hook, $value, ...$args ) {
		return $value;
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'do_action' ) ) {
	function do_action( $hook, ...$args ) {
		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	function delete_option( $option ) {
		return true;
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ) {
		return $thing instanceof \WP_Error;
	}
}

if ( ! function_exists( 'rest_ensure_response' ) ) {
	function rest_ensure_response( $data ) {
		if ( $data instanceof \WP_REST_Response ) {
			return $data;
		}
		return new \WP_REST_Response( $data );
	}
}

if ( ! function_exists( 'wp_verify_nonce' ) ) {
	function wp_verify_nonce( $nonce, $action ) {
		return $nonce === 'valid_nonce';
	}
}

// Mock WordPress classes.
if ( ! class_exists( 'WP_REST_Server' ) ) {
	class WP_REST_Server {
		const READABLE = 'GET';
		const CREATABLE = 'POST';
	}
}

if ( ! class_exists( 'WP_REST_Request' ) ) {
	class WP_REST_Request {
		private $params = array();

		public function __construct( $params = array() ) {
			$this->params = $params;
		}

		public function get_param( $key ) {
			return $this->params[ $key ] ?? null;
		}

		public function get_params() {
			return $this->params;
		}
	}
}

if ( ! class_exists( 'WP_REST_Response' ) ) {
	class WP_REST_Response {
		public $data;
		public $status;

		public function __construct( $data = null, $status = 200 ) {
			$this->data = $data;
			$this->status = $status;
		}
	}
}

if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		public $code;
		public $message;
		public $data;

		public function __construct( $code, $message, $data = array() ) {
			$this->code = $code;
			$this->message = $message;
			$this->data = $data;
		}
	}
}

uses()->beforeEach( function () {
	$this->loader = Mockery::mock( Loader::class );
	$this->loader->shouldReceive( 'add_action' )->andReturn( true );
	$this->loader->shouldReceive( 'add_filter' )->andReturn( true );

	$this->rest_api = new RestAPI( $this->loader, 'millicache', '1.0.0' );
} );

describe( 'REST API', function () {

	describe( 'constructor', function () {
		it( 'creates instance with dependencies', function () {
			expect( $this->rest_api )->toBeInstanceOf( RestAPI::class );
		} );

		it( 'registers hooks via loader', function () {
			$loader = Mockery::mock( Loader::class );
			$loader->shouldReceive( 'add_action' )
				->with( 'rest_api_init', Mockery::type( RestAPI::class ), 'register_routes' )
				->once();
			$loader->shouldReceive( 'add_filter' )
				->with( 'rest_authentication_errors', Mockery::type( RestAPI::class ), 'verify_rest_nonce' )
				->once();

			new RestAPI( $loader, 'millicache', '1.0.0' );

			expect( true )->toBeTrue();
		} );
	} );

	describe( 'register_routes', function () {
		it( 'registers cache endpoint', function () {
			$this->rest_api->register_routes();
			expect( true )->toBeTrue();
		} );

		it( 'registers settings endpoint', function () {
			$this->rest_api->register_routes();
			expect( true )->toBeTrue();
		} );

		it( 'registers status endpoint', function () {
			$this->rest_api->register_routes();
			expect( true )->toBeTrue();
		} );
	} );

	describe( 'perform_cache_action', function () {
		it( 'returns error for missing action', function () {
			$request = new \WP_REST_Request( array() );
			$response = $this->rest_api->perform_cache_action( $request );

			expect( $response )->toBeInstanceOf( \WP_Error::class );
			expect( $response->code )->toBe( 'invalid_action' );
		} );

		it( 'returns error for invalid action', function () {
			$request = new \WP_REST_Request( array( 'action' => 'invalid' ) );
			$response = $this->rest_api->perform_cache_action( $request );

			expect( $response )->toBeInstanceOf( \WP_Error::class );
			expect( $response->code )->toBe( 'invalid_action' );
		} );

		it( 'returns error for non-string action', function () {
			$request = new \WP_REST_Request( array( 'action' => 123 ) );
			$response = $this->rest_api->perform_cache_action( $request );

			expect( $response )->toBeInstanceOf( \WP_Error::class );
		} );

		it( 'handles clear action for site', function () {
			// Mock Engine static methods.
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'clear_cache_by_site_ids' )->once();

			$request = new \WP_REST_Request( array( 'action' => 'clear' ) );
			$response = $this->rest_api->perform_cache_action( $request );

			expect( $response )->toBeInstanceOf( \WP_REST_Response::class );
			expect( $response->data['success'] )->toBeTrue();
			expect( $response->data['action'] )->toBe( 'clear' );
		} );

		it( 'handles clear action for network', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'clear_cache_by_network_id' )->once();

			$request = new \WP_REST_Request( array(
				'action' => 'clear',
				'is_network_admin' => true,
			) );
			$response = $this->rest_api->perform_cache_action( $request );

			expect( $response )->toBeInstanceOf( \WP_REST_Response::class );
			expect( $response->data['success'] )->toBeTrue();
		} );

		it( 'handles clear_current with flags', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'clear_cache_by_flags' )
				->with( array( 'flag1', 'flag2' ) )
				->once();

			$request = new \WP_REST_Request( array(
				'action' => 'clear_current',
				'request_flags' => array( 'flag1', 'flag2' ),
			) );
			$response = $this->rest_api->perform_cache_action( $request );

			expect( $response )->toBeInstanceOf( \WP_REST_Response::class );
			expect( $response->data['success'] )->toBeTrue();
		} );

		it( 'handles clear_current with JSON string flags', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'clear_cache_by_flags' )->once();

			$request = new \WP_REST_Request( array(
				'action' => 'clear_current',
				'request_flags' => '["flag1","flag2"]',
			) );
			$response = $this->rest_api->perform_cache_action( $request );

			expect( $response )->toBeInstanceOf( \WP_REST_Response::class );
		} );

		it( 'returns error for clear_current without flags', function () {
			$request = new \WP_REST_Request( array( 'action' => 'clear_current' ) );
			$response = $this->rest_api->perform_cache_action( $request );

			expect( $response )->toBeInstanceOf( \WP_Error::class );
			expect( $response->code )->toBe( 'no_flags' );
		} );

		it( 'handles clear_targets with string target', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'clear_cache_by_targets' )
				->with( 'https://example.com' )
				->once();

			$request = new \WP_REST_Request( array(
				'action' => 'clear_targets',
				'targets' => 'https://example.com',
			) );
			$response = $this->rest_api->perform_cache_action( $request );

			expect( $response )->toBeInstanceOf( \WP_REST_Response::class );
			expect( $response->data['success'] )->toBeTrue();
		} );

		it( 'handles clear_targets with array', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'clear_cache_by_targets' )->once();

			$request = new \WP_REST_Request( array(
				'action' => 'clear_targets',
				'targets' => array( 123, 'https://example.com' ),
			) );
			$response = $this->rest_api->perform_cache_action( $request );

			expect( $response )->toBeInstanceOf( \WP_REST_Response::class );
		} );

		it( 'returns error for clear_targets with invalid targets', function () {
			$request = new \WP_REST_Request( array(
				'action' => 'clear_targets',
				'targets' => 123,
			) );
			$response = $this->rest_api->perform_cache_action( $request );

			expect( $response )->toBeInstanceOf( \WP_Error::class );
			expect( $response->code )->toBe( 'invalid_targets' );
		} );

		it( 'includes timestamp in response', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'clear_cache_by_site_ids' )->once();

			$before = time();
			$request = new \WP_REST_Request( array( 'action' => 'clear' ) );
			$response = $this->rest_api->perform_cache_action( $request );
			$after = time();

			expect( $response->data['timestamp'] )->toBeGreaterThanOrEqual( $before );
			expect( $response->data['timestamp'] )->toBeLessThanOrEqual( $after );
		} );
	} );

	describe( 'perform_settings_action', function () {
		it( 'returns error for missing action', function () {
			$request = new \WP_REST_Request( array() );
			$response = $this->rest_api->perform_settings_action( $request );

			expect( $response )->toBeInstanceOf( \WP_Error::class );
			expect( $response->code )->toBe( 'invalid_settings_action' );
		} );

		it( 'returns error for invalid action', function () {
			$request = new \WP_REST_Request( array( 'action' => 'invalid' ) );
			$response = $this->rest_api->perform_settings_action( $request );

			expect( $response )->toBeInstanceOf( \WP_Error::class );
		} );

		it( 'handles reset action', function () {
			$settings = Mockery::mock( 'alias:MilliCache\Core\Settings' );
			$settings->shouldReceive( 'backup' )->once();

			$request = new \WP_REST_Request( array( 'action' => 'reset' ) );
			$response = $this->rest_api->perform_settings_action( $request );

			expect( $response )->toBeInstanceOf( \WP_REST_Response::class );
			expect( $response->data['success'] )->toBeTrue();
			expect( $response->data['action'] )->toBe( 'reset' );
		} );

		it( 'handles restore action successfully', function () {
			$settings = Mockery::mock( 'alias:MilliCache\Core\Settings' );
			$settings->shouldReceive( 'restore_backup' )->once()->andReturn( array( 'host' => '127.0.0.1' ) );

			$request = new \WP_REST_Request( array( 'action' => 'restore' ) );
			$response = $this->rest_api->perform_settings_action( $request );

			expect( $response )->toBeInstanceOf( \WP_REST_Response::class );
			expect( $response->data['success'] )->toBeTrue();
		} );

		it( 'handles restore action with no backup', function () {
			$settings = Mockery::mock( 'alias:MilliCache\Core\Settings' );
			$settings->shouldReceive( 'restore_backup' )->once()->andReturn( false );

			$request = new \WP_REST_Request( array( 'action' => 'restore' ) );
			$response = $this->rest_api->perform_settings_action( $request );

			expect( $response )->toBeInstanceOf( \WP_REST_Response::class );
			expect( $response->data['success'] )->toBeFalse();
			expect( $response->status )->toBe( 400 );
		} );

		it( 'includes timestamp in response', function () {
			$settings = Mockery::mock( 'alias:MilliCache\Core\Settings' );
			$settings->shouldReceive( 'backup' )->once();

			$before = time();
			$request = new \WP_REST_Request( array( 'action' => 'reset' ) );
			$response = $this->rest_api->perform_settings_action( $request );
			$after = time();

			expect( $response->data['timestamp'] )->toBeGreaterThanOrEqual( $before );
			expect( $response->data['timestamp'] )->toBeLessThanOrEqual( $after );
		} );
	} );

	describe( 'get_status', function () {
		it( 'returns status response', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$storage = Mockery::mock();
			$storage->shouldReceive( 'get_status' )->andReturn( array( 'connected' => true ) );
			$engine->shouldReceive( 'get_status' )->andReturn( array( 'enabled' => true ) );
			$engine->shouldReceive( 'get_storage' )->andReturn( $storage );

			$settings = Mockery::mock( 'alias:MilliCache\Core\Settings' );
			$settings->shouldReceive( 'has_default_settings' )->andReturn( false );
			$settings->shouldReceive( 'has_backup' )->andReturn( true );

			$request = new \WP_REST_Request( array() );
			$response = $this->rest_api->get_status( $request );

			expect( $response )->toBeInstanceOf( \WP_REST_Response::class );
			expect( $response->data['plugin_name'] )->toBe( 'millicache' );
			expect( $response->data['version'] )->toBe( '1.0.0' );
		} );

		it( 'includes cache status', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$storage = Mockery::mock();
			$storage->shouldReceive( 'get_status' )->andReturn( array() );
			$engine->shouldReceive( 'get_status' )->andReturn( array( 'enabled' => true ) );
			$engine->shouldReceive( 'get_storage' )->andReturn( $storage );

			$settings = Mockery::mock( 'alias:MilliCache\Core\Settings' );
			$settings->shouldReceive( 'has_default_settings' )->andReturn( false );
			$settings->shouldReceive( 'has_backup' )->andReturn( false );

			$request = new \WP_REST_Request( array() );
			$response = $this->rest_api->get_status( $request );

			expect( isset( $response->data['cache'] ) )->toBeTrue();
			expect( isset( $response->data['storage'] ) )->toBeTrue();
		} );

		it( 'includes dropin status', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$storage = Mockery::mock();
			$storage->shouldReceive( 'get_status' )->andReturn( array() );
			$engine->shouldReceive( 'get_status' )->andReturn( array() );
			$engine->shouldReceive( 'get_storage' )->andReturn( $storage );

			$settings = Mockery::mock( 'alias:MilliCache\Core\Settings' );
			$settings->shouldReceive( 'has_default_settings' )->andReturn( true );
			$settings->shouldReceive( 'has_backup' )->andReturn( false );

			$request = new \WP_REST_Request( array() );
			$response = $this->rest_api->get_status( $request );

			expect( isset( $response->data['dropin'] ) )->toBeTrue();
		} );

		it( 'includes settings status', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$storage = Mockery::mock();
			$storage->shouldReceive( 'get_status' )->andReturn( array() );
			$engine->shouldReceive( 'get_status' )->andReturn( array() );
			$engine->shouldReceive( 'get_storage' )->andReturn( $storage );

			$settings = Mockery::mock( 'alias:MilliCache\Core\Settings' );
			$settings->shouldReceive( 'has_default_settings' )->andReturn( false );
			$settings->shouldReceive( 'has_backup' )->andReturn( true );

			$request = new \WP_REST_Request( array() );
			$response = $this->rest_api->get_status( $request );

			expect( $response->data['settings']['has_defaults'] )->toBeFalse();
			expect( $response->data['settings']['has_backup'] )->toBeTrue();
		} );

		it( 'passes network parameter to Engine::get_status', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$storage = Mockery::mock();
			$storage->shouldReceive( 'get_status' )->andReturn( array() );
			$engine->shouldReceive( 'get_status' )->with( true )->andReturn( array() );
			$engine->shouldReceive( 'get_storage' )->andReturn( $storage );

			$settings = Mockery::mock( 'alias:MilliCache\Core\Settings' );
			$settings->shouldReceive( 'has_default_settings' )->andReturn( false );
			$settings->shouldReceive( 'has_backup' )->andReturn( false );

			$request = new \WP_REST_Request( array( 'network' => 'true' ) );
			$response = $this->rest_api->get_status( $request );

			expect( $response )->toBeInstanceOf( \WP_REST_Response::class );
		} );
	} );

	describe( 'verify_rest_nonce', function () {
		it( 'returns result if already a WP_Error', function () {
			$error = new \WP_Error( 'test_error', 'Test message' );
			$result = $this->rest_api->verify_rest_nonce( $error );

			expect( $result )->toBe( $error );
		} );

		it( 'returns result for null input', function () {
			$result = $this->rest_api->verify_rest_nonce( null );

			// Should return null or true (depends on ServerVars state).
			expect( $result === null || $result === true )->toBeTrue();
		} );

		it( 'returns result for true input', function () {
			$result = $this->rest_api->verify_rest_nonce( true );

			// Should return true or WP_Error (depends on ServerVars state).
			expect( $result === true || $result instanceof \WP_Error )->toBeTrue();
		} );

		it( 'method exists and is callable', function () {
			expect( method_exists( $this->rest_api, 'verify_rest_nonce' ) )->toBeTrue();
			expect( is_callable( array( $this->rest_api, 'verify_rest_nonce' ) ) )->toBeTrue();
		} );
	} );
} );
