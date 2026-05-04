<?php
/**
 * Integration tests for the PHP-phase bootstrap rules.
 *
 * Verifies which rules MilliCache registers, with what order/locked metadata,
 * and the conditional registrations driven by Config (no-cache paths/cookies).
 *
 * @package MilliCache
 */

use MilliCache\Engine;
use MilliCache\Rules\Bootstrap as BootstrapRules;

it( 'registers all nine PHP-phase rules with expected order and locked state', function () {
	new Engine( null, null, create_test_config() );

	BootstrapRules::register();

	$expected = array(
		'millicache:const:wp-cache'         => array( 'order' => 0, 'locked' => true ),
		'millicache:request:xmlrpc'         => array( 'order' => 0, 'locked' => true ),
		'millicache:request:file'           => array( 'order' => 1, 'locked' => false ),
		'millicache:request:check-method'   => array( 'order' => 0, 'locked' => true ),
		'millicache:request:cli'            => array( 'order' => 0, 'locked' => true ),
		'millicache:request:rest'           => array( 'order' => 0, 'locked' => true ),
		'millicache:config:ttl-not-set'     => array( 'order' => 0, 'locked' => true ),
	);

	foreach ( $expected as $id => $meta ) {
		$rule = get_registered_rule( $id );

		expect( $rule )->not->toBeNull( "{$id} should be registered" );
		expect( $rule['_metadata']['order'] ?? null )->toBe( $meta['order'], "{$id} order" );
		expect( ! empty( $rule['_locked'] ) )->toBe( $meta['locked'], "{$id} locked" );
	}
} );

it( 'omits the nocache-paths rule when no paths are configured', function () {
	new Engine( null, null, create_test_config( nocache_paths: array() ) );

	BootstrapRules::register();

	expect( get_registered_rule( 'millicache:config:nocache-paths' ) )->toBeNull();
} );

it( 'registers the nocache-paths rule when paths are configured', function () {
	new Engine(
		null,
		null,
		create_test_config( nocache_paths: array( '/cart', '/checkout' ) )
	);

	BootstrapRules::register();

	$rule = get_registered_rule( 'millicache:config:nocache-paths' );

	expect( $rule )->not->toBeNull();
	expect( $rule['_metadata']['order'] ?? null )->toBe( 0 );
	expect( ! empty( $rule['_locked'] ) )->toBeTrue();
	expect( $rule['match_type'] ?? null )->toBe( 'any' );

	$condition_types = array_column( $rule['conditions'] ?? array(), 'type' );
	expect( $condition_types )->toBe( array( 'request_url', 'request_url' ) );
} );

it( 'omits the nocache-cookies rule when no cookies are configured', function () {
	new Engine( null, null, create_test_config( nocache_cookies: array() ) );

	BootstrapRules::register();

	expect( get_registered_rule( 'millicache:config:nocache-cookies' ) )->toBeNull();
} );

it( 'registers the nocache-cookies rule when cookies are configured', function () {
	new Engine(
		null,
		null,
		create_test_config( nocache_cookies: array( 'wp-*pass*', 'session_*' ) )
	);

	BootstrapRules::register();

	$rule = get_registered_rule( 'millicache:config:nocache-cookies' );

	expect( $rule )->not->toBeNull();
	expect( $rule['_metadata']['order'] ?? null )->toBe( 0 );
	expect( ! empty( $rule['_locked'] ) )->toBeTrue();
	expect( $rule['match_type'] ?? null )->toBe( 'any' );
} );

it( 'locks the REST bootstrap rule (regression guard for 57fbf87a6)', function () {
	new Engine( null, null, create_test_config() );

	BootstrapRules::register();

	$rule = get_registered_rule( 'millicache:request:rest' );

	expect( $rule )->not->toBeNull();
	expect( ! empty( $rule['_locked'] ) )->toBeTrue();
	expect( $rule['_metadata']['order'] ?? null )->toBe( 0 );
} );

it( 'declares MilliCache as the package origin for bootstrap rules', function () {
	new Engine( null, null, create_test_config() );

	BootstrapRules::register();

	$rule = get_registered_rule( 'millicache:const:wp-cache' );

	expect( $rule )->not->toBeNull();
	expect( $rule['_package'] ?? null )->toBe( 'PHP' );
} );
