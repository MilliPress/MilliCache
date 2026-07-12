<?php
/**
 * Tests for Flags.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Engine\Flags;

describe( 'Flags', function () {

	describe( 'single site', function () {
		beforeEach( function () {
			$this->manager = new Flags();
		} );

		it( 'adds flags', function () {
			$this->manager->add( 'post:123' );
			$this->manager->add( 'home' );

			expect( $this->manager->get_all() )->toBe( array( 'post:123', 'home' ) );
		} );

		it( 'prevents duplicate flags', function () {
			$this->manager->add( 'post:123' );
			$this->manager->add( 'post:123' );
			$this->manager->add( 'post:123' );

			expect( $this->manager->get_all() )->toBe( array( 'post:123' ) );
		} );

		it( 'removes flags', function () {
			$this->manager->add( 'post:123' );
			$this->manager->add( 'home' );
			$this->manager->remove( 'post:123' );

			expect( $this->manager->get_all() )->toBe( array( 'home' ) );
		} );

		it( 'clears all flags', function () {
			$this->manager->add( 'post:123' );
			$this->manager->add( 'home' );
			$this->manager->clear();

			expect( $this->manager->get_all() )->toBeArray()->toBeEmpty();
		} );

		it( 'adds key prefix (no-op in single site)', function () {
			expect( $this->manager->add_key_prefix( 'post:123' ) )->toBe( 'post:123' );
		} );

		it( 'prefixes array of flags', function () {
			$flags = array( 'post:123', 'home', 'archive:category:5' );
			$prefixed = $this->manager->prefix( $flags );

			expect( $prefixed )->toBe( $flags );
		} );

		it( 'handles string input in prefix', function () {
			$prefixed = $this->manager->prefix( 'single-flag' );

			expect( $prefixed )->toBe( array( 'single-flag' ) );
		} );
	} );

	describe( 'real-world scenarios', function () {
		it( 'manages post-related flags', function () {
			$manager = new Flags();

			$manager->add( 'post:123' );
			$manager->add( 'archive:post' );
			$manager->add( 'archive:category:5' );
			$manager->add( 'archive:author:10' );
			$manager->add( 'feed' );

			$flags = $manager->get_all();

			expect( $flags )->toContain( 'post:123' );
			expect( $flags )->toContain( 'archive:post' );
			expect( $flags )->toContain( 'archive:category:5' );
			expect( $flags )->toContain( 'archive:author:10' );
			expect( $flags )->toContain( 'feed' );
			expect( count( $flags ) )->toBe( 5 );
		} );

		it( 'manages URL hash flags', function () {
			$manager = new Flags();

			$manager->add( 'url:abc123def456' );

			expect( $manager->get_all() )->toContain( 'url:abc123def456' );
		} );

		it( 'removes specific flag among many', function () {
			$manager = new Flags();

			$manager->add( 'post:1' );
			$manager->add( 'post:2' );
			$manager->add( 'post:3' );
			$manager->remove( 'post:2' );

			$flags = $manager->get_all();

			expect( $flags )->toContain( 'post:1' );
			expect( $flags )->not->toContain( 'post:2' );
			expect( $flags )->toContain( 'post:3' );
		} );
	} );

	describe( 'detect_prefix', function () {
		// The url flag is stored UNPREFIXED; the site prefix lives on the
		// content/fallback flags (real shapes, verified against live Redis).
		it( 'returns empty prefix on single-site', function () {
			expect( Flags::detect_prefix( array( 'url:abc123', 'home', 'archive:post' ) ) )->toBe( '' );
		} );

		it( 'returns the site prefix on multisite, ignoring the unprefixed url flag', function () {
			expect( Flags::detect_prefix( array( 'url:abc123', '1:home', '1:archive:post' ) ) )->toBe( '1:' );
		} );

		it( 'returns the network and site prefix on multi-network', function () {
			expect( Flags::detect_prefix( array( 'url:abc123', '1:2:home', '1:2:archive:post' ) ) )->toBe( '1:2:' );
		} );

		it( 'anchors on the prefixed fallback flag in the minimal case', function () {
			expect( Flags::detect_prefix( array( 'url:abc123', '3:flag' ) ) )->toBe( '3:' );
		} );

		it( 'takes the shortest run, so a numeric-named flag cannot lengthen it', function () {
			// `2:2024:archive` starts numeric after the prefix; the real `2:`
			// from `2:home` must still win (shortest run).
			expect( Flags::detect_prefix( array( 'url:abc', '2:home', '2:2024:archive' ) ) )->toBe( '2:' );
		} );

		it( 'returns empty when only the unprefixed url flag is present', function () {
			expect( Flags::detect_prefix( array( 'url:abc123' ) ) )->toBe( '' );
		} );
	} );
} );
