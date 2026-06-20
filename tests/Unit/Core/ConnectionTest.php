<?php
/**
 * Tests for the Connection class.
 *
 * @link       https://www.millipress.com
 * @since      1.7.0
 *
 * @package    MilliCache
 */

use MilliCache\Core\Connection;
use Predis\Connection\Replication\MasterSlaveReplication;
use Predis\Connection\Replication\SentinelReplication;

describe( 'Connection', function () {

	describe( 'single mode', function () {
		it( 'builds a single-node client from a scalar host', function () {
			$connection = new Connection(
				array(
					'host' => '127.0.0.1',
					'port' => 6380,
				)
			);

			$describe = $connection->describe();

			expect( $describe['mode'] )->toBe( 'single' );
			expect( $describe['host'] )->toBe( '127.0.0.1' );
			expect( $describe['port'] )->toBe( 6380 );
			expect( $describe['scheme'] )->toBe( 'tcp' );

			$params = $connection->client()->getConnection()->getParameters();
			expect( (string) $params->host )->toBe( '127.0.0.1' );
			expect( (int) $params->port )->toBe( 6380 );
		} );

		it( 'extracts a tls:// scheme prefix from the host', function () {
			$describe = ( new Connection( array( 'host' => 'tls://redis.example.com' ) ) )->describe();

			expect( $describe['scheme'] )->toBe( 'tls' );
			expect( $describe['host'] )->toBe( 'redis.example.com' );
		} );

		it( 'treats a leading-slash host as a unix socket', function () {
			$describe = ( new Connection( array( 'host' => '/var/run/redis.sock' ) ) )->describe();

			expect( $describe['scheme'] )->toBe( 'unix' );
			expect( $describe['host'] )->toBe( '/var/run/redis.sock' );
		} );

		it( 'applies the two distinct timeouts (seconds) and shared credentials', function () {
			$connection = new Connection(
				array(
					'host'         => '127.0.0.1',
					'port'         => 6379,
					'enc_password' => 'secret',
					'db'           => 3,
					'timeout'      => 0.25,
					'read_timeout' => 1.5,
				)
			);

			$params = $connection->client()->getConnection()->getParameters();

			expect( (float) $params->timeout )->toBe( 0.25 );
			expect( (float) $params->read_write_timeout )->toBe( 1.5 );
			expect( (string) $params->password )->toBe( 'secret' );
			expect( (int) $params->database )->toBe( 3 );
		} );

		it( 'defaults the timeouts to 1.0s (connect) and 2.0s (read/write)', function () {
			$params = ( new Connection( array( 'host' => '127.0.0.1' ) ) )->client()->getConnection()->getParameters();

			expect( (float) $params->timeout )->toBe( 1.0 );
			expect( (float) $params->read_write_timeout )->toBe( 2.0 );
		} );
	} );

	describe( 'replication mode', function () {
		it( 'infers replication from a master key and marks the master first', function () {
			$connection = new Connection(
				array(
					'host' => array(
						'master'   => 'master.example.com',
						'replicas' => array( '127.0.0.1', 'replica.2' ),
					),
				)
			);

			$describe = $connection->describe();

			expect( $describe['mode'] )->toBe( 'replication' );
			expect( $describe['nodes'] )->toHaveLength( 3 );
			expect( $describe['nodes'][0]['host'] )->toBe( 'master.example.com' );
			expect( $describe['nodes'][0]['role'] )->toBe( 'master' );
			expect( $describe['nodes'][1]['host'] )->toBe( '127.0.0.1' );
			expect( $describe['nodes'][1] )->not->toHaveKey( 'role' );

			expect( $connection->client()->getConnection() )->toBeInstanceOf( MasterSlaveReplication::class );
		} );

		it( 'accepts a master with no replicas', function () {
			$describe = ( new Connection( array( 'host' => array( 'master' => 'master.example.com' ) ) ) )->describe();

			expect( $describe['mode'] )->toBe( 'replication' );
			expect( $describe['nodes'] )->toHaveLength( 1 );
			expect( $describe['nodes'][0]['role'] )->toBe( 'master' );
		} );

		it( 'accepts a single replica given as a string', function () {
			$describe = ( new Connection(
				array(
					'host' => array(
						'master'   => 'master.example.com',
						'replicas' => 'replica.1',
					),
				)
			) )->describe();

			expect( $describe['nodes'] )->toHaveLength( 2 );
			expect( $describe['nodes'][1]['host'] )->toBe( 'replica.1' );
		} );

		it( 'accepts a master node map with a custom port', function () {
			$describe = ( new Connection(
				array(
					'host' => array(
						'master'   => array(
							'host' => 'master.example.com',
							'port' => 6380,
						),
						'replicas' => array( 'replica.example.com:6390' ),
					),
				)
			) )->describe();

			expect( $describe['nodes'][0]['port'] )->toBe( 6380 );
			expect( $describe['nodes'][1]['host'] )->toBe( 'replica.example.com' );
			expect( $describe['nodes'][1]['port'] )->toBe( 6390 );
		} );
	} );

	describe( 'strict validation', function () {
		it( 'disables the cache when the array has neither master nor service', function () {
			$connection = suppressing_errors( fn() => new Connection( array( 'host' => array( 'oops' => true ) ) ) );

			expect( $connection->describe()['mode'] )->toBe( 'disabled' );
			expect( $connection->client() )->toBeNull();
		} );

		it( 'disables the cache when both master and service are present', function () {
			$connection = suppressing_errors(
				fn() => new Connection(
					array(
						'host' => array(
							'master'  => 'm.example.com',
							'service' => 'mymaster',
						),
					)
				)
			);

			expect( $connection->describe()['mode'] )->toBe( 'disabled' );
		} );

		it( 'disables the cache when sentinel mode is missing its sentinels', function () {
			$connection = suppressing_errors( fn() => new Connection( array( 'host' => array( 'service' => 'mymaster' ) ) ) );

			expect( $connection->describe()['mode'] )->toBe( 'disabled' );
		} );
	} );

	describe( 'sentinel mode', function () {
		it( 'infers sentinel from the presence of a service key', function () {
			$connection = new Connection(
				array(
					'host' => array(
						'service'   => 'mymaster',
						'sentinels' => array( '10.0.0.1:26379', '10.0.0.2:26379' ),
					),
				)
			);

			$describe = $connection->describe();

			expect( $describe['mode'] )->toBe( 'sentinel' );
			expect( $describe['service'] )->toBe( 'mymaster' );
			expect( $describe['nodes'] )->toHaveLength( 2 );

			expect( $connection->client()->getConnection() )->toBeInstanceOf( SentinelReplication::class );
		} );
	} );
} );
