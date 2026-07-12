<?php
/**
 * Integration tests for site-scope rule loading via the Custom class.
 *
 * Covers the Site Settings -> Custom::register() -> register_rule_list()
 * path. Each scenario installs a standalone Settings instance with rule
 * data, runs CustomRules::register() (and BootstrapRules::register() where
 * tests assert interaction with built-ins), and inspects the resulting
 * registry. Multisite (network-scope) coverage lives in
 * MultisiteCustomRegistrationTest.php.
 *
 * @package MilliCache
 */

use MilliCache\Engine;
use MilliCache\Rules\Bootstrap as BootstrapRules;
use MilliCache\Rules\Custom as CustomRules;
use MilliCache\Rules\WordPress as WordPressRules;
use MilliRules\MilliRules;

/**
 * Build a minimal valid custom rule for tests; callers override what they need.
 *
 * @param array<string, mixed> $overrides Keys to override on the base rule.
 * @return array<string, mixed>
 */
function make_custom_rule( array $overrides = array() ): array {
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
 * Install settings with the given rule items.
 *
 * @param list<array<string, mixed>> $items
 */
function install_custom_rules( array $items ): void {
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
				'items' => $items,
			),
		)
	);
}

it( 'loads enabled custom rules from settings', function () {
	install_custom_rules( array( make_custom_rule() ) );
	new Engine( null, null, create_test_config() );

	CustomRules::register();

	$rule = get_registered_rule( 'user:test' );

	expect( $rule )->not->toBeNull();
	expect( $rule['_metadata']['order'] ?? null )->toBe( 50 );
	expect( $rule['match_type'] ?? null )->toBe( 'all' );
} );

it( 'is a no-op when rules.items is empty', function () {
	install_custom_rules( array() );
	new Engine( null, null, create_test_config() );

	CustomRules::register();

	$user_ids = array_filter(
		registered_rule_ids(),
		static fn( string $id ): bool => str_starts_with( $id, 'user:' )
	);

	expect( $user_ids )->toBeEmpty();
} );

it( 'skips disabled custom rules', function () {
	install_custom_rules(
		array(
			make_custom_rule( array( 'id' => 'user:on',  'enabled' => true ) ),
			make_custom_rule( array( 'id' => 'user:off', 'enabled' => false ) ),
		)
	);
	new Engine( null, null, create_test_config() );

	CustomRules::register();

	expect( get_registered_rule( 'user:on' ) )->not->toBeNull();
	expect( get_registered_rule( 'user:off' ) )->toBeNull();
} );

it( 'skips entries with empty id', function () {
	install_custom_rules(
		array(
			make_custom_rule( array( 'id' => '' ) ),
			make_custom_rule( array( 'id' => 'user:valid' ) ),
		)
	);
	new Engine( null, null, create_test_config() );

	CustomRules::register();

	expect( registered_rule_ids() )->toContain( 'user:valid' );
	expect( registered_rule_ids() )->not->toContain( '' );
} );

it( 'honors match_type all / any / none', function () {
	install_custom_rules(
		array(
			make_custom_rule( array( 'id' => 'user:all',  'match_type' => 'all' ) ),
			make_custom_rule( array( 'id' => 'user:any',  'match_type' => 'any' ) ),
			make_custom_rule( array( 'id' => 'user:none', 'match_type' => 'none' ) ),
		)
	);
	new Engine( null, null, create_test_config() );

	CustomRules::register();

	expect( get_registered_rule( 'user:all' )['match_type'] ?? null )->toBe( 'all' );
	expect( get_registered_rule( 'user:any' )['match_type'] ?? null )->toBe( 'any' );
	expect( get_registered_rule( 'user:none' )['match_type'] ?? null )->toBe( 'none' );
} );

it( 'defaults order to 10 when omitted (MilliRules default)', function () {
	$rule_data = make_custom_rule();
	unset( $rule_data['order'] );

	install_custom_rules( array( $rule_data ) );
	new Engine( null, null, create_test_config() );

	CustomRules::register();

	expect( get_registered_rule( 'user:test' )['_metadata']['order'] ?? null )->toBe( 10 );
} );

