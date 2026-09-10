<?php
/**
 * Tests for the database mirror of the daily metrics counters.
 *
 * @link       https://www.millipress.com
 * @since      1.8.2
 *
 * @package    MilliCache
 */

use MilliCache\Engine\Metrics\Mirror;

describe( 'Mirror', function () {

	beforeEach( function () {
		$GLOBALS['test_site_options'] = array();
		$this->mirror                 = new Mirror();
	} );

	it( 'round-trips daily counters per prefix and indexes the prefix', function () {
		$this->mirror->write( '', array( '20260529:hit' => 8 ) );
		$this->mirror->write( '2:', array( '20260529:hit' => 3 ) );

		expect( $this->mirror->read( '' ) )->toBe( array( '20260529:hit' => 8 ) );
		expect( $this->mirror->read( '2:' ) )->toBe( array( '20260529:hit' => 3 ) );
		expect( $this->mirror->prefixes() )->toBe( array( '', '2:' ) );
	} );

	it( 'reads nothing for a prefix that was never mirrored', function () {
		expect( $this->mirror->read( '9:' ) )->toBe( array() );
		expect( $this->mirror->prefixes() )->toBe( array() );
	} );

	it( 'drops non-numeric values on read', function () {
		$GLOBALS['test_site_options'][ Mirror::OPTION ] = array(
			'20260529:hit' => '5',
			'junk'         => 'x',
		);

		expect( $this->mirror->read( '' ) )->toBe( array( '20260529:hit' => 5 ) );
	} );

	it( 'treats an empty write as a delete', function () {
		$this->mirror->write( '', array( '20260529:hit' => 8 ) );
		$this->mirror->write( '', array() );

		expect( $this->mirror->read( '' ) )->toBe( array() );
		expect( $this->mirror->prefixes() )->toBe( array() );
	} );

	it( 'removes a deleted prefix from the index but keeps the others', function () {
		$this->mirror->write( '', array( '20260529:hit' => 8 ) );
		$this->mirror->write( '2:', array( '20260529:hit' => 3 ) );

		$this->mirror->delete( '' );

		expect( $this->mirror->prefixes() )->toBe( array( '2:' ) );
		expect( $GLOBALS['test_site_options'] )->not->toHaveKey( Mirror::OPTION );
	} );
} );
