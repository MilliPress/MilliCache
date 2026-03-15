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

		it( 'constructs with a Unix socket path without error', function () {
			$settings = array(
				'host' => '/var/run/redis/redis.sock',
				'port' => 0,
				'enc_password' => '',
				'db' => 0,
				'prefix' => 'test',
				'persistent' => false,
			);

			$storage = new Storage( $settings );

			// Storage should initialize with the unix scheme without throwing.
			expect( $storage )->toBeInstanceOf( Storage::class );
		} );

		it( 'parses tls scheme from host prefix', function () {
			$settings = array(
				'host' => 'tls://master.example.cache.amazonaws.com',
				'port' => 6379,
				'enc_password' => '',
				'db' => 0,
				'prefix' => 'test',
				'persistent' => false,
			);

			$storage = new Storage( $settings );
			$status = $storage->get_status();

			expect( $storage )->toBeInstanceOf( Storage::class );
			expect( $status['config']['scheme'] )->toBe( 'tls' );
			expect( $status['config']['host'] )->toBe( 'master.example.cache.amazonaws.com' );
		} );

		it( 'parses tcp scheme from host prefix', function () {
			$settings = array(
				'host' => 'tcp://10.0.0.5',
				'port' => 6379,
				'enc_password' => '',
				'db' => 0,
				'prefix' => 'test',
				'persistent' => false,
			);

			$storage = new Storage( $settings );
			$status = $storage->get_status();

			expect( $status['config']['scheme'] )->toBe( 'tcp' );
			expect( $status['config']['host'] )->toBe( '10.0.0.5' );
		} );

		it( 'defaults to tcp scheme when host has no prefix', function () {
			$settings = array(
				'host' => '127.0.0.1',
				'port' => 6379,
				'enc_password' => '',
				'db' => 0,
				'prefix' => 'test',
				'persistent' => false,
			);

			$storage = new Storage( $settings );

			expect( $storage->get_status()['config']['scheme'] )->toBe( 'tcp' );
		} );

		it( 'uses unix scheme for socket path even with no prefix', function () {
			$settings = array(
				'host' => '/var/run/redis/redis.sock',
				'port' => 0,
				'enc_password' => '',
				'db' => 0,
				'prefix' => 'test',
				'persistent' => false,
			);

			$storage = new Storage( $settings );

			expect( $storage->get_status()['config']['scheme'] )->toBe( 'unix' );
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
			$result = suppressing_errors( fn() => $storage->set_add( 'test-set', 'member' ) );

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
			$result = suppressing_errors( fn() => $storage->set_pop( 'test-set', 1 ) );

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
			$result = suppressing_errors( fn() => $storage->set_count( 'test-set' ) );

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
			$result = suppressing_errors( fn() => $storage->get_cache( 'non-existent-hash' ) );

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
			$result = suppressing_errors( fn() => $storage->lock( 'test-hash' ) );

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
			$result = suppressing_errors( fn() => $storage->unlock( 'test-hash' ) );

			expect( $result )->toBeFalse();
		} );
	} );
} );
