<?php
/**
 * Tests for PostStatusTransition Condition.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Rules\Conditions\WP\PostStatusTransition;
use MilliCache\Deps\MilliRules\Context;

describe( 'PostStatusTransition Condition', function () {

	it( 'returns empty string when old_status is missing', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'old_status' )->once();
		$context->shouldReceive( 'load' )->with( 'new_status' )->once();
		$context->shouldReceive( 'get' )->with( 'old_status', '' )->andReturn( '' );
		$context->shouldReceive( 'get' )->with( 'new_status', '' )->andReturn( 'publish' );

		$condition = new PostStatusTransition(
			array(
				'type'     => 'post_status_transition',
				'value'    => '',
				'operator' => '=',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeFalse();
	} );

	it( 'returns empty string when new_status is missing', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'old_status' )->once();
		$context->shouldReceive( 'load' )->with( 'new_status' )->once();
		$context->shouldReceive( 'get' )->with( 'old_status', '' )->andReturn( 'draft' );
		$context->shouldReceive( 'get' )->with( 'new_status', '' )->andReturn( '' );

		$condition = new PostStatusTransition(
			array(
				'type'     => 'post_status_transition',
				'value'    => '',
				'operator' => '=',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeFalse();
	} );

	it( 'returns transition string in correct format', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'old_status' )->once();
		$context->shouldReceive( 'load' )->with( 'new_status' )->once();
		$context->shouldReceive( 'get' )->with( 'old_status', '' )->andReturn( 'draft' );
		$context->shouldReceive( 'get' )->with( 'new_status', '' )->andReturn( 'publish' );

		$condition = new PostStatusTransition(
			array(
				'type'     => 'post_status_transition',
				'value'    => 'draft->publish',
				'operator' => '=',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeTrue();
	} );

	it( 'matches exact transition', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'old_status' )->once();
		$context->shouldReceive( 'load' )->with( 'new_status' )->once();
		$context->shouldReceive( 'get' )->with( 'old_status', '' )->andReturn( 'draft' );
		$context->shouldReceive( 'get' )->with( 'new_status', '' )->andReturn( 'publish' );

		$condition = new PostStatusTransition(
			array(
				'type'     => 'post_status_transition',
				'value'    => 'draft->publish',
				'operator' => '=',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeTrue();
	} );

	it( 'does not match different transition', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'old_status' )->once();
		$context->shouldReceive( 'load' )->with( 'new_status' )->once();
		$context->shouldReceive( 'get' )->with( 'old_status', '' )->andReturn( 'draft' );
		$context->shouldReceive( 'get' )->with( 'new_status', '' )->andReturn( 'publish' );

		$condition = new PostStatusTransition(
			array(
				'type'     => 'post_status_transition',
				'value'    => 'publish->trash',
				'operator' => '=',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeFalse();
	} );

	it( 'supports != operator', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'old_status' )->once();
		$context->shouldReceive( 'load' )->with( 'new_status' )->once();
		$context->shouldReceive( 'get' )->with( 'old_status', '' )->andReturn( 'draft' );
		$context->shouldReceive( 'get' )->with( 'new_status', '' )->andReturn( 'publish' );

		$condition = new PostStatusTransition(
			array(
				'type'     => 'post_status_transition',
				'value'    => 'publish->trash',
				'operator' => '!=',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeTrue();
	} );

	it( 'matches wildcard for any old status', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'old_status' )->once();
		$context->shouldReceive( 'load' )->with( 'new_status' )->once();
		$context->shouldReceive( 'get' )->with( 'old_status', '' )->andReturn( 'draft' );
		$context->shouldReceive( 'get' )->with( 'new_status', '' )->andReturn( 'publish' );

		$condition = new PostStatusTransition(
			array(
				'type'     => 'post_status_transition',
				'value'    => 'any->publish',
				'operator' => '=',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeTrue();
	} );

	it( 'matches wildcard for any new status', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'old_status' )->once();
		$context->shouldReceive( 'load' )->with( 'new_status' )->once();
		$context->shouldReceive( 'get' )->with( 'old_status', '' )->andReturn( 'publish' );
		$context->shouldReceive( 'get' )->with( 'new_status', '' )->andReturn( 'trash' );

		$condition = new PostStatusTransition(
			array(
				'type'     => 'post_status_transition',
				'value'    => 'publish->any',
				'operator' => '=',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeTrue();
	} );

	it( 'matches wildcard for any transition', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'old_status' )->once();
		$context->shouldReceive( 'load' )->with( 'new_status' )->once();
		$context->shouldReceive( 'get' )->with( 'old_status', '' )->andReturn( 'draft' );
		$context->shouldReceive( 'get' )->with( 'new_status', '' )->andReturn( 'publish' );

		$condition = new PostStatusTransition(
			array(
				'type'     => 'post_status_transition',
				'value'    => 'any->any',
				'operator' => '=',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeTrue();
	} );

	it( 'supports IN operator with multiple transitions', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'old_status' )->once();
		$context->shouldReceive( 'load' )->with( 'new_status' )->once();
		$context->shouldReceive( 'get' )->with( 'old_status', '' )->andReturn( 'draft' );
		$context->shouldReceive( 'get' )->with( 'new_status', '' )->andReturn( 'publish' );

		$condition = new PostStatusTransition(
			array(
				'type'     => 'post_status_transition',
				'value'    => array( 'draft->publish', 'pending->publish' ),
				'operator' => 'IN',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeTrue();
	} );

	it( 'supports NOT IN operator', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'old_status' )->once();
		$context->shouldReceive( 'load' )->with( 'new_status' )->once();
		$context->shouldReceive( 'get' )->with( 'old_status', '' )->andReturn( 'draft' );
		$context->shouldReceive( 'get' )->with( 'new_status', '' )->andReturn( 'publish' );

		$condition = new PostStatusTransition(
			array(
				'type'     => 'post_status_transition',
				'value'    => array( 'publish->trash', 'trash->draft' ),
				'operator' => 'NOT IN',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeTrue();
	} );

	it( 'supports wildcards in IN operator', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'old_status' )->once();
		$context->shouldReceive( 'load' )->with( 'new_status' )->once();
		$context->shouldReceive( 'get' )->with( 'old_status', '' )->andReturn( 'pending' );
		$context->shouldReceive( 'get' )->with( 'new_status', '' )->andReturn( 'publish' );

		$condition = new PostStatusTransition(
			array(
				'type'     => 'post_status_transition',
				'value'    => array( 'any->publish', 'publish->trash' ),
				'operator' => 'IN',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeTrue();
	} );

	it( 'does not match when wildcard pattern does not match', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'old_status' )->once();
		$context->shouldReceive( 'load' )->with( 'new_status' )->once();
		$context->shouldReceive( 'get' )->with( 'old_status', '' )->andReturn( 'draft' );
		$context->shouldReceive( 'get' )->with( 'new_status', '' )->andReturn( 'trash' );

		$condition = new PostStatusTransition(
			array(
				'type'     => 'post_status_transition',
				'value'    => 'any->publish',
				'operator' => '=',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeFalse();
	} );

	it( 'handles publish to trash transition', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'old_status' )->once();
		$context->shouldReceive( 'load' )->with( 'new_status' )->once();
		$context->shouldReceive( 'get' )->with( 'old_status', '' )->andReturn( 'publish' );
		$context->shouldReceive( 'get' )->with( 'new_status', '' )->andReturn( 'trash' );

		$condition = new PostStatusTransition(
			array(
				'type'     => 'post_status_transition',
				'value'    => 'publish->trash',
				'operator' => '=',
			),
			$context
		);
		expect( $condition->matches( $context ) )->toBeTrue();
	} );

	it( 'has correct supported operators', function () {
		$context   = Mockery::mock( Context::class );
		$condition = new PostStatusTransition(
			array(
				'type'     => 'post_status_transition',
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
