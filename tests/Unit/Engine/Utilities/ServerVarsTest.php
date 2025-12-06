<?php
/**
 * Tests for ServerVars utility.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Engine\Utilities\ServerVars;

describe( 'ServerVars', function () {

	beforeEach( function () {
		// Save original $_SERVER state.
		$this->original_server = $_SERVER;
	} );

	afterEach( function () {
		// Restore original $_SERVER.
		$_SERVER = $this->original_server;
	} );

	describe( 'get', function () {
		it( 'returns server variable value', function () {
			$_SERVER['REQUEST_URI'] = '/test/path';
			expect( ServerVars::get( 'REQUEST_URI' ) )->toBe( '/test/path' );
		} );

		it( 'returns empty string for missing variable', function () {
			unset( $_SERVER['NONEXISTENT'] );
			expect( ServerVars::get( 'NONEXISTENT' ) )->toBe( '' );
		} );

		it( 'strips slashes from values', function () {
			$_SERVER['TEST_VAR'] = "test\'value\"here";
			$result = ServerVars::get( 'TEST_VAR' );
			expect( $result )->toBe( "test&#039;value&quot;here" );
		} );

		it( 'sanitizes HTML special characters', function () {
			$_SERVER['TEST_VAR'] = '<script>alert("xss")</script>';
			$result = ServerVars::get( 'TEST_VAR' );
			expect( $result )->toContain( '&lt;script&gt;' );
			expect( $result )->toContain( '&quot;' );
		} );

		it( 'handles common server variables', function () {
			$_SERVER['HTTP_HOST'] = 'example.com';
			$_SERVER['REQUEST_METHOD'] = 'GET';
			$_SERVER['HTTPS'] = 'on';

			expect( ServerVars::get( 'HTTP_HOST' ) )->toBe( 'example.com' );
			expect( ServerVars::get( 'REQUEST_METHOD' ) )->toBe( 'GET' );
			expect( ServerVars::get( 'HTTPS' ) )->toBe( 'on' );
		} );
	} );

	describe( 'has', function () {
		it( 'returns true for existing variable', function () {
			$_SERVER['REQUEST_URI'] = '/test';
			expect( ServerVars::has( 'REQUEST_URI' ) )->toBeTrue();
		} );

		it( 'returns false for missing variable', function () {
			unset( $_SERVER['NONEXISTENT'] );
			expect( ServerVars::has( 'NONEXISTENT' ) )->toBeFalse();
		} );

		it( 'returns true for empty string value', function () {
			$_SERVER['EMPTY_VAR'] = '';
			expect( ServerVars::has( 'EMPTY_VAR' ) )->toBeTrue();
		} );
	} );

	describe( 'get_many', function () {
		it( 'returns multiple server variables', function () {
			$_SERVER['HTTP_HOST'] = 'example.com';
			$_SERVER['REQUEST_METHOD'] = 'GET';
			$_SERVER['REQUEST_URI'] = '/test';

			$result = ServerVars::get_many( array( 'HTTP_HOST', 'REQUEST_METHOD', 'REQUEST_URI' ) );

			expect( $result )->toBeArray();
			expect( $result )->toHaveKey( 'HTTP_HOST' );
			expect( $result )->toHaveKey( 'REQUEST_METHOD' );
			expect( $result )->toHaveKey( 'REQUEST_URI' );
			expect( $result['HTTP_HOST'] )->toBe( 'example.com' );
			expect( $result['REQUEST_METHOD'] )->toBe( 'GET' );
			expect( $result['REQUEST_URI'] )->toBe( '/test' );
		} );

		it( 'returns empty strings for missing variables', function () {
			$_SERVER['EXISTING'] = 'value';

			$result = ServerVars::get_many( array( 'EXISTING', 'NONEXISTENT' ) );

			expect( $result['EXISTING'] )->toBe( 'value' );
			expect( $result['NONEXISTENT'] )->toBe( '' );
		} );

		it( 'handles empty array', function () {
			$result = ServerVars::get_many( array() );
			expect( $result )->toBeArray();
			expect( $result )->toBeEmpty();
		} );
	} );

	describe( 'real-world scenarios', function () {
		it( 'safely retrieves request information', function () {
			$_SERVER['REQUEST_URI'] = '/blog/post?id=123&name=Test';
			$_SERVER['HTTP_HOST'] = 'www.example.com';
			$_SERVER['REQUEST_METHOD'] = 'GET';
			$_SERVER['HTTPS'] = 'on';

			$request_info = ServerVars::get_many( array(
				'REQUEST_URI',
				'HTTP_HOST',
				'REQUEST_METHOD',
				'HTTPS',
			) );

			expect( $request_info['REQUEST_URI'] )->toContain( '/blog/post' );
			expect( $request_info['HTTP_HOST'] )->toBe( 'www.example.com' );
			expect( $request_info['REQUEST_METHOD'] )->toBe( 'GET' );
			expect( $request_info['HTTPS'] )->toBe( 'on' );
		} );
	} );
} );
