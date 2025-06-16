<?php
/**
 * The independent Cache-Engine to avoid an overhead.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/includes
 */

namespace MilliCache;

! defined( 'ABSPATH' ) && exit;

/**
 * Fired by advanced-cache.php
 *
 * This class defines all code necessary for caching.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @subpackage MilliCache/includes
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Engine {

	/**
	 * If the cache engine has been started.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var bool If the cache engine has been started.
	 */
	private static bool $started = false;

	/**
	 * The Cache Storage object.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var Redis The Cache Storage object.
	 */
	private static Redis $storage;

	/**
	 * The MilliPress Settings instance.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var Settings The MilliPress Settings instance.
	 */
	private static Settings $settings_instance;

	/**
	 * The MilliPress Settings.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array<mixed> The MilliPress Settings.
	 */
	private static array $settings;

	/**
	 * TTL.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var int The time to live for the cache.
	 */
	private static int $ttl;

	/**
	 * Max TTL.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var int The maximum time to live for the cache.
	 */
	public static int $max_ttl;

	/**
	 * Variables that make the request unique.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array<string> Unique request variables.
	 */
	private static array $unique;

	/**
	 * Cookies that avoid caching.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array<string> Do not cache cookies.
	 */
	private static array $nocache_cookies;

	/**
	 * Cookies that are ignored.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array<string> Ignore cookies for cache hash.
	 */
	private static array $ignore_cookies;

	/**
	 * Request keys that are ignored.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array<string> Ignored request keys.
	 */
	private static array $ignore_request_keys;

	/**
	 * External callback to append cache conditions.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var string Callback to append cache conditions.
	 */
	private static string $should_cache_callback;

	/**
	 * Gzip compression.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var bool Gzip compression.
	 */
	private static bool $gzip;

	/**
	 * Debug mode.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var bool Debug mode.
	 */
	private static bool $debug;

	/**
	 * Request hash.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var string The request hash.
	 */
	private static string $request_hash;

	/**
	 * Debug data.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array<string,mixed>|false Debug data.
	 */
	private static $debug_data = false;

	/**
	 * FastCGI Regenerate.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var bool If we can regenerate the request in background.
	 */
	private static bool $fcgi_regenerate = false;

	/**
	 * Flag requests and expire/delete them efficiently.
	 */

	/**
	 * Request flags.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array<string> Flags.
	 */
	private static array $flags = array();

	/**
	 * Flags to expire.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array<string> Expire flags.
	 */
	private static array $flags_expire = array();

	/**
	 * Flags to delete.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array<string> Delete flags.
	 */
	private static array $flags_delete = array();

	/**
	 * Start the cache engine.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public static function start() {
		self::get_settings();
		self::config();
		self::get_storage();
		self::warmup();

		if ( self::should_start() ) {
			self::run();
		}

		self::$started = true;
	}

	/**
	 * If not running, start the cache engine.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public function __construct() {
		if ( ! self::$started ) {
			self::start();
		}
	}

	/**
	 * Load & overwrite configuration from wp-config.php.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private static function config() {
		// Load the cache configuration.
		foreach ( (array) self::$settings['cache'] as $key => $value ) {
			self::$$key = $value;
		}

		// Ignore test cookie by default.
		$test_cookie = defined( 'TEST_COOKIE' ) ? TEST_COOKIE : 'wordpress_test_cookie';
		$logged_in_cookie = defined( 'LOGGED_IN_COOKIE' ) ? LOGGED_IN_COOKIE : 'wordpress_logged_in';

		// Ignore test cookie by default.
		self::$ignore_cookies[] = $test_cookie;

		// No caching with auth cookies by default.
		self::$nocache_cookies[] = $logged_in_cookie;

		// Always set the test-cookie for wp-login.php POST requests.
		if ( strpos( self::get_server_var( 'REQUEST_URI' ), '/wp-login.php' ) === 0 && strtoupper( self::get_server_var( 'REQUEST_METHOD' ) ) == 'POST' ) {
			$_COOKIE[ $test_cookie ] = 'WP Cookie check';
		}
	}

	/**
	 * Get the Settings.
	 *
	 * @return array<mixed> The MilliPress Settings.
	 */
	public static function get_settings(): array {
		if ( ! isset( self::$settings ) ) {
			/**
			 * The MilliPress Settings class.
			 */
			require_once __DIR__ . '/class-settings.php';

			self::$settings_instance = new Settings();
			self::$settings = self::$settings_instance->get_settings();
		}

		return self::$settings;
	}

	/**
	 * Returns the MilliCache Storage instance.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   Redis The MilliCache Storage instance.
	 */
	public static function get_storage(): Redis {
		if ( ! isset( self::$storage ) ) {
			/**
			 * The MilliPress Redis class.
			 */
			require_once __DIR__ . '/class-redis.php';
			self::$storage = new Redis( (array) self::$settings['redis'] );
		}

		return self::$storage;
	}

	/**
	 * Warmup the cache engine.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private static function warmup() {
		// Register the shutdown function to expire/delete cache flags.
		register_shutdown_function( array( __CLASS__, 'clear_cache_on_shutdown' ) );

		// Always set the initial header.
		self::set_header( 'Status', 'miss' );
	}

	/**
	 * Run the cache engine.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private static function run() {
		// Generate a unique request hash.
		self::generate_request_hash();

		// Get the cache.
		self::get_cache();

		// Start the output buffer and init output.
		add_action(
			'template_redirect',
			function () {
				if ( self::should_cache() ) {
					self::start_buffer();
				}
			},
			0
		);
	}

	/**
	 * Generate a unique request hash.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private static function generate_request_hash() {
		// Clean up request variables.
		self::clean_request();

		$request_hash = array(
			'request' => self::parse_request_uri( self::get_server_var( 'REQUEST_URI' ) ),
			'host' => self::get_server_var( 'HTTP_HOST' ),
			'https' => self::get_server_var( 'HTTPS' ),
			'method' => self::get_server_var( 'REQUEST_METHOD' ),
			'unique' => self::$unique,
			'cookies' => self::parse_cookies( $_COOKIE ),
		);

		// Make sure requests with Authorization: headers are unique.
		if ( ! empty( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
			$request_hash['unique']['mc-auth-header'] = self::get_server_var( 'HTTP_AUTHORIZATION' );
		}

		if ( self::$debug ) {
			self::$debug_data = array( 'request_hash' => $request_hash );
		}

		// Convert to an actual hash.
		self::$request_hash = md5( serialize( $request_hash ) );
		unset( $request_hash );

		if ( self::$debug ) {
			self::set_header( 'Key', self::$request_hash );
		}
	}

	/**
	 * Get the cache for the request.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private static function get_cache() {
		if ( ! self::$storage->is_available() ) {
			return;
		}

		// Look for an existing cache entry by request hash.
		$result = self::$storage->get_cache( self::$request_hash );

		// No cache found.
		if ( ! $result ) {
			return;
		}

		// Unpack the result.
		list( $cache, $flags, $locked ) = $result;

		// Something is in the cache.
		if ( is_array( $cache ) && ! empty( $cache ) ) {
			$serve_cache = true;

			if ( self::$debug ) {
				// RFC 1123 date format.
				self::set_header( 'Time', gmdate( 'D, d M Y H:i:s \G\M\T', $cache['updated'] ) );
				self::set_header( 'Flags', implode( ' ', $flags ) );
			}

			// This entry is very old, delete it.
			if ( $cache['updated'] + self::$max_ttl < time() ) {
				self::get_storage()->delete_cache( self::$request_hash );
				$serve_cache = false;
			}

			// Is the cache expired?
			$expired = $cache['updated'] + self::$ttl < time();

			// Cache is outdated or set to expire.
			if ( $expired && $serve_cache ) {
				// If it's not locked, lock it for regeneration.
				if ( ! $locked ) {
					if ( self::$storage->lock( self::$request_hash ) ) {
						if ( self::can_fcgi_regenerate() ) {
							// Serve a stale copy & regenerate the cache in the background.
							$serve_cache = true;
							self::$fcgi_regenerate = true;
						} else {
							$serve_cache = false;
						}
					}
				}
			}

			// Uncompressed cache if gzipped.
			if ( $serve_cache && $cache['gzip'] ) {
				if ( self::$gzip ) {
					if ( self::$debug ) {
						self::set_header( 'Gzip', 'true' );
					}

					$cache['output'] = gzuncompress( $cache['output'] );
				} else {
					$serve_cache = false;
				}
			}

			// Output the cache if we can.
			if ( $serve_cache ) {
				// Set the status header.
				self::set_header( 'Status', self::$fcgi_regenerate ? 'expired' : 'hit' );

				if ( self::$debug ) {
					$time_left = self::$ttl - ( time() - $cache['updated'] );
					self::set_header(
						'Expires',
						sprintf(
							'%dd %02dh %02dm %02ds',
							intdiv( $time_left, 86400 ),
							intdiv( $time_left % 86400, 3600 ),
							intdiv( $time_left % 3600, 60 ),
							$time_left % 60
						)
					);
				}

				// Output cached status code.
				if ( ! empty( $cache['status'] ) ) {
					http_response_code( $cache['status'] );
				}

				// Output cached headers.
				if ( is_array( $cache['headers'] ) && ! empty( $cache['headers'] ) ) {
					foreach ( $cache['headers'] as $header ) {
						header( $header );
					}
				}

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- We need to output the cache.
				echo $cache['output'];

				// If we can regenerate in the background, do it.
				if ( self::$fcgi_regenerate ) {
					fastcgi_finish_request();
				} else {
					exit;
				}
			}
		}
	}

	/**
	 * Start the output buffer.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   void
	 */
	private static function start_buffer() {
		ob_start( array( __CLASS__, 'output_buffer' ) );
	}

	/**
	 * Output buffer callback.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param    string $output The output buffer.
	 * @return   string The output buffer.
	 */
	private static function output_buffer( string $output ): ?string {
		// Let's start optimistically.
		$cache = true;

		// Prepare the data to store.
		$data = array(
			'output' => $output,
			'headers' => array(),
			'status' => http_response_code(),
			'gzip' => self::$gzip && function_exists( 'gzcompress' ),
			'debug' => self::$debug ? self::$debug_data : null,
			'updated' => time(),
		);

		// Repsonse: If a cookie is being set that is NOT in our ignore list, disable caching for this page.
		foreach ( headers_list() as $header ) {
			list($key, $value) = explode( ':', $header, 2 );
			$key = strtolower( $key );
			$value = trim( $value );

			// Check for cookies.
			if ( 'set-cookie' == $key ) {
				$cookie = explode( ';', $value, 2 );
				$cookie = trim( $cookie[0] );
				$cookie = wp_parse_args( $cookie );

				// If there is a cookie that is not in the ignore list, disable caching.
				foreach ( $cookie as $cookie_key => $cookie_value ) {
					$cookie_key = strtolower( $cookie_key );
					$is_ignored = false;

					foreach ( self::$ignore_cookies as $pattern ) {
						if ( strpos( $cookie_key, $pattern ) !== false ) {
							$is_ignored = true;
							break;
						}
					}

					if ( ! $is_ignored ) {
						$cache = false;
						break 2;
					}
				}

				// Ignore our own headers, add all the others.
			} elseif ( strpos( $key, 'x-millicache' ) === false ) {
				$data['headers'][] = $header;
			}
		}

		// Compress the output.
		if ( $data['gzip'] ) {
			$data['output'] = gzcompress( $data['output'] );
		}

		// Maybe cache the output.
		if ( $cache || self::$fcgi_regenerate ) {
			$flags = array_unique( array_merge( self::$flags, array( 'url:' . self::get_url_hash() ) ) );
			self::$storage->perform_cache( self::$request_hash, $data, $flags, $cache );
		}

		// Return output, but not for the background task.
		return self::$fcgi_regenerate ? null : $output;
	}

	/**
	 * Determine if cache logics should start and run MilliCache Engine & Caching.
	 * This runs very early, before WordPress is fully loaded.
	 * Only use server variables and constants that are guaranteed to be available.
	 *
	 * @return   bool   If we start MilliCache Engine & Caching.
	 * @since    1.0.0
	 * @access   private
	 */
	private static function should_start(): bool {
		// Check for a custom callback to determine if MilliCache should be skipped.
		if ( ! empty( self::$should_cache_callback ) && is_callable( self::$should_cache_callback ) ) {
			$callback_result = call_user_func( self::$should_cache_callback );
			if ( is_bool( $callback_result ) && ! $callback_result ) {
				self::set_header( 'Status', 'bypass' );
				return false;
			}
		}

		// Skip MilliCache if specific cookies are present.
		foreach ( $_COOKIE as $name => $value ) {
			foreach ( self::$nocache_cookies as $part ) {
				if ( strpos( strtolower( $name ), $part ) === 0 ) {
					self::set_header( 'Status', 'bypass' );
					return false;
				}
			}
		}

		// Skip running MilliCache if any of the following conditions are met.
		$skip_conditions = array(
			defined( 'WP_CACHE' ) && ! WP_CACHE, // Skip caching if deactivated via constant.
			defined( 'REST_REQUEST' ) && REST_REQUEST, // Skip caching for Rest API requests.
			defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST, // Skip caching for XML-RPC requests.
			php_sapi_name() === 'cli' || ( defined( 'WP_CLI' ) && WP_CLI ), // Skip caching for CLI requests.
			http_response_code() >= 500, // Don't cache 5xx errors.
			strtolower( self::get_server_var( 'REQUEST_METHOD' ) ) === 'post', // Skip caching for POST requests.
			preg_match( '/\.(ico|txt|xml|xsl)$/', self::get_server_var( 'REQUEST_URI' ) ), // Skip specific file types.
			self::$ttl < 1, // Skip caching if TTL (Time To Live) is not set.
		);

		// If any skip condition is true, return false early.
		foreach ( $skip_conditions as $condition ) {
			if ( $condition ) {
				self::set_header( 'Status', 'bypass' );
				return false;
			}
		}

		// MilliCache should start.
		return true;
	}

	/**
	 * Determine if we should cache this request.
	 * This runs after WordPress is fully loaded (during template_redirect).
	 * The full WordPress context is available here.
	 *
	 * @return   bool   If we should cache this request.
	 * @since    1.0.0
	 * @access   private
	 */
	private static function should_cache(): bool {
		$wp_skip_conditions = array_merge(
			array(
				defined( 'DOING_CRON' ) && DOING_CRON,
				defined( 'DOING_AJAX' ) && DOING_AJAX,
				defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE,
			),
			apply_filters( 'millicache_skip_caching_conditions', array() ),
		);

		foreach ( $wp_skip_conditions as $condition ) {
			if ( $condition ) {
				self::set_header( 'Status', 'bypass' );
				return false;
			}
		}

		return true;
	}

	/**
	 * Take a request uri and remove ignored request keys.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $request_uri The request uri.
	 * @return string The cleaned request uri.
	 */
	private static function parse_request_uri( string $request_uri ): string {
		// Fix for requests with no host.
		$parsed = parse_url( 'http://null' . $request_uri );

		// Set query vars.
		$query = $parsed['query'] ?? '';

		// Set the request path.
		$request_uri = $parsed['path'] ?? '';

		// Remove ignored query vars.
		$query = self::remove_query_args( $query, self::$ignore_request_keys );

		// Return the cleaned request uri.
		return $query ? $request_uri . '?' . $query : $request_uri;
	}

	/**
	 * Remove query arguments from a query string.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string        $query_string The input query string, such as foo=bar&baz=qux.
	 * @param array<string> $args An array of keys to remove.
	 * @return string The resulting query string.
	 */
	private static function remove_query_args( string $query_string, array $args ): string {
		// Split the query string into an array.
		$query = explode( '&', $query_string );

		// Remove the query arguments.
		$query = array_filter(
			$query,
			function ( $value ) use ( $args ) {
				return ! preg_match( '#^(' . implode( '|', $args ) . ')(?:=|$)#i', $value );
			}
		);

		// Sort the query arguments to avoid cache duplication.
		sort( $query );

		// Return the resulting query string.
		return implode( '&', $query );
	}

	/**
	 * Parse cookies and remove ignored cookies.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array<string> $cookies The input cookies.
	 * @return array<string> The resulting cookies.
	 */
	private static function parse_cookies( array $cookies ): array {
		return array_filter(
			$cookies,
			function ( $key ) {
				$key = strtolower( $key );

				// Starts with any pattern in the ignore list.
				foreach ( self::$ignore_cookies as $pattern ) {
					if ( strpos( $key, $pattern ) === 0 ) {
						return false;
					}
				}

				// Starts with '_'
				if ( substr( $key, 0, 1 ) === '_' ) {
					return false;
				}

				return true;
			},
			ARRAY_FILTER_USE_KEY
		);
	}

	/**
	 * Clean up the request.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function clean_request() {
		// Unset the ETag and Last-Modified headers.
		unset( $_SERVER['HTTP_IF_NONE_MATCH'], $_SERVER['HTTP_IF_MODIFIED_SINCE'] );

		// Remove ignored request keys from the query string.
		if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
			$_SERVER['QUERY_STRING'] = self::remove_query_args(
				(string) filter_var( self::get_server_var( 'QUERY_STRING' ), FILTER_SANITIZE_URL ),
				self::$ignore_request_keys
			);
		}

		// Remove ignored request keys from the request uri.
		$request_uri = self::get_server_var( 'REQUEST_URI' );
		if ( $request_uri && strpos( $request_uri, '?' ) !== false ) {
			list($path, $query) = explode( '?', $request_uri, 2 );
			$query = self::remove_query_args( $query, self::$ignore_request_keys );
			$_SERVER['REQUEST_URI'] = $path . ( ! empty( $query ) ? '?' . $query : '' );
		}

		// Remove ignored request keys from the super globals.
		foreach ( self::$ignore_request_keys as $key ) {
			unset( $_GET[ $key ], $_REQUEST[ $key ] );
		}
	}

	/**
	 * Get a md5 URL hash for domain.com/path?query.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string|null $url The URL to hash.
	 * @return string The URL hash.
	 */
	public static function get_url_hash( string $url = null ): string {
		if ( ! $url ) {
			$url = self::get_server_var( 'HTTP_HOST' ) . self::parse_request_uri( self::get_server_var( 'REQUEST_URI' ) );
		} else {
			$parsed = parse_url( $url );
			$url = ( $parsed['host'] ?? '' ) . self::parse_request_uri( $parsed['path'] ?? '' . ( $parsed['query'] ?? '' ) );
		}

		return md5( $url );
	}


	/**
	 * Clears items from the cache during shutdown.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	public static function clear_cache_on_shutdown() {
		$sets = array(
			'mll:expired-flags' => self::$flags_expire,
			'mll:deleted-flags' => self::$flags_delete,
		);

		self::$storage->clear_cache_by_flags( $sets, self::$ttl );
	}

	/**
	 * Clear cache by given Targets.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string|array<string> $targets The targets (Flags, Post-IDs or URLs) to clear the cache for.
	 * @param bool                 $expire Expire cache if set to true, or delete by default.
	 * @return void
	 */
	public static function clear_cache_by_targets( $targets, bool $expire = false ): void {
		// Convert to array.
		$targets = is_string( $targets ) ? array( $targets ) : $targets;

		// Current site only?
		$current_site_only = self::is_multisite() && ! is_network_admin();

		// Clear the full site cache.
		if ( empty( $targets ) ) {
			self::clear_cache_by_site_ids();
			return;
		}

		foreach ( $targets as $target ) {
			if ( filter_var( $target, FILTER_VALIDATE_URL ) ) {
				// Clear by URL.
				if ( str_starts_with( $target, get_home_url() ) ) {
					self::clear_cache_by_urls( $target, $expire );
				}
			} elseif ( is_numeric( $target ) ) {
				// Clear by Post ID.
				self::clear_cache_by_post_ids( (int) $target );
			} else {
				// Clear by Flag. Limit to the current site if not network admin.
				self::clear_cache_by_flags( $current_site_only ? self::get_flag_prefix() . $target : $target );
			}
		}
	}

	/**
	 * Clear cache by given URLs.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string|array<string> $urls A string or array of URLs to flush.
	 * @param bool                 $expire Expire cache if set to true, or delete by default.
	 */
	public static function clear_cache_by_urls( $urls, bool $expire = false ): void {
		// Convert to array.
		$urls = is_string( $urls ) ? array( $urls ) : $urls;

		// Add flags.
		$flags = array_map(
			function ( $url ) {
				return 'url:' . self::get_url_hash( $url );
			},
			$urls
		);

		// Add flags to expire or delete a collection.
		$expire ? array_push( self::$flags_expire, ...$flags ) : array_push( self::$flags_delete, ...$flags );
	}

	/**
	 * Expire caches by post id.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int|array<int> $post_ids The post-IDs to expire.
	 * @param bool           $expire Expire cache if set to true, or delete by default.
	 */
	public static function clear_cache_by_post_ids( $post_ids, bool $expire = false ): void {
		// Convert to array.
		$post_ids = ! is_array( $post_ids ) ? array( $post_ids ) : $post_ids;

		// Add flags to expire or delete the collection.
		self::clear_cache_by_flags(
			array_merge(
				array_map(
					function ( $post_id ) {
						return self::get_flag_prefix() . "post:$post_id";
					},
					$post_ids
				),
				array( self::get_flag_prefix() . 'feed' )
			),
			$expire
		);

		/**
		 * Clear cache by post-ids action.
		 *
		 * @since 1.0.0
		 *
		 * @param array $post_ids The post-IDs to expire.
		 * @param bool  $expire Expire cache if set to true, or delete by default.
		 */
		do_action( 'millicache_cleared_by_post_ids', $post_ids, $expire );
	}

	/**
	 * Clears cache by given flags.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string|array<string> $flags A string or array of flags to expire.
	 * @param bool                 $expire Expire cache if set to true, or delete by default.
	 */
	public static function clear_cache_by_flags( $flags, bool $expire = false ): void {
		// Convert to array.
		$flags = is_string( $flags ) ? array( $flags ) : $flags;

		// Add flags to expire or to delete the collection.
		$expire ? array_push( self::$flags_expire, ...$flags ) : array_push( self::$flags_delete, ...$flags );

		/**
		 * Clear cache by flags action.
		 *
		 * @since 1.0.0
		 * @param array $flags The flags to expire.
		 * @param bool  $expire Expire cache if set to true, or delete by default.
		 */
		do_action( 'millicache_cleared_by_flags', $flags, $expire );
	}

	/**
	 * Clear the full cache of a given website.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int|array<int> $site_ids The site IDs to clear.
	 * @param int|null       $network_id The network ID.
	 * @param bool           $expire Expire cache if set to true, or delete by default.
	 * @return void
	 */
	public static function clear_cache_by_site_ids( $site_ids = null, int $network_id = null, bool $expire = false ): void {
		// Convert to array.
		$site_ids = ! is_array( $site_ids ) ? array( $site_ids ) : $site_ids;

		// Add flags to expire or delete a collection.
		self::clear_cache_by_flags(
			array_map(
				function ( $site_id ) use ( $network_id ) {
					return self::get_flag_prefix( $site_id, $network_id ) . '*';
				},
				$site_ids
			),
			$expire
		);

		/**
		 * Clear cache by site ids action.
		 *
		 * @since 1.0.0
		 *
		 * @param array     $site_ids The site IDs to expire.
		 * @param int|null  $network_id The network ID.
		 * @param bool      $expire Expire cache if set to true, or delete by default.
		 */
		do_action( 'millicache_cleared_by_site_ids', $site_ids, $network_id, $expire );
	}

	/**
	 * Clear the full cache of each site in a given network.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int|null $network_id The network ID.
	 * @param bool     $expire Expire cache.
	 * @return void
	 */
	public static function clear_cache_by_network_id( int $network_id = null, bool $expire = false ): void {
		$site_ids = self::get_site_ids( $network_id ?? get_current_network_id() );

		foreach ( $site_ids as $site_id ) {
			self::clear_cache_by_site_ids( $site_id, $network_id, $expire );
		}

		// Clear cache action.
		do_action( 'millicache_cleared_by_network_id', $network_id, $expire );
	}

	/**
	 * Clear cache of each site in each network
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param bool $expire Expire cache if set to true, or delete by default.
	 * @return void
	 */
	public static function clear_cache( bool $expire = false ): void {
		foreach ( self::get_network_ids() as $network_id ) {
			self::clear_cache_by_network_id( $network_id, $expire );
		}

		// Clear cache action.
		do_action( 'millicache_cleared', $expire );
	}

	/**
	 * If the site is a Multisite network
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool If the site is a Multisite network.
	 */
	public static function is_multisite(): bool {
		return is_multisite();
	}

	/**
	 * Get all available site ids of a given network
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $network_id The network ID.
	 * @return array<int> The site IDs.
	 */
	public static function get_site_ids( int $network_id = 1 ): array {
		if ( self::is_multisite() && function_exists( 'get_sites' ) ) {
			return get_sites(
				array(
					'fields' => 'ids',
					'number' => 0,
					'network_id' => $network_id,
				)
			);
		}

		return array( 1 );
	}

	/**
	 * Get all available network ids
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array<int>
	 */
	public static function get_network_ids(): array {
		if ( self::is_multisite() && function_exists( 'get_networks' ) ) {
			return (array) get_networks( array( 'fields' => 'ids' ) );
		}

		return array( 1 );
	}

	/**
	 * Get the flag prefix with network and site namespace.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int|string|null $site_id The site ID.
	 * @param int|string|null $network_id The network ID.
	 *
	 * @return string The flag prefix.
	 */
	public static function get_flag_prefix( $site_id = null, $network_id = null ): string {
		$prefix = '';

		if ( self::is_multisite() ) {
			$prefix = ( is_int( $site_id ) || is_string( $site_id ) ? $site_id : get_current_blog_id() ) . ':';

			if ( count( self::get_network_ids() ) > 1 ) {
				$prefix = ( is_int( $network_id ) || is_string( $network_id ) ? $network_id : get_current_network_id() ) . ':' . $prefix;
			}
		}

		return $prefix;
	}

	/**
	 * Get the flag key.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string          $flag The flag name.
	 * @param int|string|null $site_id The site ID.
	 * @param int|string|null $network_id The network ID.
	 *
	 * @return string The flag key.
	 */
	public static function get_flag_key( string $flag, $site_id = null, $network_id = null ): string {
		return self::get_flag_prefix( $site_id, $network_id ) . $flag;
	}

	/**
	 * Add a flag to this request.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $flag Keep these short and unique, don't overuse.
	 */
	public static function add_flag( string $flag ): void {
		self::$flags[] = self::get_flag_prefix() . $flag;
	}

	/**
	 * Set HTTP header.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $key The header key.
	 * @param string $value The header value.
	 */
	private static function set_header( string $key, string $value ): void {
		if ( ! headers_sent() ) {
			header( "X-MilliCache-$key: $value" );
		}
	}

	/**
	 * Whether we can regenerate the request in the background.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return bool
	 */
	private static function can_fcgi_regenerate(): bool {
		return function_exists( 'fastcgi_finish_request' );
	}

	/**
	 * Get the value of a server variable.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $key The server variable key.
	 * @return string The server variable value.
	 */
	public static function get_server_var( string $key ): string {
		if ( isset( $_SERVER[ $key ] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- We are sanitizing & un-slashing here with PHP native functions.
			return htmlspecialchars( stripslashes( $_SERVER[ $key ] ), ENT_QUOTES, 'UTF-8' );
		}
		return '';
	}

	/**
	 * Get meaningful Cache config and info.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array<mixed> The Cache status.
	 */
	public static function get_status(): array {
		$cache = array(
			'ttl' => self::$ttl,
			'max_ttl' => self::$max_ttl,
			'gzip' => self::$gzip,
			'debug' => self::$debug,
			'ignore_cookies' => self::$ignore_cookies,
			'nocache_cookies' => self::$nocache_cookies,
			'ignore_request_keys' => self::$ignore_request_keys,
		);

		return array_merge( $cache, Admin::get_cache_size( '', true ) );
	}
}
