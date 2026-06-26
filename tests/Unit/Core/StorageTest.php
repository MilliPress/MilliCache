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

	describe( 'toggle_cache_key', function () {
		it( 'adds cache prefix when missing', function () {
			$settings = array(
				'host' => '127.0.0.1',
				'port' => 6379,
				'enc_password' => '',
				'db' => 0,
				'prefix' => 'test',
				'persistent' => false,
			);

			$storage = new Storage( $settings );
			$result = $storage->toggle_cache_key( 'my-key' );

			expect( $result )->toBe( 'test:c:my-key' );
		} );

		it( 'strips cache prefix when present', function () {
			$settings = array(
				'host' => '127.0.0.1',
				'port' => 6379,
				'enc_password' => '',
				'db' => 0,
				'prefix' => 'test',
				'persistent' => false,
			);

			$storage = new Storage( $settings );
			$result = $storage->toggle_cache_key( 'test:c:my-key' );

			expect( $result )->toBe( 'my-key' );
		} );
	} );

	describe( 'toggle_flag_key', function () {
		it( 'adds flag prefix when missing', function () {
			$settings = array(
				'host' => '127.0.0.1',
				'port' => 6379,
				'enc_password' => '',
				'db' => 0,
				'prefix' => 'mll',
				'persistent' => false,
			);

			$storage = new Storage( $settings );
			$result = $storage->toggle_flag_key( 'post-1' );

			expect( $result )->toBe( 'mll:f:post-1' );
		} );

		it( 'strips flag prefix when present', function () {
			$settings = array(
				'host' => '127.0.0.1',
				'port' => 6379,
				'enc_password' => '',
				'db' => 0,
				'prefix' => 'mll',
				'persistent' => false,
			);

			$storage = new Storage( $settings );
			$result = $storage->toggle_flag_key( 'mll:f:post-1' );

			expect( $result )->toBe( 'post-1' );
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

		it( 'constructs with a username without error', function () {
			$settings = array(
				'host'         => '127.0.0.1',
				'port'         => 6379,
				'username'     => 'myuser',
				'enc_password' => '',
				'db'           => 0,
				'prefix'       => 'test',
				'persistent'   => false,
			);

			$storage = new Storage( $settings );

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
			$status = suppressing_errors( fn() => $storage->get_status() );

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
			$status = suppressing_errors( fn() => $storage->get_status() );

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

			expect( suppressing_errors( fn() => $storage->get_status() )['config']['scheme'] )->toBe( 'tcp' );
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

			expect( suppressing_errors( fn() => $storage->get_status() )['config']['scheme'] )->toBe( 'unix' );
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

	describe( 'Layer-1 fail-fast', function () {
		it( 'dials the connection at most once per request', function () {
			$settings = array(
				'host'       => '127.0.0.1',
				'port'       => 6379,
				'prefix'     => 'test',
				'persistent' => false,
			);

			$storage = new Storage( $settings );

			// A stub client whose every command throws the connect-failure
			// exception Predis 3.x actually raises (StreamInitException, a sibling
			// of ConnectionException — not a subclass). The first call should trip
			// the memoized flag; the second should short-circuit without touching
			// the client.
			$stub = new class() extends \Predis\Client {
				public int $calls = 0;

				public function __call( $command_id, $arguments ) {
					++$this->calls;
					throw new \Predis\Connection\Resource\Exception\StreamInitException( 'Operation timed out' );
				}
			};

			$client_prop = new \ReflectionProperty( Storage::class, 'client' );
			$client_prop->setAccessible( true );
			$client_prop->setValue( $storage, $stub );

			$failed_prop = new \ReflectionProperty( Storage::class, 'connection_failed' );
			$failed_prop->setAccessible( true );

			expect( $failed_prop->getValue( $storage ) )->toBeFalse();

			$first  = suppressing_errors( fn() => $storage->lock( 'test-hash' ) );
			$second = suppressing_errors( fn() => $storage->set_count( 'test-set' ) );

			expect( $first )->toBeFalse();
			expect( $second )->toBe( 0 );
			expect( $stub->calls )->toBe( 1 );
			expect( $failed_prop->getValue( $storage ) )->toBeTrue();
		} );
	} );

	describe( 'key/value surface', function () {
		$kv_settings = array(
			'host'       => '127.0.0.1',
			'port'       => 9999, // Invalid port to trigger graceful degradation.
			'prefix'     => 'test',
			'persistent' => false,
		);

		it( 'returns null from get when storage is unavailable', function () use ( $kv_settings ) {
			$storage = new Storage( $kv_settings );
			$result  = suppressing_errors( fn() => $storage->get( 'oc:missing' ) );

			expect( $result )->toBeNull();
		} );

		it( 'returns false from set when storage is unavailable', function () use ( $kv_settings ) {
			$storage = new Storage( $kv_settings );
			$result  = suppressing_errors( fn() => $storage->set( 'oc:key', 'value' ) );

			expect( $result )->toBeFalse();
		} );

		it( 'returns 0 from delete when storage is unavailable', function () use ( $kv_settings ) {
			$storage = new Storage( $kv_settings );
			$result  = suppressing_errors( fn() => $storage->delete( 'oc:key' ) );

			expect( $result )->toBe( 0 );
		} );

		it( 'returns an empty array from get_multiple when storage is unavailable', function () use ( $kv_settings ) {
			$storage = new Storage( $kv_settings );
			$result  = suppressing_errors( fn() => $storage->get_multiple( array( 'oc:a', 'oc:b' ) ) );

			expect( $result )->toBe( array() );
		} );

		it( 'short-circuits get_multiple with no keys without touching storage', function () {
			$settings = array(
				'host'       => '127.0.0.1',
				'port'       => 6379,
				'prefix'     => 'test',
				'persistent' => false,
			);

			$storage = new Storage( $settings );

			$stub = new class() extends \Predis\Client {
				public int $calls = 0;

				public function __call( $command_id, $arguments ) {
					++$this->calls;
					return null;
				}
			};

			$client_prop = new \ReflectionProperty( Storage::class, 'client' );
			$client_prop->setAccessible( true );
			$client_prop->setValue( $storage, $stub );

			expect( $storage->get_multiple( array() ) )->toBe( array() );
			expect( $stub->calls )->toBe( 0 );
		} );

		it( 'reads many keys in one MGET, prefixing and mapping misses to null', function () {
			$settings = array(
				'host'       => '127.0.0.1',
				'port'       => 6379,
				'prefix'     => 'test',
				'persistent' => false,
			);

			$storage = new Storage( $settings );

			// A stub whose MGET returns a positional list with a miss in the
			// middle, so we can assert order-preserving remapping onto the
			// original caller keys and that MGET is called variadically.
			$stub = new class() extends \Predis\Client {
				/** @var array<int, array{0: string, 1: array<mixed>}> */
				public array $commands = array();

				public function __call( $command_id, $arguments ) {
					$id               = strtolower( (string) $command_id );
					$this->commands[] = array( $id, $arguments );

					if ( 'mget' === $id ) {
						return array( 'val-a', null, 'val-c' );
					}

					return null;
				}
			};

			$client_prop = new \ReflectionProperty( Storage::class, 'client' );
			$client_prop->setAccessible( true );
			$client_prop->setValue( $storage, $stub );

			$result = $storage->get_multiple( array( 'oc:a', 'oc:b', 'oc:c' ) );

			expect( $result )->toBe(
				array(
					'oc:a' => 'val-a',
					'oc:b' => null,
					'oc:c' => 'val-c',
				)
			);
			// MGET must receive discrete, prefixed arguments, not one nested array.
			expect( $stub->commands[0] )->toBe( array( 'mget', array( 'test:oc:a', 'test:oc:b', 'test:oc:c' ) ) );
		} );

		it( 'returns 0 from delete_by_pattern when storage is unavailable', function () use ( $kv_settings ) {
			$storage = new Storage( $kv_settings );
			$result  = suppressing_errors( fn() => $storage->delete_by_pattern( 'oc:*' ) );

			expect( $result )->toBe( 0 );
		} );

		it( 'short-circuits delete with no keys without touching storage', function () {
			$settings = array(
				'host'       => '127.0.0.1',
				'port'       => 6379,
				'prefix'     => 'test',
				'persistent' => false,
			);

			$storage = new Storage( $settings );

			$stub = new class() extends \Predis\Client {
				public int $calls = 0;

				public function __call( $command_id, $arguments ) {
					++$this->calls;
					return null;
				}
			};

			$client_prop = new \ReflectionProperty( Storage::class, 'client' );
			$client_prop->setAccessible( true );
			$client_prop->setValue( $storage, $stub );

			expect( $storage->delete() )->toBe( 0 );
			expect( $stub->calls )->toBe( 0 );
		} );

		it( 'prefixes keys and issues commands correctly', function () {
			$settings = array(
				'host'       => '127.0.0.1',
				'port'       => 6379,
				'prefix'     => 'test',
				'persistent' => false,
			);

			$storage = new Storage( $settings );

			// A stub client that records each dispatched command and returns
			// canned values, so we can assert exactly how Storage prefixes keys
			// and that DEL is called variadically (not with a nested array arg).
			$stub = new class() extends \Predis\Client {
				/** @var array<int, array{0: string, 1: array<mixed>}> */
				public array $commands = array();

				public function __call( $command_id, $arguments ) {
					$id               = strtolower( (string) $command_id );
					$this->commands[] = array( $id, $arguments );

					switch ( $id ) {
						case 'get':
							return 'stored-value';
						case 'set':
							return 'OK';
						case 'del':
							return count( $arguments );
						default:
							return null;
					}
				}
			};

			$client_prop = new \ReflectionProperty( Storage::class, 'client' );
			$client_prop->setAccessible( true );
			$client_prop->setValue( $storage, $stub );

			expect( $storage->get( 'oc:foo' ) )->toBe( 'stored-value' );
			expect( $storage->set( 'oc:foo', 'bar', 30 ) )->toBeTrue();
			expect( $storage->set( 'oc:foo', 'bar' ) )->toBeTrue();
			expect( $storage->delete( 'oc:a', 'oc:b' ) )->toBe( 2 );

			expect( $stub->commands[0] )->toBe( array( 'get', array( 'test:oc:foo' ) ) );
			expect( $stub->commands[1] )->toBe( array( 'set', array( 'test:oc:foo', 'bar', 'EX', 30 ) ) );
			expect( $stub->commands[2] )->toBe( array( 'set', array( 'test:oc:foo', 'bar' ) ) );
			// DEL must receive two discrete arguments, not one nested array.
			expect( $stub->commands[3] )->toBe( array( 'del', array( 'test:oc:a', 'test:oc:b' ) ) );
		} );
	} );
} );
