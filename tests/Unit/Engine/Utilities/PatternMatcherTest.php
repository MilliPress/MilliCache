<?php
/**
 * Tests for PatternMatcher utility.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Engine\Utilities\PatternMatcher;

describe( 'PatternMatcher', function () {

	describe( 'exact matching', function () {
		it( 'matches identical strings', function () {
			expect( PatternMatcher::match( 'test', 'test' ) )->toBeTrue();
		} );

		it( 'does not match different strings', function () {
			expect( PatternMatcher::match( 'test', 'different' ) )->toBeFalse();
		} );

		it( 'handles empty strings', function () {
			expect( PatternMatcher::match( '', '' ) )->toBeTrue();
			expect( PatternMatcher::match( 'test', '' ) )->toBeFalse();
			expect( PatternMatcher::match( '', 'test' ) )->toBeFalse();
		} );
	} );

	describe( 'wildcard matching', function () {
		it( 'matches single wildcard', function () {
			expect( PatternMatcher::match( 'anything', '*' ) )->toBeTrue();
			expect( PatternMatcher::match( '', '*' ) )->toBeTrue();
		} );

		it( 'matches prefix wildcard', function () {
			expect( PatternMatcher::match( 'test_cookie', 'test_*' ) )->toBeTrue();
			expect( PatternMatcher::match( 'test_abc_123', 'test_*' ) )->toBeTrue();
			expect( PatternMatcher::match( 'other_cookie', 'test_*' ) )->toBeFalse();
		} );

		it( 'matches suffix wildcard', function () {
			expect( PatternMatcher::match( 'wordpress_cookie', '*_cookie' ) )->toBeTrue();
			expect( PatternMatcher::match( 'my_special_cookie', '*_cookie' ) )->toBeTrue();
			expect( PatternMatcher::match( 'not_a_match', '*_cookie' ) )->toBeFalse();
		} );

		it( 'matches middle wildcard', function () {
			expect( PatternMatcher::match( 'wp_test_value', 'wp_*_value' ) )->toBeTrue();
			expect( PatternMatcher::match( 'wp_anything_here_value', 'wp_*_value' ) )->toBeTrue();
			expect( PatternMatcher::match( 'wp_value', 'wp_*_value' ) )->toBeFalse();
		} );

		it( 'is case insensitive for wildcards', function () {
			expect( PatternMatcher::match( 'TEST', 'test*' ) )->toBeTrue();
			expect( PatternMatcher::match( 'test', 'TEST*' ) )->toBeTrue();
		} );
	} );

	describe( 'regex matching', function () {
		it( 'matches regex patterns', function () {
			expect( PatternMatcher::match( 'test123', '#test\d+#' ) )->toBeTrue();
			expect( PatternMatcher::match( 'testabc', '#test\d+#' ) )->toBeFalse();
		} );

		it( 'handles complex regex', function () {
			expect( PatternMatcher::match( 'user@example.com', '#^[\w\.-]+@[\w\.-]+\.\w+$#' ) )->toBeTrue();
			expect( PatternMatcher::match( 'invalid-email', '#^[\w\.-]+@[\w\.-]+\.\w+$#' ) )->toBeFalse();
		} );

		it( 'handles invalid regex gracefully', function () {
			// Suppress preg_match warning during test.
			set_error_handler( fn() => true, E_WARNING );
			$result = PatternMatcher::match( 'test', '#[#' );
			restore_error_handler();

			expect( $result )->toBeFalse();
		} );

		it( 'matches paths with slashes without escaping', function () {
			expect( PatternMatcher::match( '/download-abc123', '#/download-[a-z0-9]+$#' ) )->toBeTrue();
			expect( PatternMatcher::match( '/api/v1/users', '#^/api/v\d+/#' ) )->toBeTrue();
		} );
	} );

	describe( 'real-world scenarios', function () {
		it( 'matches WordPress cookie patterns', function () {
			expect( PatternMatcher::match( 'wordpress_test_cookie', 'wordpress_*' ) )->toBeTrue();
			expect( PatternMatcher::match( 'wordpress_logged_in_abc123', 'wordpress_logged_in_*' ) )->toBeTrue();
			expect( PatternMatcher::match( 'comment_author_123', 'comment_author_*' ) )->toBeTrue();
		} );

		it( 'matches UTM parameters', function () {
			expect( PatternMatcher::match( 'utm_source', 'utm_*' ) )->toBeTrue();
			expect( PatternMatcher::match( 'utm_campaign', 'utm_*' ) )->toBeTrue();
			expect( PatternMatcher::match( 'fbclid', 'utm_*' ) )->toBeFalse();
		} );

		it( 'matches admin paths', function () {
			expect( PatternMatcher::match( '/wp-admin/edit.php', '/wp-admin/*' ) )->toBeTrue();
			expect( PatternMatcher::match( '/wp-login.php', '/wp-login.php' ) )->toBeTrue();
		} );
	} );
} );
