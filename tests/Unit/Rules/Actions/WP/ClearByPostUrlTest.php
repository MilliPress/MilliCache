<?php
/**
 * Tests for ClearByPostUrl Action.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Rules\Actions\WP\ClearByPostUrl;
use MilliCache\Deps\MilliRules\Context;

// Define WordPress functions for testing if they don't exist.
if ( ! function_exists( 'get_post' ) ) {
	function get_post( $post ) {
		if ( is_object( $post ) && $post instanceof WP_Post ) {
			return $post;
		}

		if ( is_numeric( $post ) && $post > 0 ) {
			$mock_post     = Mockery::mock( 'WP_Post' );
			$mock_post->ID = (int) $post;
			return $mock_post;
		}

		return null;
	}
}

if ( ! function_exists( 'get_permalink' ) ) {
	function get_permalink( $post ) {
		if ( ! $post instanceof WP_Post ) {
			return false;
		}

		// Return a test permalink based on post ID.
		return 'https://example.com/post-' . $post->ID . '/';
	}
}

describe( 'ClearByPostUrl Action', function () {

	it( 'returns correct action type', function () {
		$context = Mockery::mock( Context::class );
		$action  = new ClearByPostUrl( array( 'type' => 'clear_by_post_url' ), $context );
		expect( $action->get_type() )->toBe( 'clear_by_post_url' );
	} );

	it( 'has executable interface', function () {
		$context = Mockery::mock( Context::class );
		$action  = new ClearByPostUrl( array( 'type' => 'clear_by_post_url' ), $context );
		expect( method_exists( $action, 'execute' ) )->toBeTrue();
	} );

	it( 'executes with post ID and clears by permalink URL', function () {
		// Mock Engine to expect clear_cache_by_urls call.
		$engine = Mockery::mock( 'alias:MilliCache\Engine' );
		$engine->shouldReceive( 'clear_cache_by_urls' )
			->once()
			->with( Mockery::on( function ( $urls ) {
				return is_array( $urls ) && count( $urls ) === 1 && str_contains( $urls[0], 'post-123' );
			} ) );

		$context = Mockery::mock( Context::class );
		$action  = new ClearByPostUrl( array( 'type' => 'clear_by_post_url', 0 => 123 ), $context );
		$action->execute( $context );

		expect( true )->toBeTrue();
	} );

	it( 'executes with WP_Post object and clears by permalink URL', function () {
		// Create a WP_Post object.
		$post     = Mockery::mock( 'WP_Post' );
		$post->ID = 456;

		// Mock Engine to expect clear_cache_by_urls call.
		$engine = Mockery::mock( 'alias:MilliCache\Engine' );
		$engine->shouldReceive( 'clear_cache_by_urls' )
			->once()
			->with( Mockery::on( function ( $urls ) {
				return is_array( $urls ) && count( $urls ) === 1 && str_contains( $urls[0], 'post-456' );
			} ) );

		$context = Mockery::mock( Context::class );
		$action  = new ClearByPostUrl( array( 'type' => 'clear_by_post_url', 0 => $post ), $context );
		$action->execute( $context );

		expect( true )->toBeTrue();
	} );

	it( 'handles invalid post ID gracefully', function () {
		// Engine should NOT be called for negative post IDs.
		$engine = Mockery::mock( 'alias:MilliCache\Engine' );
		$engine->shouldReceive( 'clear_cache_by_urls' )->never();

		$context = Mockery::mock( Context::class );
		$action  = new ClearByPostUrl( array( 'type' => 'clear_by_post_url', 0 => -1 ), $context );
		$action->execute( $context );

		expect( true )->toBeTrue();
	} );

	it( 'handles zero post ID gracefully', function () {
		// Engine should NOT be called.
		$engine = Mockery::mock( 'alias:MilliCache\Engine' );
		$engine->shouldReceive( 'clear_cache_by_urls' )->never();

		$context = Mockery::mock( Context::class );
		$action  = new ClearByPostUrl( array( 'type' => 'clear_by_post_url', 0 => 0 ), $context );
		$action->execute( $context );

		expect( true )->toBeTrue();
	} );

	it( 'handles null post ID gracefully', function () {
		// Engine should NOT be called.
		$engine = Mockery::mock( 'alias:MilliCache\Engine' );
		$engine->shouldReceive( 'clear_cache_by_urls' )->never();

		$context = Mockery::mock( Context::class );
		$action  = new ClearByPostUrl( array( 'type' => 'clear_by_post_url', 0 => null ), $context );
		$action->execute( $context );

		expect( true )->toBeTrue();
	} );

	it( 'handles string post ID conversion', function () {
		// Mock Engine to expect clear_cache_by_urls call.
		$engine = Mockery::mock( 'alias:MilliCache\Engine' );
		$engine->shouldReceive( 'clear_cache_by_urls' )
			->once()
			->with( Mockery::on( function ( $urls ) {
				return is_array( $urls ) && count( $urls ) === 1 && str_contains( $urls[0], 'post-555' );
			} ) );

		$context = Mockery::mock( Context::class );
		$action  = new ClearByPostUrl( array( 'type' => 'clear_by_post_url', 0 => '555' ), $context );
		$action->execute( $context );

		expect( true )->toBeTrue();
	} );
} );
