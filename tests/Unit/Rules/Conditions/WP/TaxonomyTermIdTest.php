<?php
/**
 * Tests for TaxonomyTermId Condition.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Rules\Conditions\WP\TaxonomyTermId;
use MilliCache\Deps\MilliRules\Context;

describe( 'TaxonomyTermId Condition', function () {

	it( 'returns correct condition type', function () {
		$context   = Mockery::mock( Context::class );
		$condition = new TaxonomyTermId( array( 'type' => 'taxonomy_term_id', 'value' => 0 ), $context );
		expect( $condition->get_type() )->toBe( 'taxonomy_term_id' );
	} );

	it( 'matches term ID using context', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'term' )->once();
		$context->shouldReceive( 'get' )->with( 'term.id' )->once()->andReturn( 15 );

		$condition = new TaxonomyTermId(
			array(
				'type'  => 'taxonomy_term_id',
				'value' => 15,
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'matches when term ID is greater than 0', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'term' )->once();
		$context->shouldReceive( 'get' )->with( 'term.id' )->once()->andReturn( 15 );

		$condition = new TaxonomyTermId(
			array(
				'type'     => 'taxonomy_term_id',
				'value'    => 0,
				'operator' => '>',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'returns 0 when no term is queried', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'term' )->once();
		$context->shouldReceive( 'get' )->with( 'term.id' )->once()->andReturn( null );

		$condition = new TaxonomyTermId(
			array(
				'type'     => 'taxonomy_term_id',
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
		$context->shouldReceive( 'load' )->with( 'term' )->once();
		$context->shouldReceive( 'get' )->with( 'term.id' )->once()->andReturn( 15 );

		$condition = new TaxonomyTermId(
			array(
				'type'     => 'taxonomy_term_id',
				'value'    => array( 10, 15, 20 ),
				'operator' => 'IN',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'supports >= operator', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'term' )->once();
		$context->shouldReceive( 'get' )->with( 'term.id' )->once()->andReturn( 15 );

		$condition = new TaxonomyTermId(
			array(
				'type'     => 'taxonomy_term_id',
				'value'    => 15,
				'operator' => '>=',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'does not match different term ID', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'term' )->once();
		$context->shouldReceive( 'get' )->with( 'term.id' )->once()->andReturn( 15 );

		$condition = new TaxonomyTermId(
			array(
				'type'  => 'taxonomy_term_id',
				'value' => 20,
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeFalse();
	} );
} );
