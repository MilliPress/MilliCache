<?php
/**
 * Tests for the metrics Manager's mirror, restore and clear behaviour.
 *
 * @link       https://www.millipress.com
 * @since      1.8.2
 *
 * @package    MilliCache
 */

use MilliCache\Core\Storage;
use MilliCache\Engine\Metrics\Manager;
use MilliCache\Engine\Metrics\Mirror;
use MilliCache\Engine\Metrics\Recorder;

describe( 'Metrics Manager', function () {

	beforeEach( function () {
		$GLOBALS['test_site_options'] = array();
		$this->storage                = Mockery::mock( Storage::class );
		$this->mirror                 = new Mirror();
		$this->manager                = new Manager( $this->storage, '', false, array(), $this->mirror );
	} );

	afterEach( function () {
		Mockery::close();
	} );

	describe( 'restore', function () {
		it( 'does nothing while storage still holds daily counters', function () {
			$this->expectNotToPerformAssertions();
			$this->mirror->write( '', array( '20260529:hit' => 8 ) );
			$this->storage->shouldReceive( 'metrics_count' )->with( '', Recorder::RES_DAILY )->andReturn( 12 );
			$this->storage->shouldNotReceive( 'metrics_set' );

			$this->manager->restore();
		} );

		it( 'does nothing when nothing is mirrored', function () {
			$this->expectNotToPerformAssertions();
			$this->storage->shouldReceive( 'metrics_count' )->andReturn( 0 );
			$this->storage->shouldNotReceive( 'metrics_set' );

			$this->manager->restore();
		} );

		it( 'writes every mirrored field back when the daily hash is gone', function () {
			$this->expectNotToPerformAssertions();
			$this->mirror->write( '', array( '20260529:hit' => 8, '20260529:miss' => 1 ) );
			$this->storage->shouldReceive( 'metrics_count' )->with( '', Recorder::RES_DAILY )->andReturn( 0 );
			$this->storage->shouldReceive( 'metrics_set' )
				->once()
				->with( '', Recorder::RES_DAILY, array( '20260529:hit' => 8, '20260529:miss' => 1 ) );

			$this->manager->restore();
		} );

		it( 'merges only the missing fields and never overwrites stored ones', function () {
			$this->expectNotToPerformAssertions();
			$this->mirror->write( '2:', array( '20260528:hit' => 5, '20260529:hit' => 8 ) );
			$this->storage->shouldReceive( 'metrics_read' )
				->with( '2:', Recorder::RES_DAILY )
				->andReturn( array( '20260529:hit' => 9, '20260530:hit' => 2 ) );
			$this->storage->shouldReceive( 'metrics_set' )
				->once()
				->with( '2:', Recorder::RES_DAILY, array( '20260528:hit' => 5 ) );

			$this->manager->restore( '2:', true );
		} );
	} );

	describe( 'rollup', function () {
		// The rollup prunes against the clock, so fixtures sit inside the daily window.
		beforeEach( function () {
			$this->day = gmdate( 'Ymd', time() - DAY_IN_SECONDS );
		} );

		it( 'restores a blog known only to the mirror, rolls it up and re-mirrors it', function () {
			$daily = array( "{$this->day}:hit" => 5 );
			$this->mirror->write( '3:', $daily );
			$this->storage->shouldReceive( 'metrics_prefixes' )->andReturn( array() );
			$this->storage->shouldReceive( 'metrics_read' )->with( '3:', Recorder::RES_HOURLY )->andReturn( array() );
			// Empty before the restore, mirrored afterwards.
			$this->storage->shouldReceive( 'metrics_read' )
				->with( '3:', Recorder::RES_DAILY )
				->andReturn( array(), $daily, $daily );
			$this->storage->shouldReceive( 'metrics_set' )->once()->with( '3:', Recorder::RES_DAILY, $daily );
			$this->storage->shouldReceive( 'metrics_delete' )->never();

			$this->manager->rollup();

			expect( $this->mirror->read( '3:' ) )->toBe( $daily );
		} );

		it( 'mirrors the pruned daily hash after the rollup', function () {
			$daily = array( "{$this->day}:hit" => 8 );
			$this->storage->shouldReceive( 'metrics_prefixes' )->andReturn( array( '' ) );
			$this->storage->shouldReceive( 'metrics_read' )->with( '', Recorder::RES_HOURLY )->andReturn( array() );
			$this->storage->shouldReceive( 'metrics_read' )->with( '', Recorder::RES_DAILY )->andReturn( $daily );
			$this->storage->shouldReceive( 'metrics_delete' )->never();

			$this->manager->rollup();

			expect( $this->mirror->read( '' ) )->toBe( $daily );
			expect( $this->mirror->prefixes() )->toBe( array( '' ) );
		} );

		it( 'keeps the last good mirror when storage reads as empty', function () {
			$this->mirror->write( '', array( '20260529:hit' => 8 ) );
			$this->storage->shouldReceive( 'metrics_prefixes' )->andReturn( array() );
			$this->storage->shouldReceive( 'metrics_read' )->andReturn( array() );
			$this->storage->shouldReceive( 'metrics_set' )->andReturnNull();

			$this->manager->rollup();

			expect( $this->mirror->read( '' ) )->toBe( array( '20260529:hit' => 8 ) );
		} );
	} );

	describe( 'clear', function () {
		it( 'deletes both resolutions in storage and the mirror', function () {
			$this->mirror->write( '', array( '20260529:hit' => 8 ) );
			$this->storage->shouldReceive( 'metrics_read' )->with( '', Recorder::RES_HOURLY )->andReturn( array( '2026052910:hit' => 1 ) );
			$this->storage->shouldReceive( 'metrics_read' )->with( '', Recorder::RES_DAILY )->andReturn( array( '20260529:hit' => 8 ) );
			$this->storage->shouldReceive( 'metrics_delete' )->once()->with( '', Recorder::RES_HOURLY, array( '2026052910:hit' ) );
			$this->storage->shouldReceive( 'metrics_delete' )->once()->with( '', Recorder::RES_DAILY, array( '20260529:hit' ) );

			$this->manager->clear();

			expect( $this->mirror->read( '' ) )->toBe( array() );
			expect( $this->mirror->prefixes() )->toBe( array() );
		} );
	} );
} );
