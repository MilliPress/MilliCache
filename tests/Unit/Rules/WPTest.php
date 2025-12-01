<?php
/**
 * Tests for WordPress Rules.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Rules\WP;

describe( 'WP Rules', function () {

	describe( 'register', function () {
		it( 'method exists and is callable', function () {
			expect( method_exists( WP::class, 'register' ) )->toBeTrue();
			expect( is_callable( array( WP::class, 'register' ) ) )->toBeTrue();
		} );

		it( 'can be called without errors', function () {
			// Mock Rules to prevent actual registration.
			$rules = Mockery::mock( 'alias:MilliCache\Deps\MilliRules\Rules' );
			$builder = Mockery::mock();
			$builder->shouldReceive( 'on' )->andReturnSelf();
			$builder->shouldReceive( 'order' )->andReturnSelf();
			$builder->shouldReceive( 'when' )->andReturnSelf();
			$builder->shouldReceive( 'then' )->andReturnSelf();
			$builder->shouldReceive( 'constant' )->andReturnSelf();
			$builder->shouldReceive( 'custom' )->andReturnSelf();
			$builder->shouldReceive( 'is_user_logged_in' )->andReturnSelf();
			$builder->shouldReceive( 'do_cache' )->andReturnSelf();
			$builder->shouldReceive( 'register' )->andReturn( true );

			$rules->shouldReceive( 'create' )->andReturn( $builder );

			WP::register();

			expect( true )->toBeTrue();
		} );

		it( 'registers response code rule', function () {
			$rules = Mockery::mock( 'alias:MilliCache\Deps\MilliRules\Rules' );
			$builder = Mockery::mock();
			$builder->shouldReceive( 'on' )->andReturnSelf();
			$builder->shouldReceive( 'order' )->andReturnSelf();
			$builder->shouldReceive( 'when' )->andReturnSelf();
			$builder->shouldReceive( 'then' )->andReturnSelf();
			$builder->shouldReceive( 'constant' )->andReturnSelf();
			$builder->shouldReceive( 'custom' )->andReturnSelf();
			$builder->shouldReceive( 'is_user_logged_in' )->andReturnSelf();
			$builder->shouldReceive( 'do_cache' )->andReturnSelf();
			$builder->shouldReceive( 'register' )->andReturn( true );

			$rules->shouldReceive( 'create' )->with( 'core-wp-response-code' )->once()->andReturn( $builder );
			$rules->shouldReceive( 'create' )->andReturn( $builder );

			WP::register();

			expect( true )->toBeTrue();
		} );

		it( 'registers DONOTCACHEPAGE rule', function () {
			$rules = Mockery::mock( 'alias:MilliCache\Deps\MilliRules\Rules' );
			$builder = Mockery::mock();
			$builder->shouldReceive( 'on' )->andReturnSelf();
			$builder->shouldReceive( 'order' )->andReturnSelf();
			$builder->shouldReceive( 'when' )->andReturnSelf();
			$builder->shouldReceive( 'then' )->andReturnSelf();
			$builder->shouldReceive( 'constant' )->andReturnSelf();
			$builder->shouldReceive( 'custom' )->andReturnSelf();
			$builder->shouldReceive( 'is_user_logged_in' )->andReturnSelf();
			$builder->shouldReceive( 'do_cache' )->andReturnSelf();
			$builder->shouldReceive( 'register' )->andReturn( true );

			$rules->shouldReceive( 'create' )->with( 'core-wp-donotcachepage' )->once()->andReturn( $builder );
			$rules->shouldReceive( 'create' )->andReturn( $builder );

			WP::register();

			expect( true )->toBeTrue();
		} );

		it( 'registers logged-in user rule', function () {
			$rules = Mockery::mock( 'alias:MilliCache\Deps\MilliRules\Rules' );
			$builder = Mockery::mock();
			$builder->shouldReceive( 'on' )->andReturnSelf();
			$builder->shouldReceive( 'order' )->andReturnSelf();
			$builder->shouldReceive( 'when' )->andReturnSelf();
			$builder->shouldReceive( 'then' )->andReturnSelf();
			$builder->shouldReceive( 'constant' )->andReturnSelf();
			$builder->shouldReceive( 'custom' )->andReturnSelf();
			$builder->shouldReceive( 'is_user_logged_in' )->andReturnSelf();
			$builder->shouldReceive( 'do_cache' )->andReturnSelf();
			$builder->shouldReceive( 'register' )->andReturn( true );

			$rules->shouldReceive( 'create' )->with( 'core-wp-logged-in' )->once()->andReturn( $builder );
			$rules->shouldReceive( 'create' )->andReturn( $builder );

			WP::register();

			expect( true )->toBeTrue();
		} );

		it( 'registers cron rule', function () {
			$rules = Mockery::mock( 'alias:MilliCache\Deps\MilliRules\Rules' );
			$builder = Mockery::mock();
			$builder->shouldReceive( 'on' )->andReturnSelf();
			$builder->shouldReceive( 'order' )->andReturnSelf();
			$builder->shouldReceive( 'when' )->andReturnSelf();
			$builder->shouldReceive( 'then' )->andReturnSelf();
			$builder->shouldReceive( 'constant' )->andReturnSelf();
			$builder->shouldReceive( 'custom' )->andReturnSelf();
			$builder->shouldReceive( 'is_user_logged_in' )->andReturnSelf();
			$builder->shouldReceive( 'do_cache' )->andReturnSelf();
			$builder->shouldReceive( 'register' )->andReturn( true );

			$rules->shouldReceive( 'create' )->with( 'core-wp-no-cache-cron' )->once()->andReturn( $builder );
			$rules->shouldReceive( 'create' )->andReturn( $builder );

			WP::register();

			expect( true )->toBeTrue();
		} );

		it( 'registers AJAX rule', function () {
			$rules = Mockery::mock( 'alias:MilliCache\Deps\MilliRules\Rules' );
			$builder = Mockery::mock();
			$builder->shouldReceive( 'on' )->andReturnSelf();
			$builder->shouldReceive( 'order' )->andReturnSelf();
			$builder->shouldReceive( 'when' )->andReturnSelf();
			$builder->shouldReceive( 'then' )->andReturnSelf();
			$builder->shouldReceive( 'constant' )->andReturnSelf();
			$builder->shouldReceive( 'custom' )->andReturnSelf();
			$builder->shouldReceive( 'is_user_logged_in' )->andReturnSelf();
			$builder->shouldReceive( 'do_cache' )->andReturnSelf();
			$builder->shouldReceive( 'register' )->andReturn( true );

			$rules->shouldReceive( 'create' )->with( 'core-wp-no-cache-ajax' )->once()->andReturn( $builder );
			$rules->shouldReceive( 'create' )->andReturn( $builder );

			WP::register();

			expect( true )->toBeTrue();
		} );
	} );
} );
