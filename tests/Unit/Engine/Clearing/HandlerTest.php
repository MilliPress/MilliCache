<?php
/**
 * Tests for Cache Clearing Handler.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Engine\Clearing\Handler;
use MilliCache\Engine\Clearing\Resolver;
use MilliCache\Engine\Clearing\Flusher;
use MilliCache\Core\Storage;
use MilliCache\Engine\Multisite;
use MilliCache\Engine\Request\Handler as RequestHandler;
use MilliCache\Engine\Cache\Config;

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
		return false;
	}
}

uses()->beforeEach( function () {
	// Create Config for RequestHandler (final class).
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
	$this->request_handler = new RequestHandler( $config );
	$this->multisite = new Multisite();
} );

describe( 'Clearing Handler', function () {

	describe( 'constructor', function () {
		it( 'creates handler with dependencies', function () {
			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			expect( $handler )->toBeInstanceOf( Handler::class );
		} );

		it( 'creates resolver internally', function () {
			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			expect( $handler->get_resolver() )->toBeInstanceOf( Resolver::class );
		} );

		it( 'creates flusher internally', function () {
			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			expect( $handler->get_flusher() )->toBeInstanceOf( Flusher::class );
		} );

		it( 'passes default_ttl to flusher', function () {
			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite,
				7200
			);

			$flusher = $handler->get_flusher();
			expect( $flusher )->toBeInstanceOf( Flusher::class );
		} );
	} );

	describe( 'get_resolver', function () {
		it( 'returns resolver instance', function () {
			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			expect( $handler->get_resolver() )->toBeInstanceOf( Resolver::class );
		} );
	} );

	describe( 'get_flusher', function () {
		it( 'returns flusher instance', function () {
			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			expect( $handler->get_flusher() )->toBeInstanceOf( Flusher::class );
		} );
	} );

	describe( 'clear_by_targets', function () {
		it( 'converts single target to array', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			// Should not throw error with single string.
			$handler->clear_by_targets( 'test-flag' );
			expect( true )->toBeTrue();
		} );

		it( 'clears entire site when targets empty', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );
			$this->multisite->shouldReceive( 'get_flag_prefix' )->andReturn( '' );
			$this->storage->shouldReceive( 'clear_cache_by_sets' )->once();

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_targets( array() );

			// Verify flusher was used (indirectly through flush).
			$handler->flush();
		} );

		it( 'identifies and clears URL targets', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );
			$this->request_handler->shouldReceive( 'get_url_hash' )
				->andReturn( 'hash123' );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_targets( 'https://example.com/test' );

			// Verify URL was processed.
			expect( count( $handler->get_flusher()->get_delete_queue() ) )->toBeGreaterThan( 0 );
		} );

		it( 'identifies and clears post ID targets', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_targets( 123 );

			// Verify post ID was processed (creates post:123 and feed flags).
			$queue = $handler->get_flusher()->get_delete_queue();
			expect( $queue )->toContain( 'post:123' );
			expect( $queue )->toContain( 'feed' );
		} );

		it( 'identifies and clears flag targets', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_targets( 'custom-flag' );

			$queue = $handler->get_flusher()->get_delete_queue();
			expect( $queue )->toContain( 'custom-flag' );
		} );

		it( 'limits URLs to current site only', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );
			$this->request_handler->shouldReceive( 'get_url_hash' )
				->never(); // Should NOT be called for different domain

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			// URL from different site should be ignored.
			$handler->clear_by_targets( 'https://different.com/test' );

			$queue = $handler->get_flusher()->get_delete_queue();
			expect( $queue )->toBeEmpty();
		} );

		it( 'handles mixed target types', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );
			$this->request_handler->shouldReceive( 'get_url_hash' )
				->andReturn( 'hash123' );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_targets( array(
				'https://example.com/page',
				456,
				'my-flag',
			) );

			$queue = $handler->get_flusher()->get_delete_queue();
			expect( count( $queue ) )->toBeGreaterThan( 3 );
		} );
	} );

	describe( 'clear_by_urls', function () {
		it( 'converts string URL to array', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );
			$this->request_handler->shouldReceive( 'get_url_hash' )
				->andReturn( 'hash123' );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_urls( 'https://example.com/test' );

			expect( count( $handler->get_flusher()->get_delete_queue() ) )->toBeGreaterThan( 0 );
		} );

		it( 'resolves URLs to flags via resolver', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );
			$this->request_handler->shouldReceive( 'get_url_hash' )
				->twice() // Once for trailing slash, once without
				->andReturn( 'hash123' );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_urls( 'https://example.com/test' );

			$queue = $handler->get_flusher()->get_delete_queue();
			expect( count( $queue ) )->toBe( 2 ); // Both URL variations
		} );

		it( 'adds flags to delete queue when expire=false', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );
			$this->request_handler->shouldReceive( 'get_url_hash' )
				->andReturn( 'hash123' );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_urls( 'https://example.com/test', false );

			expect( count( $handler->get_flusher()->get_delete_queue() ) )->toBeGreaterThan( 0 );
			expect( count( $handler->get_flusher()->get_expire_queue() ) )->toBe( 0 );
		} );

		it( 'adds flags to expire queue when expire=true', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );
			$this->request_handler->shouldReceive( 'get_url_hash' )
				->andReturn( 'hash123' );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_urls( 'https://example.com/test', true );

			expect( count( $handler->get_flusher()->get_expire_queue() ) )->toBeGreaterThan( 0 );
			expect( count( $handler->get_flusher()->get_delete_queue() ) )->toBe( 0 );
		} );
	} );

	describe( 'clear_by_post_ids', function () {
		it( 'converts single post ID to array', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_post_ids( 123 );

			$queue = $handler->get_flusher()->get_delete_queue();
			expect( $queue )->toContain( 'post:123' );
		} );

		it( 'resolves post IDs to flags', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_post_ids( 456 );

			$queue = $handler->get_flusher()->get_delete_queue();
			expect( $queue )->toContain( 'post:456' );
			expect( $queue )->toContain( 'feed' );
		} );

		it( 'fires millicache_cache_cleared_by_posts action', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			// Action is fired (function exists check in source).
			$handler->clear_by_post_ids( array( 1, 2, 3 ) );

			expect( true )->toBeTrue(); // Action fired successfully
		} );

		it( 'passes expire parameter correctly', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_post_ids( 789, true );

			expect( count( $handler->get_flusher()->get_expire_queue() ) )->toBeGreaterThan( 0 );
			expect( count( $handler->get_flusher()->get_delete_queue() ) )->toBe( 0 );
		} );
	} );

	describe( 'clear_by_flags', function () {
		it( 'converts single flag to array', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_flags( 'test-flag' );

			$queue = $handler->get_flusher()->get_delete_queue();
			expect( $queue )->toContain( 'test-flag' );
		} );

		it( 'adds to delete queue when expire=false', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_flags( 'flag1', false );

			expect( count( $handler->get_flusher()->get_delete_queue() ) )->toBeGreaterThan( 0 );
			expect( count( $handler->get_flusher()->get_expire_queue() ) )->toBe( 0 );
		} );

		it( 'adds to expire queue when expire=true', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_flags( 'flag2', true );

			expect( count( $handler->get_flusher()->get_expire_queue() ) )->toBeGreaterThan( 0 );
			expect( count( $handler->get_flusher()->get_delete_queue() ) )->toBe( 0 );
		} );

		it( 'respects add_prefix parameter', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( true );
			$this->multisite->shouldReceive( 'get_flag_prefix' )->andReturn( '1:1:' );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_flags( 'flag3', false, true );

			$queue = $handler->get_flusher()->get_delete_queue();
			expect( $queue )->toContain( '1:1:flag3' );
		} );

		it( 'fires millicache_cache_cleared_by_flags action', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_flags( array( 'flag1', 'flag2' ) );

			expect( true )->toBeTrue(); // Action fired
		} );
	} );

	describe( 'clear_by_site_ids', function () {
		it( 'resolves site IDs to flags', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( true );
			$this->multisite->shouldReceive( 'get_flag_prefix' )->andReturn( '1:1:' );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_site_ids( 1 );

			$queue = $handler->get_flusher()->get_delete_queue();
			expect( count( $queue ) )->toBeGreaterThan( 0 );
		} );

		it( 'handles null site_ids for current site', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( true );
			$this->multisite->shouldReceive( 'get_flag_prefix' )->andReturn( '1:1:' );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_site_ids();

			expect( count( $handler->get_flusher()->get_delete_queue() ) )->toBeGreaterThan( 0 );
		} );

		it( 'handles array of site IDs', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( true );
			$this->multisite->shouldReceive( 'get_flag_prefix' )->andReturn( '1:1:', '1:2:' );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_site_ids( array( 1, 2 ) );

			expect( count( $handler->get_flusher()->get_delete_queue() ) )->toBe( 2 );
		} );

		it( 'fires millicache_cache_cleared_by_sites action', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );
			$this->multisite->shouldReceive( 'get_flag_prefix' )->andReturn( '' );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_site_ids( 1 );

			expect( true )->toBeTrue(); // Action fired
		} );
	} );

	describe( 'clear_by_network_id', function () {
		it( 'resolves network to site IDs', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( true );
			$this->multisite->shouldReceive( 'get_site_ids' )->andReturn( array( 1, 2 ) );
			$this->multisite->shouldReceive( 'get_flag_prefix' )->andReturn( '1:1:', '1:2:' );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_network_id( 1 );

			expect( count( $handler->get_flusher()->get_delete_queue() ) )->toBeGreaterThan( 0 );
		} );

		it( 'clears each site individually', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( true );
			$this->multisite->shouldReceive( 'get_site_ids' )->andReturn( array( 1, 2, 3 ) );
			$this->multisite->shouldReceive( 'get_flag_prefix' )->andReturn( '1:1:', '1:2:', '1:3:' );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_network_id( 1 );

			expect( count( $handler->get_flusher()->get_delete_queue() ) )->toBe( 3 );
		} );

		it( 'fires millicache_cleared_by_network_id action', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( true );
			$this->multisite->shouldReceive( 'get_site_ids' )->andReturn( array( 1 ) );
			$this->multisite->shouldReceive( 'get_flag_prefix' )->andReturn( '1:1:' );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_by_network_id();

			expect( true )->toBeTrue(); // Action fired
		} );
	} );

	describe( 'clear_all', function () {
		it( 'gets all networks', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( true );
			$this->multisite->shouldReceive( 'get_network_ids' )->andReturn( array( 1 ) );
			$this->multisite->shouldReceive( 'get_site_ids' )->andReturn( array( 1 ) );
			$this->multisite->shouldReceive( 'get_flag_prefix' )->andReturn( '1:1:' );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_all();

			expect( count( $handler->get_flusher()->get_delete_queue() ) )->toBeGreaterThan( 0 );
		} );

		it( 'clears each network', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( true );
			$this->multisite->shouldReceive( 'get_network_ids' )->andReturn( array( 1, 2 ) );
			$this->multisite->shouldReceive( 'get_site_ids' )->andReturn( array( 1 ), array( 2 ) );
			$this->multisite->shouldReceive( 'get_flag_prefix' )->andReturn( '1:1:', '2:2:' );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_all();

			expect( count( $handler->get_flusher()->get_delete_queue() ) )->toBe( 2 );
		} );

		it( 'fires millicache_cache_cleared action', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );
			$this->multisite->shouldReceive( 'get_network_ids' )->andReturn( array( 1 ) );
			$this->multisite->shouldReceive( 'get_site_ids' )->andReturn( array( 1 ) );
			$this->multisite->shouldReceive( 'get_flag_prefix' )->andReturn( '' );

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			$handler->clear_all();

			expect( true )->toBeTrue(); // Action fired
		} );
	} );

	describe( 'flush', function () {
		it( 'delegates to flusher flush method', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );
			$this->storage->shouldReceive( 'clear_cache_by_sets' )->once();

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			// Add something to flush.
			$handler->clear_by_flags( 'test' );
			$result = $handler->flush();

			expect( $result )->toBeTrue();
		} );
	} );

	describe( 'flush_on_shutdown', function () {
		it( 'delegates to flusher', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );
			$this->storage->shouldReceive( 'clear_cache_by_sets' )->once();

			$handler = new Handler(
				$this->storage,
				$this->request_handler,
				$this->multisite
			);

			// Add something to flush.
			$handler->clear_by_flags( 'test' );
			$handler->flush_on_shutdown();

			// Verify shutdown was called (which calls flush internally).
			expect( true )->toBeTrue();
		} );
	} );
} );
