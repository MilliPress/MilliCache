<?php
/**
 * Post Type Condition
 *
 * Checks WordPress post type.
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Rules\Conditions;

/**
 * Class PostTypeCondition
 *
 * Checks if the current post-type matches a value.
 *
 * @since 1.0.0
 */
class PostType extends BaseCondition {
	/**
	 * Get the condition type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The condition type identifier.
	 */
	public function get_type(): string {
		return 'post_type';
	}

	/**
	 * Get the actual value from context.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context.
	 * @return string The current post-type.
	 */
	protected function get_actual_value( array $context ): string {
		if ( ! isset( $context['post'] ) || ! is_array( $context['post'] ) ) {
			return '';
		}

		return $context['post']['type'] ?? '';
	}
}
