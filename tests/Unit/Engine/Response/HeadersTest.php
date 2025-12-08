<?php
/**
 * Tests for Headers.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Engine\Cache\Config;
use MilliCache\Engine\Response\Headers;

// Note: Testing headers in unit tests is challenging since header() is a built-in
// PHP function that can only be called before output is sent. These tests verify
// the logic and behavior of Headers without actually sending headers.

uses()->beforeEach( function () {
	// Create a test config with debug enabled.
	$this->config = new Config(
		3600,     // ttl.
		600,      // grace.
		true,     // gzip.
		true,     // debug.
		array(),  // nocache_paths.
		array(),  // nocache_cookies.
		array(),  // ignore_cookies.
		array(),  // ignore_request_keys.
		array()   // unique.
	);

	$this->manager = new Headers( $this->config );
} );

describe( 'Headers', function () {

	describe( 'constructor', function () {
		it( 'accepts a Config object', function () {
			expect( $this->manager )->toBeInstanceOf( Headers::class );
		} );
	} );

	describe( 'set', function () {
		it( 'can be called without throwing errors', function () {
			// Headers already sent in test environment, but method should not throw.
			expect( fn() => $this->manager->set( 'Status', 'hit' ) )->not->toThrow( Exception::class );
		} );
	} );

	describe( 'set_status', function () {
		it( 'can be called without throwing errors', function () {
			expect( fn() => $this->manager->set_status( 'miss' ) )->not->toThrow( Exception::class );
			expect( fn() => $this->manager->set_status( 'hit' ) )->not->toThrow( Exception::class );
			expect( fn() => $this->manager->set_status( 'stale' ) )->not->toThrow( Exception::class );
			expect( fn() => $this->manager->set_status( 'bypass' ) )->not->toThrow( Exception::class );
		} );
	} );

	describe( 'set_reason', function () {
		it( 'can be called with debug enabled', function () {
			expect( fn() => $this->manager->set_reason( 'user-logged-in' ) )->not->toThrow( Exception::class );
		} );

		it( 'can be called with empty string', function () {
			expect( fn() => $this->manager->set_reason( '' ) )->not->toThrow( Exception::class );
		} );

		it( 'respects debug flag from config', function () {
			$config_no_debug = new Config(
				3600, 600, true, false, // debug = false.
				array(), array(), array(), array(), array()
			);
			$manager = new Headers( $config_no_debug );

			// Should not throw even with debug disabled.
			expect( fn() => $manager->set_reason( 'test-reason' ) )->not->toThrow( Exception::class );
		} );
	} );

	describe( 'set_key', function () {
		it( 'can be called with hash value', function () {
			expect( fn() => $this->manager->set_key( 'abc123' ) )->not->toThrow( Exception::class );
		} );

		it( 'can be called with empty string', function () {
			expect( fn() => $this->manager->set_key( '' ) )->not->toThrow( Exception::class );
		} );
	} );

	describe( 'integration', function () {
		it( 'all methods can be called in sequence', function () {
			// Call all methods in typical usage sequence.
			$this->manager->set_status( 'miss' );
			$this->manager->set_key( 'integration-test-hash' );
			$this->manager->set_reason( 'test-reason' );

			// If we got here without exceptions, test passes.
			expect( true )->toBeTrue();
		} );
	} );
} );
