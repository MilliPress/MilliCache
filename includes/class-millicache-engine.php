<?php
/**
 * The independent Cache-Engine to avoid an overhead.
 *
 * @link       https://www.milli.press
 * @since      1.0.0
 *
 * @package    Millicache
 * @subpackage Millicache/includes
 */

! defined( 'ABSPATH' ) && exit;

/**
 * Fired by advanced-cache.php
 *
 * This class defines all code necessary for caching.
 *
 * @since      1.0.0
 * @package    Millicache
 * @subpackage Millicache/includes
 * @author     Philipp Wellmer <hello@milli.press>
 */
final class Millicache_Engine {

	/**
	 * If the cache engine has been started.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var bool If the cache engine has been started.
	 */
	private static $started = false;

	/**
	 * The Cache Storage object.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var Millicache_Redis The Cache Storage object.
	 */
	private static $storage;

	/**
	 * TTL.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var int The time to live for the cache.
	 */
	private static $ttl = 900;

	/**
	 * Max TTL.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var int The maximum time to live for the cache.
	 */
	private static $max_ttl = 3600;

	/**
	 * Variables that make the request unique.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array Unique request variables.
	 */
	private static $unique = array();

	/**
	 * Cookies that avoid caching.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array Do not cache cookies.
	 */
	private static $nocache_cookies = array( 'comment_author' );

	/**
	 * Cookies that are ignored.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array Ignore cookies for cache hash.
	 */
	private static $ignore_cookies = array();

	/**
	 * Request keys that are ignored.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array Ignored request keys.
	 */
	private static $ignore_request_keys = array( '_millicache', '_wpnonce', 'utm_source', 'utm_medium', 'utm_term', 'utm_content', 'utm_campaign' );

	/**
	 * External callback to append cache conditions.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var string Callback to append cache conditions.
	 */
	private static $should_cache_callback = '';

	/**
	 * Debug mode.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var bool Debug mode.
	 */
	private static $debug = false;

	/**
	 * Gzip compression.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var bool Gzip compression.
	 */
	private static $gzip = true;

	/**
	 * Request hash.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var string The request hash.
	 */
	private static $request_hash = '';

	/**
	 * Debug data.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array|false Debug data.
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
	private static $fcgi_regenerate = false;

	/**
	 * Flag requests and expire/delete them efficiently.
	 */

	/**
	 * Request flags.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array Flags.
	 */
	private static $flags = array();

	/**
	 * Flags to expire.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array Expire flags.
	 */
	private static $flags_expire = array();

	/**
	 * Flags to delete.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array Delete flags.
	 */
	private static $flags_delete = array();

	/**
	 * Start the cache engine.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return   void
	 */
	public static function start() {
		self::config();
		self::get_storage();
		self::warmup();

		if ( self::should_cache() ) {
			self::run();
		}

		self::$started = true;
	}

