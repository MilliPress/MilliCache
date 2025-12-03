<?php
/**
 * Tests for ClearByUrl Action.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Rules\Actions\WP\ClearByUrl;
use MilliCache\Deps\MilliRules\Context;

describe( 'ClearByUrl Action', function () {

	it( 'returns correct action type', function () {
		$context = Mockery::mock( Context::class );
		$action  = new ClearByUrl( array( 'type' => 'clear_by_url' ), $context );
		expect( $action->get_type() )->toBe( 'clear_by_url' );
	} );

	it( 'has executable interface', function () {
		$context = Mockery::mock( Context::class );
		$action  = new ClearByUrl( array( 'type' => 'clear_by_url' ), $context );
		expect( method_exists( $action, 'execute' ) )->toBeTrue();
	} );

	it( 'executes with single URL', function () {
		$engine = Mockery::mock( 'alias:MilliCache\Engine' );
		$engine->shouldReceive( 'clear_cache_by_urls' )->once()->with( array( 'https://example.com/test' ) );

		$context = Mockery::mock( Context::class );
		$action  = new ClearByUrl( array( 'type' => 'clear_by_url', 0 => 'https://example.com/test' ), $context );
		$action->execute( $context );

		expect( true )->toBeTrue();
	} );

	it( 'executes with multiple URLs', function () {
		$urls = array( 'https://example.com/test', 'https://example.com/test2' );

		$engine = Mockery::mock( 'alias:MilliCache\Engine' );
		$engine->shouldReceive( 'clear_cache_by_urls' )->once()->with( $urls );

		$context = Mockery::mock( Context::class );
		$action  = new ClearByUrl( array( 'type' => 'clear_by_url', 0 => $urls ), $context );
		$action->execute( $context );

		expect( true )->toBeTrue();
	} );

	it( 'handles empty URL gracefully', function () {
		$engine = Mockery::mock( 'alias:MilliCache\Engine' );
		$engine->shouldReceive( 'clear_cache_by_urls' )->never();

		$context = Mockery::mock( Context::class );
		$action  = new ClearByUrl( array( 'type' => 'clear_by_url', 0 => '' ), $context );
		$action->execute( $context );

		expect( true )->toBeTrue();
	} );

	it( 'handles null URL gracefully', function () {
		$engine = Mockery::mock( 'alias:MilliCache\Engine' );
		$engine->shouldReceive( 'clear_cache_by_urls' )->never();

		$context = Mockery::mock( Context::class );
		$action  = new ClearByUrl( array( 'type' => 'clear_by_url', 0 => null ), $context );
		$action->execute( $context );

		expect( true )->toBeTrue();
	} );

	it( 'filters out empty URLs from array', function () {
		$urls = array( 'https://example.com/test', '', null, 'https://example.com/test2' );

		$engine = Mockery::mock( 'alias:MilliCache\Engine' );
		$engine->shouldReceive( 'clear_cache_by_urls' )->once()->with( array( 0 => 'https://example.com/test', 3 => 'https://example.com/test2' ) );

		$context = Mockery::mock( Context::class );
		$action  = new ClearByUrl( array( 'type' => 'clear_by_url', 0 => $urls ), $context );
		$action->execute( $context );

		expect( true )->toBeTrue();
	} );
} );
