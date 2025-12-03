<?php
/**
 * ArgumentValue - Fluent wrapper for action argument access with automatic placeholder resolution.
 *
 * @package MilliCache\Deps\MilliRules
 * @since   0.4.0
 * @author  MilliPress
 */

namespace MilliCache\Deps\MilliRules;

/**
 * Provides a fluent API for accessing action arguments with automatic
 * placeholder resolution and type-safe conversion.
 *
 * This class wraps raw argument values and provides methods for converting
 * them to specific types (string, bool, int, float, array) with sensible
 * defaults and automatic placeholder resolution.
 *
 * @since 0.4.0
 */
class ArgumentValue
{
	/**
	 * The raw argument value before placeholder resolution.
	 *
	 * @since 0.4.0
	 * @var mixed
	 */
	private $raw_value;

	/**
	 * PlaceholderResolver instance for resolving placeholder strings.
	 *
	 * @since 0.4.0
	 * @var PlaceholderResolver
	 */
	private PlaceholderResolver $resolver;

	/**
	 * The resolved value after placeholder resolution (cached).
	 *
	 * @since 0.4.0
	 * @var mixed
	 */
	private $resolved_value;

	/**
	 * Whether the value has been resolved yet.
	 *
	 * @since 0.4.0
	 * @var bool
	 */
	private bool $is_resolved = false;

	/**
	 * Constructor.
	 *
	 * @since 0.4.0
	 *
	 * @param mixed               $raw_value The raw argument value.
	 * @param mixed               $default   The default value if raw value is null.
	 * @param PlaceholderResolver $resolver  PlaceholderResolver instance for resolution.
	 */
	public function __construct($raw_value, $default, PlaceholderResolver $resolver)
	{
		$this->raw_value = $raw_value ?? $default;
		$this->resolver  = $resolver;
	}

	/**
	 * Resolve placeholders in the value (lazy resolution with caching).
	 *
	 * Only resolves strings. Other types are passed through unchanged.
	 * The resolved value is cached to avoid duplicate resolution.
	 *
	 * @since 0.4.0
	 *
	 * @return mixed The resolved value.
	 */
	private function resolve()
	{
		if ( $this->is_resolved ) {
			return $this->resolved_value;
		}

		// Only resolve strings
		if ( is_string( $this->raw_value ) ) {
			$this->resolved_value = $this->resolver->resolve( $this->raw_value );
		} else {
			$this->resolved_value = $this->raw_value;
		}

		$this->is_resolved = true;

		return $this->resolved_value;
	}

	/**
	 * Convert value to string.
	 *
	 * - null → ''
	 * - array → json_encode()
	 * - scalar → (string) cast
	 *
	 * @since 0.4.0
	 *
	 * @return string The value as a string.
	 */
	public function string(): string
	{
		$value = $this->resolve();

		if ( $value === null ) {
			return '';
		}

		if ( is_array( $value ) ) {
			return json_encode( $value );
		}

		if ( is_object( $value ) ) {
			return '';
		}

		return (string) $value;
	}

	/**
	 * Convert value to boolean.
	 *
	 * Handles string booleans intelligently:
	 * - 'true', 'yes', '1' → true
	 * - 'false', 'no', '0', '' → false
	 * - null → false
	 * - Other values → PHP bool cast
	 *
	 * @since 0.4.0
	 *
	 * @return bool The value as a boolean.
	 */
	public function bool(): bool
	{
		$value = $this->resolve();

		if ( $value === null ) {
			return false;
		}

		// Handle string booleans
		if ( is_string( $value ) ) {
			$lower = strtolower( trim( $value ) );

			if ( in_array( $lower, array( 'true', 'yes', '1' ), true ) ) {
				return true;
			}

			if ( in_array( $lower, array( 'false', 'no', '0', '' ), true ) ) {
				return false;
			}
		}

		return (bool) $value;
	}

	/**
	 * Convert value to integer.
	 *
	 * - null → 0
	 * - numeric string → parsed integer
	 * - Other → PHP int cast
	 *
	 * @since 0.4.0
	 *
	 * @return int The value as an integer.
	 */
	public function int(): int
	{
		$value = $this->resolve();

		if ( $value === null ) {
			return 0;
		}

		return (int) $value;
	}

	/**
	 * Convert value to float.
	 *
	 * - null → 0.0
	 * - numeric string → parsed float
	 * - Other → PHP float cast
	 *
	 * @since 0.4.0
	 *
	 * @return float The value as a float.
	 */
	public function float(): float
	{
		$value = $this->resolve();

		if ( $value === null ) {
			return 0.0;
		}

		return (float) $value;
	}

	/**
	 * Convert value to array.
	 *
	 * - null → []
	 * - array → returned as-is
	 * - JSON string → json_decode() to array
	 * - scalar → wrapped in array
	 *
	 * @since 0.4.0
	 *
	 * @return array<mixed> The value as an array.
	 */
	public function array(): array
	{
		$value = $this->resolve();

		if ( $value === null ) {
			return array();
		}

		if ( is_array( $value ) ) {
			return $value;
		}

		// Try JSON decode for strings
		if ( is_string( $value ) && ! empty( $value ) ) {
			$decoded = json_decode( $value, true );
			if ( is_array( $decoded ) ) {
				return $decoded;
			}
		}

		// Wrap scalar in array
		return array( $value );
	}

	/**
	 * Get the resolved value without type conversion.
	 *
	 * Returns the placeholder-resolved value in its native type.
	 *
	 * @since 0.4.0
	 *
	 * @return mixed The resolved value without type casting.
	 */
	public function raw()
	{
		return $this->resolve();
	}
}
