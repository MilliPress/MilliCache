<?php
/**
 * Pattern matching utility for wildcard and regex patterns.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package     MilliCache
 * @subpackage  Engine\Utilities
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Engine\Utilities;

! defined( 'ABSPATH' ) && exit;

/**
 * Utility class for pattern matching with wildcard and regex support.
 *
 * This class provides static methods for matching strings against patterns
 * that may contain wildcards (*) or be regex patterns (enclosed in #).
 *
 * @since       1.0.0
 * @package     MilliCache
 * @subpackage  Engine\Utilities
 * @author      Philipp Wellmer <hello@millipress.com>
 */
final class PatternMatcher {

	/**
	 * Checks if a string matches a pattern that may contain wildcards or regex.
	 *
	 * Supports three pattern types:
	 * 1. Regex patterns enclosed in hash signs (#pattern#)
	 * 2. Wildcard patterns with asterisks (* matches any characters)
	 * 3. Exact string matching
	 *
	 * @since 1.0.0
	 *
	 * @param string $string  The string to check.
	 * @param string $pattern The pattern to match against. Can contain * wildcards or be a regex pattern enclosed in #.
	 * @return bool True if the string matches the pattern, false otherwise.
	 */
	public static function match( string $string, string $pattern ): bool {
		// For empty pattern, only match empty string.
		if ( '' === $pattern ) {
			return '' === $string;
		}

		// Check if the pattern is a regex (enclosed in hash signs).
		if ( self::is_regex_pattern( $pattern ) ) {
			return self::match_regex( $pattern, $string );
		}

		// If the pattern contains a wildcard *.
		if ( self::has_wildcard( $pattern ) ) {
			return self::match_wildcard( $pattern, $string );
		}

		// No wildcard, perform exact match.
		return $pattern === $string;
	}

	/**
	 * Check if pattern is a regex enclosed in hash signs.
	 *
	 * @since 1.0.0
	 *
	 * @param string $pattern The pattern to check.
	 * @return bool True if pattern is regex format.
	 */
	private static function is_regex_pattern( string $pattern ): bool {
		return strlen( $pattern ) > 2 &&
			   '#' === $pattern[0] &&
			   '#' === $pattern[ strlen( $pattern ) - 1 ];
	}

	/**
	 * Check if pattern contains wildcard character.
	 *
	 * @since 1.0.0
	 *
	 * @param string $pattern The pattern to check.
	 * @return bool True if pattern contains wildcard.
	 */
	private static function has_wildcard( string $pattern ): bool {
		return false !== strpos( $pattern, '*' );
	}

	/**
	 * Match string against regex pattern.
	 *
	 * @since 1.0.0
	 *
	 * @param string $pattern The regex pattern.
	 * @param string $string  The string to match.
	 * @return bool True if string matches regex.
	 */
	private static function match_regex( string $pattern, string $string ): bool {
		return (bool) @preg_match( $pattern, $string );
	}

	/**
	 * Match string against wildcard pattern.
	 *
	 * @since 1.0.0
	 *
	 * @param string $pattern The wildcard pattern.
	 * @param string $string  The string to match.
	 * @return bool True if string matches wildcard pattern.
	 */
	private static function match_wildcard( string $pattern, string $string ): bool {
		// Just a wildcard means match anything.
		if ( '*' === $pattern ) {
			return true;
		}

		// Convert the wildcard pattern to regex.
		$regex_pattern = preg_quote( $pattern, '/' );
		$regex_pattern = str_replace( '\*', '.*', $regex_pattern );

		return (bool) preg_match( '/^' . $regex_pattern . '$/i', $string );
	}
}
