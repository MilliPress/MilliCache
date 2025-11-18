<?php
/**
 * Default WordPress Rules
 *
 * Rules that execute after WordPress loads, with full access to WordPress APIs.
 *
 * @package     MilliCache
 * @subpackage  Core/Rules
 * @author      Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Rules;

use MilliRules\Rules;

/**
 * Class WP
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
 * @since 1.0.0
 */
class WP {
	/**
	 * The WordPress hook to attach the rules to.
	 *
	 * @var string $hook
	 */
	private string $hook = 'template_redirect';

	/**
	 * The priority of the WordPress hook.
	 *
	 * @var int $priority
	 */
	private int $priority = 20;

	/**
	 * The order of the rules.
	 *
	 * @var int $order
	 */
	private int $order = 0;

	/**
	 * Register default WordPress rules.
	 *
	 * WP rules execute after WordPress loads with full context.
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
		$instance = new self();
		$instance->register_response_code_rule();
		$instance->register_donotcachepage_rule();
		$instance->register_user_rule();
		$instance->register_cron_rule();
		$instance->register_ajax_rule();
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
	private function register_user_rule() {
		Rules::create( 'core-wp-logged-in' )
			->on( $this->hook, $this->priority )
			->order( $this->order )
			->when()
			->is_user_logged_in()
			->then()
			->do_not_cache( 'Core: Skip cache for logged-in users' )
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
	private function register_response_code_rule() {
		Rules::create( 'core-wp-response-code' )
			->on( $this->hook, $this->priority )
			->order( $this->order )
			->when()
			->custom( 'core-wp-check-response-code', fn() => 200 !== http_response_code() )
			->then()
			->do_not_cache( 'Core: Skip cache for non-200 response codes' )
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
	private function register_donotcachepage_rule(): void {
		Rules::create( 'core-wp-donotcachepage' )
			->on( $this->hook, $this->priority )
			->order( $this->order )
			->when()
			->constant( 'DONOTCACHEPAGE', true, 'IS' )
			->then()
			->do_not_cache( 'Core: Skip cache if DONOTCACHEPAGE constant is true' )
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
	private function register_cron_rule(): void {
		Rules::create( 'core-wp-no-cache-cron' )
			->on( $this->hook, $this->priority )
			->order( $this->order )
			->when()
				->constant( 'DOING_CRON', true )
			->then()
				->do_not_cache( 'Core: Skip cache for cron requests' )
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
	private function register_ajax_rule(): void {
		Rules::create( 'core-wp-no-cache-ajax' )
			->on( $this->hook, $this->priority )
			->order( $this->order )
			->when()
				->constant( 'DOING_AJAX', true, 'IS' )
			->then()
				->do_not_cache( 'Core: Skip cache for AJAX requests' )
			->register();
	}
}
