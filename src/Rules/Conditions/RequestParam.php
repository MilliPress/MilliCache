<?php
/**
 * Request Parameter Condition
 *
 * Checks URL/query parameter values.
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Rules\Conditions;

/**
 * Class RequestParamCondition
 *
 * Checks if a URL parameter exists and optionally matches a value.
 *
 * @since 1.0.0
 */
class RequestParam extends BaseCondition {
	/**
	 * Get the condition type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The condition type identifier.
	 */
	public function get_type(): string {
		return 'request_param';
	}

	/**
	 * Get the actual value from context.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context.
	 * @return string The parameter value or empty string if not found.
	 */
	protected function get_actual_value( array $context ): string {
		if ( ! isset( $context['request'] ) || ! is_array( $context['request'] ) ) {
			return '';
		}

		$param_name = $this->config['name'] ?? $this->config['param'] ?? '';

		if ( empty( $param_name ) ) {
			return '';
		}

		return $context['request']['params'][ $param_name ] ?? '';
	}
}
