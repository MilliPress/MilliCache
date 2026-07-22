<?php
/**
 * RequestFlags Rules
 *
 * Rules that add cache flags to the current request for later invalidation.
 * Replaces the imperative get_request_flags() method with declarative rules.
 *
 * @link        https://www.millipress.com
 * @since       1.0.0
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MilliRules\Context;
use MilliRules\Rules;

/**
 * Class RequestFlags
 *
 * Registers flag generation rules that execute on template_redirect.
 *
 * These are core rules that are always registered and required for normal operation.
 * Core flag rules are locked to prevent accidental removal of invalidation targets.
 * Users can add additional flags via the millicache_flags_for_request filter.
 *
 * Flags are used for bulk cache invalidation - when content changes,
 * all cached pages with matching flags are cleared.
 *
 * Flag Patterns:
 * - post:{id} - Single post/page
 * - home - Homepage
 * - archive:{post_type} - Post type archive
 * - archive:{taxonomy}:{term_id} - Taxonomy archive
 * - archive:author:{author_id} - Author archive
 * - archive:{year}:{month}:{day} - Date archive
 * - feed - RSS/Atom feeds
 *
 * @since       1.0.0
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millipress.com>
 */
final class RequestFlags {
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
	private const PRIORITY = 100;

	/**
	 * The order of the rules.
	 *
	 * @since 1.0.0
	 */
	private const ORDER = 5;

