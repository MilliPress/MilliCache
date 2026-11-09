<?php
/**
 * Integration tests: the clear_cache rule action as written through the builder.
 *
 * The documented `->clear_cache( ['post:123', 'home'] )` form passes a single
 * list argument. MilliRules 1.3.1 keeps that list as the first argument
 * instead of spreading it into positions, so every target must reach the
 * invalidation queue and the second entry must not be read as `expire`.
 *
 * @package MilliCache
 */

use MilliCache\Engine;
use MilliCache\Rules\Actions\WP\ClearCache;
use MilliRules\Context;
use MilliRules\Rules;

beforeEach( function () {
	new Engine( null, null, create_test_config() );
} );

afterEach( function () {
	millicache()->clear()->get_queue()->clear_queues();
} );

/**
 * Build a clear_cache action through the real builder and execute it.
 *
 * @param mixed ...$args The arguments as a rule author would write them.
 * @return void
 */
function run_builder_clear_cache( ...$args ): void {
	$actions = Rules::create( 'test-clear-cache' )->then()->clear_cache( ...$args )->get_actions();

	$action = new ClearCache( $actions[0], new Context() );
	$action->execute( new Context() );
}

it( 'clears every target of a single list argument', function () {
	run_builder_clear_cache( array( 'post:123', 'home' ) );

	$queue = millicache()->clear()->get_queue();
	expect( $queue->get_delete_queue() )->toContain( 'post:123' )->toContain( 'home' );
	expect( $queue->get_expire_queue() )->toBe( array() );
} );

it( 'expires every target of a list followed by the expire flag', function () {
	run_builder_clear_cache( array( 'post:123', 'home' ), true );

	$queue = millicache()->clear()->get_queue();
	expect( $queue->get_expire_queue() )->toContain( 'post:123' )->toContain( 'home' );
	expect( $queue->get_delete_queue() )->toBe( array() );
} );

it( 'expires a single string target', function () {
	run_builder_clear_cache( 'post:123', true );

	expect( millicache()->clear()->get_queue()->get_expire_queue() )->toContain( 'post:123' );
} );
