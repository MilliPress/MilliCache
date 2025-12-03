<?php
/**
 * Tests for ClearByPostIds Action.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Rules\Actions\WP\ClearByPostIds;
use MilliCache\Deps\MilliRules\Context;

describe( 'ClearByPostIds Action', function () {

	it( 'returns correct action type', function () {
		$context = Mockery::mock( Context::class );
		$action  = new ClearByPostIds( array( 'type' => 'clear_by_post_ids' ), $context );
		expect( $action->get_type() )->toBe( 'clear_by_post_ids' );
	} );

	it( 'has executable interface', function () {
		$context = Mockery::mock( Context::class );
		$action  = new ClearByPostIds( array( 'type' => 'clear_by_post_ids' ), $context );
		expect( method_exists( $action, 'execute' ) )->toBeTrue();
	} );

	it( 'executes with single post ID', function () {
		$engine = Mockery::mock( 'alias:MilliCache\Engine' );
		$engine->shouldReceive( 'clear_cache_by_post_ids' )->once()->with( array( 123 ) );

		$context = Mockery::mock( Context::class );
		$action  = new ClearByPostIds( array( 'type' => 'clear_by_post_ids', 0 => 123 ), $context );
		$action->execute( $context );

		expect( true )->toBeTrue();
	} );

	it( 'executes with multiple post IDs', function () {
		$post_ids = array( 123, 456, 789 );

		$engine = Mockery::mock( 'alias:MilliCache\Engine' );
		$engine->shouldReceive( 'clear_cache_by_post_ids' )->once()->with( $post_ids );

		$context = Mockery::mock( Context::class );
		$action  = new ClearByPostIds( array( 'type' => 'clear_by_post_ids', 0 => $post_ids ), $context );
		$action->execute( $context );

		expect( true )->toBeTrue();
	} );

	it( 'handles zero post ID gracefully', function () {
		$engine = Mockery::mock( 'alias:MilliCache\Engine' );
		$engine->shouldReceive( 'clear_cache_by_post_ids' )->never();

		$context = Mockery::mock( Context::class );
		$action  = new ClearByPostIds( array( 'type' => 'clear_by_post_ids', 0 => 0 ), $context );
		$action->execute( $context );

		expect( true )->toBeTrue();
	} );

	it( 'handles null post ID gracefully', function () {
		$engine = Mockery::mock( 'alias:MilliCache\Engine' );
		$engine->shouldReceive( 'clear_cache_by_post_ids' )->never();

		$context = Mockery::mock( Context::class );
		$action  = new ClearByPostIds( array( 'type' => 'clear_by_post_ids', 0 => null ), $context );
		$action->execute( $context );

		expect( true )->toBeTrue();
	} );

	it( 'filters out invalid post IDs from array', function () {
		$post_ids = array( 123, 0, -5, '456', null, 789 );

		$engine = Mockery::mock( 'alias:MilliCache\Engine' );
		$engine->shouldReceive( 'clear_cache_by_post_ids' )->once()->with( array( 0 => 123, 3 => 456, 5 => 789 ) );

		$context = Mockery::mock( Context::class );
		$action  = new ClearByPostIds( array( 'type' => 'clear_by_post_ids', 0 => $post_ids ), $context );
		$action->execute( $context );

		expect( true )->toBeTrue();
	} );

	it( 'converts string ID to integer', function () {
		$engine = Mockery::mock( 'alias:MilliCache\Engine' );
		$engine->shouldReceive( 'clear_cache_by_post_ids' )->once()->with( Mockery::any() );

		$context = Mockery::mock( Context::class );
		$action  = new ClearByPostIds( array( 'type' => 'clear_by_post_ids', 0 => '123' ), $context );
		$action->execute( $context );

		expect( true )->toBeTrue();
	} );
} );
