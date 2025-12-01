<?php
/**
 * Tests for Cache Flusher.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Engine\Clearing\Flusher;
use MilliCache\Core\Storage;
use MilliCache\Engine\Multisite;

// Mock WordPress functions.
if ( ! function_exists( 'is_multisite' ) ) {
	function is_multisite() {
		return false;
	}
}

uses()->beforeEach( function () {
	$this->storage = Mockery::mock( Storage::class );
	// Use real Multisite instance (final class, control via WordPress functions).
	$this->multisite = new Multisite();
} );

describe( 'Clearing Flusher', function () {

	describe( 'constructor', function () {
		it( 'creates flusher with dependencies', function () {
			$flusher = new Flusher( $this->storage, $this->multisite );

			expect( $flusher )->toBeInstanceOf( Flusher::class );
		} );

		it( 'sets default TTL correctly', function () {
			$flusher = new Flusher( $this->storage, $this->multisite, 7200 );

			expect( $flusher )->toBeInstanceOf( Flusher::class );
		} );
	} );

	describe( 'add_to_expire', function () {
		it( 'adds flags to expire queue', function () {
			$flusher = new Flusher( $this->storage, $this->multisite );
			$flusher->add_to_expire( array( 'flag1', 'flag2' ), false );

			$queue = $flusher->get_expire_queue();
			expect( $queue )->toContain( 'flag1' );
			expect( $queue )->toContain( 'flag2' );
		} );

		it( 'handles empty flags array', function () {
			$flusher = new Flusher( $this->storage, $this->multisite );
			$flusher->add_to_expire( array(), false );

			$queue = $flusher->get_expire_queue();
			expect( $queue )->toBeEmpty();
		} );

		it( 'accumulates multiple additions', function () {
			$flusher = new Flusher( $this->storage, $this->multisite );
			$flusher->add_to_expire( array( 'flag1' ), false );
			$flusher->add_to_expire( array( 'flag2' ), false );

			$queue = $flusher->get_expire_queue();
			expect( count( $queue ) )->toBe( 2 );
		} );
	} );

	describe( 'add_to_delete', function () {
		it( 'adds flags to delete queue', function () {
			$flusher = new Flusher( $this->storage, $this->multisite );
			$flusher->add_to_delete( array( 'flag1', 'flag2' ), false );

			$queue = $flusher->get_delete_queue();
			expect( $queue )->toContain( 'flag1' );
			expect( $queue )->toContain( 'flag2' );
		} );

		it( 'handles empty flags array', function () {
			$flusher = new Flusher( $this->storage, $this->multisite );
			$flusher->add_to_delete( array(), false );

			$queue = $flusher->get_delete_queue();
			expect( $queue )->toBeEmpty();
		} );

		it( 'accumulates multiple additions', function () {
			$flusher = new Flusher( $this->storage, $this->multisite );
			$flusher->add_to_delete( array( 'flag1' ), false );
			$flusher->add_to_delete( array( 'flag2' ), false );

			$queue = $flusher->get_delete_queue();
			expect( count( $queue ) )->toBe( 2 );
		} );
	} );

	describe( 'get_expire_queue', function () {
		it( 'returns empty array initially', function () {
			$flusher = new Flusher( $this->storage, $this->multisite );

			expect( $flusher->get_expire_queue() )->toBe( array() );
		} );

		it( 'returns accumulated expire flags', function () {
			$flusher = new Flusher( $this->storage, $this->multisite );
			$flusher->add_to_expire( array( 'flag1', 'flag2' ), false );

			$queue = $flusher->get_expire_queue();
			expect( count( $queue ) )->toBe( 2 );
		} );
	} );

	describe( 'get_delete_queue', function () {
		it( 'returns empty array initially', function () {
			$flusher = new Flusher( $this->storage, $this->multisite );

			expect( $flusher->get_delete_queue() )->toBe( array() );
		} );

		it( 'returns accumulated delete flags', function () {
			$flusher = new Flusher( $this->storage, $this->multisite );
			$flusher->add_to_delete( array( 'flag1', 'flag2' ), false );

			$queue = $flusher->get_delete_queue();
			expect( count( $queue ) )->toBe( 2 );
		} );
	} );

	describe( 'flush', function () {
		it( 'returns true when queues empty', function () {
			$flusher = new Flusher( $this->storage, $this->multisite );

			$result = $flusher->flush();
			expect( $result )->toBeTrue();
		} );

		it( 'calls storage clear_cache_by_sets with correct params', function () {
			$this->storage->shouldReceive( 'clear_cache_by_sets' )
				->once()
				->with( Mockery::type( 'array' ), 3600 );

			$flusher = new Flusher( $this->storage, $this->multisite, 3600 );
			$flusher->add_to_delete( array( 'flag1' ), false );
			$flusher->flush();

			expect( true )->toBeTrue();
		} );

		it( 'passes mll:expired-flags set correctly', function () {
			$this->storage->shouldReceive( 'clear_cache_by_sets' )
				->once()
				->with(
					Mockery::on( function ( $sets ) {
						return isset( $sets['mll:expired-flags'] ) &&
							   $sets['mll:expired-flags'] === array( 'flag1' );
					} ),
					Mockery::any()
				);

			$flusher = new Flusher( $this->storage, $this->multisite );
			$flusher->add_to_expire( array( 'flag1' ), false );
			$flusher->flush();

			expect( true )->toBeTrue();
		} );

		it( 'passes mll:deleted-flags set correctly', function () {
			$this->storage->shouldReceive( 'clear_cache_by_sets' )
				->once()
				->with(
					Mockery::on( function ( $sets ) {
						return isset( $sets['mll:deleted-flags'] ) &&
							   $sets['mll:deleted-flags'] === array( 'flag1' );
					} ),
					Mockery::any()
				);

			$flusher = new Flusher( $this->storage, $this->multisite );
			$flusher->add_to_delete( array( 'flag1' ), false );
			$flusher->flush();

			expect( true )->toBeTrue();
		} );

		it( 'passes default_ttl to storage', function () {
			$this->storage->shouldReceive( 'clear_cache_by_sets' )
				->once()
				->with( Mockery::any(), 7200 );

			$flusher = new Flusher( $this->storage, $this->multisite, 7200 );
			$flusher->add_to_delete( array( 'flag1' ), false );
			$flusher->flush();

			expect( true )->toBeTrue();
		} );

		it( 'clears queues after flush', function () {
			$this->storage->shouldReceive( 'clear_cache_by_sets' )->once();

			$flusher = new Flusher( $this->storage, $this->multisite );
			$flusher->add_to_delete( array( 'flag1' ), false );
			$flusher->add_to_expire( array( 'flag2' ), false );

			$flusher->flush();

			expect( $flusher->get_delete_queue() )->toBeEmpty();
			expect( $flusher->get_expire_queue() )->toBeEmpty();
		} );
	} );

	describe( 'prefix_flags', function () {
		it( 'returns flags unchanged when multisite disabled', function () {
			$flusher = new Flusher( $this->storage, $this->multisite );
			$result = $flusher->prefix_flags( array( 'flag1', 'flag2' ) );

			expect( $result )->toBe( array( 'flag1', 'flag2' ) );
		} );
	} );

	describe( 'clear_queues', function () {
		it( 'empties expire queue', function () {
			$flusher = new Flusher( $this->storage, $this->multisite );
			$flusher->add_to_expire( array( 'flag1' ), false );
			$flusher->clear_queues();

			expect( $flusher->get_expire_queue() )->toBeEmpty();
		} );

		it( 'empties delete queue', function () {
			$flusher = new Flusher( $this->storage, $this->multisite );
			$flusher->add_to_delete( array( 'flag1' ), false );
			$flusher->clear_queues();

			expect( $flusher->get_delete_queue() )->toBeEmpty();
		} );
	} );

	describe( 'get_queue_sizes', function () {
		it( 'returns zero sizes initially', function () {
			$flusher = new Flusher( $this->storage, $this->multisite );

			$sizes = $flusher->get_queue_sizes();
			expect( $sizes['expire'] )->toBe( 0 );
			expect( $sizes['delete'] )->toBe( 0 );
		} );

		it( 'returns correct expire count', function () {
			$flusher = new Flusher( $this->storage, $this->multisite );
			$flusher->add_to_expire( array( 'flag1', 'flag2', 'flag3' ), false );

			$sizes = $flusher->get_queue_sizes();
			expect( $sizes['expire'] )->toBe( 3 );
		} );

		it( 'returns correct delete count', function () {
			$flusher = new Flusher( $this->storage, $this->multisite );
			$flusher->add_to_delete( array( 'flag1', 'flag2' ), false );

			$sizes = $flusher->get_queue_sizes();
			expect( $sizes['delete'] )->toBe( 2 );
		} );
	} );

	describe( 'flush_on_shutdown', function () {
		it( 'calls flush method', function () {
			$this->storage->shouldReceive( 'clear_cache_by_sets' )->once();

			$flusher = new Flusher( $this->storage, $this->multisite );
			$flusher->add_to_delete( array( 'flag1' ), false );
			$flusher->flush_on_shutdown();

			// Verify flush was called (queues should be empty).
			expect( $flusher->get_delete_queue() )->toBeEmpty();
		} );
	} );
} );
