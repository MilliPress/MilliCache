<?php
/**
 * Integration tests for user-defined rule loading.
 *
 * Covers the Settings::rules.items -> Bootstrap::register_user_rules() path
 * at src/Rules/Bootstrap.php:316-352. Each scenario installs a standalone
 * Settings instance with rule data, runs Bootstrap::register(), and inspects
 * the resulting registry.
 *
 * @package MilliCache
 */

use MilliCache\Engine;
use MilliCache\Rules\Bootstrap as BootstrapRules;

/**
 * Build a minimal valid user rule for tests; callers override what they need.
 *
 * @param array<string, mixed> $overrides Keys to override on the base rule.
 * @return array<string, mixed>
 */
function make_user_rule( array $overrides = array() ): array {
	return array_merge(
		array(
			'id'         => 'user:test',
			'enabled'    => true,
			'order'      => 50,
			'match_type' => 'all',
			'conditions' => array(
				array( 'type' => 'request_url', 'value' => '/foo' ),
			),
			'actions'    => array(
				array( 'type' => 'do_cache', 'should_cache' => false, 'reason' => 'test' ),
			),
		),
		$overrides
	);
}

/**
 * Install settings with the given rule items and rules.load flag.
 *
 * @param list<array<string, mixed>> $items
 */
function install_user_rules( array $items, bool $load = true ): void {
	install_test_settings(
		array(
			'cache' => array(
				'ttl'             => 3600,
				'grace'           => 600,
				'nocache_paths'   => array(),
				'nocache_cookies' => array(),
				'ignore_cookies'  => array(),
				'unique'          => array(),
			),
			'rules' => array(
				'load'  => $load,
				'items' => $items,
			),
		)
	);
}

it( 'loads enabled user rules from settings when rules.load is true', function () {
	install_user_rules( array( make_user_rule() ) );
	new Engine( null, null, create_test_config() );

	BootstrapRules::register();

	$rule = get_registered_rule( 'user:test' );

	expect( $rule )->not->toBeNull();
	expect( $rule['_metadata']['order'] ?? null )->toBe( 50 );
	expect( $rule['match_type'] ?? null )->toBe( 'all' );
} );

it( 'does not load any user rules when rules.load is false', function () {
	install_user_rules( array( make_user_rule() ), load: false );
	new Engine( null, null, create_test_config() );

	BootstrapRules::register();

	expect( get_registered_rule( 'user:test' ) )->toBeNull();
} );

it( 'skips disabled user rules', function () {
	install_user_rules(
		array(
			make_user_rule( array( 'id' => 'user:on',  'enabled' => true ) ),
			make_user_rule( array( 'id' => 'user:off', 'enabled' => false ) ),
		)
	);
	new Engine( null, null, create_test_config() );

	BootstrapRules::register();

	expect( get_registered_rule( 'user:on' ) )->not->toBeNull();
	expect( get_registered_rule( 'user:off' ) )->toBeNull();
} );

it( 'skips entries with empty id', function () {
	install_user_rules(
		array(
			make_user_rule( array( 'id' => '' ) ),
			make_user_rule( array( 'id' => 'user:valid' ) ),
		)
	);
	new Engine( null, null, create_test_config() );

	BootstrapRules::register();

	expect( registered_rule_ids() )->toContain( 'user:valid' );
	expect( registered_rule_ids() )->not->toContain( '' );
} );

it( 'honors match_type all / any / none', function () {
	install_user_rules(
		array(
			make_user_rule( array( 'id' => 'user:all',  'match_type' => 'all' ) ),
			make_user_rule( array( 'id' => 'user:any',  'match_type' => 'any' ) ),
			make_user_rule( array( 'id' => 'user:none', 'match_type' => 'none' ) ),
		)
	);
	new Engine( null, null, create_test_config() );

	BootstrapRules::register();

	expect( get_registered_rule( 'user:all' )['match_type'] ?? null )->toBe( 'all' );
	expect( get_registered_rule( 'user:any' )['match_type'] ?? null )->toBe( 'any' );
	expect( get_registered_rule( 'user:none' )['match_type'] ?? null )->toBe( 'none' );
} );

it( 'defaults order to 100 when omitted', function () {
	$rule_data = make_user_rule();
	unset( $rule_data['order'] );

	install_user_rules( array( $rule_data ) );
	new Engine( null, null, create_test_config() );

	BootstrapRules::register();

	expect( get_registered_rule( 'user:test' )['_metadata']['order'] ?? null )->toBe( 100 );
} );

it( 'applies hook + priority when provided', function () {
	install_user_rules(
		array(
			make_user_rule(
				array(
					'id'       => 'user:wp',
					'hook'     => 'template_redirect',
					'priority' => 25,
				)
			),
		)
	);
	new Engine( null, null, create_test_config() );
	\MilliRules\MilliRules::load_packages( array( 'WP' ) );

	BootstrapRules::register();

	$rule = get_registered_rule( 'user:wp' );

	expect( $rule )->not->toBeNull();
	expect( $rule['_metadata']['hook'] ?? null )->toBe( 'template_redirect' );
	expect( $rule['_metadata']['hook_priority'] ?? null )->toBe( 25 );
} );

it( 'overwrites an unlocked default rule when registered with the same id at higher order', function () {
	install_user_rules(
		array(
			make_user_rule(
				array(
					'id'    => 'millicache:request:file',
					'order' => 100,
				)
			),
		)
	);
	new Engine( null, null, create_test_config() );

	BootstrapRules::register();

	$rule = get_registered_rule( 'millicache:request:file' );

	expect( $rule )->not->toBeNull();
	expect( $rule['_metadata']['order'] ?? null )->toBe( 100, 'user rule should win for unlocked default' );
} );

it( 'cannot overwrite a locked bootstrap rule via user settings', function () {
	install_user_rules(
		array(
			make_user_rule(
				array(
					'id'         => 'millicache:request:rest',
					'order'      => 200,
					'conditions' => array(
						array( 'type' => 'request_url', 'value' => '/some-other-pattern' ),
					),
					'actions'    => array(
						array( 'type' => 'do_cache', 'should_cache' => true, 'reason' => 'user override attempt' ),
					),
				)
			),
		)
	);
	new Engine( null, null, create_test_config() );

	BootstrapRules::register();

	$rule = get_registered_rule( 'millicache:request:rest' );

	expect( $rule )->not->toBeNull();
	expect( ! empty( $rule['_locked'] ) )->toBeTrue( 'locked flag should survive user override attempt' );
	expect( $rule['_metadata']['order'] ?? null )->toBe( 0, 'order should not be replaced by user rule' );
} );
