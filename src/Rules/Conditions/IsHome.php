<?php
/**
 * Is Home Condition
 *
 * Checks if current view is home/front page.
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Rules\Conditions;

/**
 * Class IsHomeCondition
 *
 * Checks if the current view is the home/front page.
 *
 * @since 1.0.0
 */
class IsHome extends BaseCondition {
	/**
	 * Get the condition type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The condition type identifier.
	 */
	public function get_type(): string {
		return 'is_home';
	}

	/**
	 * Get the actual value from context.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context.
	 * @return bool Whether the current view is the home page.
	 */
	protected function get_actual_value( array $context ): bool {
		if ( ! function_exists( 'is_home' ) ) {
			return false;
		}

		return is_home();
	}
}
