<?php
/**
 * Is User Logged In Condition
 *
 * Checks if the user is logged in.
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Rules\Conditions;

/**
 * Class IsUserLoggedInCondition
 *
 * Checks if the current user is logged in.
 *
 * @since 1.0.0
 */
class IsUserLoggedIn extends BaseCondition {
	/**
	 * Get the condition type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The condition type identifier.
	 */
	public function get_type(): string {
		return 'is_user_logged_in';
	}

	/**
	 * Get the actual value from context.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context.
	 * @return bool Whether the user is logged in.
	 */
	protected function get_actual_value( array $context ): bool {
		if ( ! function_exists( 'is_user_logged_in' ) ) {
			return false;
		}

		return is_user_logged_in();
	}
}
