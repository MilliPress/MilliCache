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

use MilliRules\Rules;

/**
 * Class WordPress
 *
 * Registers default WordPress rules that execute after WordPress initialization.
 * These rules replace hard-coded caching checks from Engine.
 *
 * Order convention (mirrors the PHP phase in Bootstrap):
 * - Order 0: locked rules that cannot be overridden (e.g. doing-cron).
 * - Order 1: unlocked rules that sites may override via custom rules at order 10+.
 *
 * Registered rules:
 * - millicache:wp:search (order 1, unlocked)
 * - millicache:wp:logged-in (order 1, unlocked)
 * - millicache:wp:response:code (order 1, unlocked)
 * - millicache:wp:const:donotcachepage (order 1, unlocked)
 * - millicache:wp:const:doing-cron (order 0, locked)
 * - millicache:wp:const:doing-ajax (order 1, unlocked)
 *
 * Options example:
 * User can create a rule with order 10 that enables caching for specific AJAX endpoints,
 * overriding the default no-cache behavior because higher order executes last.
 *
 * @since       1.0.0
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millipress.com>
 */
final class WordPress {
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
		self::register_search_rule();
		self::register_cron_rule();
		self::register_ajax_rule();
	}

	/**
	 * Register rule to skip cache for search result pages.
	 *
	 * Bots could flood the cache by search requests.
	 * Unlocked at order 1 so sites can overwrite as required.
	 *
	 * @since 1.7.0
	 * @access private
	 *
	 * @return void
	 */
	private static function register_search_rule(): void {
		Rules::create( 'millicache:wp:search' )
			->on( self::HOOK, self::PRIORITY )
			->order( 1 )
			->when()
				->is_search()
			->then()
				->do_cache( false, 'MilliCache: Search results' )
			->register();
	}

	/**
	 * Register rule to skip cache for logged-in users.
	 *
	 * Checks if the user is logged in. Unlocked at order 1 so sites can
	 * override for specific roles (e.g. cache for subscribers).
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function register_logged_in_rule(): void {
		Rules::create( 'millicache:wp:logged-in' )
			->on( self::HOOK, self::PRIORITY )
			->order( 1 )
			->when()
				->is_user_logged_in()
			->then()
				->do_cache( false, 'MilliCache: Logged-in user' )
			->register();
	}

	/**
	 * Register rule to skip cache for non-200 response codes.
	 *
	 * Checks response code from context. Unlocked at order 1 so sites can
	 * override to cache specific non-200s (e.g. 404 under heavy load).
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function register_response_code_rule(): void {
		Rules::create( 'millicache:wp:response:code' )
			->on( self::HOOK, self::PRIORITY )
			->order( 1 )
			->when()
				->custom( 'millicache:check-response-code', fn() => 200 !== http_response_code() )
			->then()
				->do_cache( false, 'MilliCache: Non-200 response codes' )
			->register();
	}

	/**
	 * Register rule to skip cache if DONOTCACHEPAGE constant is true.
	 *
	 * Respects the WordPress DONOTCACHEPAGE convention. Unlocked at order 1
	 * so sites can override in case a plugin sets it too aggressively.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function register_donotcachepage_rule(): void {
		Rules::create( 'millicache:wp:const:donotcachepage' )
			->on( self::HOOK, self::PRIORITY )
			->order( 1 )
			->when()
				->constant( 'DONOTCACHEPAGE', true, 'IS' )
			->then()
				->do_cache( false, 'MilliCache: DONOTCACHEPAGE constant is true' )
			->register();
	}

	/**
	 * Register rule to skip cache for cron requests.
	 *
	 * Locked — cron requests are internal WP executions and should never
	 * be cached.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function register_cron_rule(): void {
		Rules::create( 'millicache:wp:const:doing-cron' )
			->lock()
			->on( self::HOOK, self::PRIORITY )
			->order( 0 )
			->when()
				->constant( 'DOING_CRON', true )
			->then()
				->do_cache( false, 'MilliCache: Cron requests' )->lock()
			->register();
	}

	/**
	 * Register rule to skip cache for AJAX requests.
	 *
	 * Unlocked at order 1 so sites can override to cache public AJAX
	 * endpoints (e.g. search suggest, public product data).
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function register_ajax_rule(): void {
		Rules::create( 'millicache:wp:const:doing-ajax' )
			->on( self::HOOK, self::PRIORITY )
			->order( 1 )
			->when()
				->constant( 'DOING_AJAX', true, 'IS' )
			->then()
				->do_cache( false, 'MilliCache: AJAX request' )
			->register();
	}
}
