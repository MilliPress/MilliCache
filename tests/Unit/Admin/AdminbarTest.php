<?php
/**
 * Tests for Adminbar class.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Admin\Adminbar;

// Mock WordPress Admin Bar class.
if ( ! class_exists( 'WP_Admin_Bar' ) ) {
	class WP_Admin_Bar {
		public $menus = array();
		public $groups = array();

		public function add_menu( $args ) {
			$this->menus[] = $args;
		}

		public function add_group( $args ) {
			$this->groups[] = $args;
		}
	}
}

// Mock WordPress functions.
if ( ! function_exists( 'is_admin_bar_showing' ) ) {
	function is_admin_bar_showing() {
		global $test_admin_bar_showing;
		return $test_admin_bar_showing ?? true;
	}
}

if ( ! function_exists( 'current_user_can' ) ) {
	function current_user_can( $capability ) {
		global $test_current_user_can;
		return $test_current_user_can ?? true;
	}
}

if ( ! function_exists( 'add_query_arg' ) ) {
	function add_query_arg( $arg1, $arg2 = '' ) {
		if ( is_array( $arg1 ) ) {
			$params = array();
			foreach ( $arg1 as $key => $value ) {
				$params[] = "$key=$value";
			}
			return '?' . implode( '&', $params );
		}
		return "?$arg1=$arg2";
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'is_admin' ) ) {
	function is_admin() {
		global $test_is_admin;
		return $test_is_admin ?? false;
	}
}

if ( ! function_exists( 'is_network_admin' ) ) {
	function is_network_admin() {
		global $test_is_network_admin;
		return $test_is_network_admin ?? false;
	}
}

if ( ! function_exists( 'admin_url' ) ) {
	function admin_url( $path ) {
		return '/wp-admin/' . $path;
	}
}

if ( ! function_exists( 'size_format' ) ) {
	function size_format( $bytes, $decimals = 0 ) {
		if ( $bytes < 1024 ) {
			return $bytes . ' B';
		} elseif ( $bytes < 1048576 ) {
			return round( $bytes / 1024, $decimals ) . ' KB';
		} else {
			return round( $bytes / 1048576, $decimals ) . ' MB';
		}
	}
}

if ( ! function_exists( '_n' ) ) {
	function _n( $single, $plural, $number, $domain = 'default' ) {
		return $number === 1 ? $single : $plural;
	}
}

if ( ! function_exists( 'get_site_transient' ) ) {
	function get_site_transient( $key ) {
		global $test_site_transients;
		return $test_site_transients[ $key ] ?? false;
	}
}

if ( ! function_exists( 'set_site_transient' ) ) {
	function set_site_transient( $key, $value, $expiration ) {
		global $test_site_transients;
		$test_site_transients[ $key ] = $value;
		return true;
	}
}

if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}

uses()->beforeEach( function () {
	global $test_admin_bar_showing, $test_current_user_can, $test_is_admin, $test_is_network_admin, $test_site_transients;
	$test_admin_bar_showing = true;
	$test_current_user_can = true;
	$test_is_admin = false;
	$test_is_network_admin = false;
	$test_site_transients = array();
} );

/**
 * Note: The Adminbar class constructor requires an Engine instance which is a final class
 * and cannot be mocked. These tests focus on verifying the class structure and static behaviors.
 */
describe( 'Adminbar', function () {

	describe( 'class structure', function () {
		it( 'class exists', function () {
			expect( class_exists( Adminbar::class ) )->toBeTrue();
		} );

		it( 'has constructor method', function () {
			expect( method_exists( Adminbar::class, '__construct' ) )->toBeTrue();
		} );

		it( 'has add_adminbar_menu method', function () {
			expect( method_exists( Adminbar::class, 'add_adminbar_menu' ) )->toBeTrue();
		} );

		it( 'has enqueue_adminbar_assets method', function () {
			expect( method_exists( Adminbar::class, 'enqueue_adminbar_assets' ) )->toBeTrue();
		} );
	} );

	describe( 'constructor signature', function () {
		it( 'requires Loader as first parameter', function () {
			$reflection = new ReflectionClass( Adminbar::class );
			$constructor = $reflection->getConstructor();
			$params = $constructor->getParameters();

			expect( $params[0]->getName() )->toBe( 'loader' );
			expect( $params[0]->getType()->getName() )->toBe( 'MilliCache\Core\Loader' );
		} );

		it( 'requires Engine as second parameter', function () {
			$reflection = new ReflectionClass( Adminbar::class );
			$constructor = $reflection->getConstructor();
			$params = $constructor->getParameters();

			expect( $params[1]->getName() )->toBe( 'engine' );
			expect( $params[1]->getType()->getName() )->toBe( 'MilliCache\Engine' );
		} );

		it( 'has exactly two required parameters', function () {
			$reflection = new ReflectionClass( Adminbar::class );
			$constructor = $reflection->getConstructor();
			$params = $constructor->getParameters();

			expect( count( $params ) )->toBe( 2 );
		} );
	} );

	describe( 'add_adminbar_menu method signature', function () {
		it( 'requires WP_Admin_Bar as parameter', function () {
			$reflection = new ReflectionMethod( Adminbar::class, 'add_adminbar_menu' );
			$params = $reflection->getParameters();

			expect( $params[0]->getName() )->toBe( 'wp_admin_bar' );
			expect( $params[0]->getType()->getName() )->toBe( 'WP_Admin_Bar' );
		} );

		it( 'returns void or has no explicit return type', function () {
			$reflection = new ReflectionMethod( Adminbar::class, 'add_adminbar_menu' );
			$return_type = $reflection->getReturnType();

			// Method may or may not have explicit return type.
			if ( $return_type !== null ) {
				expect( $return_type->getName() )->toBe( 'void' );
			} else {
				expect( $return_type )->toBeNull();
			}
		} );
	} );

	describe( 'enqueue_adminbar_assets method signature', function () {
		it( 'takes no parameters', function () {
			$reflection = new ReflectionMethod( Adminbar::class, 'enqueue_adminbar_assets' );
			$params = $reflection->getParameters();

			expect( count( $params ) )->toBe( 0 );
		} );

		it( 'returns void or has no explicit return type', function () {
			$reflection = new ReflectionMethod( Adminbar::class, 'enqueue_adminbar_assets' );
			$return_type = $reflection->getReturnType();

			// Method may or may not have explicit return type.
			if ( $return_type !== null ) {
				expect( $return_type->getName() )->toBe( 'void' );
			} else {
				expect( $return_type )->toBeNull();
			}
		} );
	} );

	describe( 'class properties', function () {
		it( 'is a final class', function () {
			$reflection = new ReflectionClass( Adminbar::class );
			expect( $reflection->isFinal() )->toBeTrue();
		} );

		it( 'has protected loader property', function () {
			$reflection = new ReflectionClass( Adminbar::class );
			expect( $reflection->hasProperty( 'loader' ) )->toBeTrue();
			expect( $reflection->getProperty( 'loader' )->isProtected() )->toBeTrue();
		} );

		it( 'has private engine property', function () {
			$reflection = new ReflectionClass( Adminbar::class );
			expect( $reflection->hasProperty( 'engine' ) )->toBeTrue();
			expect( $reflection->getProperty( 'engine' )->isPrivate() )->toBeTrue();
		} );
	} );
} );
