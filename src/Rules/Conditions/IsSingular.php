<?php
/**
 * Is Singular Condition
 *
 * Checks if the current view is singular.
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Rules\Conditions;

/**
 * Class IsSingularCondition
 *
 * Checks if the current view is singular (post/page/CPT).
 *
 * @since 1.0.0
 */
class IsSingular extends BaseCondition {
	/**
	 * Get the condition type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The condition type identifier.
	 */
	public function get_type(): string {
		return 'is_singular';
	}

	/**
	 * Get the actual value from context.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context.
	 * @return mixed Boolean if checking is_singular(), or post_id if checking a specific post.
	 */
	protected function get_actual_value( array $context ) {
		// If the operator is 'equals', return post_id for comparison.
		if ( 'equals' === $this->operator ) {
			return $context['post_id'] ?? 0;
		}

		// For the 'is' operator, return boolean.
		if ( ! function_exists( 'is_singular' ) ) {
			return false;
		}

		return is_singular();
	}
}
