<?php
/**
 * Tests for QueriedObjectId Condition.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Rules\Conditions\WP\QueriedObjectId;
use MilliCache\Deps\MilliRules\Context;

describe( 'QueriedObjectId Condition', function () {

	it( 'returns correct condition type', function () {
		$context   = Mockery::mock( Context::class );
		$condition = new QueriedObjectId( array( 'type' => 'queried_object_id', 'value' => 0 ), $context );
		expect( $condition->get_type() )->toBe( 'queried_object_id' );
	} );

	it( 'matches when ID is greater than 0', function () {
		// Mock get_queried_object_id to return 123.
		$mock_id = function () {
			return 123;
		};

		if ( ! function_exists( 'get_queried_object_id' ) ) {
			function get_queried_object_id() {
				return 123;
			}
		}

		$context   = Mockery::mock( Context::class );
		$condition = new QueriedObjectId(
			array(
				'type'     => 'queried_object_id',
				'value'    => 0,
				'operator' => '>',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'matches exact ID using WordPress function', function () {
		// In WordPress test environment, get_queried_object_id() is available.
		// The current implementation returns 123 from the test setup.
		$context = Mockery::mock( Context::class );

		$condition = new QueriedObjectId(
			array(
				'type'  => 'queried_object_id',
				'value' => 123,
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'does not match different ID', function () {
		// The WordPress test setup returns 123, so 100 should not match.
		$context = Mockery::mock( Context::class );

		$condition = new QueriedObjectId(
			array(
				'type'  => 'queried_object_id',
				'value' => 100,
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeFalse();
	} );

	it( 'matches using > operator', function () {
		// WordPress test returns 123, which is > 50.
		$context = Mockery::mock( Context::class );

		$condition = new QueriedObjectId(
			array(
				'type'     => 'queried_object_id',
				'value'    => 50,
				'operator' => '>',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'supports IN operator', function () {
		// WordPress test returns 123.
		$context = Mockery::mock( Context::class );

		$condition = new QueriedObjectId(
			array(
				'type'     => 'queried_object_id',
				'value'    => array( 10, 20, 123, 50 ),
				'operator' => 'IN',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'supports NOT IN operator', function () {
		// WordPress test returns 123, which is not in [10, 20, 30].
		$context = Mockery::mock( Context::class );

		$condition = new QueriedObjectId(
			array(
				'type'     => 'queried_object_id',
				'value'    => array( 10, 20, 30 ),
				'operator' => 'NOT IN',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'supports >= operator', function () {
		// WordPress test returns 123, which is >= 123.
		$context = Mockery::mock( Context::class );

		$condition = new QueriedObjectId(
			array(
				'type'     => 'queried_object_id',
				'value'    => 123,
				'operator' => '>=',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'supports < operator', function () {
		// WordPress test returns 123, which is < 200.
		$context = Mockery::mock( Context::class );

		$condition = new QueriedObjectId(
			array(
				'type'     => 'queried_object_id',
				'value'    => 200,
				'operator' => '<',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );
} );
