<?php
/**
 * Integration tests: the clear_site_cache rule action.
 *
 * Drives the real Engine and inspects the invalidation queue plus the
 * millicache_cache_cleared_by_sites event for every way a rule can carry the
 * action's arguments: builder positional and stored named.
 *
 * @package MilliCache
 */

use MilliCache\Engine;
use MilliCache\Rules\Actions\WP\ClearSiteCache;
use MilliRules\Context;

beforeEach( function () {
	global $test_did_actions;
	$test_did_actions = array();

	new Engine( null, null, create_test_config() );
} );

afterEach( function () {
	millicache()->clear()->get_queue()->clear_queues();
} );

/**
 * Run the action with the given arguments and return the by-sites event arguments.
 *
 * @param array<int|string, mixed> $args The action arguments without the type.
 * @return array<int, mixed>|null The event arguments, or null when it did not fire.
 */
function run_clear_site_cache_action( array $args ): ?array {
	global $test_did_actions;

	$action = new ClearSiteCache( array( 'type' => 'clear_site_cache' ) + $args, new Context() );
	$action->execute( new Context() );

	foreach ( $test_did_actions as $call ) {
		if ( 'millicache_cache_cleared_by_sites' === $call['hook'] ) {
			return $call['args'];
		}
	}

	return null;
}

describe( 'builder (positional) arguments', function () {
	it( 'clears the current site when called without arguments', function () {
		expect( run_clear_site_cache_action( array() ) )->toBe( array( null, null, false ) );
		expect( millicache()->clear()->get_queue()->get_delete_queue() )->not->toBe( array() );
	} );

	it( 'clears a single site by ID', function () {
		expect( run_clear_site_cache_action( array( 0 => 5 ) ) )->toBe( array( array( 5 ), null, false ) );
	} );

	it( 'expires a single site by ID', function () {
		expect( run_clear_site_cache_action( array( 0 => 5, 1 => true ) ) )->toBe( array( array( 5 ), null, true ) );
		expect( millicache()->clear()->get_queue()->get_expire_queue() )->not->toBe( array() );
		expect( millicache()->clear()->get_queue()->get_delete_queue() )->toBe( array() );
	} );

	it( 'clears a list of sites', function () {
		expect( run_clear_site_cache_action( array( 0 => array( 5, 6 ), 1 => true ) ) )->toBe( array( array( 5, 6 ), null, true ) );
	} );

	it( 'expires the current site with an explicit null', function () {
		expect( run_clear_site_cache_action( array( 0 => null, 1 => true ) ) )->toBe( array( null, null, true ) );
	} );

	it( 'skips the clear when the value holds no valid site ID', function () {
		$event = suppressing_errors( fn() => run_clear_site_cache_action( array( 0 => 'abc' ) ) );

		expect( $event )->toBeNull();
		expect( millicache()->clear()->get_queue()->get_delete_queue() )->toBe( array() );
	} );
} );

describe( 'stored (named) arguments', function () {
	it( 'expires the current site when only expire is stored', function () {
		expect( run_clear_site_cache_action( array( 'expire' => true ) ) )->toBe( array( null, null, true ) );
	} );

	it( 'deletes the current site for a stored false flag', function () {
		expect( run_clear_site_cache_action( array( 'expire' => false ) ) )->toBe( array( null, null, false ) );
		expect( millicache()->clear()->get_queue()->get_delete_queue() )->not->toBe( array() );
	} );

	it( 'parses a comma-separated site ID string', function () {
		expect( run_clear_site_cache_action( array( 'site_ids' => ' 5, 6 ,5', 'expire' => false ) ) )->toBe( array( array( 5, 6 ), null, false ) );
	} );

	it( 'treats an empty site ID string as the current site', function () {
		expect( run_clear_site_cache_action( array( 'site_ids' => '', 'expire' => true ) ) )->toBe( array( null, null, true ) );
	} );

	it( 'accepts a stored integer site ID', function () {
		expect( run_clear_site_cache_action( array( 'site_ids' => 7 ) ) )->toBe( array( array( 7 ), null, false ) );
	} );
} );
