<?php
/**
 * Integration test: daily metrics survive a storage-server restart.
 *
 * Runs the nightly rollup against a real server, wipes the metrics keys the
 * way a restart without persistence would, and checks the database mirror
 * brings the daily history back. Skipped when Redis is unreachable.
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

describe( 'Metrics mirror (integration)', function () {

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

		$this->redis = new \Predis\Client( array( 'host' => '127.0.0.1', 'port' => 6379 ) );
		$this->wipe  = function () {
			$keys = $this->redis->keys( 'mc_itest:m:*' );
			if ( ! empty( $keys ) ) {
				$this->redis->del( $keys );
			}
		};
		( $this->wipe )();

		$GLOBALS['test_site_options'] = array();
		$this->manager                = new Manager( $this->storage, '', false, array(), new Mirror() );

		// Yesterday's traffic; the rollup prunes against the clock.
		$this->day   = gmdate( 'Ymd', time() - DAY_IN_SECONDS );
		$this->today = gmdate( 'Ymd' );
		$this->storage->metrics_increment( '', Recorder::RES_HOURLY, array(
			"{$this->day}10:hit"  => 5,
			"{$this->day}11:hit"  => 3,
			"{$this->day}11:miss" => 1,
		) );
	} );

	afterEach( function () {
		if ( isset( $this->wipe ) ) {
			( $this->wipe )();
		}
	} );

	it( 'restores the daily history after the metrics keys are lost', function () {
		$this->manager->rollup();
		expect( $this->storage->metrics_read( '', Recorder::RES_DAILY )[ "{$this->day}:hit" ] )->toBe( 8 );

		( $this->wipe )(); // The restart.
		expect( $this->storage->metrics_count( '', Recorder::RES_DAILY ) )->toBe( 0 );

		$this->manager->restore();

		expect( $this->storage->metrics_read( '', Recorder::RES_DAILY ) )->toBe( array(
			"{$this->day}:hit"  => 8,
			"{$this->day}:miss" => 1,
		) );
	} );

	it( 'heals a restart the nightly job was not around for, keeping newer days', function () {
		$this->manager->rollup();
		( $this->wipe )();
		$this->storage->metrics_set( '', Recorder::RES_DAILY, array( "{$this->today}:hit" => 2 ) );

		$this->manager->rollup();

		$daily = $this->storage->metrics_read( '', Recorder::RES_DAILY );
		expect( $daily[ "{$this->day}:hit" ] )->toBe( 8 );
		expect( $daily[ "{$this->today}:hit" ] )->toBe( 2 );
	} );

	it( 'does not resurrect counters after a clear', function () {
		$this->manager->rollup();
		$this->manager->clear();

		$this->manager->restore();

		expect( $this->storage->metrics_read( '', Recorder::RES_DAILY ) )->toBe( array() );
		expect( $GLOBALS['test_site_options'] )->toBe( array() );
	} );
} );
