<?php
/**
 * Rules Manager
 *
 * Thin wrapper around MilliRules API for fluent access via millicache()->rules().
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage Rules
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MilliRules\Actions\ActionMeta;
use MilliRules\Conditions\ConditionMeta;
use MilliRules\MilliRules;
use MilliRules\Packages\PackageManager;
use MilliRules\Rules;

/**
 * Class Manager
 *
 * Proxies MilliRules static API to enable fluent access via millicache()->rules().
 *
 * Example usage:
 * ```php
 * millicache()->rules()->create('my:custom-rule', 'wp')
 *     ->order(10)
 *     ->when()
 *         ->is_singular('post')
 *     ->then()
 *         ->set_ttl(7200)
 *     ->register();
 * ```
 *
 * @since      1.0.0
 * @package    MilliCache
 * @subpackage Rules
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Manager {

	/**
	 * Create a new rule builder.
	 *
	 * Proxies to Rules::create().
	 *
	 * @since 1.0.0
	 *
	 * @param string      $id   The rule ID.
	 * @param string|null $type Optional. The rule type: 'php' or 'wp'. If null, auto-detected.
	 * @return Rules The rule builder instance.
	 */
	public function create( string $id, ?string $type = null ): Rules {
		self::ensure_rules_registered();

		return Rules::create( $id, $type );
	}

	/**
	 * Register a custom condition callback.
	 *
	 * Proxies to Rules::register_condition().
	 *
	 * @since 1.0.0
	 *
	 * @param string   $type     The condition type identifier.
	 * @param callable $callback The callback function.
	 * @return void
	 */
	public function register_condition( string $type, callable $callback ): void {
		Rules::register_condition( $type, $callback );
	}

	/**
	 * Register a custom action callback.
	 *
	 * Proxies to Rules::register_action(). Returns an ActionMeta
	 * for fluent configuration (e.g., ->scope('flag')).
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Returns ActionMeta for fluent scope configuration.
	 *
	 * @param string   $type     The action type identifier.
	 * @param callable $callback The callback function.
	 * @return ActionMeta Fluent builder for action configuration.
	 */
	public function register_action( string $type, callable $callback ): ActionMeta {
		return Rules::register_action( $type, $callback );
	}

	/**
	 * Register a namespace for condition/action resolution.
	 *
	 * Proxies to Rules::register_namespace().
	 *
	 * @since 1.0.0
	 *
	 * @param string      $type      The type: 'Conditions' or 'Actions'.
	 * @param string      $namespace The namespace to search.
	 * @param string|null $package   Optional package name this namespace belongs to.
	 * @return void
	 */
	public function register_namespace( string $type, string $namespace, ?string $package = null ): void {
		Rules::register_namespace( $type, $namespace, $package );
	}

	/**
	 * Register a custom placeholder resolver.
	 *
	 * Proxies to Rules::register_placeholder().
	 *
	 * @since 1.0.0
	 *
	 * @param string   $placeholder The placeholder name.
	 * @param callable $resolver    The resolver callback.
	 * @return void
	 */
	public function register_placeholder( string $placeholder, callable $resolver ): void {
		Rules::register_placeholder( $placeholder, $resolver );
	}

	/**
	 * Remove a registered rule, built-in ones included.
	 *
	 * @since 1.8.0
	 *
	 * @param string $rule_id The rule ID to remove.
	 * @return bool True when a rule was found and removed.
	 */
	public function unregister( string $rule_id ): bool {
		self::ensure_rules_registered();

		return Rules::unregister( $rule_id );
	}

	/**
	 * Check if a custom condition is registered.
	 *
	 * Proxies to Rules::has_custom_condition().
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The condition type.
	 * @return bool True if registered, false otherwise.
	 */
	public function has_custom_condition( string $type ): bool {
		return Rules::has_custom_condition( $type );
	}

	/**
	 * Check if a custom action is registered.
	 *
	 * Proxies to Rules::has_custom_action().
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The action type.
	 * @return bool True if registered, false otherwise.
	 */
	public function has_custom_action( string $type ): bool {
		return Rules::has_custom_action( $type );
	}

	/**
	 * Get a registered custom condition callback.
	 *
	 * Proxies to Rules::get_custom_condition().
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The condition type.
	 * @return callable|null The callback or null if not found.
	 */
	public function get_custom_condition( string $type ): ?callable {
		return Rules::get_custom_condition( $type );
	}

	/**
	 * Get a registered custom action callback.
	 *
	 * Proxies to Rules::get_custom_action().
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The action type.
	 * @return callable|null The callback or null if not found.
	 */
	public function get_custom_action( string $type ): ?callable {
		return Rules::get_custom_action( $type );
	}

	/**
	 * Compare values using WP_Query-style operators.
	 *
	 * Proxies to Rules::compare_values().
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $actual   The actual value from context.
	 * @param mixed  $expected The expected value from config.
	 * @param string $operator The comparison operator.
	 * @return bool True if comparison matches, false otherwise.
	 */
	public function compare_values( $actual, $expected, string $operator = '=' ): bool {
		return Rules::compare_values( $actual, $expected, $operator );
	}

	/**
	 * Get all rules from all loaded packages, tagged with their package name.
	 *
	 * Proxies to PackageManager::get_all_rules(). Each rule includes a
	 * '_package' key identifying its origin package.
	 *
	 * @since 1.6.0
	 *
	 * @return array<int, array<string, mixed>> Flat array of rules, each with '_package' key added.
	 */
	public function get_packages_rules(): array {
		self::ensure_rules_registered();

		return PackageManager::get_all_rules();
	}

	/**
	 * Orders of registrations that lost an id to another rule.
	 *
	 * The registry holds only the winner, so this is what tells a caller that
	 * a rule it cannot see is competing for the same id.
	 *
	 * @since 1.8.0
	 *
	 * @param string $rule_id The rule ID.
	 * @return array<int, int>
	 */
	public function discarded_orders( string $rule_id ): array {
		self::ensure_rules_registered();

		return PackageManager::discarded_orders( $rule_id );
	}

	/**
	 * Get IDs of rules that overrode a previously registered same-ID rule.
	 *
	 * @since 1.7.0
	 *
	 * @return array<int, string> Overriding rule IDs.
	 */
	public function overridden_rule_ids(): array {
		self::ensure_rules_registered();

		return PackageManager::get_overridden_rule_ids();
	}

	/**
	 * Whether MilliCache's own rules are in the registry.
	 *
	 * Deliberately not derived from the registry being non-empty: a plugin can
	 * register a rule of its own first, and reading that as "the drop-in
	 * already ran" would leave every built-in unregistered.
	 *
	 * @since 1.8.0
	 * @var bool
	 */
	private static bool $registered = false;

	/**
	 * Record that {@see \MilliCache\Engine::register_rules()} has run.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public static function mark_registered(): void {
		self::$registered = true;
	}

	/**
	 * Register the rule set when the drop-in has not.
	 *
	 * {@see \MilliCache\Engine::start()} does this on its way to serving a
	 * cached response, so it only runs from the drop-in. Under WP-CLI, where the
	 * drop-in is skipped, a rule registered through this manager would otherwise
	 * sit in MilliRules' pending queue forever: nothing loads the packages that
	 * would flush it, and the rule ends up in code and in nobody's list.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	private static function ensure_rules_registered(): void {
		if ( self::$registered ) {
			return;
		}

		self::$registered = true;

		MilliRules::init( array( 'PHP' ) );

		Bootstrap::register();
		WordPress::register();
		Custom::register();
		RequestFlags::register();

		// Flushes anything the PHP phase left pending.
		MilliRules::load_packages( array( 'WP' ) );
	}

	/**
	 * Make sure condition and action types can be resolved.
	 *
	 * Type resolution needs the rule packages *registered* — that is what maps
	 * `is_*` and `has_*` to their conditional classes. Registering them is
	 * cheap; loading them and running the rules is not, and neither is needed
	 * to answer what a type is.
	 *
	 * {@see \MilliCache\Engine::start()} does this on its way to executing
	 * rules, but it only runs from the drop-in. Anything that asks about types
	 * outside a cached request — the Rules Builder, the REST routes, the
	 * abilities — would otherwise see an empty registry and report every
	 * WordPress conditional as an unknown type, while the same rules evaluate
	 * fine on the front end.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	private static function ensure_types_resolvable(): void {
		if ( PackageManager::has_packages() ) {
			return;
		}

		// No package names: register the defaults, load nothing.
		MilliRules::init( array() );
	}

	/**
	 * Get the list of valid match types ('all', 'any', 'none').
	 *
	 * Proxies to the Rules::MATCH_TYPES constant.
	 *
	 * @since 1.6.0
	 *
	 * @return array<int, string> The valid match type identifiers.
	 */
	public function match_types(): array {
		self::ensure_types_resolvable();

		return Rules::MATCH_TYPES;
	}

	/**
	 * Validate a rule configuration against the engine's registry.
	 *
	 * Proxies to Rules::validate(). Returns plain-English error strings
	 * for engine-level concerns (match_type, condition/action types,
	 * operators, action arguments). An empty array means the rule is valid.
	 *
	 * @since 1.6.0
	 *
	 * @param array<string, mixed> $rule The rule configuration array.
	 * @return array<int, string> Error messages. Empty if valid.
	 */
	public function validate( array $rule ): array {
		self::ensure_types_resolvable();

		return Rules::validate( $rule );
	}

	/**
	 * Get metadata for all available condition types.
	 *
	 * Proxies to Rules::get_all_condition_metas(). Discovers types from
	 * both class-based namespace registrations and callback-based
	 * registrations.
	 *
	 * @since 1.6.0
	 *
	 * @return array<string, ConditionMeta> Map of type string => ConditionMeta.
	 */
	public function get_all_condition_metas(): array {
		self::ensure_types_resolvable();

		return Rules::get_all_condition_metas();
	}

	/**
	 * Get the full metadata for a single condition type.
	 *
	 * Proxies to Rules::get_condition_meta().
	 *
	 * @since 1.6.0
	 *
	 * @param string $type The condition type.
	 * @return ConditionMeta|null The metadata, or null if not found.
	 */
	public function get_condition_meta( string $type ): ?ConditionMeta {
		self::ensure_types_resolvable();

		return Rules::get_condition_meta( $type );
	}

	/**
	 * Get metadata for all available action types.
	 *
	 * Proxies to Rules::get_all_action_metas(). Discovers types from both
	 * class-based namespace registrations and callback-based registrations.
	 *
	 * @since 1.6.0
	 *
	 * @return array<string, ActionMeta> Map of type string => ActionMeta.
	 */
	public function get_all_action_metas(): array {
		self::ensure_types_resolvable();

		return Rules::get_all_action_metas();
	}

	/**
	 * Get the full metadata for a single action type.
	 *
	 * Proxies to Rules::get_action_meta().
	 *
	 * @since 1.6.0
	 *
	 * @param string $type The action type.
	 * @return ActionMeta|null The metadata, or null if not found.
	 */
	public function get_action_meta( string $type ): ?ActionMeta {
		self::ensure_types_resolvable();

		return Rules::get_action_meta( $type );
	}

	/**
	 * Every placeholder category a rule value may use, as `{category.key}`.
	 *
	 * An empty `keys` array means the key is the caller's to choose, as with a
	 * cookie name. Descriptions are raw English; MilliRules is vendored, so a
	 * text domain there would never reach our POT.
	 *
	 * @since 1.8.0
	 *
	 * @return array<string, array{label: string, description: string, keys: array<int, string>, source: string}> Map of category => metadata.
	 */
	public function get_all_placeholder_metas(): array {
		self::ensure_types_resolvable();

		return Rules::get_all_placeholder_metas();
	}
}
