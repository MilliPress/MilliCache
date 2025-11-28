<?php
/**
 * Server variable utility for safe $_SERVER access.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

namespace MilliCache\Engine\Utilities;

! defined( 'ABSPATH' ) && exit;

/**
 * Utility class for safe access to $_SERVER variables.
 *
 * Provides sanitization and default value handling for server variables.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class ServerVars {

	/**
	 * Get the value of a server variable safely.
	 *
	 * Sanitizes the value by:
	 * - Stripping slashes (handles magic_quotes_gpc)
	 * - Converting special characters to HTML entities
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The server variable key (e.g., 'REQUEST_URI', 'HTTP_HOST').
	 * @return string The sanitized server variable value, or empty string if not set.
	 */
	public static function get( string $key ): string {
		if ( ! isset( $_SERVER[ $key ] ) ) {
			return '';
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- We are sanitizing & un-slashing here with PHP native functions.
		return htmlspecialchars( stripslashes( $_SERVER[ $key ] ), ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Check if a server variable is set.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The server variable key.
	 * @return bool True if the server variable exists.
	 */
	public static function has( string $key ): bool {
		return isset( $_SERVER[ $key ] );
	}

	/**
	 * Get multiple server variables at once.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string> $keys Array of server variable keys.
	 * @return array<string, string> Associative array of key => value pairs.
	 */
	public static function get_many( array $keys ): array {
		$result = array();

		foreach ( $keys as $key ) {
			$result[ $key ] = self::get( $key );
		}

		return $result;
	}
}
