<?php
/**
 * Tests for CacheEntry value object.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Engine\Cache\Entry;

describe( 'CacheEntry', function () {

	describe( 'constructor', function () {
		it( 'creates entry with required properties', function () {
			$entry = new Entry(
				'<html>Test</html>',
				array( 'Content-Type: text/html' ),
				200,
				false,
				time()
			);

			expect( $entry->output )->toBe( '<html>Test</html>' );
			expect( $entry->headers )->toBe( array( 'Content-Type: text/html' ) );
			expect( $entry->status )->toBe( 200 );
			expect( $entry->gzip )->toBeFalse();
			expect( $entry->custom_ttl )->toBeNull();
			expect( $entry->custom_grace )->toBeNull();
			expect( $entry->debug )->toBeNull();
		} );

		it( 'creates entry with optional properties', function () {
			$debug_data = array( 'request_hash' => 'abc123' );
			$entry = new Entry(
				'<html>Test</html>',
				array(),
				200,
				true,
				time(),
				7200,
				600,
				$debug_data
			);

			expect( $entry->custom_ttl )->toBe( 7200 );
			expect( $entry->custom_grace )->toBe( 600 );
			expect( $entry->debug )->toBe( $debug_data );
		} );
	} );

	describe( 'from_array', function () {
		it( 'creates entry from storage data', function () {
			$data = array(
				'output' => '<html>Cached</html>',
				'headers' => array( 'X-Custom: value' ),
				'status' => 200,
				'gzip' => true,
				'updated' => 1700000000,
				'custom_ttl' => 3600,
				'custom_grace' => 300,
				'debug' => array( 'key' => 'value' ),
			);

			$entry = Entry::from_array( $data );

			expect( $entry->output )->toBe( '<html>Cached</html>' );
			expect( $entry->headers )->toBe( array( 'X-Custom: value' ) );
			expect( $entry->status )->toBe( 200 );
			expect( $entry->gzip )->toBeTrue();
			expect( $entry->updated )->toBe( 1700000000 );
			expect( $entry->custom_ttl )->toBe( 3600 );
			expect( $entry->custom_grace )->toBe( 300 );
			expect( $entry->debug )->toBe( array( 'key' => 'value' ) );
		} );

		it( 'handles missing optional fields', function () {
			$data = array(
				'output' => 'Test',
				'headers' => array(),
				'status' => 404,
				'gzip' => false,
				'updated' => time(),
			);

			$entry = Entry::from_array( $data );

			expect( $entry->custom_ttl )->toBeNull();
			expect( $entry->custom_grace )->toBeNull();
			expect( $entry->debug )->toBeNull();
		} );

		it( 'provides defaults for completely empty array', function () {
			$entry = Entry::from_array( array() );

			expect( $entry->output )->toBe( '' );
			expect( $entry->headers )->toBeArray()->toBeEmpty();
			expect( $entry->status )->toBe( 200 );
			expect( $entry->gzip )->toBeFalse();
		} );
	} );

	describe( 'to_array', function () {
		it( 'converts entry to storage format', function () {
			$entry = new Entry(
				'<html>Test</html>',
				array( 'X-Custom: value' ),
				200,
				true,
				1700000000,
				3600,
				300,
				array( 'test' => 'data' )
			);

			$array = $entry->to_array();

			expect( $array )->toBeArray();
			expect( $array['output'] )->toBe( '<html>Test</html>' );
			expect( $array['headers'] )->toBe( array( 'X-Custom: value' ) );
			expect( $array['status'] )->toBe( 200 );
			expect( $array['gzip'] )->toBeTrue();
			expect( $array['updated'] )->toBe( 1700000000 );
			expect( $array['custom_ttl'] )->toBe( 3600 );
			expect( $array['custom_grace'] )->toBe( 300 );
			expect( $array['debug'] )->toBe( array( 'test' => 'data' ) );
		} );

		it( 'omits null optional fields', function () {
			$entry = new Entry(
				'Test',
				array(),
				200,
				false,
				time()
			);

			$array = $entry->to_array();

			expect( $array )->not->toHaveKey( 'custom_ttl' );
			expect( $array )->not->toHaveKey( 'custom_grace' );
			expect( $array )->not->toHaveKey( 'debug' );
		} );
	} );

	describe( 'round-trip conversion', function () {
		it( 'maintains data through array conversion', function () {
			$original_data = array(
				'output' => '<html>Test Content</html>',
				'headers' => array( 'Content-Type: text/html', 'X-Custom: value' ),
				'status' => 200,
				'gzip' => true,
				'updated' => 1700000000,
				'custom_ttl' => 7200,
				'custom_grace' => 600,
				'debug' => array( 'key' => 'value', 'nested' => array( 'data' => 'test' ) ),
			);

			$entry = Entry::from_array( $original_data );
			$converted_back = $entry->to_array();

			expect( $converted_back )->toBe( $original_data );
		} );
	} );
} );