	/**
	 * Register flag generation rules.
	 *
	 * These rules execute after WordPress loads with full context.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public static function register(): void {
		self::register_singular_post_rule();
		self::register_home_rules();
		self::register_post_type_archive_rule();
		self::register_taxonomy_archive_rule();
		self::register_author_archive_rule();
		self::register_date_archive_rules();
		self::register_feed_rule();
		self::register_custom_flags_filter();
	}

	/**
	 * Register singular post/page flag rule.
	 *
	 * Adds flag: post:{id}
	 * When: Viewing a single post, page, or custom post-type
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function register_singular_post_rule(): void {
		Rules::create( 'millicache:flags:singular-post' )
			->lock()
			->on( self::HOOK, self::PRIORITY )
			->order( self::ORDER )
			->when()
				->is_singular()
			->then()
				->add_flag( 'post:{post.id}' )->lock()
			->register();
	}

	/**
	 * Register home page flag rules.
	 *
	 * Adds flags based on homepage configuration:
	 * - home + archive:post (when homepage shows blog)
	 * - home (when homepage is a static page)
	 * - archive:post (when homepage is blog but not front page)
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function register_home_rules(): void {
		// Homepage showing blog posts (both front page and blog page).
		Rules::create( 'millicache:flags:home-blog' )
			->lock()
			->on( self::HOOK, self::PRIORITY )
			->order( self::ORDER )
			->when()
				->is_front_page()
				->is_home()
			->then()
				->add_flag( 'home' )->lock()
				->add_flag( 'archive:post' )->lock()
			->register();

		// Static front page only.
		Rules::create( 'millicache:flags:front-page' )
			->lock()
			->on( self::HOOK, self::PRIORITY )
			->order( self::ORDER )
			->when()
				->is_front_page()
			->and()->when_none()
				->is_home()
			->then()
				->add_flag( 'home' )->lock()
			->register();

		// Blog page (not front page).
		Rules::create( 'millicache:flags:blog-page' )
			->lock()
			->on( self::HOOK, self::PRIORITY )
			->order( self::ORDER )
			->when()
				->is_home()
			->and()->when_none()
				->is_front_page()
			->then()
				->add_flag( 'archive:post' )->lock()
			->register();
	}

	/**
	 * Register post-type archive flag rule.
	 *
	 * Adds flag: archive:{post_type}
	 * When: Viewing a post-type archive (e.g., /products/)
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function register_post_type_archive_rule(): void {
		Rules::create( 'millicache:flags:post-type-archive' )
			->lock()
			->on( self::HOOK, self::PRIORITY )
			->order( self::ORDER )
			->when()
				->is_post_type_archive()
			->then()
				->add_flag( 'archive:{query.post_type}' )->lock()
			->register();
	}

	/**
	 * Register taxonomy archive flag rule.
	 *
	 * Adds flag: archive:{taxonomy}:{term_id}
	 * When: Viewing a category, tag, or custom taxonomy archive
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function register_taxonomy_archive_rule(): void {
		Rules::create( 'millicache:flags:taxonomy-archive' )
			->lock()
			->on( self::HOOK, self::PRIORITY )
			->order( self::ORDER )
			->when_any()
				->is_category()
				->is_tag()
				->is_tax()
			->then()
				->add_flag( 'archive:{term.taxonomy}:{term.id}' )->lock()
			->register();
	}

	/**
	 * Register author archive flag rule.
	 *
	 * Adds flag: archive:author:{author_id}
	 * When: Viewing an author archive
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function register_author_archive_rule(): void {
		Rules::create( 'millicache:flags:author-archive' )
			->lock()
			->on( self::HOOK, self::PRIORITY )
			->order( self::ORDER )
			->when()
				->is_author()
			->then()
				->add_flag( 'archive:author:{query.author}' )->lock()
			->register();
	}

	/**
	 * Register date archive flag rules.
	 *
	 * Adds flags based on date archive depth:
	 * - archive:{year} (yearly archive)
	 * - archive:{year}:{month} (monthly archive)
	 * - archive:{year}:{month}:{day} (daily archive)
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function register_date_archive_rules(): void {
		Rules::create( 'millicache:flags:date' )
			->lock()
			->on( self::HOOK, self::PRIORITY )
			->order( self::ORDER )
			->when()
				->is_date()
			->then()
				->custom(
					'millicache:action:add-date-flag',
					function ( Context $context ) {
						$date_parts = array();

						foreach ( array( 'year', 'monthnum', 'day' ) as $key ) {
							$part = $context->get( "query.$key", array() );
							if ( is_numeric( $part ) && $part > 0 ) {
								$date_parts[] = str_pad( (string) $part, 2, '0', STR_PAD_LEFT );
							}
						}

						if ( ! empty( $date_parts ) ) {
							millicache()->flags()->add( 'archive:' . implode( ':', $date_parts ) );
						}
					}
				)
			->register();
	}

	/**
	 * Register feed flag rule.
	 *
	 * Adds flag: feed
	 * When: Viewing an RSS/Atom feed
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function register_feed_rule(): void {
		Rules::create( 'millicache:flags:feed' )
			->lock()
			->on( self::HOOK, self::PRIORITY )
			->order( self::ORDER )
			->when()
				->is_feed()
			->then()
				->add_flag( 'feed' )->lock()
			->register();
	}

	/**
	 * Register custom flags filter rule.
	 *
	 * Applies the millicache_flags_for_request filter to allow
	 * users to add custom flags via filter hooks.
	 *
	 * This rule executes last (order 999) to ensure all core flags
	 * are added before custom ones.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function register_custom_flags_filter(): void {
		Rules::create( 'millicache:flags:apply-filter' )
			->on( self::HOOK, self::PRIORITY )
			->order( 999 ) // Execute after all core flag rules.
			->then()
				->custom(
					'millicache:action:apply-custom-flags-filter',
					function () {
						/**
						 * Filter to add additional cache flags for the current request.
						 *
						 * These flags are stored alongside the cache and determine when and how it can be targeted & invalidated.
						 * This hook runs with WordPress fully loaded, so you may use conditional logic based on user roles, templates, queries, etc.
						 * Note: Don't use this too excessively, as it will increase the cache size.
						 *
						 * @since 1.0.0
						 *
						 * @param array $custom_flags The custom flags.
						 */
						$custom_flags = (array) apply_filters( 'millicache_flags_for_request', array() );
						if ( ! empty( $custom_flags ) ) {
							foreach ( $custom_flags as $flag ) {
								if ( is_string( $flag ) && '' !== $flag ) {
									millicache()->flags()->add( $flag );
								}
							}
						}
					}
				)
			->register();
	}
}