	/**
	 * If not running start the cache engine.
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
		// Ignore test cookie by default.
		$test_cookie = defined( 'TEST_COOKIE' ) ? TEST_COOKIE : 'wordpress_test_cookie';
		$logged_in_cookie = defined( 'LOGGED_IN_COOKIE' ) ? LOGGED_IN_COOKIE : 'wordpress_logged_in';

		// Ignore test cookie by default.
		self::$ignore_cookies[] = $test_cookie;

		// No caching with auth cookies by default.
		self::$nocache_cookies[] = $logged_in_cookie;

		// Load the configuration from wp-config.php.
		foreach ( array(
			'ttl',
			'unique',
			'nocache_cookies',
			'ignore_cookies',
			'ignore_request_keys',
			'whitelist_cookies',
			'should_cache_callback',
			'debug',
			'gzip',
		) as $key ) {
			$constant = strtoupper( 'MC_' . $key );
			if ( defined( $constant ) ) {
				self::$$key = constant( $constant );
			}
		}

		// Always set test cookie for wp-login.php POST requests.
		if ( strpos( self::get_server_var( 'REQUEST_URI' ), '/wp-login.php' ) === 0 && strtoupper( self::get_server_var( 'REQUEST_METHOD' ) ) == 'POST' ) {
			$_COOKIE[ $test_cookie ] = 'WP Cookie check';
		}
	}

	/**
	 * Returns the MilliCache Storage instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   Millicache_Redis The MilliCache Storage instance.
	 */
	private static function get_storage() {
		if ( ! ( self::$storage instanceof Millicache_Redis ) ) {

			/**
			 * The MilliPress Redis class.
			 */
			require_once __DIR__ . '/class-millicache-redis.php';

			self::$storage = new Millicache_Redis();
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
		// Register shutdown function to expire/delete cache flags.
		register_shutdown_function( array( __CLASS__, 'clear_cache_on_shutdown' ) );

		// Always set initial header.
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

		// Start the output buffer & init output.
		self::start_buffer();
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
		list( $cache, $flags, $locked ) = self::$storage->get_cache( self::$request_hash );

		// Something is in cache.
		if ( is_array( $cache ) && ! empty( $cache ) ) {
			$serve_cache = true;

			if ( self::$debug ) {
				self::set_header( 'Time', $cache['updated'] );
				self::set_header( 'Flags', implode( ' ', $flags ) );
			}

			// This entry is very old, delete it.
			if ( $cache['updated'] + self::$max_ttl < time() ) {
				self::get_storage()->delete_cache( self::$request_hash );
				$serve_cache = false;
			}

			$expired = $cache['updated'] + self::$ttl < time();

			// Cache is outdated or set to expire.
			if ( $expired && $serve_cache ) {
				// If it's not locked, lock it for regeneration.
				if ( ! $locked ) {
					if ( self::$storage->lock( self::$request_hash ) ) {
						if ( self::can_fcgi_regenerate() ) {
							// Serve a stale copy & regenerate the cache in background.
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
				if ( self::$gzip && function_exists( 'gzuncompress' ) ) {
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
					self::set_header( 'Expires', (string) round( self::$ttl - ( time() - $cache['updated'] ) ) );
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

				// todo: Better escape this with native PHP functions?
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
	 * @since    1.0.0
	 * @access   private
	 *
	 * @param    string $output The output buffer.
	 * @return   string The output buffer.
	 */
	private static function output_buffer( $output ) {
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

		// Don't cache 5xx errors.
		if ( $data['status'] >= 500 ) {
			$cache = false;
		}

		// Don't cache if the page is set to not cache.
		if ( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE ) {
			$cache = false;
		}

		// Pass ignored cookies, but not cache non-ignored cookies.
		foreach ( headers_list() as $header ) {
			list($key, $value) = explode( ':', $header, 2 );
			$key = strtolower( $key );
			$value = trim( $value );

			// Check for cookies.
			if ( 'set-cookie' == $key ) {
				$cookie = explode( ';', $value, 2 );
				$cookie = trim( $cookie[0] );
				$cookie = wp_parse_args( $cookie );

				foreach ( $cookie as $cookie_key => $cookie_value ) {
					if ( ! in_array( strtolower( $cookie_key ), self::$ignore_cookies ) ) {
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

		// Return output, but not for background task.
		return self::$fcgi_regenerate ? null : $output;
	}

	/**
	 * Determine if we should cache this request.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   bool   If we should cache this request.
	 */
	private static function should_cache() {
		// Check for a custom callback to determine if caching should be skipped.
		if ( ! empty( self::$should_cache_callback ) && is_callable( self::$should_cache_callback ) ) {
			$callback_result = call_user_func( self::$should_cache_callback );
			if ( is_bool( $callback_result ) && ! $callback_result ) {
				self::set_header( 'Status', 'bypass' );
				return false;
			}
		}

		// Skip caching if specific cookies are present.
		foreach ( $_COOKIE as $key => $value ) {
			$key = strtolower( $key );

			foreach ( self::$nocache_cookies as $part ) {
				if ( strpos( $key, $part ) === 0 && ! in_array( $key, self::$ignore_cookies ) ) {
					self::set_header( 'Status', 'bypass' );
					return false;
				}
			}
		}

		// Skip caching if any of the following conditions are met.
		$skip_conditions = array(
			defined( 'WP_CACHE' ) && ! WP_CACHE, // Skip caching if deactivated via constant.
			defined( 'REST_REQUEST' ) && REST_REQUEST, // Skip caching for Rest API requests.
			defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST, // Skip caching for XML-RPC requests.
			php_sapi_name() === 'cli' || ( defined( 'WP_CLI' ) && WP_CLI ), // Skip caching for CLI requests.
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

		// Caching should proceed.
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
	private static function parse_request_uri( $request_uri ) {
		// Fix for requests with no host.
		$parsed = parse_url( 'http://null' . $request_uri );

		// Set query vars.
		$query = isset( $parsed['query'] ) ? $parsed['query'] : '';

		// Set request path.
		$request_uri = isset( $parsed['path'] ) ? $parsed['path'] : '';

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
	 * @param string $query_string The input query string, such as foo=bar&baz=qux.
	 * @param array  $args An array of keys to remove.
	 * @return string The resulting query string.
	 */
	private static function remove_query_args( $query_string, $args ) {
		// Split the query string into an array.
		$query = explode( '&', $query_string );

		// Remove the query arguments.
		$query = array_filter(
			$query,
			function ( $value ) use ( $args ) {
				return ! preg_match( '#^(?:' . implode( '|', array_map( 'preg_quote', $args ) ) . ')(?:=|$)#i', $value );
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
	 * @param array $cookies The input cookies.
	 * @return array The resulting cookies.
	 */
	private static function parse_cookies( $cookies ) {
		return array_filter(
			$cookies,
			function ( $key ) {
				return ! in_array( strtolower( $key ), self::$ignore_cookies ) && '_' !== $key[0];
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
			$_SERVER['QUERY_STRING'] = self::remove_query_args( filter_var( self::get_server_var( 'QUERY_STRING' ), FILTER_SANITIZE_URL ), self::$ignore_request_keys );
		}

		// Remove ignored request keys from the request uri.
		$request_uri = self::get_server_var( 'REQUEST_URI' );
		if ( $request_uri && strpos( $request_uri, '?' ) !== false ) {
			list($path, $query) = explode( '?', $request_uri, 2 );
			$query = self::remove_query_args( $query, self::$ignore_request_keys );
			$_SERVER['REQUEST_URI'] = $path . ( ! empty( $query ) ? '?' . $query : '' );
		}

		// Remove ignored request keys from the superglobals.
		foreach ( self::$ignore_request_keys as $key ) {
			unset( $_GET[ $key ], $_REQUEST[ $key ] );
		}

		// Remove ignored cookies.
		if ( ! empty( self::$ignore_cookies ) && ! ( defined( 'WP_ADMIN' ) && WP_ADMIN && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) ) {
			$_COOKIE = array_filter(
				$_COOKIE,
				function ( $key ) {
					return array_reduce(
						self::$ignore_cookies,
						function ( $carry, $part ) use ( $key ) {
							return $carry || strpos( $key, $part ) === 0;
						},
						false
					);
				},
				ARRAY_FILTER_USE_KEY
			);
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
	public static function get_url_hash( $url = null ) {
		if ( ! $url ) {
			$url = self::get_server_var( 'HTTP_HOST' ) . self::parse_request_uri( self::get_server_var( 'REQUEST_URI' ) );
		} else {
			$parsed = parse_url( $url );
			$url = $parsed['host'] . self::parse_request_uri( $parsed['path'] . ( isset( $parsed['query'] ) ? $parsed['query'] : '' ) );
		}

		return md5( $url );
	}


	/**
	 * Clears items from cache during shutdown.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	public static function clear_cache_on_shutdown() {
		$sets = array(
			'mll:expired-flags' => self::$flags_expire ?: array(),
			'mll:deleted-flags' => self::$flags_delete ?: array(),
		);

		self::$storage->clear_cache_by_flags( $sets, self::$ttl );
	}

	/**
	 * Clear cache by given URLs.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string|array $urls A string or array of URLs to flush.
	 * @param bool         $expire Expire cache if set to true, or delete by default.
	 */
	public static function clear_cache_by_urls( $urls, $expire = false ) {
		// Convert to array.
		$urls = is_string( $urls ) ? array( $urls ) : $urls;

		// Add flags.
		$flags = array_map(
			function ( $url ) {
				return 'url:' . self::get_url_hash( $url );
			},
			$urls
		);

		// Add flags to expire or delete collection.
		$expire ? array_push( self::$flags_expire, ...$flags ) : array_push( self::$flags_delete, ...$flags );
	}

	/**
	 * Expire caches by post id.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int|array $post_ids The post IDs to expire.
	 * @param bool      $expire Expire cache if set to true, or delete by default.
	 */
	public static function clear_cache_by_post_ids( $post_ids, $expire = false ) {
		// Convert to array.
		$post_ids = ! is_array( $post_ids ) ? array( $post_ids ) : $post_ids;

		// Add flags to expire or delete collection.
		self::clear_cache_by_flags(
			array_merge(
				array_map(
					function ( $post_id ) {
						return sprintf( 'post:%d:%d', get_current_blog_id(), $post_id );
					},
					$post_ids
				),
				array( sprintf( 'feed:%d', get_current_blog_id() ) )
			),
			$expire
		);

		/**
		 * Clear cache by post ids action.
		 *
		 * @since 1.0.0
		 *
		 * @param array $post_ids The post IDs to expire.
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
	 * @param string|array $flags A string or array of flags to expire.
	 * @param bool         $expire Expire cache if set to true, or delete by default.
	 */
	public static function clear_cache_by_flags( $flags, $expire = false ) {
		// Convert to array.
		$flags = is_string( $flags ) ? array( $flags ) : $flags;

		// Add flags to expire or delete collection.
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
	 * Clear full cache of a given website.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int|array $site_ids The site IDs to clear.
	 * @param int       $network_id The network ID.
	 * @param bool      $expire Expire cache if set to true, or delete by default.
	 * @return void
	 */
	public static function clear_cache_by_site_ids( $site_ids = null, $network_id = null, $expire = false ) {
		// Convert to array.
		$site_ids = ! is_array( $site_ids ) ? array( $site_ids ) : $site_ids;

		// Add flags to expire or delete collection.
		self::clear_cache_by_flags(
			array_map(
				function ( $site_id ) use ( $network_id ) {
					$network_id = $network_id ? $network_id : get_current_network_id();
					$site_id = $site_id ? $site_id : get_current_blog_id();
					return sprintf( 'site:%d:%d', $network_id, $site_id );
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
		 * @param array $site_ids The site IDs to expire.
		 * @param int   $network_id The network ID.
		 * @param bool  $expire Expire cache if set to true, or delete by default.
		 */
		do_action( 'millicache_cleared_by_site_ids', $site_ids, $network_id, $expire );
	}

	/**
	 * Clear full cache of each site in a given network.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int  $network_id The network ID.
	 * @param bool $expire Expire cache.
	 * @return void
	 */
	public static function clear_cache_by_network_id( $network_id = null, $expire = false ) {
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
	public static function clear_cache( $expire = false ) {
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
	public static function is_multisite() {
		return is_multisite();
	}

	/**
	 * Get all available site ids of a given network
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $network_id The network ID.
	 * @return array The site IDs.
	 */
	public static function get_site_ids( $network_id = 1 ) {
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
	 * @return array
	 */
	public static function get_network_ids() {
		if ( self::is_multisite() && function_exists( 'get_networks' ) ) {
			return get_networks( array( 'fields' => 'ids' ) );
		}

		return array( 1 );
	}

	/**
	 * Add a flag to this request.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $flag Keep these short and unique, don't overuse.
	 */
	public static function add_flag( $flag ) {
		self::$flags[] = $flag;
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
	private static function set_header( $key, $value ) {
		header( "X-MilliCache-$key: $value" );
	}

	/**
	 * Whether we can regenerate the request in the background.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return bool
	 */
	private static function can_fcgi_regenerate() {
		return function_exists( 'fastcgi_finish_request' );
	}

	/**
	 * Get the value of a server variable.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $key The server variable key.
	 * @return string|null The server variable value.
	 */
	private static function get_server_var( $key ) {
		if ( isset( $_SERVER[ $key ] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- We are sanitizing & unslashing here with PHP native functions.
			return htmlspecialchars( stripslashes( $_SERVER[ $key ] ), ENT_QUOTES, 'UTF-8' );
		}
		return null;
	}
}
