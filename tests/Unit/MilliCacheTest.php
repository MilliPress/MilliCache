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
		it( 'method exists and is callable', function () {
			$millicache = new MilliCache();

			expect( method_exists( $millicache, 'clear_post_cache' ) )->toBeTrue();
			expect( is_callable( array( $millicache, 'clear_post_cache' ) ) )->toBeTrue();
		} );

		it( 'runs without error for published post by ID', function () {
			global $test_posts, $test_taxonomies;

			$test_posts[123] = new WP_Post( array(
				'ID'           => 123,
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_author'  => 5,
				'post_date'    => '2024-03-15 10:30:00',
			) );
			$test_taxonomies['post'] = array();

			$millicache = new MilliCache();
			$millicache->clear_post_cache( 123 );

			expect( true )->toBeTrue();
		} );

		it( 'runs without error for published post object', function () {
			global $test_taxonomies;
			$test_taxonomies['post'] = array();

			$post = new WP_Post( array(
				'ID'           => 456,
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_author'  => 7,
				'post_date'    => '2024-01-01 00:00:00',
			) );

			$millicache = new MilliCache();
			$millicache->clear_post_cache( $post );

			expect( true )->toBeTrue();
		} );

		it( 'runs without error for non-published post', function () {
			$post = new WP_Post( array(
				'ID'           => 789,
				'post_type'    => 'post',
				'post_status'  => 'draft',
				'post_author'  => 5,
				'post_date'    => '2024-03-15 10:30:00',
			) );

			$millicache = new MilliCache();
			$millicache->clear_post_cache( $post );

			expect( true )->toBeTrue();
		} );

		it( 'handles null post gracefully', function () {
			global $test_posts;
			$test_posts[999] = null;

			$millicache = new MilliCache();
			$millicache->clear_post_cache( 999 );

			expect( true )->toBeTrue();
		} );
	} );

	describe( 'transition_post_status', function () {
		it( 'method exists and is callable', function () {
			$millicache = new MilliCache();

			expect( method_exists( $millicache, 'transition_post_status' ) )->toBeTrue();
			expect( is_callable( array( $millicache, 'transition_post_status' ) ) )->toBeTrue();
		} );

		it( 'runs without error when publishing new post', function () {
			global $test_taxonomies;
			$test_taxonomies['post'] = array();

			$post = new WP_Post( array(
				'ID'           => 123,
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_author'  => 5,
				'post_date'    => '2024-03-15 10:30:00',
			) );

			$millicache = new MilliCache();
			$millicache->transition_post_status( 'publish', 'draft', $post );

			expect( true )->toBeTrue();
		} );

		it( 'runs without error when updating published post', function () {
			$post = new WP_Post( array(
				'ID'           => 456,
				'post_type'    => 'post',
				'post_status'  => 'publish',
			) );

			$millicache = new MilliCache();
			$millicache->transition_post_status( 'publish', 'publish', $post );

			expect( true )->toBeTrue();
		} );

		it( 'runs without error when changing non-publish status', function () {
			$post = new WP_Post( array(
				'ID'           => 789,
				'post_type'    => 'post',
				'post_status'  => 'pending',
			) );

			$millicache = new MilliCache();
			$millicache->transition_post_status( 'pending', 'draft', $post );

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
		it( 'post flag generation and clearing workflow', function () {
			global $test_posts, $test_taxonomies;

			// Setup test data.
			$test_posts[100] = new WP_Post( array(
				'ID'           => 100,
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_author'  => 5,
				'post_date'    => '2024-03-15 10:30:00',
			) );
			$test_taxonomies['post'] = array();

			// Test flag generation.
			$post_flags = MilliCache::get_post_related_flags( $test_posts[100] );
			expect( $post_flags )->toContain( 'post:100' );
			expect( $post_flags )->toContain( 'archive:post' );
			expect( $post_flags )->not->toBeEmpty();

			// Test cache clearing methods work without errors.
			$millicache = new MilliCache();
			$millicache->clear_post_cache( 100 );
			$millicache->transition_post_status( 'publish', 'draft', $test_posts[100] );

			expect( true )->toBeTrue();
		} );
	} );
} );
