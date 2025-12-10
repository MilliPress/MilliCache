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

use MilliCache\Deps\MilliRules\Rules;

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
	 * Proxies to Rules::register_action().
	 *
	 * @since 1.0.0
	 *
	 * @param string   $type     The action type identifier.
	 * @param callable $callback The callback function.
	 * @return void
	 */
	public function register_action( string $type, callable $callback ): void {
		Rules::register_action( $type, $callback );
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
}
