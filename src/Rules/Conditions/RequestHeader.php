<?php
/**
 * Request Header Condition
 *
 * Checks HTTP request header values.
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Rules\Conditions;

/**
 * Class RequestHeaderCondition
 *
 * Checks if a request header exists and optionally matches a value.
 *
 * @since 1.0.0
 */
class RequestHeader extends BaseCondition {
	/**
	 * Get the condition type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The condition type identifier.
	 */
	public function get_type(): string {
		return 'request_header';
	}

	/**
	 * Get the actual value from context.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context.
	 * @return string The header value or empty string if not found.
	 */
	protected function get_actual_value( array $context ): string {
		$header_name = $this->config['name'] ?? $this->config['header'] ?? '';
		$headers     = $context['headers'] ?? array();

		if ( ! is_string( $header_name ) || empty( $header_name ) ) {
			return '';
		}

		if ( ! is_array( $headers ) ) {
			return '';
		}

		// Headers are case-insensitive, normalize to lowercase.
		$headers     = array_change_key_case( $headers, CASE_LOWER );
		$header_name = strtolower( $header_name );

		$value = $headers[ $header_name ] ?? '';
		return is_string( $value ) ? $value : '';
	}
}
