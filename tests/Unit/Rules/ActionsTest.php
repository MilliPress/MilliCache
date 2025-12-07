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

/**
 * Note: The Action classes require Engine which is a final class.
 * These tests focus on verifying the class structure and signatures without executing
 * methods that depend on Engine::instance().
 */
describe( 'Rule Actions', function () {

	describe( 'DoCache Action', function () {
		it( 'class exists', function () {
			expect( class_exists( DoCache::class ) )->toBeTrue();
		} );

		it( 'extends BaseAction', function () {
			$reflection = new ReflectionClass( DoCache::class );
			expect( $reflection->isSubclassOf( 'MilliCache\Deps\MilliRules\Actions\BaseAction' ) )->toBeTrue();
		} );

		it( 'has get_type method', function () {
			expect( method_exists( DoCache::class, 'get_type' ) )->toBeTrue();
		} );

		it( 'has execute method', function () {
			expect( method_exists( DoCache::class, 'execute' ) )->toBeTrue();
		} );

		it( 'returns correct action type', function () {
			$context = Mockery::mock( Context::class );
			$action  = new DoCache( array( 'type' => 'do_cache' ), $context );
			expect( $action->get_type() )->toBe( 'do_cache' );
		} );
	} );

	describe( 'SetTtl Action', function () {
		it( 'class exists', function () {
			expect( class_exists( SetTtl::class ) )->toBeTrue();
		} );

		it( 'extends BaseAction', function () {
			$reflection = new ReflectionClass( SetTtl::class );
			expect( $reflection->isSubclassOf( 'MilliCache\Deps\MilliRules\Actions\BaseAction' ) )->toBeTrue();
		} );

		it( 'has get_type method', function () {
			expect( method_exists( SetTtl::class, 'get_type' ) )->toBeTrue();
		} );

		it( 'has execute method', function () {
			expect( method_exists( SetTtl::class, 'execute' ) )->toBeTrue();
		} );

		it( 'returns correct action type', function () {
			$context = Mockery::mock( Context::class );
			$action  = new SetTtl( array( 'type' => 'set_ttl' ), $context );
			expect( $action->get_type() )->toBe( 'set_ttl' );
		} );
	} );

	describe( 'SetGrace Action', function () {
		it( 'class exists', function () {
			expect( class_exists( SetGrace::class ) )->toBeTrue();
		} );

		it( 'extends BaseAction', function () {
			$reflection = new ReflectionClass( SetGrace::class );
			expect( $reflection->isSubclassOf( 'MilliCache\Deps\MilliRules\Actions\BaseAction' ) )->toBeTrue();
		} );

		it( 'has get_type method', function () {
			expect( method_exists( SetGrace::class, 'get_type' ) )->toBeTrue();
		} );

		it( 'has execute method', function () {
			expect( method_exists( SetGrace::class, 'execute' ) )->toBeTrue();
		} );

		it( 'returns correct action type', function () {
			$context = Mockery::mock( Context::class );
			$action  = new SetGrace( array( 'type' => 'set_grace' ), $context );
			expect( $action->get_type() )->toBe( 'set_grace' );
		} );
	} );

	describe( 'AddFlag Action', function () {
		it( 'class exists', function () {
			expect( class_exists( AddFlag::class ) )->toBeTrue();
		} );

		it( 'extends BaseAction', function () {
			$reflection = new ReflectionClass( AddFlag::class );
			expect( $reflection->isSubclassOf( 'MilliCache\Deps\MilliRules\Actions\BaseAction' ) )->toBeTrue();
		} );

		it( 'has get_type method', function () {
			expect( method_exists( AddFlag::class, 'get_type' ) )->toBeTrue();
		} );

		it( 'has execute method', function () {
			expect( method_exists( AddFlag::class, 'execute' ) )->toBeTrue();
		} );

		it( 'returns correct action type', function () {
			$context = Mockery::mock( Context::class );
			$action  = new AddFlag( array( 'type' => 'add_flag' ), $context );
			expect( $action->get_type() )->toBe( 'add_flag' );
		} );
	} );

	describe( 'RemoveFlag Action', function () {
		it( 'class exists', function () {
			expect( class_exists( RemoveFlag::class ) )->toBeTrue();
		} );

		it( 'extends BaseAction', function () {
			$reflection = new ReflectionClass( RemoveFlag::class );
			expect( $reflection->isSubclassOf( 'MilliCache\Deps\MilliRules\Actions\BaseAction' ) )->toBeTrue();
		} );

		it( 'has get_type method', function () {
			expect( method_exists( RemoveFlag::class, 'get_type' ) )->toBeTrue();
		} );

		it( 'has execute method', function () {
			expect( method_exists( RemoveFlag::class, 'execute' ) )->toBeTrue();
		} );

		it( 'returns correct action type', function () {
			$context = Mockery::mock( Context::class );
			$action  = new RemoveFlag( array( 'type' => 'remove_flag' ), $context );
			expect( $action->get_type() )->toBe( 'remove_flag' );
		} );
	} );

	describe( 'ClearCache Action', function () {
		it( 'class exists', function () {
			expect( class_exists( ClearCache::class ) )->toBeTrue();
		} );

		it( 'extends BaseAction', function () {
			$reflection = new ReflectionClass( ClearCache::class );
			expect( $reflection->isSubclassOf( 'MilliCache\Deps\MilliRules\Actions\BaseAction' ) )->toBeTrue();
		} );

		it( 'has get_type method', function () {
			expect( method_exists( ClearCache::class, 'get_type' ) )->toBeTrue();
		} );

		it( 'has execute method', function () {
			expect( method_exists( ClearCache::class, 'execute' ) )->toBeTrue();
		} );

		it( 'returns correct action type', function () {
			$context = Mockery::mock( Context::class );
			$action  = new ClearCache( array( 'type' => 'clear_cache' ), $context );
			expect( $action->get_type() )->toBe( 'clear_cache' );
		} );
	} );

	describe( 'ClearSiteCache Action', function () {
		it( 'class exists', function () {
			expect( class_exists( ClearSiteCache::class ) )->toBeTrue();
		} );

		it( 'extends BaseAction', function () {
			$reflection = new ReflectionClass( ClearSiteCache::class );
			expect( $reflection->isSubclassOf( 'MilliCache\Deps\MilliRules\Actions\BaseAction' ) )->toBeTrue();
		} );

		it( 'has get_type method', function () {
			expect( method_exists( ClearSiteCache::class, 'get_type' ) )->toBeTrue();
		} );

		it( 'has execute method', function () {
			expect( method_exists( ClearSiteCache::class, 'execute' ) )->toBeTrue();
		} );

		it( 'returns correct action type', function () {
			$context = Mockery::mock( Context::class );
			$action  = new ClearSiteCache( array( 'type' => 'clear_site_cache' ), $context );
			expect( $action->get_type() )->toBe( 'clear_site_cache' );
		} );
	} );

	describe( 'Action Interface Consistency', function () {
		it( 'all PHP actions extend BaseAction', function () {
			expect( is_subclass_of( DoCache::class, 'MilliCache\Deps\MilliRules\Actions\BaseAction' ) )->toBeTrue();
			expect( is_subclass_of( SetTtl::class, 'MilliCache\Deps\MilliRules\Actions\BaseAction' ) )->toBeTrue();
			expect( is_subclass_of( SetGrace::class, 'MilliCache\Deps\MilliRules\Actions\BaseAction' ) )->toBeTrue();
		} );

		it( 'all WP actions extend BaseAction', function () {
			expect( is_subclass_of( AddFlag::class, 'MilliCache\Deps\MilliRules\Actions\BaseAction' ) )->toBeTrue();
			expect( is_subclass_of( RemoveFlag::class, 'MilliCache\Deps\MilliRules\Actions\BaseAction' ) )->toBeTrue();
			expect( is_subclass_of( ClearCache::class, 'MilliCache\Deps\MilliRules\Actions\BaseAction' ) )->toBeTrue();
			expect( is_subclass_of( ClearSiteCache::class, 'MilliCache\Deps\MilliRules\Actions\BaseAction' ) )->toBeTrue();
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
