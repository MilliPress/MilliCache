<?php
/**
 * Tests for Rule Actions.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Rules\Actions\PHP\DoCache;
use MilliCache\Rules\Actions\PHP\SetTtl;
use MilliCache\Rules\Actions\PHP\SetGrace;
use MilliCache\Rules\Actions\WP\AddFlag;
use MilliCache\Rules\Actions\WP\RemoveFlag;
use MilliCache\Rules\Actions\WP\ClearCache;
use MilliCache\Rules\Actions\WP\ClearSiteCache;
use MilliCache\Deps\MilliRules\Context;

describe( 'Rule Actions', function () {

	describe( 'DoCache Action', function () {
		it( 'returns correct action type', function () {
			$context = Mockery::mock( Context::class );
			$action  = new DoCache( array( 'type' => 'do_cache' ), $context );
			expect( $action->get_type() )->toBe( 'do_cache' );
		} );

		it( 'has executable interface', function () {
			$context = Mockery::mock( Context::class );
			$action  = new DoCache( array( 'type' => 'do_cache' ), $context );
			expect( method_exists( $action, 'execute' ) )->toBeTrue();
		} );

		it( 'executes with context', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'set_cache_decision' )->once()->with( true, Mockery::type( 'string' ) );

			$context = Mockery::mock( Context::class );
			$action  = new DoCache( array( 'type' => 'do_cache', 0 => true, 1 => 'Test reason' ), $context );
			$action->execute( $context );

			expect( true )->toBeTrue();
		} );

		it( 'handles cache=false', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'set_cache_decision' )->once()->with( false, Mockery::type( 'string' ) );

			$context = Mockery::mock( Context::class );
			$action  = new DoCache( array( 'type' => 'do_cache', 0 => false, 1 => 'No cache' ), $context );
			$action->execute( $context );

			expect( true )->toBeTrue();
		} );
	} );

	describe( 'SetTtl Action', function () {
		it( 'returns correct action type', function () {
			$context = Mockery::mock( Context::class );
			$action  = new SetTtl( array( 'type' => 'set_ttl' ), $context );
			expect( $action->get_type() )->toBe( 'set_ttl' );
		} );

		it( 'has executable interface', function () {
			$context = Mockery::mock( Context::class );
			$action  = new SetTtl( array( 'type' => 'set_ttl' ), $context );
			expect( method_exists( $action, 'execute' ) )->toBeTrue();
		} );

		it( 'executes with context', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'set_ttl' )->once()->with( 3600 );

			$context = Mockery::mock( Context::class );
			$action  = new SetTtl( array( 'type' => 'set_ttl', 0 => 3600 ), $context );
			$action->execute( $context );

			expect( true )->toBeTrue();
		} );

		it( 'passes TTL value to Engine', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'set_ttl' )->once()->with( 0 );

			$context = Mockery::mock( Context::class );
			$action  = new SetTtl( array( 'type' => 'set_ttl', 0 => 0 ), $context );
			$action->execute( $context );

			expect( true )->toBeTrue();
		} );
	} );

	describe( 'SetGrace Action', function () {
		it( 'returns correct action type', function () {
			$context = Mockery::mock( Context::class );
			$action  = new SetGrace( array( 'type' => 'set_grace' ), $context );
			expect( $action->get_type() )->toBe( 'set_grace' );
		} );

		it( 'has executable interface', function () {
			$context = Mockery::mock( Context::class );
			$action  = new SetGrace( array( 'type' => 'set_grace' ), $context );
			expect( method_exists( $action, 'execute' ) )->toBeTrue();
		} );

		it( 'executes with context', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'set_grace' )->once()->with( 600 );

			$context = Mockery::mock( Context::class );
			$action  = new SetGrace( array( 'type' => 'set_grace', 0 => 600 ), $context );
			$action->execute( $context );

			expect( true )->toBeTrue();
		} );
	} );

	describe( 'AddFlag Action', function () {
		it( 'returns correct action type', function () {
			$context = Mockery::mock( Context::class );
			$action  = new AddFlag( array( 'type' => 'add_flag' ), $context );
			expect( $action->get_type() )->toBe( 'add_flag' );
		} );

		it( 'has executable interface', function () {
			$context = Mockery::mock( Context::class );
			$action  = new AddFlag( array( 'type' => 'add_flag' ), $context );
			expect( method_exists( $action, 'execute' ) )->toBeTrue();
		} );

		it( 'executes with context', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'add_flag' )->once()->with( 'test-flag' );

			$context = Mockery::mock( Context::class );
			$action  = new AddFlag( array( 'type' => 'add_flag', 0 => 'test-flag' ), $context );
			$action->execute( $context );

			expect( true )->toBeTrue();
		} );

		it( 'passes empty flag to Engine', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'add_flag' )->once()->with( '' );

			$context = Mockery::mock( Context::class );
			$action  = new AddFlag( array( 'type' => 'add_flag', 0 => '' ), $context );
			$action->execute( $context );

			expect( true )->toBeTrue();
		} );

		it( 'converts null flag to empty string', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'add_flag' )->once()->with( '' );

			$context = Mockery::mock( Context::class );
			$action  = new AddFlag( array( 'type' => 'add_flag', 0 => null ), $context );
			$action->execute( $context );

			expect( true )->toBeTrue();
		} );
	} );

	describe( 'RemoveFlag Action', function () {
		it( 'returns correct action type', function () {
			$context = Mockery::mock( Context::class );
			$action  = new RemoveFlag( array( 'type' => 'remove_flag' ), $context );
			expect( $action->get_type() )->toBe( 'remove_flag' );
		} );

		it( 'has executable interface', function () {
			$context = Mockery::mock( Context::class );
			$action  = new RemoveFlag( array( 'type' => 'remove_flag' ), $context );
			expect( method_exists( $action, 'execute' ) )->toBeTrue();
		} );

		it( 'executes with context', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'remove_flag' )->once()->with( 'test-flag' );

			$context = Mockery::mock( Context::class );
			$action  = new RemoveFlag( array( 'type' => 'remove_flag', 0 => 'test-flag' ), $context );
			$action->execute( $context );

			expect( true )->toBeTrue();
		} );

		it( 'passes empty flag to Engine', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'remove_flag' )->once()->with( '' );

			$context = Mockery::mock( Context::class );
			$action  = new RemoveFlag( array( 'type' => 'remove_flag', 0 => '' ), $context );
			$action->execute( $context );

			expect( true )->toBeTrue();
		} );
	} );

	describe( 'ClearCache Action', function () {
		it( 'returns correct action type', function () {
			$context = Mockery::mock( Context::class );
			$action  = new ClearCache( array( 'type' => 'clear_cache' ), $context );
			expect( $action->get_type() )->toBe( 'clear_cache' );
		} );

		it( 'has executable interface', function () {
			$context = Mockery::mock( Context::class );
			$action  = new ClearCache( array( 'type' => 'clear_cache' ), $context );
			expect( method_exists( $action, 'execute' ) )->toBeTrue();
		} );

		it( 'executes with context', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'clear_cache_by_targets' )->once()->with( array( 'test-flag' ), false );

			$context = Mockery::mock( Context::class );
			$action  = new ClearCache( array( 'type' => 'clear_cache', 0 => array( 'test-flag' ) ), $context );
			$action->execute( $context );

			expect( true )->toBeTrue();
		} );

		it( 'handles expire parameter', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'clear_cache_by_targets' )->once()->with( array( 'test-flag' ), true );

			$context = Mockery::mock( Context::class );
			$action  = new ClearCache( array( 'type' => 'clear_cache', 0 => array( 'test-flag' ), 1 => true ), $context );
			$action->execute( $context );

			expect( true )->toBeTrue();
		} );
	} );

	describe( 'ClearSiteCache Action', function () {
		it( 'returns correct action type', function () {
			$context = Mockery::mock( Context::class );
			$action  = new ClearSiteCache( array( 'type' => 'clear_site_cache' ), $context );
			expect( $action->get_type() )->toBe( 'clear_site_cache' );
		} );

		it( 'has executable interface', function () {
			$context = Mockery::mock( Context::class );
			$action  = new ClearSiteCache( array( 'type' => 'clear_site_cache' ), $context );
			expect( method_exists( $action, 'execute' ) )->toBeTrue();
		} );

		it( 'executes with context', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'clear_cache_by_site_ids' )->once()->with( array(), null, false );

			$context = Mockery::mock( Context::class );
			$action  = new ClearSiteCache( array( 'type' => 'clear_site_cache' ), $context );
			$action->execute( $context );

			expect( true )->toBeTrue();
		} );

		it( 'handles site_ids parameter', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'clear_cache_by_site_ids' )->once()->with( array( 5 ), null, false );

			$context = Mockery::mock( Context::class );
			$action  = new ClearSiteCache( array( 'type' => 'clear_site_cache', 0 => array( 5 ) ), $context );
			$action->execute( $context );

			expect( true )->toBeTrue();
		} );

		it( 'handles network_id and expire parameters', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$engine->shouldReceive( 'clear_cache_by_site_ids' )->once()->with( array( 5 ), 1, true );

			$context = Mockery::mock( Context::class );
			$action  = new ClearSiteCache( array( 'type' => 'clear_site_cache', 0 => array( 5 ), 1 => 1, 2 => true ), $context );
			$action->execute( $context );

			expect( true )->toBeTrue();
		} );
	} );

	describe( 'Action Interface Consistency', function () {
		it( 'all PHP actions extend BaseAction', function () {
			$context = Mockery::mock( Context::class );
			expect( new DoCache( array( 'type' => 'do_cache' ), $context ) )->toBeInstanceOf( 'MilliCache\Deps\MilliRules\Actions\BaseAction' );
			expect( new SetTtl( array( 'type' => 'set_ttl' ), $context ) )->toBeInstanceOf( 'MilliCache\Deps\MilliRules\Actions\BaseAction' );
			expect( new SetGrace( array( 'type' => 'set_grace' ), $context ) )->toBeInstanceOf( 'MilliCache\Deps\MilliRules\Actions\BaseAction' );
		} );

		it( 'all WP actions extend BaseAction', function () {
			$context = Mockery::mock( Context::class );
			expect( new AddFlag( array( 'type' => 'add_flag' ), $context ) )->toBeInstanceOf( 'MilliCache\Deps\MilliRules\Actions\BaseAction' );
			expect( new RemoveFlag( array( 'type' => 'remove_flag' ), $context ) )->toBeInstanceOf( 'MilliCache\Deps\MilliRules\Actions\BaseAction' );
			expect( new ClearCache( array( 'type' => 'clear_cache' ), $context ) )->toBeInstanceOf( 'MilliCache\Deps\MilliRules\Actions\BaseAction' );
			expect( new ClearSiteCache( array( 'type' => 'clear_site_cache' ), $context ) )->toBeInstanceOf( 'MilliCache\Deps\MilliRules\Actions\BaseAction' );
		} );

		it( 'all actions have unique type identifiers', function () {
			$context = Mockery::mock( Context::class );
			$types   = array(
				( new DoCache( array( 'type' => 'do_cache' ), $context ) )->get_type(),
				( new SetTtl( array( 'type' => 'set_ttl' ), $context ) )->get_type(),
				( new SetGrace( array( 'type' => 'set_grace' ), $context ) )->get_type(),
				( new AddFlag( array( 'type' => 'add_flag' ), $context ) )->get_type(),
				( new RemoveFlag( array( 'type' => 'remove_flag' ), $context ) )->get_type(),
				( new ClearCache( array( 'type' => 'clear_cache' ), $context ) )->get_type(),
				( new ClearSiteCache( array( 'type' => 'clear_site_cache' ), $context ) )->get_type(),
			);

			// All types should be unique.
			expect( count( $types ) )->toBe( count( array_unique( $types ) ) );
		} );
	} );
} );
