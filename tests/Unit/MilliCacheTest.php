<?php
/**
 * Tests for MilliCache class.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\MilliCache;
use MilliCache\Core\Loader;
use MilliCache\Engine;

// Mock WordPress core functions.
if ( ! class_exists( 'WP_Post' ) ) {
	class WP_Post {
		public $ID;
		public $post_type;
		public $post_status;
		public $post_author;
		public $post_date;

		public function __construct( $data = array() ) {
			foreach ( $data as $key => $value ) {
				$this->$key = $value;
			}
		}
	}
}

if ( ! function_exists( 'is_singular' ) ) {
	function is_singular() {
		global $test_is_singular;
		return $test_is_singular ?? false;
	}
}

if ( ! function_exists( 'get_queried_object_id' ) ) {
	function get_queried_object_id() {
		global $test_queried_object_id;
		return $test_queried_object_id ?? 0;
	}
}

if ( ! function_exists( 'is_front_page' ) ) {
	function is_front_page() {
		global $test_is_front_page;
		return $test_is_front_page ?? false;
	}
}

if ( ! function_exists( 'is_home' ) ) {
	function is_home() {
		global $test_is_home;
		return $test_is_home ?? false;
	}
}

if ( ! function_exists( 'is_archive' ) ) {
	function is_archive() {
		global $test_is_archive;
		return $test_is_archive ?? false;
	}
}

if ( ! function_exists( 'is_post_type_archive' ) ) {
	function is_post_type_archive() {
		global $test_is_post_type_archive;
		return $test_is_post_type_archive ?? false;
	}
}

if ( ! function_exists( 'get_query_var' ) ) {
	function get_query_var( $var, $default = '' ) {
		global $test_query_vars;
		return $test_query_vars[ $var ] ?? $default;
	}
}

if ( ! function_exists( 'is_category' ) ) {
	function is_category() {
		global $test_is_category;
		return $test_is_category ?? false;
	}
}

if ( ! function_exists( 'is_tag' ) ) {
	function is_tag() {
		global $test_is_tag;
		return $test_is_tag ?? false;
	}
}

if ( ! function_exists( 'is_tax' ) ) {
	function is_tax() {
		global $test_is_tax;
		return $test_is_tax ?? false;
	}
}

if ( ! function_exists( 'get_queried_object' ) ) {
	function get_queried_object() {
		global $test_queried_object;
		return $test_queried_object ?? null;
	}
}

if ( ! function_exists( 'is_author' ) ) {
	function is_author() {
		global $test_is_author;
		return $test_is_author ?? false;
	}
}

if ( ! function_exists( 'is_date' ) ) {
	function is_date() {
		global $test_is_date;
		return $test_is_date ?? false;
	}
}

if ( ! function_exists( 'is_feed' ) ) {
	function is_feed() {
		global $test_is_feed;
		return $test_is_feed ?? false;
	}
}

if ( ! function_exists( 'get_post' ) ) {
	function get_post( $post_id ) {
		global $test_posts;
		return $test_posts[ $post_id ] ?? null;
	}
}

if ( ! function_exists( 'get_object_taxonomies' ) ) {
	function get_object_taxonomies( $post_type ) {
		global $test_taxonomies;
		return $test_taxonomies[ $post_type ] ?? array();
	}
}

if ( ! function_exists( 'get_the_terms' ) ) {
	function get_the_terms( $post, $taxonomy ) {
		global $test_terms;
		$post_id = is_object( $post ) ? $post->ID : $post;
		return $test_terms[ $post_id ][ $taxonomy ] ?? false;
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ) {
		return $thing instanceof WP_Error;
	}
}

if ( ! function_exists( 'get_permalink' ) ) {
	function get_permalink( $post_id ) {
		return 'https://example.com/post-' . $post_id . '/';
	}
}

uses()->beforeEach( function () {
	// Reset all global test variables.
	global $test_is_singular, $test_queried_object_id, $test_is_front_page, $test_is_home;
	global $test_is_archive, $test_is_post_type_archive, $test_query_vars, $test_is_category;
	global $test_is_tag, $test_is_tax, $test_queried_object, $test_is_author, $test_is_date;
	global $test_is_feed, $test_filters, $test_posts, $test_taxonomies, $test_terms;

	$test_is_singular = false;
	$test_queried_object_id = 0;
	$test_is_front_page = false;
	$test_is_home = false;
	$test_is_archive = false;
	$test_is_post_type_archive = false;
	$test_query_vars = array();
	$test_is_category = false;
	$test_is_tag = false;
	$test_is_tax = false;
	$test_queried_object = null;
	$test_is_author = false;
	$test_is_date = false;
	$test_is_feed = false;
	$test_filters = array();
	$test_posts = array();
	$test_taxonomies = array();
	$test_terms = array();
} );

describe( 'MilliCache', function () {

	describe( 'constructor', function () {
		it( 'initializes with default version when MILLICACHE_VERSION not defined', function () {
			$millicache = new MilliCache();

			expect( $millicache->get_version() )->toBe( '1.0.0' );
			expect( $millicache->get_plugin_name() )->toBe( 'millicache' );
		} );

		it( 'uses MILLICACHE_VERSION constant when defined', function () {
			if ( ! defined( 'MILLICACHE_VERSION' ) ) {
				define( 'MILLICACHE_VERSION', '2.5.3' );
			}

			$millicache = new MilliCache();

			expect( $millicache->get_version() )->toBe( MILLICACHE_VERSION );
		} );

		it( 'initializes loader and engine', function () {
			$millicache = new MilliCache();

			expect( $millicache->get_loader() )->toBeInstanceOf( Loader::class );
			expect( $millicache->get_engine() )->toBeInstanceOf( Engine::class );
		} );
	} );

	describe( 'get_request_flags', function () {

		describe( 'singular posts', function () {
			it( 'returns post flag for singular post', function () {
				global $test_is_singular, $test_queried_object_id;
				$test_is_singular = true;
				$test_queried_object_id = 42;

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toContain( 'post:42' );
			} );

			it( 'returns empty array when singular but no post ID', function () {
				global $test_is_singular, $test_queried_object_id;
				$test_is_singular = true;
				$test_queried_object_id = 0;

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toBe( array() );
			} );
		} );

		describe( 'home and front page', function () {
			it( 'returns home flag for front page only', function () {
				global $test_is_front_page, $test_is_home;
				$test_is_front_page = true;
				$test_is_home = false;

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toContain( 'home' );
				expect( $flags )->not->toContain( 'archive:post' );
			} );

			it( 'returns archive:post flag for blog page only', function () {
				global $test_is_front_page, $test_is_home;
				$test_is_front_page = false;
				$test_is_home = true;

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toContain( 'archive:post' );
				expect( $flags )->not->toContain( 'home' );
			} );

			it( 'returns both home and archive:post when front page is blog', function () {
				global $test_is_front_page, $test_is_home;
				$test_is_front_page = true;
				$test_is_home = true;

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toContain( 'home' );
				expect( $flags )->toContain( 'archive:post' );
			} );
		} );

		describe( 'post type archives', function () {
			it( 'returns archive flag for single post type archive', function () {
				global $test_is_archive, $test_is_post_type_archive, $test_query_vars;
				$test_is_archive = true;
				$test_is_post_type_archive = true;
				$test_query_vars['post_type'] = 'product';

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toContain( 'archive:product' );
			} );

			it( 'returns archive flags for multiple post types', function () {
				global $test_is_archive, $test_is_post_type_archive, $test_query_vars;
				$test_is_archive = true;
				$test_is_post_type_archive = true;
				$test_query_vars['post_type'] = array( 'product', 'service' );

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toContain( 'archive:product' );
				expect( $flags )->toContain( 'archive:service' );
			} );

			it( 'filters out empty post type strings', function () {
				global $test_is_archive, $test_is_post_type_archive, $test_query_vars;
				$test_is_archive = true;
				$test_is_post_type_archive = true;
				$test_query_vars['post_type'] = array( 'product', '', 'service', null );

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toContain( 'archive:product' );
				expect( $flags )->toContain( 'archive:service' );
				expect( count( $flags ) )->toBe( 2 );
			} );
		} );

		describe( 'taxonomy archives', function () {
			it( 'returns archive flag for category', function () {
				global $test_is_archive, $test_is_category, $test_queried_object;
				$test_is_archive = true;
				$test_is_category = true;
				$test_queried_object = (object) array(
					'taxonomy' => 'category',
					'term_id'  => 5,
				);

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toContain( 'archive:category:5' );
			} );

			it( 'returns archive flag for tag', function () {
				global $test_is_archive, $test_is_tag, $test_queried_object;
				$test_is_archive = true;
				$test_is_tag = true;
				$test_queried_object = (object) array(
					'taxonomy' => 'post_tag',
					'term_id'  => 10,
				);

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toContain( 'archive:post_tag:10' );
			} );

			it( 'returns archive flag for custom taxonomy', function () {
				global $test_is_archive, $test_is_tax, $test_queried_object;
				$test_is_archive = true;
				$test_is_tax = true;
				$test_queried_object = (object) array(
					'taxonomy' => 'product_cat',
					'term_id'  => 15,
				);

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toContain( 'archive:product_cat:15' );
			} );

			it( 'handles missing term data gracefully', function () {
				global $test_is_archive, $test_is_category, $test_queried_object;
				$test_is_archive = true;
				$test_is_category = true;
				$test_queried_object = (object) array();

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toBe( array() );
			} );
		} );

		describe( 'author archives', function () {
			it( 'returns archive flag for author with numeric ID', function () {
				global $test_is_archive, $test_is_author, $test_query_vars;
				$test_is_archive = true;
				$test_is_author = true;
				$test_query_vars['author'] = 7;

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toContain( 'archive:author:7' );
			} );

			it( 'returns archive flag for author with string ID', function () {
				global $test_is_archive, $test_is_author, $test_query_vars;
				$test_is_archive = true;
				$test_is_author = true;
				$test_query_vars['author'] = '12';

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toContain( 'archive:author:12' );
			} );

			it( 'does not return flag for invalid author ID', function () {
				global $test_is_archive, $test_is_author, $test_query_vars;
				$test_is_archive = true;
				$test_is_author = true;
				$test_query_vars['author'] = 0;

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toBe( array() );
			} );
		} );

		describe( 'date archives', function () {
			it( 'returns archive flag for year only', function () {
				global $test_is_archive, $test_is_date, $test_query_vars;
				$test_is_archive = true;
				$test_is_date = true;
				$test_query_vars['year'] = 2024;

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toContain( 'archive:2024' );
			} );

			it( 'returns archive flag for year and month', function () {
				global $test_is_archive, $test_is_date, $test_query_vars;
				$test_is_archive = true;
				$test_is_date = true;
				$test_query_vars['year'] = 2024;
				$test_query_vars['monthnum'] = 3;

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toContain( 'archive:2024:03' );
			} );

			it( 'returns archive flag for full date', function () {
				global $test_is_archive, $test_is_date, $test_query_vars;
				$test_is_archive = true;
				$test_is_date = true;
				$test_query_vars['year'] = 2024;
				$test_query_vars['monthnum'] = 12;
				$test_query_vars['day'] = 5;

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toContain( 'archive:2024:12:05' );
			} );

			it( 'pads single digit month and day with zero', function () {
				global $test_is_archive, $test_is_date, $test_query_vars;
				$test_is_archive = true;
				$test_is_date = true;
				$test_query_vars['year'] = 2024;
				$test_query_vars['monthnum'] = 3;
				$test_query_vars['day'] = 7;

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toContain( 'archive:2024:03:07' );
			} );

			it( 'returns empty array when no valid date parts', function () {
				global $test_is_archive, $test_is_date, $test_query_vars;
				$test_is_archive = true;
				$test_is_date = true;
				$test_query_vars = array();

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toBe( array() );
			} );
		} );

		describe( 'feeds', function () {
			it( 'returns feed flag for feed pages', function () {
				global $test_is_feed;
				$test_is_feed = true;

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toContain( 'feed' );
			} );
		} );

		describe( 'combined contexts', function () {
			it( 'returns multiple flags for combined contexts', function () {
				global $test_is_singular, $test_queried_object_id, $test_is_feed;
				$test_is_singular = true;
				$test_queried_object_id = 99;
				$test_is_feed = true;

				$flags = MilliCache::get_request_flags();

				expect( $flags )->toContain( 'post:99' );
				expect( $flags )->toContain( 'feed' );
			} );
		} );
	} );

	describe( 'set_cache_flags', function () {
		it( 'method exists and is callable', function () {
			$millicache = new MilliCache();

			expect( method_exists( $millicache, 'set_cache_flags' ) )->toBeTrue();
			expect( is_callable( array( $millicache, 'set_cache_flags' ) ) )->toBeTrue();
		} );

		it( 'runs without error for singular post', function () {
			global $test_is_singular, $test_queried_object_id;
			$test_is_singular = true;
			$test_queried_object_id = 50;

			$millicache = new MilliCache();
			$millicache->set_cache_flags();

			// If we got here without exception, the method works.
			expect( true )->toBeTrue();
		} );

		it( 'runs without error with custom filter flags', function () {
			global $test_is_singular, $test_queried_object_id, $test_filters;
			$test_is_singular = true;
			$test_queried_object_id = 50;
			$test_filters['millicache_flags_for_request'] = array( 'custom_flag_1', 'custom_flag_2' );

			$millicache = new MilliCache();
			$millicache->set_cache_flags();

			// If we got here without exception, the method works.
			expect( true )->toBeTrue();
		} );
	} );

	describe( 'get_post_related_flags', function () {
		it( 'returns empty array for null post', function () {
			$flags = MilliCache::get_post_related_flags( null );

			expect( $flags )->toBe( array() );
		} );

		it( 'returns post and archive flags for basic post', function () {
			$post = new WP_Post( array(
				'ID'           => 123,
				'post_type'    => 'post',
				'post_author'  => 5,
				'post_date'    => '2024-03-15 10:30:00',
				'post_status'  => 'publish',
			) );

			global $test_taxonomies, $test_terms;
			$test_taxonomies['post'] = array();
			$test_terms = array();

			$flags = MilliCache::get_post_related_flags( $post );

			expect( $flags )->toContain( 'post:123' );
			expect( $flags )->toContain( 'archive:post' );
			expect( $flags )->toContain( 'archive:author:5' );
			expect( $flags )->toContain( 'archive:2024' );
			expect( $flags )->toContain( 'archive:2024:03' );
			expect( $flags )->toContain( 'archive:2024:03:15' );
		} );

		it( 'includes taxonomy term flags', function () {
			$post = new WP_Post( array(
				'ID'           => 456,
				'post_type'    => 'post',
				'post_author'  => 7,
				'post_date'    => '2024-01-01 00:00:00',
				'post_status'  => 'publish',
			) );

			global $test_taxonomies, $test_terms;
			$test_taxonomies['post'] = array( 'category', 'post_tag' );
			$test_terms[456] = array(
				'category' => array(
					(object) array( 'term_id' => 10 ),
					(object) array( 'term_id' => 11 ),
				),
				'post_tag' => array(
					(object) array( 'term_id' => 20 ),
				),
			);

			$flags = MilliCache::get_post_related_flags( $post );

			expect( $flags )->toContain( 'archive:category:10' );
			expect( $flags )->toContain( 'archive:category:11' );
			expect( $flags )->toContain( 'archive:post_tag:20' );
		} );

		it( 'handles custom post type', function () {
			$post = new WP_Post( array(
				'ID'           => 789,
				'post_type'    => 'product',
				'post_author'  => 3,
				'post_date'    => '2023-12-25 18:45:30',
				'post_status'  => 'publish',
			) );

			global $test_taxonomies, $test_terms;
			$test_taxonomies['product'] = array( 'product_cat' );
			$test_terms[789] = array(
				'product_cat' => array(
					(object) array( 'term_id' => 100 ),
				),
			);

			$flags = MilliCache::get_post_related_flags( $post );

			expect( $flags )->toContain( 'post:789' );
			expect( $flags )->toContain( 'archive:product' );
			expect( $flags )->toContain( 'archive:author:3' );
			expect( $flags )->toContain( 'archive:product_cat:100' );
			expect( $flags )->toContain( 'archive:2023:12:25' );
		} );

		it( 'handles post without author', function () {
			$post = new WP_Post( array(
				'ID'           => 111,
				'post_type'    => 'page',
				'post_author'  => 0,
				'post_date'    => '2024-06-10 12:00:00',
				'post_status'  => 'publish',
			) );

			global $test_taxonomies, $test_terms;
			$test_taxonomies['page'] = array();

			$flags = MilliCache::get_post_related_flags( $post );

			expect( $flags )->toContain( 'post:111' );
			expect( $flags )->toContain( 'archive:page' );
			expect( $flags )->not->toContain( 'archive:author:0' );
		} );

		it( 'removes duplicate flags', function () {
			global $test_filters;
			$test_filters['millicache_flags_related_to_post'] = array( 'post:999', 'post:999', 'custom' );

			$post = new WP_Post( array(
				'ID'           => 999,
				'post_type'    => 'post',
				'post_author'  => 1,
				'post_date'    => '2024-01-01 00:00:00',
				'post_status'  => 'publish',
			) );

			global $test_taxonomies;
			$test_taxonomies['post'] = array();

			$flags = MilliCache::get_post_related_flags( $post );

			// Check that duplicates are removed.
			$unique_flags = array_unique( $flags );
			expect( count( $flags ) )->toBe( count( $unique_flags ) );
		} );
	} );

	describe( 'clear_post_cache', function () {
		it( 'runs without error for published post object', function () {
			$post = new WP_Post( array(
				'ID'           => 555,
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_author'  => 2,
				'post_date'    => '2024-05-20 14:30:00',
			) );

			global $test_taxonomies;
			$test_taxonomies['post'] = array();

			$millicache = new MilliCache();
			$millicache->clear_post_cache( $post );

			// If we got here without exception, the method works.
			expect( true )->toBeTrue();
		} );

		it( 'runs without error for published post by ID', function () {
			global $test_posts, $test_taxonomies;
			$test_posts[666] = new WP_Post( array(
				'ID'           => 666,
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_author'  => 3,
				'post_date'    => '2024-04-10 09:15:00',
			) );
			$test_taxonomies['post'] = array();

			$millicache = new MilliCache();
			$millicache->clear_post_cache( 666 );

			// If we got here without exception, the method works.
			expect( true )->toBeTrue();
		} );

		it( 'runs without error for non-published post', function () {
			$post = new WP_Post( array(
				'ID'           => 777,
				'post_type'    => 'post',
				'post_status'  => 'draft',
				'post_author'  => 2,
				'post_date'    => '2024-05-20 14:30:00',
			) );

			$millicache = new MilliCache();
			$millicache->clear_post_cache( $post );

			// Should return early without error for non-published posts.
			expect( true )->toBeTrue();
		} );

		it( 'runs without error for invalid post', function () {
			global $test_posts;
			$test_posts[888] = null;

			$millicache = new MilliCache();
			$millicache->clear_post_cache( 888 );

			// Should return early without error for invalid posts.
			expect( true )->toBeTrue();
		} );
	} );

	describe( 'transition_post_status', function () {
		it( 'runs without error when post transitions to publish', function () {
			$post = new WP_Post( array(
				'ID'           => 123,
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_author'  => 1,
				'post_date'    => '2024-01-01 00:00:00',
			) );

			global $test_taxonomies;
			$test_taxonomies['post'] = array();

			$millicache = new MilliCache();
			$millicache->transition_post_status( 'publish', 'draft', $post );

			// If we got here without exception, the method works.
			expect( true )->toBeTrue();
		} );

		it( 'runs without error when post already published', function () {
			$post = new WP_Post( array(
				'ID'           => 456,
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_author'  => 1,
				'post_date'    => '2024-01-01 00:00:00',
			) );

			$millicache = new MilliCache();
			$millicache->transition_post_status( 'publish', 'publish', $post );

			// Should return early without error.
			expect( true )->toBeTrue();
		} );

		it( 'runs without error when post transitions to non-publish status', function () {
			$post = new WP_Post( array(
				'ID'           => 789,
				'post_type'    => 'post',
				'post_status'  => 'draft',
				'post_author'  => 1,
				'post_date'    => '2024-01-01 00:00:00',
			) );

			$millicache = new MilliCache();
			$millicache->transition_post_status( 'draft', 'pending', $post );

			// Should return early without error.
			expect( true )->toBeTrue();
		} );
	} );

	describe( 'get_clear_site_cache_hooks', function () {
		it( 'returns array of hooks with priorities', function () {
			$millicache = new MilliCache();
			$hooks = $millicache->get_clear_site_cache_hooks();

			expect( $hooks )->toBeArray();
			expect( $hooks )->toHaveKey( 'save_post_wp_template_part' );
			expect( $hooks )->toHaveKey( 'save_post_wp_global_styles' );
			expect( $hooks )->toHaveKey( 'customize_save_after' );
			expect( $hooks )->toHaveKey( 'wp_update_nav_menu' );
			expect( $hooks )->toHaveKey( 'switch_theme' );
		} );

		it( 'allows hooks to be filtered', function () {
			global $test_filters;
			$test_filters['millicache_settings_clear_site_hooks'] = array(
				'custom_hook' => 20,
			);

			$millicache = new MilliCache();
			$hooks = $millicache->get_clear_site_cache_hooks();

			expect( $hooks )->toBe( array( 'custom_hook' => 20 ) );
		} );
	} );

	describe( 'register_clear_site_cache_options', function () {
		it( 'runs without error for standard option', function () {
			$millicache = new MilliCache();
			$millicache->register_clear_site_cache_options( 'blogname', 'Old Name', 'New Name' );

			// If we got here without exception, the method works.
			expect( true )->toBeTrue();
		} );

		it( 'runs without error for page_on_front', function () {
			$millicache = new MilliCache();
			$millicache->register_clear_site_cache_options( 'page_on_front', 10, 20 );

			// If we got here without exception, the method works.
			expect( true )->toBeTrue();
		} );

		it( 'runs without error for page_for_posts', function () {
			$millicache = new MilliCache();
			$millicache->register_clear_site_cache_options( 'page_for_posts', 5, 15 );

			// If we got here without exception, the method works.
			expect( true )->toBeTrue();
		} );

		it( 'runs without error for unregistered option', function () {
			$millicache = new MilliCache();
			$millicache->register_clear_site_cache_options( 'unknown_option', 'old', 'new' );

			// Should return early without error for unregistered options.
			expect( true )->toBeTrue();
		} );
	} );

	describe( 'cleanup_expired_flags', function () {
		it( 'runs without error', function () {
			$millicache = new MilliCache();
			$millicache->cleanup_expired_flags();

			// If we got here without exception, the method works.
			expect( true )->toBeTrue();
		} );
	} );

	describe( 'get_clear_cache_capability', function () {
		it( 'returns default capability', function () {
			$capability = MilliCache::get_clear_cache_capability();

			expect( $capability )->toBe( 'publish_pages' );
		} );

		it( 'allows capability to be filtered', function () {
			global $test_filters;
			$test_filters['millicache_clear_cache_capability'] = 'manage_options';

			$capability = MilliCache::get_clear_cache_capability();

			expect( $capability )->toBe( 'manage_options' );
		} );
	} );

	describe( 'getters', function () {
		it( 'get_plugin_name returns correct name', function () {
			$millicache = new MilliCache();

			expect( $millicache->get_plugin_name() )->toBe( 'millicache' );
		} );

		it( 'get_loader returns Loader instance', function () {
			$millicache = new MilliCache();

			expect( $millicache->get_loader() )->toBeInstanceOf( Loader::class );
		} );

		it( 'get_engine returns Engine instance', function () {
			$millicache = new MilliCache();

			expect( $millicache->get_engine() )->toBeInstanceOf( Engine::class );
		} );

		it( 'get_version returns correct version', function () {
			$millicache = new MilliCache();

			expect( $millicache->get_version() )->toBeString();
		} );
	} );

	describe( 'run', function () {
		it( 'method exists and is callable', function () {
			$millicache = new MilliCache();

			expect( method_exists( $millicache, 'run' ) )->toBeTrue();
			expect( is_callable( array( $millicache, 'run' ) ) )->toBeTrue();
		} );
	} );

	describe( 'integration', function () {
		it( 'complete cache workflow works correctly', function () {
			global $test_is_singular, $test_queried_object_id, $test_posts, $test_taxonomies;

			// Setup test data.
			$test_is_singular = true;
			$test_queried_object_id = 100;
			$test_posts[100] = new WP_Post( array(
				'ID'           => 100,
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_author'  => 5,
				'post_date'    => '2024-03-15 10:30:00',
			) );
			$test_taxonomies['post'] = array();

			// Test flag generation.
			$request_flags = MilliCache::get_request_flags();
			expect( $request_flags )->toContain( 'post:100' );

			$post_flags = MilliCache::get_post_related_flags( $test_posts[100] );
			expect( $post_flags )->toContain( 'post:100' );
			expect( $post_flags )->toContain( 'archive:post' );

			// Verify flags are not empty.
			expect( $request_flags )->not->toBeEmpty();
			expect( $post_flags )->not->toBeEmpty();
		} );
	} );
} );
