<?php
/**
 * Integration tests for the WP-phase rule registrations.
 *
 * Covers MilliCache\Rules\WordPress and MilliCache\Rules\RequestFlags. Loads
 * the WordPress MilliRules package, runs both register() entry points, then
 * inspects the resulting registry.
 *
 * @package MilliCache
 */

use MilliCache\Engine;
use MilliCache\Rules\RequestFlags;
use MilliCache\Rules\WordPress as WordPressRules;
use MilliRules\MilliRules;

beforeEach( function () {
	new Engine( null, null, create_test_config() );

	MilliRules::load_packages( array( 'WP' ) );

	WordPressRules::register();
	RequestFlags::register();
} );

it( 'registers the five WordPress-phase rules with expected order/locked/hook', function () {
	$expected = array(
		'millicache:wp:logged-in'           => array( 'order' => 1, 'locked' => false, 'priority' => 20 ),
		'millicache:wp:response:code'       => array( 'order' => 1, 'locked' => false, 'priority' => 20 ),
		'millicache:wp:const:donotcachepage' => array( 'order' => 1, 'locked' => false, 'priority' => 20 ),
		'millicache:wp:const:doing-cron'    => array( 'order' => 0, 'locked' => true,  'priority' => 20 ),
		'millicache:wp:const:doing-ajax'    => array( 'order' => 1, 'locked' => false, 'priority' => 20 ),
	);

	foreach ( $expected as $id => $meta ) {
		$rule = get_registered_rule( $id );

		expect( $rule )->not->toBeNull( "{$id} should be registered" );
		expect( $rule['_metadata']['order'] ?? null )->toBe( $meta['order'], "{$id} order" );
		expect( ! empty( $rule['_locked'] ) )->toBe( $meta['locked'], "{$id} locked" );
		expect( $rule['_metadata']['hook'] ?? null )->toBe( 'template_redirect', "{$id} hook" );
		expect( $rule['_metadata']['hook_priority'] ?? null )->toBe( $meta['priority'], "{$id} priority" );
	}
} );

it( 'registers the nine RequestFlags rules at order 5 priority 100', function () {
	$flag_rules = array(
		'millicache:flags:singular-post',
		'millicache:flags:home-blog',
		'millicache:flags:front-page',
		'millicache:flags:blog-page',
		'millicache:flags:post-type-archive',
		'millicache:flags:taxonomy-archive',
		'millicache:flags:author-archive',
		'millicache:flags:date',
		'millicache:flags:feed',
	);

	foreach ( $flag_rules as $id ) {
		$rule = get_registered_rule( $id );

		expect( $rule )->not->toBeNull( "{$id} should be registered" );
		expect( $rule['_metadata']['order'] ?? null )->toBe( 5, "{$id} order" );
		expect( ! empty( $rule['_locked'] ) )->toBeTrue( "{$id} locked" );
		expect( $rule['_metadata']['hook'] ?? null )->toBe( 'template_redirect', "{$id} hook" );
		expect( $rule['_metadata']['hook_priority'] ?? null )->toBe( 100, "{$id} priority" );
	}
} );

it( 'registers the apply-filter rule at order 999', function () {
	$rule = get_registered_rule( 'millicache:flags:apply-filter' );

	expect( $rule )->not->toBeNull();
	expect( $rule['_metadata']['order'] ?? null )->toBe( 999 );
	expect( ! empty( $rule['_locked'] ) )->toBeFalse();
	expect( $rule['_metadata']['hook'] ?? null )->toBe( 'template_redirect' );
	expect( $rule['_metadata']['hook_priority'] ?? null )->toBe( 100 );
} );

it( 'registers each WP-phase rule under the WP package', function () {
	$ids = array(
		'millicache:wp:logged-in',
		'millicache:wp:const:doing-cron',
		'millicache:flags:singular-post',
		'millicache:flags:apply-filter',
	);

	foreach ( $ids as $id ) {
		$rule = get_registered_rule( $id );
		expect( $rule['_package'] ?? null )->toBe( 'WP', "{$id} package" );
	}
} );

it( 'attaches each WP-phase rule to template_redirect via add_action', function () {
	$priorities_seen = array();

	foreach ( $GLOBALS['test_actions'] ?? array() as $action ) {
		if ( ( $action['hook'] ?? null ) === 'template_redirect' ) {
			$priorities_seen[ (int) $action['priority'] ] = true;
		}
	}

	expect( $priorities_seen )->toHaveKey( 20 );  // WordPress rules.
	expect( $priorities_seen )->toHaveKey( 100 ); // RequestFlags rules.
} );
