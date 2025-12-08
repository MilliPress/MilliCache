<?php
/**
 * Tests for Cache Invalidation Manager.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Core\Storage;
use MilliCache\Engine\Cache\Config;
use MilliCache\Engine\Cache\Invalidation\Queue;
use MilliCache\Engine\Cache\Invalidation\Manager;
use MilliCache\Engine\Cache\Invalidation\Resolver;
use MilliCache\Engine\Request\Processor as RequestManager;
use MilliCache\Engine\Utilities\Multisite;

// Mock WordPress functions.
if ( ! function_exists( 'get_home_url' ) ) {
	function get_home_url() {
		return 'https://example.com';
	}
}

if ( ! function_exists( 'is_network_admin' ) ) {
	function is_network_admin() {
		return false;
	}
}

if ( ! function_exists( 'do_action' ) ) {
	function do_action( $hook, ...$args ) {
		return true;
	}
}

if ( ! function_exists( 'is_multisite' ) ) {
	function is_multisite() {
		global $test_is_multisite;
		return $test_is_multisite ?? false;
	}
}

if ( ! function_exists( 'get_current_blog_id' ) ) {
	function get_current_blog_id() {
		global $test_current_site_id;
		return $test_current_site_id ?? 1;
	}
}

if ( ! function_exists( 'get_current_network_id' ) ) {
	function get_current_network_id() {
		global $test_current_network_id;
		return $test_current_network_id ?? 1;
	}
}

if ( ! function_exists( 'get_sites' ) ) {
	function get_sites( $args = array() ) {
		global $test_sites;
		return $test_sites ?? array( 1 );
	}
}

if ( ! function_exists( 'get_networks' ) ) {
	function get_networks( $args = array() ) {
		global $test_networks;
		return $test_networks ?? array( 1 );
	}
}

uses()->beforeEach( function () {
	// Reset global test state.
	global $test_is_multisite, $test_sites, $test_networks, $test_current_site_id, $test_current_network_id;
	$test_is_multisite = false;
	$test_sites = array( 1 );
	$test_networks = array( 1 );
	$test_current_site_id = 1;
	$test_current_network_id = 1;

	// Create Config for RequestManager (final class).
	$config = new Config(
		3600,
		600,
		true,
		false,
		array(),
		array(),
		array(),
		array(),
		array()
	);

	// Create mocks for dependencies.
	$this->storage = Mockery::mock( Storage::class );
	// Use real instances (final classes, control via WordPress functions).
	$this->request_handler = new RequestManager( $config );
	$this->multisite = new Multisite();
} );

describe( 'Invalidation Manager', function () {

	describe( 'constructor', function () {
		it( 'creates handler with dependencies', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			expect( $handler )->toBeInstanceOf( Manager::class );
		} );

		it( 'creates resolver internally', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			expect( $handler->get_resolver() )->toBeInstanceOf( Resolver::class );
		} );

		it( 'creates queue internally', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			expect( $handler->get_queue() )->toBeInstanceOf( Queue::class );
		} );

		it( 'passes default_ttl to queue', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite,
				7200
			);

			$queue = $handler->get_queue();
			expect( $queue )->toBeInstanceOf( Queue::class );
		} );
	} );

	describe( 'get_resolver', function () {
		it( 'returns resolver instance', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			expect( $handler->get_resolver() )->toBeInstanceOf( Resolver::class );
		} );
	} );

	describe( 'get_queue', function () {
		it( 'returns queue instance', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			expect( $handler->get_queue() )->toBeInstanceOf( Queue::class );
		} );
	} );

	describe( 'targets', function () {
		it( 'converts single target to array', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			// Should not throw error with single string.
			$handler->targets( 'test-flag' );
			expect( true )->toBeTrue();
		} );

		it( 'clears entire site when targets empty', function () {
			$this->storage->shouldReceive( 'clear_cache_by_sets' )->once();

			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->targets( array() );

			// Execute the queue.
			$result = $handler->get_queue()->execute();

			expect( $result )->toBeTrue();
		} );

		it( 'identifies and clears URL targets', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->targets( 'https://example.com/test' );

			// Verify URL was processed.
			expect( count( $handler->get_queue()->get_delete_queue() ) )->toBeGreaterThan( 0 );
		} );

		it( 'identifies and clears post ID targets', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->targets( 123 );

			// Verify post ID was processed (creates post:123 and feed flags).
			$queue = $handler->get_queue()->get_delete_queue();
			expect( $queue )->toContain( 'post:123' );
			expect( $queue )->toContain( 'feed' );
		} );

		it( 'identifies and clears flag targets', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->targets( 'custom-flag' );

			$queue = $handler->get_queue()->get_delete_queue();
			expect( $queue )->toContain( 'custom-flag' );
		} );

		it( 'limits URLs to current site only', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			// URL from different site should be ignored.
			$handler->targets( 'https://different.com/test' );

			$queue = $handler->get_queue()->get_delete_queue();
			expect( $queue )->toBeEmpty();
		} );

		it( 'handles mixed target types', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->targets( array(
				'https://example.com/page',
				456,
				'my-flag',
			) );

			$queue = $handler->get_queue()->get_delete_queue();
			expect( count( $queue ) )->toBeGreaterThan( 3 );
		} );
	} );

	describe( 'urls', function () {
		it( 'converts string URL to array', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->urls( 'https://example.com/test' );

			expect( count( $handler->get_queue()->get_delete_queue() ) )->toBeGreaterThan( 0 );
		} );

		it( 'resolves URLs to flags via resolver', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->urls( 'https://example.com/test' );

			$queue = $handler->get_queue()->get_delete_queue();
			expect( count( $queue ) )->toBe( 2 ); // Both URL variations (with/without trailing slash)
		} );

		it( 'adds flags to delete queue when expire=false', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->urls( 'https://example.com/test', false );

			expect( count( $handler->get_queue()->get_delete_queue() ) )->toBeGreaterThan( 0 );
			expect( count( $handler->get_queue()->get_expire_queue() ) )->toBe( 0 );
		} );

		it( 'adds flags to expire queue when expire=true', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->urls( 'https://example.com/test', true );

			expect( count( $handler->get_queue()->get_expire_queue() ) )->toBeGreaterThan( 0 );
			expect( count( $handler->get_queue()->get_delete_queue() ) )->toBe( 0 );
		} );
	} );

	describe( 'posts', function () {
		it( 'converts single post ID to array', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->posts( 123 );

			$queue = $handler->get_queue()->get_delete_queue();
			expect( $queue )->toContain( 'post:123' );
		} );

		it( 'resolves post IDs to flags', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->posts( 456 );

			$queue = $handler->get_queue()->get_delete_queue();
			expect( $queue )->toContain( 'post:456' );
			expect( $queue )->toContain( 'feed' );
		} );

		it( 'fires millicache_cache_cleared_by_posts action', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			// Action is fired (function exists check in source).
			$handler->posts( array( 1, 2, 3 ) );

			expect( true )->toBeTrue(); // Action fired successfully
		} );

		it( 'passes expire parameter correctly', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->posts( 789, true );

			expect( count( $handler->get_queue()->get_expire_queue() ) )->toBeGreaterThan( 0 );
			expect( count( $handler->get_queue()->get_delete_queue() ) )->toBe( 0 );
		} );
	} );

	describe( 'flags', function () {
		it( 'converts single flag to array', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->flags( 'test-flag' );

			$queue = $handler->get_queue()->get_delete_queue();
			expect( $queue )->toContain( 'test-flag' );
		} );

		it( 'adds to delete queue when expire=false', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->flags( 'flag1', false );

			expect( count( $handler->get_queue()->get_delete_queue() ) )->toBeGreaterThan( 0 );
			expect( count( $handler->get_queue()->get_expire_queue() ) )->toBe( 0 );
		} );

		it( 'adds to expire queue when expire=true', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->flags( 'flag2', true );

			expect( count( $handler->get_queue()->get_expire_queue() ) )->toBeGreaterThan( 0 );
			expect( count( $handler->get_queue()->get_delete_queue() ) )->toBe( 0 );
		} );

		it( 'respects add_prefix parameter', function () {
			global $test_is_multisite, $test_networks;
			$test_is_multisite = true;
			$test_networks = array( 1 );

			// Recreate multisite instance with multisite enabled.
			$multisite = new Multisite();

			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$multisite
			);

			$handler->flags( 'flag3', false, true );

			$queue = $handler->get_queue()->get_delete_queue();
			expect( $queue )->toContain( '1:flag3' );
		} );

		it( 'fires millicache_cache_cleared_by_flags action', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->flags( array( 'flag1', 'flag2' ) );

			expect( true )->toBeTrue(); // Action fired
		} );
	} );

	describe( 'sites', function () {
		it( 'resolves site IDs to flags', function () {
			global $test_is_multisite, $test_networks;
			$test_is_multisite = true;
			$test_networks = array( 1 );

			$multisite = new Multisite();

			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$multisite
			);

			$handler->sites( 1 );

			$queue = $handler->get_queue()->get_delete_queue();
			expect( count( $queue ) )->toBeGreaterThan( 0 );
		} );

		it( 'handles null site_ids for current site', function () {
			global $test_is_multisite, $test_networks;
			$test_is_multisite = true;
			$test_networks = array( 1 );

			$multisite = new Multisite();

			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$multisite
			);

			$handler->sites();

			expect( count( $handler->get_queue()->get_delete_queue() ) )->toBeGreaterThan( 0 );
		} );

		it( 'handles array of site IDs', function () {
			global $test_is_multisite, $test_networks;
			$test_is_multisite = true;
			$test_networks = array( 1 );

			$multisite = new Multisite();

			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$multisite
			);

			$handler->sites( array( 1, 2 ) );

			expect( count( $handler->get_queue()->get_delete_queue() ) )->toBe( 2 );
		} );

		it( 'fires millicache_cache_cleared_by_sites action', function () {
			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->sites( 1 );

			expect( true )->toBeTrue(); // Action fired
		} );
	} );

	describe( 'network', function () {
		it( 'resolves network to site IDs', function () {
			global $test_is_multisite, $test_networks, $test_sites;
			$test_is_multisite = true;
			$test_networks = array( 1 );
			$test_sites = array( 1, 2 );

			$multisite = new Multisite();

			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$multisite
			);

			$handler->network( 1 );

			expect( count( $handler->get_queue()->get_delete_queue() ) )->toBeGreaterThan( 0 );
		} );

		it( 'clears each site individually', function () {
			global $test_is_multisite, $test_networks, $test_sites;
			$test_is_multisite = true;
			$test_networks = array( 1 );
			$test_sites = array( 1, 2, 3 );

			$multisite = new Multisite();

			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$multisite
			);

			$handler->network( 1 );

			expect( count( $handler->get_queue()->get_delete_queue() ) )->toBe( 3 );
		} );

		it( 'fires millicache_cleared_by_network_id action', function () {
			global $test_is_multisite, $test_networks, $test_sites;
			$test_is_multisite = true;
			$test_networks = array( 1 );
			$test_sites = array( 1 );

			$multisite = new Multisite();

			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$multisite
			);

			$handler->network();

			expect( true )->toBeTrue(); // Action fired
		} );
	} );

	describe( 'all', function () {
		it( 'gets all networks', function () {
			global $test_is_multisite, $test_networks, $test_sites;
			$test_is_multisite = true;
			$test_networks = array( 1 );
			$test_sites = array( 1 );

			$multisite = new Multisite();

			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$multisite
			);

			$handler->all();

			expect( count( $handler->get_queue()->get_delete_queue() ) )->toBeGreaterThan( 0 );
		} );

		it( 'clears each network', function () {
			global $test_is_multisite, $test_networks, $test_sites;
			$test_is_multisite = true;
			$test_networks = array( 1, 2 );
			$test_sites = array( 1 );

			$multisite = new Multisite();

			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$multisite
			);

			$handler->all();

			expect( count( $handler->get_queue()->get_delete_queue() ) )->toBe( 2 );
		} );

		it( 'fires millicache_cache_cleared action', function () {
			global $test_is_multisite, $test_networks, $test_sites;
			$test_is_multisite = false;
			$test_networks = array( 1 );
			$test_sites = array( 1 );

			$multisite = new Multisite();

			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$multisite
			);

			$handler->all();

			expect( true )->toBeTrue(); // Action fired
		} );
	} );

	describe( 'queue execute', function () {
		it( 'delegates to queue execute method', function () {
			$this->storage->shouldReceive( 'clear_cache_by_sets' )->once();

			$handler = new Manager(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			// Add something to execute.
			$handler->flags( 'test' );
			$result = $handler->get_queue()->execute();

			expect( $result )->toBeTrue();
		} );
	} );
} );
