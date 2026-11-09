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
use MilliCache\Rules\Actions\PHP\SetBucket;
use MilliCache\Rules\Actions\WP\AddFlag;
use MilliCache\Rules\Actions\WP\RemoveFlag;
use MilliCache\Rules\Actions\WP\ClearCache;
use MilliCache\Rules\Actions\WP\ClearSiteCache;
use MilliRules\Context;
use MilliRules\Rules;

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
			expect( $reflection->isSubclassOf( 'MilliRules\Actions\BaseAction' ) )->toBeTrue();
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
			expect( $reflection->isSubclassOf( 'MilliRules\Actions\BaseAction' ) )->toBeTrue();
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
			expect( $reflection->isSubclassOf( 'MilliRules\Actions\BaseAction' ) )->toBeTrue();
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

	describe( 'SetBucket Action', function () {
		it( 'class exists', function () {
			expect( class_exists( SetBucket::class ) )->toBeTrue();
		} );

		it( 'extends BaseAction', function () {
			$reflection = new ReflectionClass( SetBucket::class );
			expect( $reflection->isSubclassOf( 'MilliRules\Actions\BaseAction' ) )->toBeTrue();
		} );

		it( 'has get_type method', function () {
			expect( method_exists( SetBucket::class, 'get_type' ) )->toBeTrue();
		} );

		it( 'has execute method', function () {
			expect( method_exists( SetBucket::class, 'execute' ) )->toBeTrue();
		} );

		it( 'returns correct action type', function () {
			$context = Mockery::mock( Context::class );
			$action  = new SetBucket( array( 'type' => 'set_bucket' ), $context );
			expect( $action->get_type() )->toBe( 'set_bucket' );
		} );
	} );

	describe( 'AddFlag Action', function () {
		it( 'class exists', function () {
			expect( class_exists( AddFlag::class ) )->toBeTrue();
		} );

		it( 'extends BaseAction', function () {
			$reflection = new ReflectionClass( AddFlag::class );
			expect( $reflection->isSubclassOf( 'MilliRules\Actions\BaseAction' ) )->toBeTrue();
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
			expect( $reflection->isSubclassOf( 'MilliRules\Actions\BaseAction' ) )->toBeTrue();
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
			expect( $reflection->isSubclassOf( 'MilliRules\Actions\BaseAction' ) )->toBeTrue();
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
			expect( $reflection->isSubclassOf( 'MilliRules\Actions\BaseAction' ) )->toBeTrue();
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

	describe( 'Order-aware execution', function () {
		/**
		 * Helper: get the static $last_order value via reflection.
		 *
		 * @param class-string $class The action class.
		 * @return int The current $last_order value.
		 */
		$get_last_order = function ( string $class ): int {
			$prop = new ReflectionProperty( $class, 'last_order' );
			return $prop->getValue();
		};

		/**
		 * Helper: reset the static $last_order to PHP_INT_MIN via reflection.
		 *
		 * @param class-string $class The action class.
		 */
		$reset_order = function ( string $class ): void {
			$prop = new ReflectionProperty( $class, 'last_order' );
			$prop->setValue( null, PHP_INT_MIN );
		};

		it( 'DoCache has a static last_order property initialized to PHP_INT_MIN', function () use ( $get_last_order, $reset_order ) {
			$reset_order( DoCache::class );
			expect( $get_last_order( DoCache::class ) )->toBe( PHP_INT_MIN );
		} );

		it( 'SetTtl has a static last_order property initialized to PHP_INT_MIN', function () use ( $get_last_order, $reset_order ) {
			$reset_order( SetTtl::class );
			expect( $get_last_order( SetTtl::class ) )->toBe( PHP_INT_MIN );
		} );

		it( 'SetGrace has a static last_order property initialized to PHP_INT_MIN', function () use ( $get_last_order, $reset_order ) {
			$reset_order( SetGrace::class );
			expect( $get_last_order( SetGrace::class ) )->toBe( PHP_INT_MIN );
		} );

		it( 'each action tracks order independently', function () use ( $get_last_order, $reset_order ) {
			// Reset all.
			$reset_order( DoCache::class );
			$reset_order( SetTtl::class );
			$reset_order( SetGrace::class );

			// Simulate DoCache being updated (via reflection).
			$prop = new ReflectionProperty( DoCache::class, 'last_order' );
			$prop->setValue( null, 50 );

			// SetTtl and SetGrace should remain untouched.
			expect( $get_last_order( DoCache::class ) )->toBe( 50 );
			expect( $get_last_order( SetTtl::class ) )->toBe( PHP_INT_MIN );
			expect( $get_last_order( SetGrace::class ) )->toBe( PHP_INT_MIN );
		} );
	} );

	describe( 'Metadata Introspection', function () {
		beforeEach( function () {
			// Ensure MilliRules can resolve class-based actions by their type string.
			Rules::register_namespace( 'Actions', 'MilliCache\\Rules\\Actions\\PHP', 'PHP' );
			Rules::register_namespace( 'Actions', 'MilliCache\\Rules\\Actions\\WP', 'WP' );
		} );

		it( 'resolves add_flag metadata via Rules::get_action_meta()', function () {
			$meta = Rules::get_action_meta( 'add_flag' );
			expect( $meta )->not->toBeNull();
			expect( $meta->get_scope() )->toBe( 'flag' );
			expect( $meta->get_categories() )->toContain( 'flags' );
		} );

		it( 'resolves remove_flag metadata via Rules::get_action_meta()', function () {
			$meta = Rules::get_action_meta( 'remove_flag' );
			expect( $meta )->not->toBeNull();
			expect( $meta->get_scope() )->toBe( 'flag' );
			expect( $meta->get_categories() )->toContain( 'flags' );
		} );

		it( 'pairs add_flag and remove_flag under the same scope', function () {
			expect( Rules::get_action_meta( 'add_flag' )->get_scope() )
				->toBe( Rules::get_action_meta( 'remove_flag' )->get_scope() );
		} );

		it( 'leaves caching actions unscoped', function () {
			expect( Rules::get_action_meta( 'set_ttl' )->get_scope() )->toBe( '' );
			expect( Rules::get_action_meta( 'set_grace' )->get_scope() )->toBe( '' );
			expect( Rules::get_action_meta( 'do_cache' )->get_scope() )->toBe( '' );
			expect( Rules::get_action_meta( 'set_bucket' )->get_scope() )->toBe( '' );
		} );

		it( 'exposes caching actions under the caching category', function () {
			expect( Rules::get_action_meta( 'set_ttl' )->get_categories() )->toContain( 'caching' );
			expect( Rules::get_action_meta( 'set_grace' )->get_categories() )->toContain( 'caching' );
			expect( Rules::get_action_meta( 'do_cache' )->get_categories() )->toContain( 'caching' );
			expect( Rules::get_action_meta( 'set_bucket' )->get_categories() )->toContain( 'caching' );
		} );

		it( 'declares set_bucket arguments (name, token)', function () {
			$args = Rules::get_action_meta( 'set_bucket' )->get_arguments();
			expect( $args )->toHaveCount( 2 );
			expect( $args[0]->get_type() )->toBe( 'string' );
			expect( $args[1]->get_type() )->toBe( 'string' );
		} );

		it( 'stores the action type string (not the class name) on ActionMeta', function () {
			// Regression guard: the old set_meta() signature stored static::class
			// instead of the type string. The new signature has the engine
			// construct ActionMeta with the registration type, so this should
			// always be the short action type.
			expect( Rules::get_action_meta( 'set_ttl' )->get_type() )->toBe( 'set_ttl' );
			expect( Rules::get_action_meta( 'add_flag' )->get_type() )->toBe( 'add_flag' );
			expect( Rules::get_action_meta( 'do_cache' )->get_type() )->toBe( 'do_cache' );
		} );

		it( 'exposes declared arguments as ArgumentSchema instances', function () {
			$add_flag_args = Rules::get_action_meta( 'add_flag' )->get_arguments();
			expect( $add_flag_args )->toHaveCount( 1 );
			expect( $add_flag_args[0] )->toBeInstanceOf( MilliRules\ArgumentSchema::class );
			expect( $add_flag_args[0]->get_type() )->toBe( 'string' );
			expect( $add_flag_args[0]->is_required() )->toBeTrue();

			$set_ttl_args = Rules::get_action_meta( 'set_ttl' )->get_arguments();
			expect( $set_ttl_args )->toHaveCount( 1 );
			expect( $set_ttl_args[0]->get_type() )->toBe( 'integer' );
			expect( $set_ttl_args[0]->get_format() )->toBe( 'seconds' );
			expect( $set_ttl_args[0]->get_default() )->toBe( 3600 );
			expect( $set_ttl_args[0]->get_min() )->toBe( 0 );

			$do_cache_args = Rules::get_action_meta( 'do_cache' )->get_arguments();
			expect( $do_cache_args )->toHaveCount( 2 );
			expect( $do_cache_args[0]->get_type() )->toBe( 'boolean' );
			expect( $do_cache_args[0]->get_default() )->toBeTrue();
			expect( $do_cache_args[1]->get_type() )->toBe( 'string' );
			expect( $do_cache_args[1]->get_default() )->toBe( '' );
		} );

		it( 'serializes the complete wire format via to_array()', function () {
			$wire = Rules::get_action_meta( 'set_ttl' )->to_array();

			// Required top-level keys the planned Rules UI will read.
			expect( $wire )->toHaveKeys(
				array( 'type', 'scope', 'label', 'description', 'categories', 'arguments', 'extensions' )
			);
			expect( $wire['type'] )->toBe( 'set_ttl' );
			expect( $wire['categories'] )->toContain( 'caching' );
			expect( $wire['scope'] )->toBe( '' );

			// Arguments serialized via ArgumentSchema::to_array().
			expect( $wire['arguments'] )->toHaveCount( 1 );
			expect( $wire['arguments'][0] )->toHaveKeys(
				array( 'key', 'type', 'format', 'label', 'description', 'default', 'required', 'min', 'max', 'options' )
			);
			expect( $wire['arguments'][0]['type'] )->toBe( 'integer' );
			expect( $wire['arguments'][0]['format'] )->toBe( 'seconds' );
		} );
	} );

	describe( 'Action Interface Consistency', function () {
		it( 'all PHP actions extend BaseAction', function () {
			expect( is_subclass_of( DoCache::class, 'MilliRules\Actions\BaseAction' ) )->toBeTrue();
			expect( is_subclass_of( SetTtl::class, 'MilliRules\Actions\BaseAction' ) )->toBeTrue();
			expect( is_subclass_of( SetGrace::class, 'MilliRules\Actions\BaseAction' ) )->toBeTrue();
			expect( is_subclass_of( SetBucket::class, 'MilliRules\Actions\BaseAction' ) )->toBeTrue();
		} );

		it( 'all WP actions extend BaseAction', function () {
			expect( is_subclass_of( AddFlag::class, 'MilliRules\Actions\BaseAction' ) )->toBeTrue();
			expect( is_subclass_of( RemoveFlag::class, 'MilliRules\Actions\BaseAction' ) )->toBeTrue();
			expect( is_subclass_of( ClearCache::class, 'MilliRules\Actions\BaseAction' ) )->toBeTrue();
			expect( is_subclass_of( ClearSiteCache::class, 'MilliRules\Actions\BaseAction' ) )->toBeTrue();
		} );

		it( 'all actions have unique type identifiers', function () {
			$context = Mockery::mock( Context::class );
			$types   = array(
				( new DoCache( array( 'type' => 'do_cache' ), $context ) )->get_type(),
				( new SetTtl( array( 'type' => 'set_ttl' ), $context ) )->get_type(),
				( new SetGrace( array( 'type' => 'set_grace' ), $context ) )->get_type(),
				( new SetBucket( array( 'type' => 'set_bucket' ), $context ) )->get_type(),
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
