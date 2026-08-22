<?php
/**
 * Tests for Admin Utils.
 *
 * @link       https://www.millipress.com
 * @since      1.7.8
 *
 * @package    MilliCache
 */

use MilliCache\Admin\Utils;

describe( 'Utils::cleared_entries_message', function () {

	it( 'reports removed entries with singular/plural forms', function () {
		expect( Utils::cleared_entries_message( 1 ) )->toBe( 'Cleared 1 cache entry.' );
		expect( Utils::cleared_entries_message( 5 ) )->toBe( 'Cleared 5 cache entries.' );
	} );

	it( 'reports expired entries when expiring instead of deleting', function () {
		expect( Utils::cleared_entries_message( 1, true ) )->toBe( 'Expired 1 cache entry.' );
		expect( Utils::cleared_entries_message( 5, true ) )->toBe( 'Expired 5 cache entries.' );
	} );

	it( 'warns about zero matches regardless of mode', function () {
		$expected = 'No cache entries matched the given targets.';

		expect( Utils::cleared_entries_message( 0 ) )->toBe( $expected );
		expect( Utils::cleared_entries_message( 0, true ) )->toBe( $expected );
	} );
} );
