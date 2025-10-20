<?php
/**
 * Request Method Condition
 *
 * Checks the HTTP request method.
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Rules\Conditions;

/**
 * Class RequestMethodCondition
 *
 * Checks if the HTTP request method matches (GET, POST, etc.).
 *
 * @since 1.0.0
 */
class RequestMethod extends BaseCondition {
	/**
	 * Get the condition type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The condition type identifier.
	 */
	public function get_type(): string {
		return 'request_method';
	}

	/**
	 * Get the actual value from context.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context.
	 * @return string The HTTP request method (uppercase).
	 */
	protected function get_actual_value( array $context ): string {
		$method = $context['request_method'] ?? '';
		return is_string( $method ) ? strtoupper( $method ) : '';
	}
}
