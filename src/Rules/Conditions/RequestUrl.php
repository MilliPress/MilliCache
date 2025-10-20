<?php
/**
 * Request URL Condition
 *
 * Checks the request URL/URI.
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Rules\Conditions;

/**
 * Class RequestUrlCondition
 *
 * Checks if the request URL matches a pattern.
 *
 * @since 1.0.0
 */
class RequestUrl extends BaseCondition {
	/**
	 * Get the condition type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The condition type identifier.
	 */
	public function get_type(): string {
		return 'request_url';
	}

	/**
	 * Get the actual value from context.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context.
	 * @return string The request URI.
	 */
	protected function get_actual_value( array $context ): string {
		$uri = $context['request_uri'] ?? '';
		return is_string( $uri ) ? $uri : '';
	}
}
