<?php
/**
 * Tests for PostTypeArchiveSlug Condition.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Rules\Conditions\WP\PostTypeArchiveSlug;
use MilliCache\Deps\MilliRules\Context;

describe( 'PostTypeArchiveSlug Condition', function () {

	it( 'returns correct condition type', function () {
		$context   = Mockery::mock( Context::class );
		$condition = new PostTypeArchiveSlug( array( 'type' => 'post_type_archive_slug', 'value' => '' ), $context );
		expect( $condition->get_type() )->toBe( 'post_type_archive_slug' );
	} );

	it( 'matches post type slug using context', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'query_vars' )->once();
		$context->shouldReceive( 'get' )->with( 'query_vars' )->once()->andReturn( array( 'post_type' => 'product' ) );

		$condition = new PostTypeArchiveSlug(
			array(
				'type'  => 'post_type_archive_slug',
				'value' => 'product',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'handles array of post types', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'query_vars' )->once();
		$context->shouldReceive( 'get' )->with( 'query_vars' )->once()->andReturn(
			array( 'post_type' => array( 'product', 'service' ) )
		);

		$condition = new PostTypeArchiveSlug(
			array(
				'type'  => 'post_type_archive_slug',
				'value' => 'product', // Should match first item in array
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'returns empty string when no post type', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'query_vars' )->once();
		$context->shouldReceive( 'get' )->with( 'query_vars' )->once()->andReturn( array() );

		$condition = new PostTypeArchiveSlug(
			array(
				'type'     => 'post_type_archive_slug',
				'value'    => '',
				'operator' => '=',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'supports != operator', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'query_vars' )->once();
		$context->shouldReceive( 'get' )->with( 'query_vars' )->once()->andReturn( array( 'post_type' => 'product' ) );

		$condition = new PostTypeArchiveSlug(
			array(
				'type'     => 'post_type_archive_slug',
				'value'    => 'page',
				'operator' => '!=',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );

	it( 'supports IN operator', function () {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'load' )->with( 'query_vars' )->once();
		$context->shouldReceive( 'get' )->with( 'query_vars' )->once()->andReturn( array( 'post_type' => 'product' ) );

		$condition = new PostTypeArchiveSlug(
			array(
				'type'     => 'post_type_archive_slug',
				'value'    => array( 'product', 'service', 'portfolio' ),
				'operator' => 'IN',
			),
			$context
		);

		$result = $condition->matches( $context );
		expect( $result )->toBeTrue();
	} );
} );
