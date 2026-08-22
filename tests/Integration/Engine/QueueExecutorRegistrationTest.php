<?php
/**
 * Regression for hook-driven cache clears in processes where the drop-in
 * never loads.
 *
 * WP-CLI filters `enable_loading_advanced_cache_dropin` to false, so
 * `advanced-cache.php` — and with it `Engine::start()` — never runs there.
 * Before 1.7.8 the shutdown function that executes the invalidation queue
 * was registered only in start(), so a publish via `wp post update`
 * enqueued its flags (post:{id}, archive:post, feed, …) and dropped them
 * silently at process exit; nothing was ever cleared.
 *
 * The fix registers the executor when the invalidation manager is first
 * built (Engine::invalidation()) — the chokepoint every enqueue path goes
 * through — so any process that clears also executes.
 *
 * @package MilliCache
 */

use MilliBase\Settings as BaseSettings;
use MilliCache\Base\Site;
use MilliCache\Engine;

beforeEach( function () {
	global $test_is_multisite;
	$test_is_multisite = false;

	$ref = new ReflectionProperty( Engine::class, 'instance' );
	$ref->setAccessible( true );
	$ref->setValue( null, null );

	Site::inject_settings(
		new BaseSettings(
			array(
				'slug'        => 'millicache',
				'defaults'    => array( 'cache' => array( 'ttl' => 3600 ) ),
				'config_file' => array(
					'directory' => sys_get_temp_dir() . '/millicache-queue-executor/',
				),
			)
		)
	);
} );

afterEach( function () {
	foreach ( array( array( Site::class, 'settings' ), array( Engine::class, 'instance' ) ) as $target ) {
		$ref = new ReflectionProperty( $target[0], $target[1] );
		$ref->setAccessible( true );
		$ref->setValue( null, null );
	}
} );

it( 'flags enqueued via clear() reach the queue awaiting shutdown execution, without start()', function () {
	$engine = new Engine();

	// Mirror what transition_post_status enqueues on publish.
	$engine->clear()->flags( array( 'post:123', 'archive:post', 'feed' ) );

	$queue = $engine->clear()->get_queue()->get_delete_queue();

	expect( $queue )->toContain( 'post:123' );
	expect( $queue )->toContain( 'archive:post' );
	expect( $queue )->toContain( 'feed' );

	// Drain so the process-shutdown executor finds nothing to touch.
	$engine->clear()->get_queue()->clear_queues();
} );

it( 'reports zero removed entries for an empty queue', function () {
	$engine = new Engine();

	expect( $engine->clear()->execute_queue() )->toBe( 0 );
} );
