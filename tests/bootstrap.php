<?php
/**
 * Test bootstrap — WordPress constants and function mocks.
 *
 * Loaded by Pest.php before autoloading and test helpers.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

// Define WordPress constants for compatibility.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../' );
}

if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', '/tmp/wp-content' );
}

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

if ( ! defined( 'MONTH_IN_SECONDS' ) ) {
	define( 'MONTH_IN_SECONDS', 2592000 );
}

if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}

// Define common WordPress functions for testing.
if ( ! function_exists( 'is_network_admin' ) ) {
	function is_network_admin() {
		return false;
	}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		global $test_actions;
		if ( ! isset( $test_actions ) ) {
			$test_actions = array();
		}
		$test_actions[] = array(
			'hook' => $hook,
			'callable' => $callback,
			'priority' => $priority,
			'accepted_args' => $accepted_args,
		);
		return true;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		global $test_filters;
		if ( ! isset( $test_filters ) ) {
			$test_filters = array();
		}
		$test_filters[] = array(
			'hook' => $hook,
			'callable' => $callback,
			'priority' => $priority,
			'accepted_args' => $accepted_args,
		);
		return true;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $hook, $value, ...$args ) {
		global $test_filters;
		if ( isset( $test_filters[ $hook ] ) ) {
			return $test_filters[ $hook ];
		}
		return $value;
	}
}

if ( ! function_exists( 'did_action' ) ) {
	function did_action( $hook ) {
		return 0;
	}
}

if ( ! function_exists( 'do_action' ) ) {
	function do_action( $hook, ...$args ) {
		return null;
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data, $options = 0, $depth = 512 ) {
		return json_encode( $data, $options, $depth );
	}
}

if ( ! function_exists( 'trailingslashit' ) ) {
	function trailingslashit( $value ) {
		return rtrim( $value, '/' ) . '/';
	}
}

if ( ! function_exists( 'untrailingslashit' ) ) {
	function untrailingslashit( $value ) {
		return rtrim( $value, '/' );
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( '_n' ) ) {
	function _n( $single, $plural, $number, $domain = 'default' ) {
		return $number === 1 ? $single : $plural;
	}
}

if ( ! function_exists( 'number_format_i18n' ) ) {
	function number_format_i18n( $number, $decimals = 0 ) {
		return number_format( $number, $decimals );
	}
}

if ( ! function_exists( 'size_format' ) ) {
	function size_format( $bytes, $decimals = 0 ) {
		$units = array( 'B', 'KB', 'MB', 'GB', 'TB' );
		$bytes = max( $bytes, 0 );
		$pow = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow = min( $pow, count( $units ) - 1 );
		$bytes /= pow( 1024, $pow );
		return round( $bytes, $decimals ) . ' ' . $units[ $pow ];
	}
}

if ( ! function_exists( 'get_site_option' ) ) {
	function get_site_option( $option, $default_value = false ) {
		global $test_site_options;
		if ( ! isset( $test_site_options ) ) {
			$test_site_options = array();
		}
		return array_key_exists( $option, $test_site_options ) ? $test_site_options[ $option ] : $default_value;
	}
}

if ( ! function_exists( 'add_site_option' ) ) {
	function add_site_option( $option, $value ) {
		global $test_site_options;
		if ( ! isset( $test_site_options ) ) {
			$test_site_options = array();
		}
		if ( array_key_exists( $option, $test_site_options ) ) {
			return false;
		}
		$test_site_options[ $option ] = $value;
		return true;
	}
}

if ( ! function_exists( 'update_site_option' ) ) {
	function update_site_option( $option, $value ) {
		global $test_site_options;
		if ( ! isset( $test_site_options ) ) {
			$test_site_options = array();
		}
		$test_site_options[ $option ] = $value;
		return true;
	}
}

if ( ! function_exists( 'delete_site_option' ) ) {
	function delete_site_option( $option ) {
		global $test_site_options;
		if ( isset( $test_site_options[ $option ] ) ) {
			unset( $test_site_options[ $option ] );
			return true;
		}
		return false;
	}
}

if ( ! function_exists( 'get_current_network_id' ) ) {
	function get_current_network_id() {
		global $test_current_network_id;
		return $test_current_network_id ?? 1;
	}
}

if ( ! function_exists( 'get_site_transient' ) ) {
	function get_site_transient( $transient ) {
		global $test_site_transients;
		if ( ! isset( $test_site_transients ) ) {
			$test_site_transients = array();
		}
		return $test_site_transients[ $transient ] ?? false;
	}
}

if ( ! function_exists( 'set_site_transient' ) ) {
	function set_site_transient( $transient, $value, $expiration = 0 ) {
		global $test_site_transients;
		if ( ! isset( $test_site_transients ) ) {
			$test_site_transients = array();
		}
		$test_site_transients[ $transient ] = $value;
		return true;
	}
}

if ( ! function_exists( 'delete_site_transient' ) ) {
	function delete_site_transient( $transient ) {
		global $test_site_transients;
		if ( isset( $test_site_transients[ $transient ] ) ) {
			unset( $test_site_transients[ $transient ] );
		}
		return true;
	}
}

if ( ! function_exists( 'wp_delete_file' ) ) {
	function wp_delete_file( $file ) {
		global $test_wp_delete_file_called;
		$test_wp_delete_file_called = $file;
		return @unlink( $file );
	}
}

if ( ! function_exists( 'get_file_data' ) ) {
	function get_file_data( $file_path, $headers, $context = '' ) {
		$content = '';
		if ( is_link( $file_path ) ) {
			$real = readlink( $file_path );
			if ( $real && file_exists( $real ) ) {
				$content = (string) file_get_contents( $real );
			}
		} elseif ( file_exists( $file_path ) ) {
			$content = (string) file_get_contents( $file_path );
		}
		$content = substr( $content, 0, 8192 );

		$result = array();
		foreach ( $headers as $key => $name ) {
			if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( $name, '/' ) . ':(.*)$/mi', $content, $m ) ) {
				$result[ $key ] = trim( (string) preg_replace( '/\s*(?:\*\/|\?>).*/', '', $m[1] ) );
			} else {
				$result[ $key ] = '';
			}
		}
		return $result;
	}
}
