<?php
/**
 * Integration tests: the clear_site_cache rule action.
 *
 * Drives the real Engine and inspects the millicache_cache_cleared_by_sites
 * event for both ways a rule can carry the action's argument: builder
 * positional and stored named.
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

it( 'clears the current site when called without arguments', function () {
	expect( run_clear_site_cache_action( array() ) )->toBe( array( null, null, false ) );
	expect( millicache()->clear()->get_queue()->get_delete_queue() )->not->toBe( array() );
} );

it( 'expires the current site for a positional true', function () {
	expect( run_clear_site_cache_action( array( 0 => true ) ) )->toBe( array( null, null, true ) );
	expect( millicache()->clear()->get_queue()->get_expire_queue() )->not->toBe( array() );
	expect( millicache()->clear()->get_queue()->get_delete_queue() )->toBe( array() );
} );

it( 'expires the current site for a stored expire flag', function () {
	expect( run_clear_site_cache_action( array( 'expire' => true ) ) )->toBe( array( null, null, true ) );
} );

it( 'deletes the current site for a stored false flag', function () {
	expect( run_clear_site_cache_action( array( 'expire' => false ) ) )->toBe( array( null, null, false ) );
	expect( millicache()->clear()->get_queue()->get_delete_queue() )->not->toBe( array() );
} );
