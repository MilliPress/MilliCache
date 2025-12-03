<?php
/**
 * Default WordPress Rules
 *
 * Rules that execute after WordPress loads, with full access to WordPress APIs.
 *
 * @link        https://www.millipress.com
 * @since       1.0.0
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Rules;

use MilliCache\Deps\MilliRules\Rules;

/**
 * Class WordPress
 *
 * Registers default WordPress rules that execute after WordPress initialization.
 * These rules replace hard-coded caching checks from Engine and use order 0
 * so user rules can override them.
 *
 * Registered rules:
 * - core-wp-no-cache-cron: Skip cache for cron requests
 * - core-wp-no-cache-ajax: Skip cache for AJAX requests
 * - core-wp-donotcachepage: Skip cache if DONOTCACHEPAGE constant is true
 *
 * Override example:
 * User can create a rule with order 10 that enables caching for specific AJAX endpoints,
 * overriding the default no-cache behavior because higher order executes last.
 *
 * @since       1.0.0
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millipress.com>
 */
class WordPress {
	/**
	 * The WordPress hook to attach the rules to.
	 *
	 * @since 1.0.0
	 */
	private const HOOK = 'template_redirect';

	/**
	 * The priority of the WordPress hook.
	 *
	 * @since 1.0.0
	 */
	private const PRIORITY = 20;

	/**
	 * The order of the rules.
	 *
	 * @since 1.0.0
	 */
	private const ORDER = 0;

	/**
	 * Register default WordPress rules.
	 *
	 * WordPress rules execute after WordPress loads with full context.
	 * They can use:
	 * - All WordPress functions
	 * - Post/user/taxonomy data
	 * - Database queries
	 * - All context data from ContextBuilder
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public static function register(): void {
		self::register_response_code_rule();
		self::register_donotcachepage_rule();
		self::register_logged_in_rule();
		self::register_cron_rule();
		self::register_ajax_rule();
	}

	/**
	 * Register rule to skip cache for logged-in users.
	 *
	 * Checks if the user is logged in. Uses order 0 so user rules can override.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function register_logged_in_rule(): void {
		Rules::create( 'core-wp-logged-in' )
			->on( self::HOOK, self::PRIORITY )
			->order( self::ORDER )
			->when()
			->is_user_logged_in()
			->then()
			->do_cache( false, 'Core: Skip cache for logged-in users' )
			->register();
	}

	/**
	 * Register rule to skip cache for non-200 response codes.
	 *
	 * Checks response code from context. Uses order 0 so user rules can override.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function register_response_code_rule(): void {
		Rules::create( 'core-wp-response-code' )
			->on( self::HOOK, self::PRIORITY )
			->order( self::ORDER )
			->when()
			->custom( 'core-wp-check-response-code', fn() => 200 !== http_response_code() )
			->then()
			->do_cache( false, 'Core: Skip cache for non-200 response codes' )
			->register();
	}

	/**
	 * Register rule to skip cache if DONOTCACHEPAGE constant is true.
	 *
	 * Checks if DONOTCACHEPAGE constant is set and true. Uses order 0 so user rules can override.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function register_donotcachepage_rule(): void {
		Rules::create( 'core-wp-donotcachepage' )
			->on( self::HOOK, self::PRIORITY )
			->order( self::ORDER )
			->when()
			->constant( 'DONOTCACHEPAGE', true, 'IS' )
			->then()
			->do_cache( false, 'Core: Skip cache if DONOTCACHEPAGE constant is true' )
			->register();
	}

	/**
	 * Register rule to skip cache for cron requests.
	 *
	 * Checks if DOING_CRON constant is true. Uses order 0 so user rules can override.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function register_cron_rule(): void {
		Rules::create( 'core-wp-no-cache-cron' )
			->on( self::HOOK, self::PRIORITY )
			->order( self::ORDER )
			->when()
				->constant( 'DOING_CRON', true )
			->then()
				->do_cache( false, 'Core: Skip cache for cron requests' )
			->register();
	}

	/**
	 * Register rule to skip cache for AJAX requests.
	 *
	 * Checks if DOING_AJAX constant is true. Uses order 0 so user rules can override.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function register_ajax_rule(): void {
		Rules::create( 'core-wp-no-cache-ajax' )
			->on( self::HOOK, self::PRIORITY )
			->order( self::ORDER )
			->when()
				->constant( 'DOING_AJAX', true, 'IS' )
			->then()
				->do_cache( false, 'Core: Skip cache for AJAX requests' )
			->register();
	}
}
