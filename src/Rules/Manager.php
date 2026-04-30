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

use MilliRules\Actions\ActionMeta;
use MilliRules\Conditions\ConditionMeta;
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
		return PackageManager::get_all_rules();
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
		return Rules::get_action_meta( $type );
	}
}
