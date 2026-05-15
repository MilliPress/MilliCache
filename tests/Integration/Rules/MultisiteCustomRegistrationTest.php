<?php
/**
 * Integration tests for dual-source (network + site) rule loading via Custom.
 *
 * Covers Custom::register() on multisite, where Network settings register
 * first and Site settings register second so network-locked rules are
 * honored against same-ID site rules.
 *
 * @package MilliCache
 */

use MilliBase\Settings as BaseSettings;
use MilliCache\Base\Network;
use MilliCache\Base\Site;
use MilliCache\Engine;
use MilliCache\Rules\Custom as CustomRules;

if ( ! function_exists( 'is_multisite' ) ) {
	function is_multisite() {
		global $test_is_multisite;
		return $test_is_multisite ?? false;
	}
}

/**
 * Install standalone Settings instances for both network and site scopes.
 *
 * The cache module is provided so Site::settings()->get('cache.*') reads
 * succeed during Engine construction; only the rules block varies per
 * test.
 *
 * @param list<array<string, mixed>> $network_items
 * @param list<array<string, mixed>> $site_items
 */
function install_multisite_rules( array $network_items, array $site_items ): void {
	$cache_defaults = array(
		'ttl'             => 3600,
		'grace'           => 600,
		'nocache_paths'   => array(),
		'nocache_cookies' => array(),
		'ignore_cookies'  => array(),
		'unique'          => array(),
	);

	Site::inject_settings(
		BaseSettings::standalone(
			array(
				'slug'     => 'millicache',
				'defaults' => array(
					'cache' => $cache_defaults,
					'rules' => array( 'items' => $site_items ),
				),
			)
		)
	);

	Network::inject_settings(
		BaseSettings::standalone(
			array(
				'slug'     => 'millicache-network',
				'defaults' => array(
					'storage' => array(),
					'rules'   => array( 'items' => $network_items ),
				),
			)
		)
	);
}

beforeEach( function () {
	$GLOBALS['test_is_multisite'] = true;
} );

it( 'registers rules from both network and site scopes on multisite', function () {
	install_multisite_rules(
		network_items: array(
			array(
				'id'         => 'user:net',
				'enabled'    => true,
				'order'      => 50,
				'match_type' => 'all',
				'conditions' => array( array( 'type' => 'request_url', 'value' => '/n' ) ),
				'actions'    => array( array( 'type' => 'do_cache', 'should_cache' => false, 'reason' => 'net' ) ),
			),
		),
		site_items: array(
			array(
				'id'         => 'user:site',
				'enabled'    => true,
				'order'      => 50,
				'match_type' => 'all',
				'conditions' => array( array( 'type' => 'request_url', 'value' => '/s' ) ),
				'actions'    => array( array( 'type' => 'do_cache', 'should_cache' => false, 'reason' => 'site' ) ),
			),
		),
	);
	new Engine( null, null, create_test_config() );

	CustomRules::register();

	expect( get_registered_rule( 'user:net' ) )->not->toBeNull();
	expect( get_registered_rule( 'user:site' ) )->not->toBeNull();
} );

it( 'lets a network-locked rule reject a same-ID site rule', function () {
	install_multisite_rules(
		network_items: array(
			array(
				'id'         => 'user:dup',
				'enabled'    => true,
				'order'      => 10,
				'locked'     => true,
				'match_type' => 'all',
				'conditions' => array( array( 'type' => 'request_url', 'value' => '/network-owned' ) ),
				'actions'    => array( array( 'type' => 'do_cache', 'should_cache' => false, 'reason' => 'network' ) ),
			),
		),
		site_items: array(
			array(
				'id'         => 'user:dup',
				'enabled'    => true,
				'order'      => 999,
				'match_type' => 'all',
				'conditions' => array( array( 'type' => 'request_url', 'value' => '/site-attempt' ) ),
				'actions'    => array( array( 'type' => 'do_cache', 'should_cache' => true, 'reason' => 'site' ) ),
			),
		),
	);
	new Engine( null, null, create_test_config() );

	CustomRules::register();

	$rule = get_registered_rule( 'user:dup' );

	expect( $rule )->not->toBeNull();
	expect( $rule['_metadata']['order'] ?? null )->toBe( 10, 'network-locked rule must win against site re-registration' );
	expect( ! empty( $rule['_locked'] ) )->toBeTrue();
} );

it( 'lets an unlocked network rule be overridden by a same-ID site rule', function () {
	install_multisite_rules(
		network_items: array(
			array(
				'id'         => 'user:dup',
				'enabled'    => true,
				'order'      => 10,
				'match_type' => 'all',
				'conditions' => array( array( 'type' => 'request_url', 'value' => '/n' ) ),
				'actions'    => array( array( 'type' => 'do_cache', 'should_cache' => false, 'reason' => 'network' ) ),
			),
		),
		site_items: array(
			array(
				'id'         => 'user:dup',
				'enabled'    => true,
				'order'      => 999,
				'match_type' => 'all',
				'conditions' => array( array( 'type' => 'request_url', 'value' => '/s' ) ),
				'actions'    => array( array( 'type' => 'do_cache', 'should_cache' => true, 'reason' => 'site' ) ),
			),
		),
	);
	new Engine( null, null, create_test_config() );

	CustomRules::register();

	$rule = get_registered_rule( 'user:dup' );

	expect( $rule )->not->toBeNull();
	expect( $rule['_metadata']['order'] ?? null )->toBe( 999, 'unlocked network rule should yield to site override' );
} );
