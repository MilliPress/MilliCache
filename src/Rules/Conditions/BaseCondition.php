<?php
/**
 * Base Condition
 *
 * Abstract base class for all condition implementations.
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Rules\Conditions;

use MilliCache\Rules\PlaceholderResolver;

/**
 * Class BaseCondition
 *
 * Provides common functionality for all the conditions, including operator logic
 * and multi-value matching.
 *
 * @since 1.0.0
 */
abstract class BaseCondition implements ConditionInterface {
	/**
	 * Comparison operator.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $operator;

	/**
	 * Expected value(s) to compare against.
	 *
	 * @since 1.0.0
	 * @var string|array<int|string, mixed>
	 */
	protected $value;

	/**
	 * Match type for array values (all/any/none).
	 *
	 * @since 1.0.0
	 * @var string|null
	 */
	protected $match_type;

	/**
	 * Full condition configuration.
	 *
	 * @since 1.0.0
	 * @var array<string, mixed>
	 */
	protected array $config;

	/**
	 * Placeholder resolver instance.
	 *
	 * @since 1.0.0
	 * @var PlaceholderResolver
	 */
	protected PlaceholderResolver $resolver;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $config  The condition configuration.
	 * @param array<string, mixed> $context The execution context.
	 */
	public function __construct( array $config, array $context ) {
		$this->config     = $config;
		$operator_value   = $config['operator'] ?? '=';
		$this->operator   = $this->normalize_operator( is_string( $operator_value ) ? $operator_value : '=' );
		$value_from_config = $config['value'] ?? '';
		// Ensure the value is either string or array as per property type.
		if ( is_array( $value_from_config ) || is_string( $value_from_config ) ) {
			$this->value = $value_from_config;
		} else {
			$this->value = '';
		}
		$match_type_value = $config['match_type'] ?? null;
		$this->match_type = is_string( $match_type_value ) ? $match_type_value : null;
		$this->resolver   = new PlaceholderResolver( $context );
	}

	/**
	 * Normalize operator to uppercase.
	 *
	 * @since 1.0.0
	 *
	 * @param string $operator The operator to normalize.
	 * @return string The normalized operator.
	 */
	private function normalize_operator( string $operator ): string {
		return strtoupper( trim( $operator ) );
	}

	/**
	 * Check if the condition matches.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context.
	 * @return bool True if the condition matches, false otherwise.
	 */
	public function matches( array $context ): bool {
		$actual_value = $this->get_actual_value( $context );

		// Handle array values with match_type logic.
		if ( is_array( $this->value ) ) {
			return $this->check_multiple_values( $actual_value, $this->value );
		}

		// Resolve placeholders in expected value.
		$expected_value = $this->resolver->resolve( $this->value );

		return $this->compare( $actual_value, $expected_value );
	}

	/**
	 * Public static helper to compare values using WP_Query-style operators.
	 *
	 * This method is available for use in custom condition callbacks registered
	 * via Rules::registerCondition(). It provides access to all the operator
	 * logic without needing to extend BaseCondition.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $actual   The actual value from context.
	 * @param mixed  $expected The expected value from config.
	 * @param string $operator The comparison operator (=, !=, >, >=, <, <=, IN, LIKE, REGEXP, etc.).
	 * @return bool True if comparison matches, false otherwise.
	 */
	public static function compareValues( $actual, $expected, string $operator = '=' ): bool {
		// Normalize operator to uppercase.
		$operator = strtoupper( trim( $operator ) );

		// Convert to strings for string operations.
		$actual_str   = is_scalar( $actual ) ? (string) $actual : '';
		$expected_str = is_scalar( $expected ) ? (string) $expected : '';

		switch ( $operator ) {
			// Equality operators.
			case '=':
				return $actual_str === $expected_str;

			case '!=':
				return $actual_str !== $expected_str;

			// Numeric comparison operators.
			case '>':
				return is_numeric( $actual ) && is_numeric( $expected ) && (float) $actual > (float) $expected;

			case '>=':
				return is_numeric( $actual ) && is_numeric( $expected ) && (float) $actual >= (float) $expected;

			case '<':
				return is_numeric( $actual ) && is_numeric( $expected ) && (float) $actual < (float) $expected;

			case '<=':
				return is_numeric( $actual ) && is_numeric( $expected ) && (float) $actual <= (float) $expected;

			// Pattern matching operators.
			case 'LIKE':
				return self::likeMatch( $actual_str, $expected_str );

			case 'NOT LIKE':
				return ! self::likeMatch( $actual_str, $expected_str );

			case 'REGEXP':
				return self::regexpMatch( $actual_str, $expected_str );

			// Array operators.
			case 'IN':
				$expected_array = is_array( $expected ) ? $expected : array( $expected );
				return in_array( $actual_str, array_map( 'strval', $expected_array ), true );

			case 'NOT IN':
				$expected_array = is_array( $expected ) ? $expected : array( $expected );
				return ! in_array( $actual_str, array_map( 'strval', $expected_array ), true );

			// Existence operators.
			case 'EXISTS':
				return ! empty( $actual ) || '0' === $actual || 0 === $actual;

			case 'NOT EXISTS':
				return empty( $actual ) && '0' !== $actual && 0 !== $actual;

			// Boolean operators.
			case 'IS':
				return (bool) $actual === (bool) $expected;

			case 'IS NOT':
				return (bool) $actual !== (bool) $expected;

			default:
				error_log( 'MilliCache Rules: Unknown operator: ' . $operator );
				return false;
		}
	}

