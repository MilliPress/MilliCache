<?php
/**
 * Tests for OptionName Condition.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Rules\Conditions\WP\OptionName;
use MilliCache\Deps\MilliRules\Context;

describe( 'OptionName Condition', function () {

	it( 'returns option name from option_name key', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'option_name' )->once();
		$context->shouldReceive( 'get' )->with( 'option_name', '' )->andReturn( 'blogname' );

		$condition = new OptionName(
			array(
				'type'     => 'option_name',
				'value'    => 'blogname',
				'operator' => '=',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeTrue();
	} );

	it( 'falls back to option key when option_name is empty', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'option_name' )->once();
		$context->shouldReceive( 'get' )->with( 'option_name', '' )->andReturn( '' );
		$context->shouldReceive( 'load' )->with( 'option' )->once();
		$context->shouldReceive( 'get' )->with( 'option', '' )->andReturn( 'blogname' );

		$condition = new OptionName(
			array(
				'type'     => 'option_name',
				'value'    => 'blogname',
				'operator' => '=',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeTrue();
	} );

	it( 'returns empty string when both keys are missing', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'option_name' )->once();
		$context->shouldReceive( 'get' )->with( 'option_name', '' )->andReturn( '' );
		$context->shouldReceive( 'load' )->with( 'option' )->once();
		$context->shouldReceive( 'get' )->with( 'option', '' )->andReturn( '' );

		$condition = new OptionName(
			array(
				'type'     => 'option_name',
				'value'    => 'blogname',
				'operator' => '=',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeFalse();
	} );

	it( 'handles non-string values gracefully', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'option_name' )->once();
		$context->shouldReceive( 'get' )->with( 'option_name', '' )->andReturn( 123 );
		// Non-empty integer doesn't trigger fallback, it just returns empty string
		// because it's not a string type

		$condition = new OptionName(
			array(
				'type'     => 'option_name',
				'value'    => 'blogname',
				'operator' => '=',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeFalse();
	} );

	it( 'matches exact option name', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'option_name' )->once();
		$context->shouldReceive( 'get' )->with( 'option_name', '' )->andReturn( 'permalink_structure' );

		$condition = new OptionName(
			array(
				'type'     => 'option_name',
				'value'    => 'permalink_structure',
				'operator' => '=',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeTrue();
	} );

	it( 'does not match different option name', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'option_name' )->once();
		$context->shouldReceive( 'get' )->with( 'option_name', '' )->andReturn( 'blogname' );

		$condition = new OptionName(
			array(
				'type'     => 'option_name',
				'value'    => 'permalink_structure',
				'operator' => '=',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeFalse();
	} );

	it( 'supports != operator', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'option_name' )->once();
		$context->shouldReceive( 'get' )->with( 'option_name', '' )->andReturn( 'blogname' );

		$condition = new OptionName(
			array(
				'type'     => 'option_name',
				'value'    => 'permalink_structure',
				'operator' => '!=',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeTrue();
	} );

	it( 'supports IN operator with multiple option names', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'option_name' )->once();
		$context->shouldReceive( 'get' )->with( 'option_name', '' )->andReturn( 'blogname' );

		$condition = new OptionName(
			array(
				'type'     => 'option_name',
				'value'    => array( 'blogname', 'permalink_structure', 'active_plugins' ),
				'operator' => 'IN',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeTrue();
	} );

	it( 'supports NOT IN operator', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'option_name' )->once();
		$context->shouldReceive( 'get' )->with( 'option_name', '' )->andReturn( 'blogname' );

		$condition = new OptionName(
			array(
				'type'     => 'option_name',
				'value'    => array( 'permalink_structure', 'active_plugins' ),
				'operator' => 'NOT IN',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeTrue();
	} );

	it( 'matches common WordPress options', function () {
		$test_options = array(
			'blogname',
			'blogdescription',
			'permalink_structure',
			'active_plugins',
			'template',
			'stylesheet',
			'timezone_string',
		);

		foreach ( $test_options as $option ) {
			$context = Mockery::mock( Context::class );
			$context->shouldReceive( 'load' )->with( 'option_name' )->once();
			$context->shouldReceive( 'get' )->with( 'option_name', '' )->andReturn( $option );

			$condition = new OptionName(
				array(
					'type'     => 'option_name',
					'value'    => $option,
					'operator' => '=',
				),
				$context
			);
			expect( $condition->matches( $context ) )->toBeTrue();
		}
	} );

	it( 'handles prefixed option names', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'option_name' )->once();
		$context->shouldReceive( 'get' )->with( 'option_name', '' )->andReturn( '_transient_doing_cron' );

		$condition = new OptionName(
			array(
				'type'     => 'option_name',
				'value'    => '_transient_doing_cron',
				'operator' => '=',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeTrue();
	} );

	it( 'has correct supported operators', function () {
		$context   = Mockery::mock( Context::class );
		$condition = new OptionName(
			array(
				'type'     => 'option_name',
				'value'    => '',
				'operator' => '=',
			),
			$context
		);
		$operators = $condition->get_supported_operators();

		expect( $operators )->toContain( '=' );
		expect( $operators )->toContain( '!=' );
		expect( $operators )->toContain( 'IN' );
		expect( $operators )->toContain( 'NOT IN' );
	} );
} );
