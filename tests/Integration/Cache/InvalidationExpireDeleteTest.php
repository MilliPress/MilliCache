<?php
/**
 * Integration test for the expire-then-delete-by-flag interaction.
 *
 * Regression guard: expiring an entry by flag must only age it, preserving its
 * flag membership so a later clear-by-flag can still reach it. A previous
 * version re-stored the entry via set_cache() with an empty flag list, which
 * stripped every flag and orphaned the entry. Exercised against a real server;
 * skipped when Redis is unreachable.
 *
 * @link       https://www.millipress.com
 * @since      1.7.0
 *
 * @package    MilliCache
 */

use MilliCache\Core\Storage;

describe( 'Invalidation expire/delete by flag (integration)', function () {

	beforeEach( function () {
		$this->storage = new Storage(
			array(
				'host'         => '127.0.0.1',
				'port'         => 6379,
				'enc_password' => '',
				'db'           => 0,
				'persistent'   => false,
				'prefix'       => 'mc_itest',
			)
		);

		if ( ! $this->storage->ping() ) {
			$this->markTestSkipped( 'Redis not reachable on 127.0.0.1:6379' );
		}

		// Independent raw client for seeding state and verification.
		$this->redis = new \Predis\Client( array( 'host' => '127.0.0.1', 'port' => 6379 ) );
		$this->cleanup = function () {
			foreach ( array( 'mc_itest:c:*', 'mc_itest:f:*', 'mc_itest:o:*' ) as $pattern ) {
				$keys = $this->redis->keys( $pattern );
				if ( ! empty( $keys ) ) {
					$this->redis->del( $keys );
				}
			}
		};
		( $this->cleanup )();

		// Seed an entry exactly as set_cache() would: a cache hash carrying one
		// flag field, plus that key as a member of the flag's set. Empty body so
		// get_cache() treats it as present without a separate output lookup.
		$this->seed = function ( string $hash, string $flag ) {
			$key        = 'mc_itest:c:' . $hash;
			$flag_field = 'mc_itest:f:' . $flag;

			$this->redis->hmset(
				$key,
				array(
					'output'     => '',
					'meta'       => json_encode(
						array(
							'status'  => 200,
							'updated' => time(),
							'headers' => array(),
							'url'     => 'https://example.com/x',
						)
					),
					'updated'    => time(),
					'size_raw'   => 0,
					$flag_field => 1,
				)
			);
			$this->redis->sadd( $flag_field, array( $key ) );
		};
	} );

	afterEach( function () {
		if ( isset( $this->cleanup ) ) {
			( $this->cleanup )();
		}
	} );

	it( 'deletes an entry by flag when it was not expired first (control)', function () {
		( $this->seed )( 'hashctl', 'post:1' );

		expect( (int) $this->redis->exists( 'mc_itest:c:hashctl' ) )->toBe( 1 );

		$this->storage->clear_cache_by_sets( array( 'mll:deleted-flags' => array( 'post:1' ) ), 100 );

		// Reachable via its flag, so delete-by-flag removes it.
		expect( (int) $this->redis->exists( 'mc_itest:c:hashctl' ) )->toBe( 0 );
	} );

	it( 'still deletes an entry by flag after it was expired by that flag', function () {
		( $this->seed )( 'hashfix', 'post:1' );

		// Expire by flag. The entry is re-stored with its own flags, so it is
		// only aged, not detached from its flag set.
		$this->storage->clear_cache_by_sets( array( 'mll:expired-flags' => array( 'post:1' ) ), 100 );

		// The entry still exists (expire keeps it, only ages it)...
		expect( (int) $this->redis->exists( 'mc_itest:c:hashfix' ) )->toBe( 1 );
		// ...and crucially its flag membership is preserved.
		expect( (int) $this->redis->scard( 'mc_itest:f:post:1' ) )->toBe( 1 );
		expect( $this->redis->sismember( 'mc_itest:f:post:1', 'mc_itest:c:hashfix' ) )->toBeTruthy();

		// Now delete it by the same flag, with no regeneration in between.
		$this->storage->clear_cache_by_sets( array( 'mll:deleted-flags' => array( 'post:1' ) ), 100 );

		// It is gone: delete-by-flag reached it because the link survived expiry.
		expect( (int) $this->redis->exists( 'mc_itest:c:hashfix' ) )->toBe( 0 );
	} );
} );
