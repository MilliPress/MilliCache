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
use MilliCache\Core\Loader;

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

uses()->beforeEach( function () {
	global $test_admin_bar_showing, $test_current_user_can, $test_is_admin, $test_is_network_admin;
	$test_admin_bar_showing = true;
	$test_current_user_can = true;
	$test_is_admin = false;
	$test_is_network_admin = false;
} );

describe( 'Adminbar', function () {

	describe( 'constructor', function () {
		it( 'initializes with loader', function () {
			$loader = Mockery::mock( Loader::class );
			$loader->shouldReceive( 'add_action' )->andReturn( null );

			$adminbar = new Adminbar( $loader );

			expect( $adminbar )->toBeInstanceOf( Adminbar::class );
		} );

		it( 'registers hooks', function () {
			$loader = Mockery::mock( Loader::class );
			$loader->shouldReceive( 'add_action' )->with( 'wp_enqueue_scripts', Mockery::type( Adminbar::class ), 'enqueue_adminbar_assets' )->once();
			$loader->shouldReceive( 'add_action' )->with( 'admin_enqueue_scripts', Mockery::type( Adminbar::class ), 'enqueue_adminbar_assets' )->once();
			$loader->shouldReceive( 'add_action' )->with( 'admin_bar_menu', Mockery::type( Adminbar::class ), 'add_adminbar_menu', 999 )->once();

			new Adminbar( $loader );

			expect( true )->toBeTrue();
		} );
	} );

	describe( 'add_adminbar_menu', function () {
		it( 'adds root menu to admin bar', function () {
			$loader = Mockery::mock( Loader::class );
			$loader->shouldReceive( 'add_action' )->andReturn( null );

			$adminbar = new Adminbar( $loader );

			$millicache = Mockery::mock( 'alias:MilliCache\MilliCache' );
			$millicache->shouldReceive( 'get_clear_cache_capability' )->andReturn( 'manage_options' );

			// Mock Engine for flags and cache size
			$storage = Mockery::mock();
			$storage->shouldReceive( 'get_cache_size' )->andReturn( array( 'index' => 10, 'size' => 5120 ) );
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'get_flags' )->andReturn( array( 'flag1', 'flag2' ) );
			$engine->shouldReceive( 'get_storage' )->andReturn( $storage );
			$engine->shouldReceive( 'get_flag_prefix' )->andReturn( '' );

			$wp_admin_bar = new WP_Admin_Bar();
			$adminbar->add_adminbar_menu( $wp_admin_bar );

			expect( $wp_admin_bar->menus )->toBeArray();
			expect( count( $wp_admin_bar->menus ) )->toBeGreaterThan( 0 );
		} );

		it( 'does not add menu when admin bar is not showing', function () {
			global $test_admin_bar_showing;
			$test_admin_bar_showing = false;

			$loader = Mockery::mock( Loader::class );
			$loader->shouldReceive( 'add_action' )->andReturn( null );

			$adminbar = new Adminbar( $loader );

			$wp_admin_bar = new WP_Admin_Bar();
			$adminbar->add_adminbar_menu( $wp_admin_bar );

			expect( $wp_admin_bar->menus )->toHaveCount( 0 );
		} );

		it( 'does not add menu when user cannot clear cache', function () {
			global $test_current_user_can;
			$test_current_user_can = false;

			$loader = Mockery::mock( Loader::class );
			$loader->shouldReceive( 'add_action' )->andReturn( null );

			$millicache = Mockery::mock( 'alias:MilliCache\MilliCache' );
			$millicache->shouldReceive( 'get_clear_cache_capability' )->andReturn( 'manage_options' );

			$adminbar = new Adminbar( $loader );

			$wp_admin_bar = new WP_Admin_Bar();
			$adminbar->add_adminbar_menu( $wp_admin_bar );

			expect( $wp_admin_bar->menus )->toHaveCount( 0 );
		} );

		it( 'adds settings menu when user can manage options', function () {
			$loader = Mockery::mock( Loader::class );
			$loader->shouldReceive( 'add_action' )->andReturn( null );

			$millicache = Mockery::mock( 'alias:MilliCache\MilliCache' );
			$millicache->shouldReceive( 'get_clear_cache_capability' )->andReturn( 'manage_options' );

			// Mock Engine for flags and cache size
			$storage = Mockery::mock();
			$storage->shouldReceive( 'get_cache_size' )->andReturn( array( 'index' => 10, 'size' => 10240 ) );
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'get_flags' )->andReturn( array() );
			$engine->shouldReceive( 'get_storage' )->andReturn( $storage );
			$engine->shouldReceive( 'get_flag_prefix' )->andReturn( '' );

			$adminbar = new Adminbar( $loader );

			$wp_admin_bar = new WP_Admin_Bar();
			$adminbar->add_adminbar_menu( $wp_admin_bar );

			// Should have added a group for secondary items.
			expect( $wp_admin_bar->groups )->toBeArray();
			expect( count( $wp_admin_bar->groups ) )->toBeGreaterThan( 0 );
		} );
	} );

	describe( 'enqueue_adminbar_assets', function () {
		it( 'method exists and is callable', function () {
			expect( method_exists( Adminbar::class, 'enqueue_adminbar_assets' ) )->toBeTrue();
		} );
	} );
} );
