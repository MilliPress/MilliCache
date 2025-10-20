<?php
/**
 * WP Constant Condition
 *
 * Checks WordPress constant values.
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Rules\Conditions;

/**
 * Class WpConstantCondition
 *
 * Checks if a WordPress constant exists and optionally matches a value.
 *
 * @since 1.0.0
 */
class WpConstant extends BaseCondition {
	/**
	 * Get the condition type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The condition type identifier.
	 */
	public function get_type(): string {
		return 'wp_constant';
	}

	/**
	 * Get the actual value from context.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context.
	 * @return mixed The constant value or null if not defined.
	 */
	protected function get_actual_value( array $context ) {
		$constant_name = $this->config['name'] ?? $this->config['constant'] ?? '';

		if ( ! is_string( $constant_name ) || empty( $constant_name ) ) {
			return null;
		}

		return defined( $constant_name ) ? constant( $constant_name ) : null;
	}
}
