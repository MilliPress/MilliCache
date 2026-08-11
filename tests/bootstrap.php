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
		global $test_did_actions;
		if ( ! isset( $test_did_actions ) ) {
			$test_did_actions = array();
		}
		$test_did_actions[] = array(
			'hook' => $hook,
			'args' => $args,
		);
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

if ( ! function_exists( 'wp_opcache_invalidate' ) ) {
	function wp_opcache_invalidate( $filepath, $force = false ) {
		global $test_opcache_invalidated;
		$test_opcache_invalidated[] = $filepath;
		return true;
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

// Simulated home URL, shared by every suite.
if ( ! function_exists( 'home_url' ) ) {
	function home_url( $path = '', $scheme = null ) {
		global $millicache_reentry_probe;

		if ( is_callable( $millicache_reentry_probe ) ) {
			$probe = $millicache_reentry_probe;
			// Fire at most once: caps recursion so a regressed fix fails the
			// reentrancy assertions instead of crashing the whole test run.
			$millicache_reentry_probe = null;
			$probe();
		}

		return 'http://millicache.test' . ( is_string( $path ) ? $path : '' );
	}
}

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
	function wp_strip_all_tags( $text ) {
		return trim( strip_tags( (string) $text ) );
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ) {
		return $thing instanceof WP_Error;
	}
}

// Canonical stubs. Individual test files guard their own copies with
// class_exists, so defining these here makes every unit test share one
// definition instead of racing on file order.
if ( ! class_exists( 'WP_Error' ) ) {
	// phpcs:ignore Generic.Files.OneObjectStructurePerFile.MultipleFound
	class WP_Error {
		private string $code;
		private string $message;
		/** @var array<string, mixed> */
		private array $data;

		/**
		 * @param array<string, mixed> $data Error data.
		 */
		public function __construct( string $code = '', string $message = '', array $data = array() ) {
			$this->code    = $code;
			$this->message = $message;
			$this->data    = $data;
		}

		public function get_error_code(): string {
			return $this->code;
		}

		public function get_error_message(): string {
			return $this->message;
		}

		/**
		 * @return array<string, mixed>
		 */
		public function get_error_data(): array {
			return $this->data;
		}
	}
}

if ( ! class_exists( 'WP_REST_Request' ) ) {
	// phpcs:ignore Generic.Files.OneObjectStructurePerFile.MultipleFound
	class WP_REST_Request {
		/** @var array<string, mixed> */
		private array $params = array();

		private string $method;

		public function __construct( string $method = 'GET' ) {
			$this->method = $method;
		}

		public function get_method(): string {
			return $this->method;
		}

		/**
		 * @param mixed $value The value to set.
		 */
		public function set_param( string $key, $value ): void {
			$this->params[ $key ] = $value;
		}

		/**
		 * @return mixed
		 */
		public function get_param( string $key ) {
			return $this->params[ $key ] ?? null;
		}

		/**
		 * @return array<string, mixed>
		 */
		public function get_params(): array {
			return $this->params;
		}
	}
}

if ( ! class_exists( 'WP_REST_Response' ) ) {
	// phpcs:ignore Generic.Files.OneObjectStructurePerFile.MultipleFound
	class WP_REST_Response {
		/** @var mixed */
		private $data;

		/**
		 * @param mixed $data The response data.
		 */
		public function __construct( $data = null ) {
			$this->data = $data;
		}

		/**
		 * @return mixed
		 */
		public function get_data() {
			return $this->data;
		}
	}
}
