<?php
/**
 * General-purpose static helper methods.
 *
 * @link       https://www.millipress.com
 * @since      1.4.0
 *
 * @package     MilliCache
 * @subpackage  Engine\Utilities
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Engine\Utilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utility class for reusable static helper methods.
 *
 * @since       1.4.0
 * @package     MilliCache
 * @subpackage  Engine\Utilities
 * @author      Philipp Wellmer <hello@millipress.com>
 */
final class Helpers {

	/**
	 * Safely pluck a string array from an associative array by key.
	 *
	 * Returns an empty array when the key is missing or not an array.
	 * All values are cast to strings and filtered to ensure type safety.
	 *
	 * @since 1.4.0
	 *
	 * @param array<string,mixed> $data Source array.
	 * @param string              $key  Key to pluck.
	 * @return array<string> String array or empty array.
	 */
	public static function pluck_string_array( array $data, string $key ): array {
		if ( ! isset( $data[ $key ] ) || ! is_array( $data[ $key ] ) ) {
			return array();
		}

		return array_map(
			static fn ( $v ) => is_scalar( $v ) ? (string) $v : '',
			$data[ $key ]
		);
	}
}