	/**
	 * Compare actual and expected values based on the operator.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $actual   The actual value from context.
	 * @param mixed $expected The expected value from config.
	 * @return bool True if comparison matches, false otherwise.
	 */
	protected function compare( $actual, $expected ): bool {
		return self::compareValues( $actual, $expected, $this->operator );
	}

	/**
	 * Static helper for LIKE pattern matching with wildcard support.
	 *
	 * @since 1.0.0
	 *
	 * @param string $string  The string to test.
	 * @param string $pattern The pattern to match against (* and ? wildcards).
	 * @return bool True if the pattern matches, false otherwise.
	 */
	private static function likeMatch( string $string, string $pattern ): bool {
		// Convert LIKE wildcards to regex: * = .*, ? = .
		$regex = '/^' . str_replace(
			array( '\\*', '\\?' ),
			array( '.*', '.' ),
			preg_quote( $pattern, '/' )
		) . '$/i';

		return preg_match( $regex, $string ) === 1;
	}

	/**
	 * Static helper for REGEXP pattern matching.
	 *
	 * @since 1.0.0
	 *
	 * @param string $string  The string to test.
	 * @param string $pattern The pattern to match against.
	 * @return bool True if the pattern matches, false otherwise.
	 */
	private static function regexpMatch( string $string, string $pattern ): bool {
		// Check if the pattern is a regex (enclosed in /).
		if ( preg_match( '/^\/.*\/$/', $pattern ) ) {
			return @preg_match( $pattern, $string ) === 1;
		}

		// Wildcard support: convert * to regex.
		$regex = '/^' . str_replace( array( '\\*', '\\?' ), array( '.*', '.' ), preg_quote( $pattern, '/' ) ) . '$/i';
		return preg_match( $regex, $string ) === 1;
	}

	/**
	 * Perform LIKE pattern matching with wildcard support.
	 *
	 * @since 1.0.0
	 *
	 * @param string $string  The string to test.
	 * @param string $pattern The pattern to match against (* and ? wildcards).
	 * @return bool True if the pattern matches, false otherwise.
	 */
	protected function like_match( string $string, string $pattern ): bool {
		return self::likeMatch( $string, $pattern );
	}

	/**
	 * Check multiple values with match_type logic.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed                    $actual_value    The actual value from context.
	 * @param array<int|string, mixed> $expected_values Array of expected values.
	 * @return bool True if the match_type condition is met, false otherwise.
	 */
	protected function check_multiple_values( $actual_value, array $expected_values ): bool {
		$match_type = $this->match_type ?? 'any';
		$matches    = array();

		foreach ( $expected_values as $expected ) {
			// Resolve placeholders.
			$resolved_expected = is_string( $expected ) ? $this->resolver->resolve( $expected ) : $expected;
			$matches[]         = $this->compare( $actual_value, $resolved_expected );
		}

		switch ( $match_type ) {
			case 'all':
				return ! in_array( false, $matches, true );

			case 'none':
				return ! in_array( true, $matches, true );

			case 'any':
			default:
				return in_array( true, $matches, true );
		}
	}

	/**
	 * Perform pattern matching with wildcard or regex support.
	 *
	 * @since 1.0.0
	 *
	 * @param string $string  The string to test.
	 * @param string $pattern The pattern to match against.
	 * @return bool True if the pattern matches, false otherwise.
	 */
	protected function pattern_match( string $string, string $pattern ): bool {
		return self::regexpMatch( $string, $pattern );
	}

	/**
	 * Get the actual value from context.
	 *
	 * Must be implemented by concrete condition classes.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context.
	 * @return mixed The actual value to compare.
	 */
	abstract protected function get_actual_value( array $context );
}
