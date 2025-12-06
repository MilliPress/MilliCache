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

/**
 * Note: The RestAPI class constructor requires an Engine instance which is a final class
 * and cannot be mocked. These tests focus on verifying the class structure and signatures.
 */
describe( 'REST API', function () {

	describe( 'class structure', function () {
		it( 'class exists', function () {
			expect( class_exists( RestAPI::class ) )->toBeTrue();
		} );

		it( 'has constructor method', function () {
			expect( method_exists( RestAPI::class, '__construct' ) )->toBeTrue();
		} );

		it( 'has register_routes method', function () {
			expect( method_exists( RestAPI::class, 'register_routes' ) )->toBeTrue();
		} );

		it( 'has perform_cache_action method', function () {
			expect( method_exists( RestAPI::class, 'perform_cache_action' ) )->toBeTrue();
		} );

		it( 'has perform_settings_action method', function () {
			expect( method_exists( RestAPI::class, 'perform_settings_action' ) )->toBeTrue();
		} );

		it( 'has get_status method', function () {
			expect( method_exists( RestAPI::class, 'get_status' ) )->toBeTrue();
		} );

		it( 'has verify_rest_nonce method', function () {
			expect( method_exists( RestAPI::class, 'verify_rest_nonce' ) )->toBeTrue();
		} );
	} );

	describe( 'constructor signature', function () {
		it( 'requires Loader as first parameter', function () {
			$reflection = new ReflectionClass( RestAPI::class );
			$constructor = $reflection->getConstructor();
			$params = $constructor->getParameters();

			expect( $params[0]->getName() )->toBe( 'loader' );
			expect( $params[0]->getType()->getName() )->toBe( 'MilliCache\Core\Loader' );
		} );

		it( 'requires Engine as second parameter', function () {
			$reflection = new ReflectionClass( RestAPI::class );
			$constructor = $reflection->getConstructor();
			$params = $constructor->getParameters();

			expect( $params[1]->getName() )->toBe( 'engine' );
			expect( $params[1]->getType()->getName() )->toBe( 'MilliCache\Engine' );
		} );

		it( 'requires plugin_name as third parameter', function () {
			$reflection = new ReflectionClass( RestAPI::class );
			$constructor = $reflection->getConstructor();
			$params = $constructor->getParameters();

			expect( $params[2]->getName() )->toBe( 'plugin_name' );
			expect( $params[2]->getType()->getName() )->toBe( 'string' );
		} );

		it( 'requires version as fourth parameter', function () {
			$reflection = new ReflectionClass( RestAPI::class );
			$constructor = $reflection->getConstructor();
			$params = $constructor->getParameters();

			expect( $params[3]->getName() )->toBe( 'version' );
			expect( $params[3]->getType()->getName() )->toBe( 'string' );
		} );

		it( 'has exactly four required parameters', function () {
			$reflection = new ReflectionClass( RestAPI::class );
			$constructor = $reflection->getConstructor();
			$params = $constructor->getParameters();

			expect( count( $params ) )->toBe( 4 );
		} );
	} );

	describe( 'register_routes method signature', function () {
		it( 'takes no parameters', function () {
			$reflection = new ReflectionMethod( RestAPI::class, 'register_routes' );
			$params = $reflection->getParameters();

			expect( count( $params ) )->toBe( 0 );
		} );

		it( 'returns void or has no explicit return type', function () {
			$reflection = new ReflectionMethod( RestAPI::class, 'register_routes' );
			$return_type = $reflection->getReturnType();

			// Method may or may not have explicit return type.
			if ( $return_type !== null ) {
				expect( $return_type->getName() )->toBe( 'void' );
			} else {
				expect( $return_type )->toBeNull();
			}
		} );
	} );

	describe( 'perform_cache_action method signature', function () {
		it( 'requires WP_REST_Request as parameter', function () {
			$reflection = new ReflectionMethod( RestAPI::class, 'perform_cache_action' );
			$params = $reflection->getParameters();

			expect( $params[0]->getName() )->toBe( 'request' );
			expect( $params[0]->getType()->getName() )->toBe( 'WP_REST_Request' );
		} );

		it( 'has one parameter', function () {
			$reflection = new ReflectionMethod( RestAPI::class, 'perform_cache_action' );
			$params = $reflection->getParameters();

			expect( count( $params ) )->toBe( 1 );
		} );
	} );

	describe( 'perform_settings_action method signature', function () {
		it( 'requires WP_REST_Request as parameter', function () {
			$reflection = new ReflectionMethod( RestAPI::class, 'perform_settings_action' );
			$params = $reflection->getParameters();

			expect( $params[0]->getName() )->toBe( 'request' );
			expect( $params[0]->getType()->getName() )->toBe( 'WP_REST_Request' );
		} );

		it( 'has one parameter', function () {
			$reflection = new ReflectionMethod( RestAPI::class, 'perform_settings_action' );
			$params = $reflection->getParameters();

			expect( count( $params ) )->toBe( 1 );
		} );
	} );

	describe( 'get_status method signature', function () {
		it( 'requires WP_REST_Request as parameter', function () {
			$reflection = new ReflectionMethod( RestAPI::class, 'get_status' );
			$params = $reflection->getParameters();

			expect( $params[0]->getName() )->toBe( 'request' );
			expect( $params[0]->getType()->getName() )->toBe( 'WP_REST_Request' );
		} );

		it( 'has one parameter', function () {
			$reflection = new ReflectionMethod( RestAPI::class, 'get_status' );
			$params = $reflection->getParameters();

			expect( count( $params ) )->toBe( 1 );
		} );
	} );

	describe( 'verify_rest_nonce method signature', function () {
		it( 'takes result parameter', function () {
			$reflection = new ReflectionMethod( RestAPI::class, 'verify_rest_nonce' );
			$params = $reflection->getParameters();

			expect( count( $params ) )->toBeGreaterThanOrEqual( 1 );
			expect( $params[0]->getName() )->toBe( 'result' );
		} );
	} );

	describe( 'class properties', function () {
		it( 'is a final class', function () {
			$reflection = new ReflectionClass( RestAPI::class );
			expect( $reflection->isFinal() )->toBeTrue();
		} );

		it( 'has protected loader property', function () {
			$reflection = new ReflectionClass( RestAPI::class );
			expect( $reflection->hasProperty( 'loader' ) )->toBeTrue();
			expect( $reflection->getProperty( 'loader' )->isProtected() )->toBeTrue();
		} );

		it( 'has private engine property', function () {
			$reflection = new ReflectionClass( RestAPI::class );
			expect( $reflection->hasProperty( 'engine' ) )->toBeTrue();
			expect( $reflection->getProperty( 'engine' )->isPrivate() )->toBeTrue();
		} );

		it( 'has private plugin_name property', function () {
			$reflection = new ReflectionClass( RestAPI::class );
			expect( $reflection->hasProperty( 'plugin_name' ) )->toBeTrue();
			expect( $reflection->getProperty( 'plugin_name' )->isPrivate() )->toBeTrue();
		} );

		it( 'has private version property', function () {
			$reflection = new ReflectionClass( RestAPI::class );
			expect( $reflection->hasProperty( 'version' ) )->toBeTrue();
			expect( $reflection->getProperty( 'version' )->isPrivate() )->toBeTrue();
		} );
	} );
} );
