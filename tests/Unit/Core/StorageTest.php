<?php
/**
 * Tests for Storage class.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Core\Storage;

describe( 'Storage', function () {

	describe( 'is_available', function () {
		it( 'returns true when Predis Autoloader is available', function () {
			expect( Storage::is_available() )->toBeTrue();
		} );
	} );

	describe( 'get_key', function () {
		it( 'generates cache key with prefix', function () {
			$settings = array(
				'host' => '127.0.0.1',
				'port' => 6379,
				'enc_password' => '',
				'db' => 0,
				'prefix' => 'test',
				'persistent' => false,
			);

			$storage = new Storage( $settings );
			$result = $storage->get_key( 'my-key', 'c' );

			expect( $result )->toBe( 'test:c:my-key' );
		} );

		it( 'removes existing prefix from key', function () {
			$settings = array(
				'host' => '127.0.0.1',
				'port' => 6379,
				'enc_password' => '',
				'db' => 0,
				'prefix' => 'test',
				'persistent' => false,
			);

			$storage = new Storage( $settings );
			$result = $storage->get_key( 'test:c:my-key', 'c' );

			expect( $result )->toBe( 'my-key' );
		} );

		it( 'generates flag key with correct prefix', function () {
			$settings = array(
				'host' => '127.0.0.1',
				'port' => 6379,
				'enc_password' => '',
				'db' => 0,
				'prefix' => 'mll',
				'persistent' => false,
			);

			$storage = new Storage( $settings );
			$result = $storage->get_key( 'post-1', 'f' );

			expect( $result )->toBe( 'mll:f:post-1' );
		} );

		it( 'generates key without type prefix', function () {
			$settings = array(
				'host' => '127.0.0.1',
				'port' => 6379,
				'enc_password' => '',
				'db' => 0,
				'prefix' => 'mll',
				'persistent' => false,
			);

			$storage = new Storage( $settings );
			$result = $storage->get_key( 'generic-key', '' );

			expect( $result )->toBe( 'mll:generic-key' );
		} );
	} );

	describe( 'connection handling', function () {
		it( 'detects when not connected to Redis', function () {
			$settings = array(
				'host' => '127.0.0.1',
				'port' => 9999, // Invalid port.
				'enc_password' => '',
				'db' => 0,
				'prefix' => 'test',
				'persistent' => false,
			);

			$storage = new Storage( $settings );

			// Storage should handle connection failure gracefully.
			expect( $storage )->toBeInstanceOf( Storage::class );
		} );
	} );

	describe( 'set operations', function () {
		it( 'handles set_add errors gracefully', function () {
			$settings = array(
				'host' => '127.0.0.1',
				'port' => 9999, // Invalid port to trigger error.
				'enc_password' => '',
				'db' => 0,
				'prefix' => 'test',
				'persistent' => false,
			);

			$storage = new Storage( $settings );
			$result = $storage->set_add( 'test-set', 'member' );

			// Should return 0 on error.
			expect( $result )->toBe( 0 );
		} );

		it( 'handles set_pop errors gracefully', function () {
			$settings = array(
				'host' => '127.0.0.1',
				'port' => 9999, // Invalid port to trigger error.
				'enc_password' => '',
				'db' => 0,
				'prefix' => 'test',
				'persistent' => false,
			);

			$storage = new Storage( $settings );
			$result = $storage->set_pop( 'test-set', 1 );

			// Should return empty array on error.
			expect( $result )->toBe( array() );
		} );

		it( 'handles set_count errors gracefully', function () {
			$settings = array(
				'host' => '127.0.0.1',
				'port' => 9999, // Invalid port to trigger error.
				'enc_password' => '',
				'db' => 0,
				'prefix' => 'test',
				'persistent' => false,
			);

			$storage = new Storage( $settings );
			$result = $storage->set_count( 'test-set' );

			// Should return 0 on error.
			expect( $result )->toBe( 0 );
		} );
	} );

	describe( 'cache operations', function () {
		it( 'returns null when getting non-existent cache', function () {
			$settings = array(
				'host' => '127.0.0.1',
				'port' => 9999, // Invalid port.
				'enc_password' => '',
				'db' => 0,
				'prefix' => 'test',
				'persistent' => false,
			);

			$storage = new Storage( $settings );
			$result = $storage->get_cache( 'non-existent-hash' );

			expect( $result )->toBeNull();
		} );

		it( 'returns false when lock fails', function () {
			$settings = array(
				'host' => '127.0.0.1',
				'port' => 9999, // Invalid port.
				'enc_password' => '',
				'db' => 0,
				'prefix' => 'test',
				'persistent' => false,
			);

			$storage = new Storage( $settings );
			$result = $storage->lock( 'test-hash' );

			expect( $result )->toBeFalse();
		} );

		it( 'returns false when unlock fails', function () {
			$settings = array(
				'host' => '127.0.0.1',
				'port' => 9999, // Invalid port.
				'enc_password' => '',
				'db' => 0,
				'prefix' => 'test',
				'persistent' => false,
			);

			$storage = new Storage( $settings );
			$result = $storage->unlock( 'test-hash' );

			expect( $result )->toBeFalse();
		} );
	} );
} );
