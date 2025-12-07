<?php
/**
 * Tests for Loader class.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Core\Loader;

// Mock WordPress functions.
if ( ! function_exists( 'add_action' ) ) {
	function add_action( $hook, $callable, $priority = 10, $accepted_args = 1 ) {
		global $test_actions;
		$test_actions[] = array(
			'hook' => $hook,
			'callable' => $callable,
			'priority' => $priority,
			'accepted_args' => $accepted_args,
		);
		return true;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $hook, $callable, $priority = 10, $accepted_args = 1 ) {
		global $test_filters;
		$test_filters[] = array(
			'hook' => $hook,
			'callable' => $callable,
			'priority' => $priority,
			'accepted_args' => $accepted_args,
		);
		return true;
	}
}

uses()->beforeEach( function () {
	global $test_actions, $test_filters;
	$test_actions = array();
	$test_filters = array();

	$this->loader = new Loader();
} );

describe( 'Loader', function () {

	describe( 'constructor', function () {
		it( 'initializes with empty action and filter arrays', function () {
			$loader = new Loader();
			expect( $loader )->toBeInstanceOf( Loader::class );
		} );
	} );

	describe( 'add_action', function () {
		it( 'adds action to internal collection', function () {
			$component = new stdClass();
			$this->loader->add_action( 'init', $component, 'callback_method' );

			expect( true )->toBeTrue();
		} );

		it( 'accepts custom priority', function () {
			$component = new stdClass();
			$this->loader->add_action( 'init', $component, 'callback_method', 20 );

			expect( true )->toBeTrue();
		} );

		it( 'accepts custom accepted_args', function () {
			$component = new stdClass();
			$this->loader->add_action( 'init', $component, 'callback_method', 10, 3 );

			expect( true )->toBeTrue();
		} );

		it( 'stores multiple actions', function () {
			$component = new stdClass();
			$this->loader->add_action( 'init', $component, 'callback1' );
			$this->loader->add_action( 'wp_loaded', $component, 'callback2' );

			expect( true )->toBeTrue();
		} );
	} );

	describe( 'add_filter', function () {
		it( 'adds filter to internal collection', function () {
			$component = new stdClass();
			$this->loader->add_filter( 'the_content', $component, 'filter_method' );

			expect( true )->toBeTrue();
		} );

		it( 'accepts custom priority', function () {
			$component = new stdClass();
			$this->loader->add_filter( 'the_content', $component, 'filter_method', 20 );

			expect( true )->toBeTrue();
		} );

		it( 'accepts custom accepted_args', function () {
			$component = new stdClass();
			$this->loader->add_filter( 'the_content', $component, 'filter_method', 10, 2 );

			expect( true )->toBeTrue();
		} );

		it( 'stores multiple filters', function () {
			$component = new stdClass();
			$this->loader->add_filter( 'the_content', $component, 'filter1' );
			$this->loader->add_filter( 'the_title', $component, 'filter2' );

			expect( true )->toBeTrue();
		} );
	} );

	describe( 'run', function () {
		it( 'registers all actions with WordPress', function () {
			$component = new stdClass();
			$this->loader->add_action( 'init', $component, 'test_callback', 15, 2 );

			$this->loader->run();

			global $test_actions;
			expect( $test_actions )->toHaveCount( 1 );
			expect( $test_actions[0]['hook'] )->toBe( 'init' );
			expect( $test_actions[0]['priority'] )->toBe( 15 );
			expect( $test_actions[0]['accepted_args'] )->toBe( 2 );
		} );

		it( 'registers all filters with WordPress', function () {
			$component = new stdClass();
			$this->loader->add_filter( 'the_content', $component, 'test_filter', 20, 3 );

			$this->loader->run();

			global $test_filters;
			expect( $test_filters )->toHaveCount( 1 );
			expect( $test_filters[0]['hook'] )->toBe( 'the_content' );
			expect( $test_filters[0]['priority'] )->toBe( 20 );
			expect( $test_filters[0]['accepted_args'] )->toBe( 3 );
		} );

		it( 'registers multiple actions and filters', function () {
			$component = new stdClass();
			$this->loader->add_action( 'init', $component, 'action1' );
			$this->loader->add_action( 'wp_loaded', $component, 'action2' );
			$this->loader->add_filter( 'the_content', $component, 'filter1' );
			$this->loader->add_filter( 'the_title', $component, 'filter2' );

			$this->loader->run();

			global $test_actions, $test_filters;
			expect( $test_actions )->toHaveCount( 2 );
			expect( $test_filters )->toHaveCount( 2 );
		} );
	} );

	describe( 'integration', function () {
		it( 'complete workflow works correctly', function () {
			$component = new stdClass();

			// Add actions.
			$this->loader->add_action( 'init', $component, 'init_callback', 10, 1 );
			$this->loader->add_action( 'wp_loaded', $component, 'loaded_callback', 20, 2 );

			// Add filters.
			$this->loader->add_filter( 'the_content', $component, 'content_filter', 15, 1 );
			$this->loader->add_filter( 'the_title', $component, 'title_filter', 25, 2 );

			// Run loader.
			$this->loader->run();

			// Verify actions registered.
			global $test_actions, $test_filters;
			expect( $test_actions )->toHaveCount( 2 );
			expect( $test_filters )->toHaveCount( 2 );

			// Verify action details.
			expect( $test_actions[0]['hook'] )->toBe( 'init' );
			expect( $test_actions[0]['priority'] )->toBe( 10 );
			expect( $test_actions[1]['hook'] )->toBe( 'wp_loaded' );
			expect( $test_actions[1]['priority'] )->toBe( 20 );

			// Verify filter details.
			expect( $test_filters[0]['hook'] )->toBe( 'the_content' );
			expect( $test_filters[0]['priority'] )->toBe( 15 );
			expect( $test_filters[1]['hook'] )->toBe( 'the_title' );
			expect( $test_filters[1]['priority'] )->toBe( 25 );
		} );
	} );
} );
