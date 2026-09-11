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
use MilliCache\Engine\Cache\Config;
use MilliCache\Engine\Cache\Entry;
use MilliCache\Engine\Cache\Validator;

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
		$this->seed = function ( string $hash, string $flag, ?int $custom_ttl = null ) {
			$key        = 'mc_itest:c:' . $hash;
			$flag_field = 'mc_itest:f:' . $flag;

			$meta = array(
				'status'  => 200,
				'updated' => time(),
				'headers' => array(),
				'url'     => 'https://example.com/x',
			);
			if ( null !== $custom_ttl ) {
				$meta['custom_ttl'] = $custom_ttl;
			}

			$this->redis->hmset(
				$key,
				array(
					'output'     => '',
					'meta'       => json_encode( $meta ),
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

	it( 'emits millicache_entry_deleted carrying the entry URL for edge mirrors', function () {
		global $test_did_actions;
		$test_did_actions = array();

		( $this->seed )( 'hashurl', 'post:1' );

		$this->storage->clear_cache_by_sets( array( 'mll:deleted-flags' => array( 'post:1' ) ), 100 );

		$deleted = array_values(
			array_filter(
				$test_did_actions,
				fn( $a ) => 'millicache_entry_deleted' === $a['hook']
			)
		);

		expect( $deleted )->toHaveCount( 1 );
		// Payload shape: (hash, key, flags, url). Flags are canonical, not storage keys.
		expect( $deleted[0]['args'][3] )->toBe( 'https://example.com/x' );
		expect( $deleted[0]['args'][2] )->toContain( 'post:1' );
		expect( $deleted[0]['args'][2] )->not->toContain( 'mc_itest:f:post:1' );
	} );

	it( 'expires an entry by its own custom_ttl, not the global TTL', function () {
		// Regression: a set_ttl rule TTL far above the global TTL must not let
		// the entry survive an expire pass fresh (previously only the global
		// TTL was subtracted from `updated`).
		( $this->seed )( 'hashcustom', 'post:1', 999999 );

		$this->storage->clear_cache_by_sets( array( 'mll:expired-flags' => array( 'post:1' ) ), 100 );

		$meta = json_decode( (string) $this->redis->hget( 'mc_itest:c:hashcustom', 'meta' ), true );

		// The custom TTL survives the re-store...
		expect( $meta['custom_ttl'] )->toBe( 999999 );
		// ...and the entry is stale by its own TTL: `updated + custom_ttl <= now`.
		expect( $meta['updated'] )->toBeLessThanOrEqual( time() - 999999 );
		expect( $meta['updated'] )->toBeGreaterThanOrEqual( time() - 999999 - 5 );
	} );

	it( 'pins expired entries to exactly stale, idempotently across repeat passes', function () {
		( $this->seed )( 'hashpin', 'post:1' );

		$this->storage->clear_cache_by_sets( array( 'mll:expired-flags' => array( 'post:1' ) ), 100 );
		// A second pass must not age it further into the grace window.
		$this->storage->clear_cache_by_sets( array( 'mll:expired-flags' => array( 'post:1' ) ), 100 );

		$meta = json_decode( (string) $this->redis->hget( 'mc_itest:c:hashpin', 'meta' ), true );

		// Stale as of now by the default TTL, not cumulatively older.
		expect( $meta['updated'] )->toBeLessThanOrEqual( time() - 100 );
		expect( $meta['updated'] )->toBeGreaterThanOrEqual( time() - 100 - 5 );
		// The surfaced top-level field mirrors the meta timestamp.
		expect( (int) $this->redis->hget( 'mc_itest:c:hashpin', 'updated' ) )->toBe( (int) $meta['updated'] );
	} );

	it( 'routes an expired custom_ttl entry into the stale/SWR serve path', function () {
		( $this->seed )( 'hashserve', 'post:1', 999999 );

		$this->storage->clear_cache_by_sets( array( 'mll:expired-flags' => array( 'post:1' ) ), 100 );

		// Judge the re-stored entry exactly as Reader::should_serve() does.
		// get_cache() takes the bare hash; it prefixes the storage key itself.
		list( $data ) = $this->storage->get_cache( 'hashserve' );
		$entry     = Entry::from_array( $data );
		$validator = new Validator( Config::from_settings( array( 'ttl' => 100, 'grace' => 3600 ) ) );

		// Stale: the serve path takes the lock-and-regenerate branch...
		expect( $validator->is_stale( $entry ) )->toBeTrue();
		// ...but not too old: the entry is not deleted, its stale copy stays
		// servable while regeneration runs (SWR).
		expect( $validator->is_too_old( $entry ) )->toBeFalse();
		// The full grace window remains for that regeneration.
		expect( $validator->time_to_deletion( $entry ) )->toBeGreaterThan( 3590 );
	} );

	it( 'does not push an already-stale entry past its grace window into deletion', function () {
		( $this->seed )( 'hashgrace', 'post:1' );

		// Age the entry into its grace window: stale for 50s (ttl 100, grace 100).
		$key  = 'mc_itest:c:hashgrace';
		$meta = json_decode( (string) $this->redis->hget( $key, 'meta' ), true );

		$meta['updated'] = time() - 150;
		$this->redis->hset( $key, 'meta', json_encode( $meta ) );

		$this->storage->clear_cache_by_sets( array( 'mll:expired-flags' => array( 'post:1' ) ), 100 );

		list( $data ) = $this->storage->get_cache( 'hashgrace' );
		$entry     = Entry::from_array( $data );
		$validator = new Validator( Config::from_settings( array( 'ttl' => 100, 'grace' => 100 ) ) );

		// The old subtraction (updated - ttl = now - 250) landed past TTL+grace,
		// turning the soft expire into a hard delete on the next read. Pinned to
		// now - ttl, the entry stays inside grace and serves stale instead.
		expect( $validator->is_stale( $entry ) )->toBeTrue();
		expect( $validator->is_too_old( $entry ) )->toBeFalse();
	} );

	it( 'emits millicache_entry_expired with the URL when an entry is aged by flag', function () {
		global $test_did_actions;
		$test_did_actions = array();

		( $this->seed )( 'hashexp', 'post:1' );

		$this->storage->clear_cache_by_sets( array( 'mll:expired-flags' => array( 'post:1' ) ), 100 );

		$expired = array_values(
			array_filter(
				$test_did_actions,
				fn( $a ) => 'millicache_entry_expired' === $a['hook']
			)
		);

		expect( $expired )->toHaveCount( 1 );
		// Same (hash, key, flags, url) shape as entry_deleted; flags are canonical.
		expect( $expired[0]['args'][0] )->toBe( 'hashexp' );
		expect( $expired[0]['args'][1] )->toBe( 'mc_itest:c:hashexp' );
		expect( $expired[0]['args'][2] )->toContain( 'post:1' );
		expect( $expired[0]['args'][2] )->not->toContain( 'mc_itest:f:post:1' );
		expect( $expired[0]['args'][3] )->toBe( 'https://example.com/x' );
	} );
} );
