<?php
/**
 * Tests for AuthorId Condition.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Rules\Conditions\WP\AuthorId;
use MilliCache\Deps\MilliRules\Context;

describe( 'AuthorId Condition', function () {

	it( 'returns correct condition type', function () {
		$context   = Mockery::mock( Context::class );
		$condition = new AuthorId( array( 'type' => 'author_id', 'value' => 0 ), $context );
		expect( $condition->get_type() )->toBe( 'author_id' );
	} );

	it( 'matches author ID using context', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'query_vars' )->once();
		$context->shouldReceive( 'get' )->with( 'query_vars' )->once()->andReturn( array( 'author' => 3 ) );

		$condition = new AuthorId(
			array(
				'type'  => 'author_id',
				'value' => 3,
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'matches when author ID is greater than 0', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'query_vars' )->once();
		$context->shouldReceive( 'get' )->with( 'query_vars' )->once()->andReturn( array( 'author' => 3 ) );

		$condition = new AuthorId(
			array(
				'type'     => 'author_id',
				'value'    => 0,
				'operator' => '>',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'returns 0 when no author is queried', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'query_vars' )->once();
		$context->shouldReceive( 'get' )->with( 'query_vars' )->once()->andReturn( array() );

		$condition = new AuthorId(
			array(
				'type'     => 'author_id',
				'value'    => 0,
				'operator' => '=',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'supports IN operator', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'query_vars' )->once();
		$context->shouldReceive( 'get' )->with( 'query_vars' )->once()->andReturn( array( 'author' => 3 ) );

		$condition = new AuthorId(
			array(
				'type'     => 'author_id',
				'value'    => array( 1, 2, 3, 4 ),
				'operator' => 'IN',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'supports != operator', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'query_vars' )->once();
		$context->shouldReceive( 'get' )->with( 'query_vars' )->once()->andReturn( array( 'author' => 3 ) );

		$condition = new AuthorId(
			array(
				'type'     => 'author_id',
				'value'    => 1,
				'operator' => '!=',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'handles string author ID from query vars', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'query_vars' )->once();
		$context->shouldReceive( 'get' )->with( 'query_vars' )->once()->andReturn( array( 'author' => '3' ) );

		$condition = new AuthorId(
			array(
				'type'  => 'author_id',
				'value' => 3,
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );
} );
