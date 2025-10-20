<?php
/**
 * Cookie Condition
 *
 * Checks cookie existence and values.
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Rules\Conditions;

/**
 * Class CookieCondition
 *
 * Checks if a cookie exists and/or matches a value.
 * When the value is null, checks existence only.
 * When value is provided, checks cookie value.
 *
 * @since 1.0.0
 */
class Cookie extends BaseCondition {
	/**
	 * Get the condition type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The condition type identifier.
	 */
	public function get_type(): string {
		return 'cookie';
	}

	/**
	 * Check if the condition matches.
	 *
	 * Override matches() for special cookie logic.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context.
	 * @return bool True if the condition matches, false otherwise.
	 */
	public function matches( array $context ): bool {
		$cookie_name = $this->get_cookie_name();

		// If no cookie name specified, check if any cookies exist.
		if ( empty( $cookie_name ) ) {
			$cookies = $this->get_cookies_from_context( $context );
			return ! empty( $cookies );
		}

		// Check if value comparison is requested.
		$check_value = array_key_exists( 'value', $this->config );

		if ( ! $check_value ) {
			// Existence checks only.
			$cookies_lower = $this->get_normalized_cookies( $context );
			$cookie_exists = isset( $cookies_lower[ strtolower( $cookie_name ) ] );

			// Handle operators for existence check.
			if ( 'IS' === $this->operator || '=' === $this->operator ) {
				return $cookie_exists;
			} elseif ( 'IS NOT' === $this->operator || '!=' === $this->operator ) {
				return ! $cookie_exists;
			}

			return $cookie_exists;
		}

		// Value comparison - use standard BaseCondition logic.
		return parent::matches( $context );
	}

	/**
	 * Get the actual value from context.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context.
	 * @return string The cookie value or empty string if not found.
	 */
	protected function get_actual_value( array $context ): string {
		$cookie_name = $this->get_cookie_name();

		if ( empty( $cookie_name ) ) {
			return '';
		}

		$cookies_lower = $this->get_normalized_cookies( $context );
		$value = $cookies_lower[ strtolower( $cookie_name ) ] ?? '';
		return is_string( $value ) ? $value : '';
	}

	/**
	 * Get the cookie name from config.
	 *
	 * @since 1.0.0
	 *
	 * @return string The cookie name or empty string.
	 */
	private function get_cookie_name(): string {
		$cookie_name_raw = $this->config['name'] ?? $this->config['cookie'] ?? '';
		return is_string( $cookie_name_raw ) ? $cookie_name_raw : '';
	}

	/**
	 * Get the cookie array from context.
	 *
	 * Tries both locations for backwards compatibility.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context.
	 * @return array<string, mixed> The cookie array.
	 */
	private function get_cookies_from_context( array $context ): array {
		$cookies_from_request = isset( $context['request'] ) && is_array( $context['request'] ) && isset( $context['request']['cookie'] ) ? $context['request']['cookie'] : array();
		return array() !== $cookies_from_request ? $cookies_from_request : ( $context['cookies'] ?? array() );
	}

	/**
	 * Get normalized cookies with lowercase keys.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context.
	 * @return array<string, mixed> Cookies array with lowercase keys.
	 */
	private function get_normalized_cookies( array $context ): array {
		return array_change_key_case( $this->get_cookies_from_context( $context ) );
	}
}
