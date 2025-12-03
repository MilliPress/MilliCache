<?php
/**
 * Tests for DatePart Condition.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Rules\Conditions\WP\DatePart;
use MilliCache\Deps\MilliRules\Context;

describe( 'DatePart Condition', function () {

	it( 'returns correct condition type', function () {
		$context   = Mockery::mock( Context::class );
		$condition = new DatePart( array( 'type' => 'date_part', 'part' => 'year', 'value' => '' ), $context );
		expect( $condition->get_type() )->toBe( 'date_part' );
	} );

	it( 'matches year using context', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'query_vars' )->once();
		$context->shouldReceive( 'get' )->with( 'query_vars' )->once()->andReturn( array( 'year' => 2025 ) );

		$condition = new DatePart(
			array(
				'type'  => 'date_part',
				'part'  => 'year',
				'value' => '2025',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'formats month with leading zero', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'query_vars' )->once();
		$context->shouldReceive( 'get' )->with( 'query_vars' )->once()->andReturn( array( 'monthnum' => 3 ) );

		$condition = new DatePart(
			array(
				'type'  => 'date_part',
				'part'  => 'monthnum',
				'value' => '03',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'formats day with leading zero', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'query_vars' )->once();
		$context->shouldReceive( 'get' )->with( 'query_vars' )->once()->andReturn( array( 'day' => 5 ) );

		$condition = new DatePart(
			array(
				'type'  => 'date_part',
				'part'  => 'day',
				'value' => '05',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'returns empty string when date part not set', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'query_vars' )->once();
		$context->shouldReceive( 'get' )->with( 'query_vars' )->once()->andReturn( array() );

		$condition = new DatePart(
			array(
				'type'     => 'date_part',
				'part'     => 'year',
				'value'    => '',
				'operator' => '=',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'defaults to year when part not specified', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'query_vars' )->once();
		$context->shouldReceive( 'get' )->with( 'query_vars' )->once()->andReturn( array( 'year' => 2025 ) );

		$condition = new DatePart(
			array(
				'type'  => 'date_part',
				'value' => '2025',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'supports != operator', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'query_vars' )->once();
		$context->shouldReceive( 'get' )->with( 'query_vars' )->once()->andReturn( array( 'year' => 2025 ) );

		$condition = new DatePart(
			array(
				'type'     => 'date_part',
				'part'     => 'year',
				'value'    => '2024',
				'operator' => '!=',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'supports IN operator for years', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'query_vars' )->once();
		$context->shouldReceive( 'get' )->with( 'query_vars' )->once()->andReturn( array( 'year' => 2025 ) );

		$condition = new DatePart(
			array(
				'type'     => 'date_part',
				'part'     => 'year',
				'value'    => array( '2023', '2024', '2025' ),
				'operator' => 'IN',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );
} );
