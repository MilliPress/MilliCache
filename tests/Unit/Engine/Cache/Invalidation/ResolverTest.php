<?php
/**
 * Tests for Cache Invalidation Resolver.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Engine\Cache\Config;
use MilliCache\Engine\Cache\Invalidation\Resolver;
use MilliCache\Engine\Request\Processor as RequestManager;
use MilliCache\Engine\Utilities\Multisite;

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
		global $test_current_network_id;
		return $test_current_network_id ?? 1;
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

	// Use real instances (final classes, control via WordPress functions).
	$this->request_handler = new RequestManager( $config );
	$this->multisite = new Multisite();
} );

describe( 'Invalidation Resolver', function () {

	describe( 'constructor', function () {
		it( 'creates resolver with dependencies', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );

			expect( $resolver )->toBeInstanceOf( Resolver::class );
		} );
	} );

	describe( 'resolve', function () {
		it( 'converts single target to array', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve( 'https://example.com/test' );

			expect( $flags )->toBeArray();
			expect( count( $flags ) )->toBe( 2 ); // With and without trailing slash
		} );

		it( 'resolves URL targets', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve( 'https://example.com/page' );

			// Check that at least one url: flag was generated.
			$url_flags = array_filter( $flags, fn( $f ) => str_starts_with( $f, 'url:' ) );
			expect( count( $url_flags ) )->toBeGreaterThan( 0 );
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
			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve_url( 'https://example.com/test' );

			// Should contain 2 url: flags (with and without trailing slash).
			$url_flags = array_filter( $flags, fn( $f ) => str_starts_with( $f, 'url:' ) );
			expect( count( $url_flags ) )->toBe( 2 );
		} );

		it( 'generates hash for URL without trailing slash', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve_url( 'https://example.com/test' );

			// Should have URL hashes.
			expect( count( $flags ) )->toBe( 2 );
		} );

		it( 'returns both variations', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve_url( 'https://example.com/test' );

			expect( count( $flags ) )->toBe( 2 );
		} );

		it( 'uses RequestManager for hashing', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );
			$flags = $resolver->resolve_url( 'https://example.com/test' );

			// Both should be url: prefixed.
			expect( $flags[0] )->toStartWith( 'url:' );
			expect( $flags[1] )->toStartWith( 'url:' );
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
			global $test_is_multisite;
			$test_is_multisite = true;

			$multisite = new Multisite();
			$resolver = new Resolver( $this->request_handler, $multisite );
			$flags = $resolver->resolve_site_ids( 1 );

			expect( $flags )->toBeArray();
			expect( count( $flags ) )->toBe( 1 );
		} );

		it( 'generates flag with multisite prefix', function () {
			global $test_is_multisite;
			$test_is_multisite = true;

			$multisite = new Multisite();
			$resolver = new Resolver( $this->request_handler, $multisite );
			$flags = $resolver->resolve_site_ids( 5 );

			// With single network, format is site_id:*.
			expect( $flags[0] )->toBe( '5:*' );
		} );

		it( 'adds wildcard suffix', function () {
			global $test_is_multisite;
			$test_is_multisite = true;

			$multisite = new Multisite();
			$resolver = new Resolver( $this->request_handler, $multisite );
			$flags = $resolver->resolve_site_ids( 1 );

			expect( $flags[0] )->toEndWith( '*' );
		} );

		it( 'uses provided network_id when multiple networks exist', function () {
			global $test_is_multisite, $test_networks;
			$test_is_multisite = true;
			$test_networks = array( 1, 2 ); // Multiple networks.

			$multisite = new Multisite();
			$resolver = new Resolver( $this->request_handler, $multisite );
			$flags = $resolver->resolve_site_ids( 1, 2 );

			// With multiple networks, format is network:site:*.
			expect( $flags[0] )->toStartWith( '2:' );
		} );

		it( 'handles null site_ids', function () {
			global $test_is_multisite;
			$test_is_multisite = true;

			$multisite = new Multisite();
			$resolver = new Resolver( $this->request_handler, $multisite );
			$flags = $resolver->resolve_site_ids();

			// With single network, format is site_id:*.
			expect( $flags[0] )->toBe( '1:*' );
		} );
	} );

	describe( 'resolve_network_to_sites', function () {
		it( 'returns [1] when multisite disabled', function () {
			global $test_is_multisite;
			$test_is_multisite = false;

			$multisite = new Multisite();
			$resolver = new Resolver( $this->request_handler, $multisite );
			$site_ids = $resolver->resolve_network_to_sites();

			expect( $site_ids )->toBe( array( 1 ) );
		} );

		it( 'gets sites from multisite helper', function () {
			global $test_is_multisite, $test_sites;
			$test_is_multisite = true;
			$test_sites = array( 1, 2, 3 );

			$multisite = new Multisite();
			$resolver = new Resolver( $this->request_handler, $multisite );
			$site_ids = $resolver->resolve_network_to_sites();

			expect( $site_ids )->toBe( array( 1, 2, 3 ) );
		} );

		it( 'uses provided network_id', function () {
			global $test_is_multisite, $test_sites;
			$test_is_multisite = true;
			$test_sites = array( 4, 5 );

			$multisite = new Multisite();
			$resolver = new Resolver( $this->request_handler, $multisite );
			$site_ids = $resolver->resolve_network_to_sites( 2 );

			expect( $site_ids )->toBe( array( 4, 5 ) );
		} );

		it( 'uses current network when null', function () {
			global $test_is_multisite, $test_sites;
			$test_is_multisite = true;
			$test_sites = array( 1 );

			$multisite = new Multisite();
			$resolver = new Resolver( $this->request_handler, $multisite );
			$site_ids = $resolver->resolve_network_to_sites();

			expect( $site_ids )->toBe( array( 1 ) );
		} );
	} );

	describe( 'get_all_networks', function () {
		it( 'delegates to multisite helper', function () {
			global $test_is_multisite, $test_networks;
			$test_is_multisite = true;
			$test_networks = array( 1, 2, 3 );

			$multisite = new Multisite();
			$resolver = new Resolver( $this->request_handler, $multisite );
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

		it( 'returns false for non-strings', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );

			expect( $resolver->is_flag( 123 ) )->toBeFalse();
			expect( $resolver->is_flag( array() ) )->toBeFalse();
		} );

		it( 'returns true for numeric strings (they are strings)', function () {
			$resolver = new Resolver( $this->request_handler, $this->multisite );

			// Numeric strings are technically strings (not URLs), so they are flags.
			expect( $resolver->is_flag( '456' ) )->toBeTrue();
		} );
	} );
} );
