<?php
/**
 * Tests for RequestFlags.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Rules\RequestFlags;

describe( 'RequestFlags', function () {

	describe( 'class structure', function () {
		it( 'is a final class', function () {
			$reflection = new ReflectionClass( RequestFlags::class );
			expect( $reflection->isFinal() )->toBeTrue();
		} );

		it( 'has register method', function () {
			$reflection = new ReflectionClass( RequestFlags::class );
			expect( $reflection->hasMethod( 'register' ) )->toBeTrue();
		} );

		it( 'has public static register method', function () {
			$method = new ReflectionMethod( RequestFlags::class, 'register' );
			expect( $method->isPublic() )->toBeTrue();
			expect( $method->isStatic() )->toBeTrue();
		} );
	} );

	describe( 'constants', function () {
		it( 'has HOOK constant', function () {
			$reflection = new ReflectionClass( RequestFlags::class );
			$constants = $reflection->getConstants();
			expect( array_key_exists( 'HOOK', $constants ) )->toBeTrue();
		} );

		it( 'has PRIORITY constant', function () {
			$reflection = new ReflectionClass( RequestFlags::class );
			$constants = $reflection->getConstants();
			expect( array_key_exists( 'PRIORITY', $constants ) )->toBeTrue();
		} );

		it( 'has ORDER constant', function () {
			$reflection = new ReflectionClass( RequestFlags::class );
			$constants = $reflection->getConstants();
			expect( array_key_exists( 'ORDER', $constants ) )->toBeTrue();
		} );

		it( 'uses template_redirect hook', function () {
			$reflection = new ReflectionClass( RequestFlags::class );
			$hookProperty = $reflection->getConstant( 'HOOK' );
			expect( $hookProperty )->toBe( 'template_redirect' );
		} );

		it( 'has priority 100', function () {
			$reflection = new ReflectionClass( RequestFlags::class );
			$priorityProperty = $reflection->getConstant( 'PRIORITY' );
			expect( $priorityProperty )->toBe( 100 );
		} );

		it( 'has order 5', function () {
			$reflection = new ReflectionClass( RequestFlags::class );
			$orderProperty = $reflection->getConstant( 'ORDER' );
			expect( $orderProperty )->toBe( 5 );
		} );
	} );

	describe( 'private methods', function () {
		it( 'has register_singular_post_rule method', function () {
			$reflection = new ReflectionClass( RequestFlags::class );
			expect( $reflection->hasMethod( 'register_singular_post_rule' ) )->toBeTrue();
		} );

		it( 'has register_home_rules method', function () {
			$reflection = new ReflectionClass( RequestFlags::class );
			expect( $reflection->hasMethod( 'register_home_rules' ) )->toBeTrue();
		} );

		it( 'has register_post_type_archive_rule method', function () {
			$reflection = new ReflectionClass( RequestFlags::class );
			expect( $reflection->hasMethod( 'register_post_type_archive_rule' ) )->toBeTrue();
		} );

		it( 'has register_taxonomy_archive_rule method', function () {
			$reflection = new ReflectionClass( RequestFlags::class );
			expect( $reflection->hasMethod( 'register_taxonomy_archive_rule' ) )->toBeTrue();
		} );

		it( 'has register_author_archive_rule method', function () {
			$reflection = new ReflectionClass( RequestFlags::class );
			expect( $reflection->hasMethod( 'register_author_archive_rule' ) )->toBeTrue();
		} );

		it( 'has register_date_archive_rules method', function () {
			$reflection = new ReflectionClass( RequestFlags::class );
			expect( $reflection->hasMethod( 'register_date_archive_rules' ) )->toBeTrue();
		} );

		it( 'has register_feed_rule method', function () {
			$reflection = new ReflectionClass( RequestFlags::class );
			expect( $reflection->hasMethod( 'register_feed_rule' ) )->toBeTrue();
		} );

		it( 'has register_custom_flags_filter method', function () {
			$reflection = new ReflectionClass( RequestFlags::class );
			expect( $reflection->hasMethod( 'register_custom_flags_filter' ) )->toBeTrue();
		} );

		it( 'private methods are private', function () {
			$private_methods = array(
				'register_singular_post_rule',
				'register_home_rules',
				'register_post_type_archive_rule',
				'register_taxonomy_archive_rule',
				'register_author_archive_rule',
				'register_date_archive_rules',
				'register_feed_rule',
				'register_custom_flags_filter',
			);

			foreach ( $private_methods as $method_name ) {
				$method = new ReflectionMethod( RequestFlags::class, $method_name );
				expect( $method->isPrivate() )->toBeTrue();
				expect( $method->isStatic() )->toBeTrue();
			}
		} );
	} );

	describe( 'method signatures', function () {
		it( 'register method returns void', function () {
			$method = new ReflectionMethod( RequestFlags::class, 'register' );
			$return_type = $method->getReturnType();
			expect( $return_type )->not->toBeNull();
			expect( $return_type->getName() )->toBe( 'void' );
		} );

		it( 'register method takes no parameters', function () {
			$method = new ReflectionMethod( RequestFlags::class, 'register' );
			expect( $method->getNumberOfParameters() )->toBe( 0 );
		} );
	} );
} );
