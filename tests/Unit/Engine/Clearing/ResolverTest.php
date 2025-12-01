<?php
/**
 * Tests for Cache Resolver.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Engine\Clearing\Resolver;
use MilliCache\Engine\Multisite;
use MilliCache\Engine\Request\Handler as RequestHandler;
use MilliCache\Engine\Cache\Config;

// Mock WordPress functions.
if ( ! function_exists( 'trailingslashit' ) ) {
	function trailingslashit( $string ) {
		return rtrim( $string, '/\\' ) . '/';
	}
}

if ( ! function_exists( 'untrailingslashit' ) ) {
	function untrailingslashit( $string ) {
		return rtrim( $string, '/\\' );
	}
}

if ( ! function_exists( 'get_current_network_id' ) ) {
	function get_current_network_id() {
		return 1;
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

	// Use real instances (final classes, control via WordPress functions).
	$this->request_handler = new RequestHandler( $config );
	$this->multisite = new Multisite();
} );

describe( 'Clearing Resolver', function () {

	describe( 'constructor', function () {
		it( 'creates resolver with dependencies', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );

			expect( $resolver )->toBeInstanceOf( Resolver::class );
		} );
	} );

	describe( 'resolve', function () {
		it( 'converts single target to array', function () {
			$this->request_handler->shouldReceive( 'get_url_hash' )->andReturn( 'hash123' );

			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve( 'https://example.com/test' );

			expect( $flags )->toBeArray();
			expect( count( $flags ) )->toBe( 2 ); // With and without trailing slash
		} );

		it( 'resolves URL targets', function () {
			$this->request_handler->shouldReceive( 'get_url_hash' )->andReturn( 'hash123' );

			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve( 'https://example.com/page' );

			expect( $flags )->toContain( 'url:hash123' );
		} );

		it( 'resolves post ID targets (numeric)', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve( 123 );

			expect( $flags )->toContain( 'post:123' );
			expect( $flags )->toContain( 'feed' );
		} );

		it( 'resolves flag targets (strings)', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve( 'custom-flag' );

			expect( $flags )->toContain( 'custom-flag' );
		} );

		it( 'handles mixed target types', function () {
			$this->request_handler->shouldReceive( 'get_url_hash' )->andReturn( 'hash123' );

			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve( array(
				'https://example.com/page',
				456,
				'my-flag',
			) );

			expect( count( $flags ) )->toBeGreaterThan( 3 );
			expect( $flags )->toContain( 'post:456' );
			expect( $flags )->toContain( 'my-flag' );
		} );
	} );

	describe( 'resolve_url', function () {
		it( 'generates hash for URL with trailing slash', function () {
			$this->request_handler->shouldReceive( 'get_url_hash' )
				->with( 'https://example.com/test/' )
				->andReturn( 'hash1' );
			$this->request_handler->shouldReceive( 'get_url_hash' )
				->with( 'https://example.com/test' )
				->andReturn( 'hash2' );

			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve_url( 'https://example.com/test' );

			expect( $flags )->toContain( 'url:hash1' );
		} );

		it( 'generates hash for URL without trailing slash', function () {
			$this->request_handler->shouldReceive( 'get_url_hash' )
				->andReturn( 'hash1', 'hash2' );

			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve_url( 'https://example.com/test' );

			expect( $flags )->toContain( 'url:hash2' );
		} );

		it( 'returns both variations', function () {
			$this->request_handler->shouldReceive( 'get_url_hash' )
				->andReturn( 'hash1', 'hash2' );

			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve_url( 'https://example.com/test' );

			expect( count( $flags ) )->toBe( 2 );
		} );

		it( 'uses RequestHandler for hashing', function () {
			$this->request_handler->shouldReceive( 'get_url_hash' )
				->twice()
				->andReturn( 'hash123' );

			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$resolver->resolve_url( 'https://example.com/test' );

			expect( true )->toBeTrue(); // Verified via shouldReceive
		} );
	} );

	describe( 'resolve_post_id', function () {
		it( 'returns post:ID flag', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve_post_id( 789 );

			expect( $flags )->toContain( 'post:789' );
		} );

		it( 'includes feed flag', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve_post_id( 123 );

			expect( $flags )->toContain( 'feed' );
		} );

		it( 'returns array of flags', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve_post_id( 456 );

			expect( $flags )->toBeArray();
			expect( count( $flags ) )->toBe( 2 );
		} );
	} );

	describe( 'resolve_site_ids', function () {
		it( 'converts single site_id to array', function () {
			$this->multisite->shouldReceive( 'get_flag_prefix' )->andReturn( '1:1:' );

			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve_site_ids( 1 );

			expect( $flags )->toBeArray();
			expect( count( $flags ) )->toBe( 1 );
		} );

		it( 'generates flag with multisite prefix', function () {
			$this->multisite->shouldReceive( 'get_flag_prefix' )
				->with( 5, null )
				->andReturn( '1:5:' );

			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve_site_ids( 5 );

			expect( $flags )->toContain( '1:5:*' );
		} );

		it( 'adds wildcard suffix', function () {
			$this->multisite->shouldReceive( 'get_flag_prefix' )->andReturn( '1:1:' );

			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve_site_ids( 1 );

			expect( $flags[0] )->toEndWith( '*' );
		} );

		it( 'uses provided network_id', function () {
			$this->multisite->shouldReceive( 'get_flag_prefix' )
				->with( 1, 2 )
				->andReturn( '2:1:' );

			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve_site_ids( 1, 2 );

			expect( $flags )->toContain( '2:1:*' );
		} );

		it( 'handles null site_ids', function () {
			$this->multisite->shouldReceive( 'get_flag_prefix' )
				->with( null, null )
				->andReturn( '1:1:' );

			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve_site_ids();

			expect( $flags )->toContain( '1:1:*' );
		} );
	} );

	describe( 'resolve_network_to_sites', function () {
		it( 'returns [1] when multisite disabled', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( false );

			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$site_ids = $resolver->resolve_network_to_sites();

			expect( $site_ids )->toBe( array( 1 ) );
		} );

		it( 'gets sites from multisite helper', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( true );
			$this->multisite->shouldReceive( 'get_site_ids' )
				->with( 1 )
				->andReturn( array( 1, 2, 3 ) );

			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$site_ids = $resolver->resolve_network_to_sites();

			expect( $site_ids )->toBe( array( 1, 2, 3 ) );
		} );

		it( 'uses provided network_id', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( true );
			$this->multisite->shouldReceive( 'get_site_ids' )
				->with( 2 )
				->andReturn( array( 4, 5 ) );

			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$site_ids = $resolver->resolve_network_to_sites( 2 );

			expect( $site_ids )->toBe( array( 4, 5 ) );
		} );

		it( 'uses current network when null', function () {
			$this->multisite->shouldReceive( 'is_enabled' )->andReturn( true );
			$this->multisite->shouldReceive( 'get_site_ids' )
				->with( 1 ) // From get_current_network_id()
				->andReturn( array( 1 ) );

			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$resolver->resolve_network_to_sites();

			expect( true )->toBeTrue();
		} );
	} );

	describe( 'get_all_networks', function () {
		it( 'delegates to multisite helper', function () {
			$this->multisite->shouldReceive( 'get_network_ids' )
				->andReturn( array( 1, 2, 3 ) );

			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$networks = $resolver->get_all_networks();

			expect( $networks )->toBe( array( 1, 2, 3 ) );
		} );
	} );

	describe( 'is_url', function () {
		it( 'returns true for valid URLs', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );

			expect( $resolver->is_url( 'https://example.com' ) )->toBeTrue();
			expect( $resolver->is_url( 'http://example.com/path' ) )->toBeTrue();
		} );

		it( 'returns false for non-strings', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );

			expect( $resolver->is_url( 123 ) )->toBeFalse();
			expect( $resolver->is_url( array() ) )->toBeFalse();
		} );

		it( 'returns false for invalid URLs', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );

			expect( $resolver->is_url( 'not-a-url' ) )->toBeFalse();
			expect( $resolver->is_url( 'test-flag' ) )->toBeFalse();
		} );

		it( 'returns false for post IDs', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );

			expect( $resolver->is_url( '123' ) )->toBeFalse();
		} );
	} );

	describe( 'is_post_id', function () {
		it( 'returns true for positive numbers', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );

			expect( $resolver->is_post_id( 1 ) )->toBeTrue();
			expect( $resolver->is_post_id( 123 ) )->toBeTrue();
			expect( $resolver->is_post_id( '456' ) )->toBeTrue(); // Numeric string
		} );

		it( 'returns false for zero', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );

			expect( $resolver->is_post_id( 0 ) )->toBeFalse();
			expect( $resolver->is_post_id( '0' ) )->toBeFalse();
		} );

		it( 'returns false for negative numbers', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );

			expect( $resolver->is_post_id( -1 ) )->toBeFalse();
			expect( $resolver->is_post_id( '-123' ) )->toBeFalse();
		} );

		it( 'returns false for non-numeric strings', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );

			expect( $resolver->is_post_id( 'test' ) )->toBeFalse();
			expect( $resolver->is_post_id( 'post-123' ) )->toBeFalse();
		} );
	} );

	describe( 'is_flag', function () {
		it( 'returns true for non-URL strings', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );

			expect( $resolver->is_flag( 'custom-flag' ) )->toBeTrue();
			expect( $resolver->is_flag( 'post:123' ) )->toBeTrue();
		} );

		it( 'returns false for URLs', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );

			expect( $resolver->is_flag( 'https://example.com' ) )->toBeFalse();
		} );

		it( 'returns false for numbers', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );

			expect( $resolver->is_flag( 123 ) )->toBeFalse();
			expect( $resolver->is_flag( '456' ) )->toBeFalse(); // Numeric string not a flag
		} );
	} );
} );
