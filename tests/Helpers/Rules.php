<?php
/**
 * Integration test helpers for the rules subsystem.
 *
 * Resets MilliRules + MilliCache global state between tests, plus convenience
 * helpers for asserting on the registry built up by Bootstrap / WordPress /
 * RequestFlags / register_user_rules. Intended for tests under tests/Integration.
 *
 * @package    MilliCache
 */

use MilliBase\Settings as BaseSettings;
use MilliCache\Base\Network;
use MilliCache\Base\Site;
use MilliCache\Engine;
use MilliCache\Rules\Actions\PHP\DoCache;
use MilliCache\Rules\Actions\PHP\SetGrace;
use MilliCache\Rules\Actions\PHP\SetTtl;
use MilliRules\MilliRules;
use MilliRules\Packages\PackageManager;
use MilliRules\Rules;

/**
 * Reset all rule-related global state.
 *
 * Called from a Pest beforeEach hook. Order matters: PackageManager state
 * must be reset before the Engine constructor runs (which re-registers
 * MilliCache action namespaces), and MilliRules::init must follow Engine
 * construction (it loads the PHP package fresh).
 *
 * Mirrors the reflection-reset pattern used by upstream MilliRules tests
 * (see vendor/millipress/millirules tests for precedent) — the Rules
 * class doesn't expose a public reset hook for its private static caches.
 */
function reset_rules_state(): void {
	PackageManager::reset();

	reset_rules_class_statics();
	reset_action_last_orders();
	reset_test_globals();
	reset_settings_singleton();

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
				'items' => array(),
			),
		)
	);

	new Engine();

	MilliRules::init( array( 'PHP' ) );
}

/**
 * Clear the private static caches on \MilliRules\Rules.
 *
 * No public reset API exists. Each property is set to its empty
 * baseline; the cache fields default to null.
 */
function reset_rules_class_statics(): void {
	$reflection = new ReflectionClass( Rules::class );

	$arrays_to_empty = array(
		'custom_conditions',
		'custom_actions',
		'action_metas',
		'meta_cache',
		'scope_cache',
		'condition_metas',
		'condition_meta_cache',
	);

	foreach ( $arrays_to_empty as $name ) {
		$property = $reflection->getProperty( $name );
		$property->setAccessible( true );
		$property->setValue( null, array() );
	}

	foreach ( array( 'all_action_metas_cache', 'all_condition_metas_cache' ) as $name ) {
		$property = $reflection->getProperty( $name );
		$property->setAccessible( true );
		$property->setValue( null, null );
	}
}

/**
 * Reset the per-action $last_order tracker for the order-aware MilliCache actions.
 *
 * DoCache, SetTtl and SetGrace each cache the highest order they've seen so
 * later, lower-order rules can't override them. Tests that re-execute rules
 * with the same engine across cases need this cleared.
 */
function reset_action_last_orders(): void {
	foreach ( array( DoCache::class, SetTtl::class, SetGrace::class ) as $class ) {
		$property = ( new ReflectionClass( $class ) )->getProperty( 'last_order' );
		$property->setAccessible( true );
		$property->setValue( null, PHP_INT_MIN );
	}
}

/**
 * Reset the test globals seeded by tests/bootstrap.php WordPress mocks.
 *
 * The mocks accumulate filter and action registrations across tests; rule
 * registration calls add_action('template_redirect', ...) so this matters
 * for tests that inspect $test_actions.
 */
function reset_test_globals(): void {
	$GLOBALS['test_filters']         = array();
	$GLOBALS['test_actions']         = array();
	$GLOBALS['test_site_transients'] = array();
	$GLOBALS['test_is_multisite']    = false;
}

/**
 * Reset the Settings singletons so tests can install their own instances.
 */
function reset_settings_singleton(): void {
	foreach ( array( Site::class, Network::class ) as $class ) {
		$property = ( new ReflectionClass( $class ) )->getProperty( 'settings' );
		$property->setAccessible( true );
		$property->setValue( null, null );
	}
}

/**
 * Install a Settings instance backed by the provided defaults.
 *
 * Uses MilliBase's standalone mode so no WordPress option/database is touched
 * and no hooks are registered. Injected as the per-site instance — rules,
 * cache, and (on single-site) storage all live there.
 *
 * @param array<string, array<string, mixed>> $defaults Module => key => value defaults.
 */
function install_test_settings( array $defaults ): void {
	$settings = BaseSettings::standalone(
		array(
			'slug'     => 'millicache',
			'defaults' => $defaults,
		)
	);

	Site::inject_settings( $settings );
}

/**
 * Find a registered rule by id.
 *
 * @param string $id Rule id (e.g. 'millicache:request:rest').
 * @return array<string, mixed>|null The rule entry from PackageManager, or null.
 */
function get_registered_rule( string $id ): ?array {
	foreach ( PackageManager::get_all_rules() as $rule ) {
		if ( ( $rule['id'] ?? null ) === $id ) {
			return $rule;
		}
	}

	return null;
}

/**
 * Return ids of all rules registered with PackageManager.
 *
 * @return list<string>
 */
function registered_rule_ids(): array {
	$ids = array();

	foreach ( PackageManager::get_all_rules() as $rule ) {
		if ( isset( $rule['id'] ) && is_string( $rule['id'] ) ) {
			$ids[] = $rule['id'];
		}
	}

	return $ids;
}

/**
 * Captured WP-phase action callback for a given hook+priority, or null.
 *
 * Tests that invoke template_redirect callbacks manually use this to find
 * the closure that WordPressRules / RequestFlags registered.
 *
 * @return callable|null
 */
function captured_action( string $hook, int $priority ) {
	$actions = $GLOBALS['test_actions'] ?? array();

	if ( ! is_array( $actions ) ) {
		return null;
	}

	foreach ( $actions as $entry ) {
		if (
			is_array( $entry )
			&& ( $entry['hook'] ?? null ) === $hook
			&& ( $entry['priority'] ?? null ) === $priority
			&& is_callable( $entry['callable'] ?? null )
		) {
			return $entry['callable'];
		}
	}

	return null;
}
