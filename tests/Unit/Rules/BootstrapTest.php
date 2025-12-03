<?php
/**
 * Tests for Bootstrap Rules.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Rules\Bootstrap;
use MilliCache\Engine\Cache\Config;

describe( 'Bootstrap Rules', function () {

	describe( 'register', function () {
		it( 'method exists and is callable', function () {
			expect( method_exists( Bootstrap::class, 'register' ) )->toBeTrue();
			expect( is_callable( array( Bootstrap::class, 'register' ) ) )->toBeTrue();
		} );

		it( 'can be called without errors', function () {
			// Mock Engine to prevent errors.
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$config = new Config( 3600, 600, true, false, array(), array(), array(), array(), array() );
			$engine->shouldReceive( 'get_config' )->andReturn( $config );

			// Mock Rules to prevent actual registration.
			$rules   = Mockery::mock( 'alias:MilliCache\Deps\MilliRules\Rules' );
			$builder = Mockery::mock();
			$builder->shouldReceive( 'order' )->andReturnSelf();
			$builder->shouldReceive( 'when' )->andReturnSelf();
			$builder->shouldReceive( 'when_any' )->andReturnSelf();
			$builder->shouldReceive( 'when_none' )->andReturnSelf();
			$builder->shouldReceive( 'then' )->andReturnSelf();
			$builder->shouldReceive( 'constant' )->andReturnSelf();
			$builder->shouldReceive( 'custom' )->andReturnSelf();
			$builder->shouldReceive( 'request_method' )->andReturnSelf();
			$builder->shouldReceive( 'request_url' )->andReturnSelf();
			$builder->shouldReceive( 'cookie' )->andReturnSelf();
			$builder->shouldReceive( 'do_cache' )->andReturnSelf();
			$builder->shouldReceive( 'register' )->andReturn( true );

			$rules->shouldReceive( 'create' )->andReturn( $builder );

			Bootstrap::register();

			expect( true )->toBeTrue();
		} );

		it( 'registers WP_CACHE rule', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$config = new Config( 3600, 600, true, false, array(), array(), array(), array(), array() );
			$engine->shouldReceive( 'get_config' )->andReturn( $config );

			$rules   = Mockery::mock( 'alias:MilliCache\Deps\MilliRules\Rules' );
			$builder = Mockery::mock();
			$builder->shouldReceive( 'order' )->andReturnSelf();
			$builder->shouldReceive( 'when' )->andReturnSelf();
			$builder->shouldReceive( 'when_any' )->andReturnSelf();
			$builder->shouldReceive( 'when_none' )->andReturnSelf();
			$builder->shouldReceive( 'then' )->andReturnSelf();
			$builder->shouldReceive( 'constant' )->andReturnSelf();
			$builder->shouldReceive( 'custom' )->andReturnSelf();
			$builder->shouldReceive( 'request_method' )->andReturnSelf();
			$builder->shouldReceive( 'request_url' )->andReturnSelf();
			$builder->shouldReceive( 'cookie' )->andReturnSelf();
			$builder->shouldReceive( 'do_cache' )->andReturnSelf();
			$builder->shouldReceive( 'register' )->andReturn( true );

			$rules->shouldReceive( 'create' )->with( 'core-wp-cache', 'php' )->once()->andReturn( $builder );
			$rules->shouldReceive( 'create' )->andReturn( $builder );

			Bootstrap::register();

			expect( true )->toBeTrue();
		} );

		it( 'registers REST request rule', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$config = new Config( 3600, 600, true, false, array(), array(), array(), array(), array() );
			$engine->shouldReceive( 'get_config' )->andReturn( $config );

			$rules   = Mockery::mock( 'alias:MilliCache\Deps\MilliRules\Rules' );
			$builder = Mockery::mock();
			$builder->shouldReceive( 'order' )->andReturnSelf();
			$builder->shouldReceive( 'when' )->andReturnSelf();
			$builder->shouldReceive( 'when_any' )->andReturnSelf();
			$builder->shouldReceive( 'when_none' )->andReturnSelf();
			$builder->shouldReceive( 'then' )->andReturnSelf();
			$builder->shouldReceive( 'constant' )->andReturnSelf();
			$builder->shouldReceive( 'custom' )->andReturnSelf();
			$builder->shouldReceive( 'request_method' )->andReturnSelf();
			$builder->shouldReceive( 'request_url' )->andReturnSelf();
			$builder->shouldReceive( 'cookie' )->andReturnSelf();
			$builder->shouldReceive( 'do_cache' )->andReturnSelf();
			$builder->shouldReceive( 'register' )->andReturn( true );

			$rules->shouldReceive( 'create' )->with( 'core-rest-request', 'php' )->once()->andReturn( $builder );
			$rules->shouldReceive( 'create' )->andReturn( $builder );

			Bootstrap::register();

			expect( true )->toBeTrue();
		} );

		it( 'skips nocache cookies when config is empty', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$config = new Config( 3600, 600, true, false, array(), array(), array(), array(), array() );
			$engine->shouldReceive( 'get_config' )->andReturn( $config );

			$rules   = Mockery::mock( 'alias:MilliCache\Deps\MilliRules\Rules' );
			$builder = Mockery::mock();
			$builder->shouldReceive( 'order' )->andReturnSelf();
			$builder->shouldReceive( 'when' )->andReturnSelf();
			$builder->shouldReceive( 'when_any' )->andReturnSelf();
			$builder->shouldReceive( 'when_none' )->andReturnSelf();
			$builder->shouldReceive( 'then' )->andReturnSelf();
			$builder->shouldReceive( 'constant' )->andReturnSelf();
			$builder->shouldReceive( 'custom' )->andReturnSelf();
			$builder->shouldReceive( 'request_method' )->andReturnSelf();
			$builder->shouldReceive( 'request_url' )->andReturnSelf();
			$builder->shouldReceive( 'do_cache' )->andReturnSelf();
			$builder->shouldReceive( 'register' )->andReturn( true );

			// Should NOT create core-nocache-cookies rule.
			$rules->shouldReceive( 'create' )->with( 'core-nocache-cookies', 'php' )->never();
			$rules->shouldReceive( 'create' )->andReturn( $builder );

			Bootstrap::register();

			expect( true )->toBeTrue();
		} );

		it( 'registers nocache cookies when configured', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$config = new Config( 3600, 600, true, false, array(), array( 'wordpress_logged_in*' ), array(), array(), array() );
			$engine->shouldReceive( 'get_config' )->andReturn( $config );

			$rules   = Mockery::mock( 'alias:MilliCache\Deps\MilliRules\Rules' );
			$builder = Mockery::mock();
			$builder->shouldReceive( 'order' )->andReturnSelf();
			$builder->shouldReceive( 'when' )->andReturnSelf();
			$builder->shouldReceive( 'when_any' )->andReturnSelf();
			$builder->shouldReceive( 'when_none' )->andReturnSelf();
			$builder->shouldReceive( 'then' )->andReturnSelf();
			$builder->shouldReceive( 'constant' )->andReturnSelf();
			$builder->shouldReceive( 'custom' )->andReturnSelf();
			$builder->shouldReceive( 'request_method' )->andReturnSelf();
			$builder->shouldReceive( 'request_url' )->andReturnSelf();
			$builder->shouldReceive( 'cookie' )->andReturnSelf();
			$builder->shouldReceive( 'do_cache' )->andReturnSelf();
			$builder->shouldReceive( 'register' )->andReturn( true );

			// SHOULD create core-nocache-cookies rule.
			$rules->shouldReceive( 'create' )->with( 'core-nocache-cookies', 'php' )->once()->andReturn( $builder );
			$rules->shouldReceive( 'create' )->andReturn( $builder );

			Bootstrap::register();

			expect( true )->toBeTrue();
		} );

		it( 'skips nocache paths when config is empty', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$config = new Config( 3600, 600, true, false, array(), array(), array(), array(), array() );
			$engine->shouldReceive( 'get_config' )->andReturn( $config );

			$rules   = Mockery::mock( 'alias:MilliCache\Deps\MilliRules\Rules' );
			$builder = Mockery::mock();
			$builder->shouldReceive( 'order' )->andReturnSelf();
			$builder->shouldReceive( 'when' )->andReturnSelf();
			$builder->shouldReceive( 'when_any' )->andReturnSelf();
			$builder->shouldReceive( 'when_none' )->andReturnSelf();
			$builder->shouldReceive( 'then' )->andReturnSelf();
			$builder->shouldReceive( 'constant' )->andReturnSelf();
			$builder->shouldReceive( 'custom' )->andReturnSelf();
			$builder->shouldReceive( 'request_method' )->andReturnSelf();
			$builder->shouldReceive( 'request_url' )->andReturnSelf();
			$builder->shouldReceive( 'do_cache' )->andReturnSelf();
			$builder->shouldReceive( 'register' )->andReturn( true );

			// Should NOT create core-nocache-paths rule.
			$rules->shouldReceive( 'create' )->with( 'core-nocache-paths', 'php' )->never();
			$rules->shouldReceive( 'create' )->andReturn( $builder );

			Bootstrap::register();

			expect( true )->toBeTrue();
		} );

		it( 'registers nocache paths when configured', function () {
			$engine = Mockery::mock( 'alias:MilliCache\Engine' );
			$config = new Config( 3600, 600, true, false, array( '/checkout/*', '/cart' ), array(), array(), array(), array() );
			$engine->shouldReceive( 'get_config' )->andReturn( $config );

			$rules   = Mockery::mock( 'alias:MilliCache\Deps\MilliRules\Rules' );
			$builder = Mockery::mock();
			$builder->shouldReceive( 'order' )->andReturnSelf();
			$builder->shouldReceive( 'when' )->andReturnSelf();
			$builder->shouldReceive( 'when_any' )->andReturnSelf();
			$builder->shouldReceive( 'when_none' )->andReturnSelf();
			$builder->shouldReceive( 'then' )->andReturnSelf();
			$builder->shouldReceive( 'constant' )->andReturnSelf();
			$builder->shouldReceive( 'custom' )->andReturnSelf();
			$builder->shouldReceive( 'request_method' )->andReturnSelf();
			$builder->shouldReceive( 'request_url' )->andReturnSelf();
			$builder->shouldReceive( 'cookie' )->andReturnSelf();
			$builder->shouldReceive( 'do_cache' )->andReturnSelf();
			$builder->shouldReceive( 'register' )->andReturn( true );

			// SHOULD create core-nocache-paths rule.
			$rules->shouldReceive( 'create' )->with( 'core-nocache-paths', 'php' )->once()->andReturn( $builder );
			$rules->shouldReceive( 'create' )->andReturn( $builder );

			Bootstrap::register();

			expect( true )->toBeTrue();
		} );
	} );
} );
