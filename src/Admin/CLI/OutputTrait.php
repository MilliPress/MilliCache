<?php
/**
 * Trait for shared CLI output formatting.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/CLI
 */

namespace MilliCache\Admin\CLI;

! defined( 'ABSPATH' ) && exit;

/**
 * Provides common output formatting methods for CLI commands.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/CLI
 * @author     Philipp Wellmer <hello@millipress.com>
 */
trait OutputTrait {

	/**
	 * Output items in the requested format.
	 *
	 * @since 1.0.0
	 *
	 * @param array<int, array<string, string>> $items    The rows to display.
	 * @param mixed                             $raw_data The original data structure (for JSON).
	 * @param string                            $format   The output format (table, json, yaml).
	 * @param array<int, string>                $columns  The column names for table/yaml output.
	 * @return void
	 */
	protected function output_items( array $items, $raw_data, string $format, array $columns = array( 'key', 'value' ) ): void {
		if ( 'json' === $format ) {
			\WP_CLI::line( (string) wp_json_encode( $raw_data, JSON_PRETTY_PRINT ) );
			return;
		}

		if ( 'yaml' === $format ) {
			$key_col = $columns[0];
			$val_col = $columns[1];
			$yaml    = '';
			foreach ( $items as $item ) {
				$yaml .= sprintf( "%s: %s\n", $item[ $key_col ], $item[ $val_col ] );
			}
			\WP_CLI::line( rtrim( $yaml ) );
			return;
		}

		\WP_CLI\Utils\format_items( 'table', $items, $columns );
	}

	/**
	 * Format a value for display.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value The value to format.
	 * @return string The formatted value.
	 */
	protected function format_value( $value ): string {
		if ( is_bool( $value ) ) {
			return $value ? 'true' : 'false';
		}

		if ( null === $value ) {
			return 'null';
		}

		if ( is_array( $value ) ) {
			return (string) wp_json_encode( $value );
		}

		if ( is_scalar( $value ) ) {
			return (string) $value;
		}

		return (string) wp_json_encode( $value );
	}

	/**
	 * Build rows from a flat key-value array.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $data       The key-value data.
	 * @param string               $key_column The name of the key column.
	 * @param string               $val_column The name of the value column.
	 * @return array<int, array<string, string>> The formatted rows.
	 */
	protected function build_rows_from_array( array $data, string $key_column = 'key', string $val_column = 'value' ): array {
		$items = array();
		foreach ( $data as $key => $value ) {
			$items[] = array(
				$key_column => $key,
				$val_column => $this->format_value( $value ),
			);
		}
		return $items;
	}
}
