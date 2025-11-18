<?php
/**
 * Default PHP Rules
 *
 * Rules that execute before WordPress loads, using only server variables and PHP context.
 *
 * @package     MilliCache
 * @subpackage  Core/Rules
 * @author      Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Rules;

use MilliCache\Engine;
use MilliRules\Rules;

/**
 * Class PHP
 *
 * Registers default PHP rules that execute before WordPress initialization.
 * These rules use order 0 so user rules can override them.
 *
 * Override example:
 * User can create a rule with order 10 that sets TTL for specific paths,
 * overriding the default no-cache behavior because higher order executes last.
 *
 * @since 1.0.0
 */
class PHP {
	/**
	 * Register default PHP rules.
	 *
	 * PHP rules execute before WordPress loads with limited context.
	 * They can use:
	 * - $_SERVER variables
	 * - $_COOKIE
	 * - $_GET parameters
	 * - Request headers
	 *
	 * PHP rules CANNOT use:
	 * - WordPress functions
	 * - Post/user data
	 * - Database queries
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function register(): void {
		self::register_fundamental_checks();
		self::register_nocache_cookies();
		self::register_nocache_paths();
	}

	/**
	 * Register fundamental cache checks.
	 *
	 * Validates all base requirements for caching:
	 * - WP_CACHE constant enabled
	 * - Not a REST request
	 * - Not an XML-RPC request
	 * - Not a file request (static assets)
	 * - GET/HEAD request methods only
	 * - Not CLI request
	 * - Not WP-JSON request
	 * - TTL is configured
	 *
	 * Uses order -10 to run before other PHP rules.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private static function register_fundamental_checks(): void {
		// Check WP_CACHE constant.
		Rules::create( 'core-wp-cache', 'php' )
			->order( -10 )
			->when()
				->constant( 'WP_CACHE', true, '!=' )
			->then()
				->do_not_cache( 'Core: WP_CACHE not enabled' )
			->register();

		// Check REST request.
		Rules::create( 'core-rest-request', 'php' )
			->order( -10 )
			->when()
				->constant( 'REST_REQUEST', true )
			->then()
				->do_not_cache( 'Core: REST request' )
			->register();

		// Check XML-RPC request.
		Rules::create( 'core-xmlrpc-request', 'php' )
			->order( -10 )
			->when()
				->constant( 'XMLRPC_REQUEST', true )
			->then()
				->do_not_cache( 'Core: XML-RPC request' )
			->register();

		// Check file request (static assets).
		Rules::create( 'core-file-request', 'php' )
			->order( -10 )
			->when()
				->custom(
					'is-file-request',
					function ( $context ) {
						$uri = $context['request']['uri'] ?? '';

						if ( ! is_string( $uri ) || empty( $uri ) ) {
							return false;
						}

						return (bool) preg_match( '/\.[a-z0-9]+($|\?)/i', $uri );
					}
				)
			->then()
				->do_not_cache( 'Core: File request' )
			->register();

		// Check the request method (only GET/HEAD).
		Rules::create( 'core-request-method', 'php' )
			->order( -10 )
			->when_none()
				->request_method( 'GET' )
				->request_method( 'HEAD' )
			->then()
				->do_not_cache( 'Core: Non-GET/HEAD request' )
			->register();

		// Check CLI request.
		Rules::create( 'core-cli-request', 'php' )
			->order( -10 )
			->when()
				->custom(
					'cli-request',
					function () {
						return php_sapi_name() === 'cli';
					}
				)
				->constant( 'WP_CLI', true )
			->then()
				->do_not_cache( 'Core: CLI request' )
			->register();

		// Check WP-JSON request.
		Rules::create( 'core-wp-json-request', 'php' )
			->order( -10 )
			->when()
				->request_url( '*wp-json*' )
			->then()
				->do_not_cache( 'Core: WP-JSON request' )
			->register();

		// Check TTL is configured.
		Rules::create( 'core-ttl-not-set', 'php' )
			->order( -10 )
			->when()
			->custom(
				'ttl-not-set',
				function () {
					return Engine::$ttl <= 0;
				}
			)
			->then()
				->do_not_cache( 'Core: TTL not set' )
			->register();
	}

	/**
	 * Register rule to skip cache for no-cache cookies.
	 *
	 * Checks for WordPress login cookies and custom no-cache cookies
	 * from settings. Uses order 0 so user rules can override.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private static function register_nocache_cookies(): void {
		$nocache_cookies = Engine::$nocache_cookies;

		if ( empty( $nocache_cookies ) ) {
			return;
		}

		// Build rule using fluent API.
		$builder = Rules::create( 'core-nocache-cookies', 'php' )
			->order( 0 )
			->when_any(); // Any cookie match triggers.

		// Add a cookie condition for each pattern.
		foreach ( $nocache_cookies as $pattern ) {
			$builder->cookie( $pattern );
		}

		// Set action and register.
		$builder->then()
			->do_not_cache( 'Core: Skip cache for no-cache cookies' )
			->register();
	}

	/**
	 * Register rule to skip cache for no-cache paths.
	 *
	 * Checks request URL against no-cache path patterns from settings.
	 * Uses order 0 so user rules can override.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private static function register_nocache_paths(): void {
		$nocache_paths = Engine::$nocache_paths;

		// If no paths to check, skip rule registration.
		if ( empty( $nocache_paths ) ) {
			return;
		}

		// Build rule using fluent API.
		$builder = Rules::create( 'core-nocache-paths', 'php' )
			->order( 0 )
			->when_any();

		// Add request URL condition for each pattern.
		foreach ( $nocache_paths as $pattern ) {
			$builder->request_url( $pattern );
		}

		// Set action and register.
		$builder->then()
			->do_not_cache( 'Core: Skip cache for no-cache paths' )
			->register();
	}
}