it( 'applies hook + priority when provided', function () {
	install_custom_rules(
		array(
			make_custom_rule(
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

	CustomRules::register();

	$rule = get_registered_rule( 'user:wp' );

	expect( $rule )->not->toBeNull();
	expect( $rule['_metadata']['hook'] ?? null )->toBe( 'template_redirect' );
	expect( $rule['_metadata']['hook_priority'] ?? null )->toBe( 25 );
} );

it( 'overwrites an unlocked default rule when registered with the same id at higher order', function () {
	install_custom_rules(
		array(
			make_custom_rule(
				array(
					'id'    => 'millicache:request:file',
					'order' => 100,
				)
			),
		)
	);
	new Engine( null, null, create_test_config() );

	BootstrapRules::register();
	CustomRules::register();

	$rule = get_registered_rule( 'millicache:request:file' );

	expect( $rule )->not->toBeNull();
	expect( $rule['_metadata']['order'] ?? null )->toBe( 100, 'custom rule should win for unlocked default' );
} );

it( 'cannot overwrite a locked bootstrap rule via custom settings', function () {
	install_custom_rules(
		array(
			make_custom_rule(
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
	CustomRules::register();

	$rule = get_registered_rule( 'millicache:request:rest' );

	expect( $rule )->not->toBeNull();
	expect( ! empty( $rule['_locked'] ) )->toBeTrue( 'locked flag should survive custom override attempt' );
	expect( $rule['_metadata']['order'] ?? null )->toBe( 0, 'order should not be replaced by custom rule' );
} );

it( 'translates rule-level locked: true into the engine _locked flag', function () {
	install_custom_rules(
		array(
			make_custom_rule(
				array(
					'id'     => 'user:locked',
					'locked' => true,
				)
			),
		)
	);
	new Engine( null, null, create_test_config() );

	CustomRules::register();

	$rule = get_registered_rule( 'user:locked' );

	expect( $rule )->not->toBeNull();
	expect( ! empty( $rule['_locked'] ) )->toBeTrue();
} );

it( 'lets a locked custom rule reject a later same-ID re-registration', function () {
	install_custom_rules(
		array(
			make_custom_rule(
				array(
					'id'     => 'user:dup',
					'order'  => 10,
					'locked' => true,
				)
			),
			make_custom_rule(
				array(
					'id'    => 'user:dup',
					'order' => 999,
				)
			),
		)
	);
	new Engine( null, null, create_test_config() );

	CustomRules::register();

	$rule = get_registered_rule( 'user:dup' );

	expect( $rule )->not->toBeNull();
	expect( $rule['_metadata']['order'] ?? null )->toBe( 10, 'first locked registration must win' );
} );

it( 'translates action-level locked: true into the engine _locked flag', function () {
	install_custom_rules(
		array(
			make_custom_rule(
				array(
					'id'      => 'user:action-lock',
					'actions' => array(
						array(
							'type'         => 'do_cache',
							'should_cache' => false,
							'reason'       => 'locked action',
							'locked'       => true,
						),
					),
				)
			),
		)
	);
	new Engine( null, null, create_test_config() );

	CustomRules::register();

	$rule = get_registered_rule( 'user:action-lock' );

	expect( $rule )->not->toBeNull();

	$actions = $rule['actions'] ?? array();

	expect( $actions )->not->toBeEmpty();
	expect( ! empty( $actions[0]['_locked'] ) )->toBeTrue( 'action-level lock must reach the engine as _locked' );
	expect( array_key_exists( 'locked', $actions[0] ) )->toBeFalse( 'public-facing locked key should be stripped' );
} );

it( 'leaves actions without locked flag untouched', function () {
	install_custom_rules( array( make_custom_rule( array( 'id' => 'user:plain' ) ) ) );
	new Engine( null, null, create_test_config() );

	CustomRules::register();

	$rule = get_registered_rule( 'user:plain' );

	expect( $rule )->not->toBeNull();

	$actions = $rule['actions'] ?? array();

	expect( $actions )->not->toBeEmpty();
	expect( array_key_exists( '_locked', $actions[0] ) )->toBeFalse();
} );

it( 'overrides an unlocked WP-phase built-in by ID when registered after WordPressRules', function () {
	install_custom_rules(
		array(
			make_custom_rule(
				array(
					'id'    => 'millicache:wp:logged-in',
					'order' => 50,
				)
			),
		)
	);
	new Engine( null, null, create_test_config() );

	MilliRules::load_packages( array( 'WP' ) );

	WordPressRules::register();
	CustomRules::register();

	$rule = get_registered_rule( 'millicache:wp:logged-in' );

	expect( $rule )->not->toBeNull();
	expect( $rule['_metadata']['order'] ?? null )->toBe(
		50,
		'scoped rule registering after the unlocked WP-phase built-in must win by ID'
	);
} );

it( 'cannot overwrite a locked WP-phase built-in (doing-cron)', function () {
	install_custom_rules(
		array(
			make_custom_rule(
				array(
					'id'    => 'millicache:wp:const:doing-cron',
					'order' => 200,
				)
			),
		)
	);
	new Engine( null, null, create_test_config() );

	MilliRules::load_packages( array( 'WP' ) );

	WordPressRules::register();
	CustomRules::register();

	$rule = get_registered_rule( 'millicache:wp:const:doing-cron' );

	expect( $rule )->not->toBeNull();
	expect( ! empty( $rule['_locked'] ) )->toBeTrue();
	expect( $rule['_metadata']['order'] ?? null )->toBe(
		0,
		'locked WP-phase built-in must reject scoped same-ID re-registration'
	);
} );

it( 'auto-detects scoped rules using WP-package conditions as type=wp', function () {
	install_custom_rules(
		array(
			make_custom_rule(
				array(
					'id'         => 'user:wp-condition',
					'conditions' => array(
						array( 'type' => 'is_user_logged_in', 'value' => true ),
					),
					'actions'    => array(
						array( 'type' => 'do_cache', 'should_cache' => false, 'reason' => 'wp condition' ),
					),
				)
			),
		)
	);
	new Engine( null, null, create_test_config() );

	MilliRules::load_packages( array( 'WP' ) );

	CustomRules::register();

	$rule = get_registered_rule( 'user:wp-condition' );

	expect( $rule )->not->toBeNull();
	expect( $rule['_metadata']['type'] ?? null )->toBe(
		'wp',
		'WP-only conditions must drive auto-detect to the WP package (regression guard for PL:2 placement)'
	);
} );

it( 'registers explicit php-typed settings rules at PHP-phase before the WP package loads', function () {
	install_custom_rules(
		array(
			make_custom_rule(
				array(
					'id'         => 'user:early-bypass',
					'type'       => 'php',
					'conditions' => array(
						array( 'type' => 'request_url', 'value' => '/foo' ),
					),
				)
			),
		)
	);
	new Engine( null, null, create_test_config() );

	// Deliberately NO MilliRules::load_packages(['WP']) — this test simulates
	// the PHP-phase entry path where only the PHP package is available.
	CustomRules::register();

	$rule = get_registered_rule( 'user:early-bypass' );

	expect( $rule )->not->toBeNull(
		'PHP-typed settings rules must register at PHP-phase so they fire on the current request'
	);
	expect( $rule['_metadata']['type'] ?? null )->toBe( 'php' );
} );

it( 'queues WP-package settings rules at PHP-phase and finalizes them when the WP package loads', function () {
	install_custom_rules(
		array(
			make_custom_rule(
				array(
					'id'         => 'user:wp-pending',
					'conditions' => array(
						array( 'type' => 'is_user_logged_in', 'value' => true ),
					),
					'actions'    => array(
						array( 'type' => 'do_cache', 'should_cache' => false, 'reason' => 'pending' ),
					),
				)
			),
		)
	);
	new Engine( null, null, create_test_config() );

	// Simulate the production sequence: Custom::register() at PHP-phase,
	// then load_packages(['WP']) on plugins_loaded:1.
	CustomRules::register();

	expect( get_registered_rule( 'user:wp-pending' ) )->toBeNull(
		'WP-package rule must queue as pending while only the PHP package is loaded'
	);

	MilliRules::load_packages( array( 'WP' ) );

	$rule = get_registered_rule( 'user:wp-pending' );

	expect( $rule )->not->toBeNull(
		'pending WP-package rule must finalize when the WP package loads'
	);
	expect( $rule['_metadata']['type'] ?? null )->toBe( 'wp' );
} );
