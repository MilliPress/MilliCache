<?php
/**
 * Bootstrap Rules
 *
 * Built-in PHP-phase rule registration executed during advanced-cache.php
 * boot, before WordPress loads.
 *
 * @link        https://www.millipress.com
 * @since       1.0.0
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Rules;

use MilliCache\Engine\Cache\Config;
use MilliRules\Context;
use MilliRules\Rules;

/**
 * Class Bootstrap
 *
 * @since       1.0.0
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millipress.com>
 */
final class Bootstrap {
	/**
	 * Register default bootstrap rules.
	 *
	 * Bootstrap rules execute before WordPress loads with limited context.
	 * They can use:
	 * - $_SERVER variables
	 * - $_COOKIE
	 * - $_GET parameters
	 * - Request headers
	 *
	 * Bootstrap rules CANNOT use:
	 * - WordPress functions
	 * - Post/user data
	 * - Database queries
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function register(): void {
		$config = millicache()->config();

		self::register_wp_cache_rule();
		self::register_xmlrpc_request_rule();
		self::register_file_request_rule();
		self::register_request_method_rule();
		self::register_cli_request_rule();
		self::register_rest_request_rule();
		self::register_ttl_check_rule( $config );
		self::register_nocache_cookies( $config );
		self::register_nocache_paths( $config );
	}

	/**
	 * Register WP_CACHE constant check rule.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private static function register_wp_cache_rule(): void {
		// Check WP_CACHE constant.
		Rules::create( 'millicache:const:wp-cache', 'php' )
			->lock()
			->order( 0 )
			->when()
				->constant( 'WP_CACHE', true, '!=' )
			->then()
				->do_cache( false, 'MilliCache: WP_CACHE not enabled' )->lock()
			->register();
	}

	/**
	 * Register XML-RPC request check rule.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private static function register_xmlrpc_request_rule(): void {
		// Check XML-RPC request.
		Rules::create( 'millicache:request:xmlrpc', 'php' )
			->lock()
			->order( 0 )
			->when()
				->constant( 'XMLRPC_REQUEST', true )
			->then()
				->do_cache( false, 'MilliCache: XML-RPC request' )->lock()
			->register();
	}

	/**
	 * Register file request (static assets) check rule.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private static function register_file_request_rule(): void {
		// Check file request (static assets).
		Rules::create( 'millicache:request:file', 'php' )
			->order( 1 )
			->when()
				->custom(
					'is-file-request',
					function ( Context $context ) {
						$uri = $context->get( 'request.uri', '' );

						if ( ! is_string( $uri ) || empty( $uri ) ) {
							return false;
						}

						return (bool) preg_match( '/\.[a-z0-9]+($|\?)/i', $uri );
					}
				)
			->then()
				->do_cache( false, 'MilliCache: File request' )
			->register();
	}

	/**
	 * Register request method check rule.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private static function register_request_method_rule(): void {
		// Check the request method (only GET/HEAD).
		Rules::create( 'millicache:request:check-method', 'php' )
			->lock()
			->order( 0 )
			->when_none()
				->request_method( 'GET' )
				->request_method( 'HEAD' )
			->then()
				->do_cache( false, 'MilliCache: Non-GET/HEAD request' )->lock()
			->register();
	}

	/**
	 * Register CLI request check rule.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private static function register_cli_request_rule(): void {
		// Check CLI request.
		Rules::create( 'millicache:request:cli', 'php' )
			->lock()
			->order( 0 )
			->when()
				->custom(
					'cli-request',
					function () {
						return php_sapi_name() === 'cli';
					}
				)
				->constant( 'WP_CLI', true )
			->then()
				->do_cache( false, 'MilliCache: CLI request' )->lock()
			->register();
	}

	/**
	 * Register REST API request check rule.
	 *
	 * Detects REST API requests by checking for 'wp-json' in the URL.
	 * This runs early (before WordPress loads) to avoid unnecessary processing.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private static function register_rest_request_rule(): void {
		Rules::create( 'millicache:request:rest', 'php' )
			->lock()
			->order( 0 )
			->when()
				->request_url( '*wp-json*' )
			->and()
			->when_any()
				->request_method( 'GET' )
				->request_method( 'HEAD' )
			->then()
				->do_cache( false, 'MilliCache: REST API request' )
			->register();
	}

	/**
	 * Register TTL check rule.
	 *
	 * @since 1.0.0
	 *
	 * @param Config $config Cache configuration.
	 * @return void
	 */
	private static function register_ttl_check_rule( Config $config ): void {
		// Check TTL is configured.
		Rules::create( 'millicache:config:ttl-not-set', 'php' )
			->lock()
			->order( 0 )
			->when()
				->custom(
					'ttl-not-set',
					function () use ( $config ) {
						return $config->ttl <= 0;
					}
				)
			->then()
				->do_cache( false, 'MilliCache: TTL not set' )->lock()
			->register();
	}

	/**
	 * Register rule to skip cache for no-cache cookies.
	 *
	 * Checks for WordPress login cookies and custom no-cache cookies
	 * from settings. Locked — user-configured exclusions are authoritative
	 * and cannot be overridden by custom rules.
	 *
	 * @since 1.0.0
	 *
	 * @param Config $config Plugin configuration.
	 * @return void
	 */
	private static function register_nocache_cookies( Config $config ): void {
		$nocache_cookies = $config->nocache_cookies;

		if ( empty( $nocache_cookies ) ) {
			return;
		}

		// Build rule using fluent API.
		$builder = Rules::create( 'millicache:config:nocache-cookies', 'php' )
			->lock()
			->order( 0 )
			->when_any(); // Any cookie match triggers.

		// Add a cookie condition for each pattern.
		foreach ( $nocache_cookies as $pattern ) {
			$builder->cookie( $pattern );
		}

		// Set action and register.
		$builder->then()
			->do_cache( false, 'MilliCache: Skip cache for no-cache cookies' )->lock()
			->register();
	}

	/**
	 * Register rule to skip cache for no-cache paths.
	 *
	 * Checks request URL against no-cache path patterns from settings.
	 * Locked — user-configured exclusions are authoritative and cannot be
	 * overridden by custom rules.
	 *
	 * @since 1.0.0
	 *
	 * @param Config $config Plugin configuration.
	 * @return void
	 */
	private static function register_nocache_paths( Config $config ): void {
		$nocache_paths = $config->nocache_paths;

		// If no paths to check, skip rule registration.
		if ( empty( $nocache_paths ) ) {
			return;
		}

		// Build rule using fluent API.
		$builder = Rules::create( 'millicache:config:nocache-paths', 'php' )
			->lock()
			->order( 0 )
			->when_any();

		// Add request URL condition for each pattern.
		foreach ( $nocache_paths as $pattern ) {
			$builder->request_url( $pattern );
		}

		// Set action and register.
		$builder->then()
			->do_cache( false, 'MilliCache: Skip cache for no-cache paths' )->lock()
			->register();
	}
}
