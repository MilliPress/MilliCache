<?php
/**
 * Tests for Engine class.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Engine;
use MilliCache\Core\Storage;
use MilliCache\Engine\Cache\Config;

// Mock WordPress functions.
if ( ! function_exists( 'register_shutdown_function' ) ) {
	function register_shutdown_function( $callback ) {
		return true;
	}
}

if ( ! function_exists( 'get_site_transient' ) ) {
	function get_site_transient( $transient ) {
		return false;
	}
}

if ( ! function_exists( 'set_site_transient' ) ) {
	function set_site_transient( $transient, $value, $expiration = 0 ) {
		return true;
	}
}

// Mock header function.
if ( ! function_exists( 'header' ) ) {
	function header( $header, $replace = true, $http_response_code = null ) {
		global $test_headers;
		if ( ! isset( $test_headers ) ) {
			$test_headers = array();
		}
		$test_headers[] = $header;
	}
}

// Mock headers_sent function.
if ( ! function_exists( 'headers_sent' ) ) {
	function headers_sent( &$file = null, &$line = null ) {
		return false;
	}
}

// Mock fastcgi_finish_request function.
if ( ! function_exists( 'fastcgi_finish_request' ) ) {
	function fastcgi_finish_request() {
		return true;
	}
}

// Mock ob_start function if not exists.
if ( ! function_exists( 'ob_start' ) ) {
	function ob_start( $callback = null, $chunk_size = 0, $flags = PHP_OUTPUT_HANDLER_STDFLAGS ) {
		return true;
	}
}

// Note: Cannot mock assert() as it's a language construct.
// Note: Cannot mock spl_autoload_register() as it's needed by autoloader.
// Note: Cannot mock gmdate() as it's a core PHP function.

uses()->beforeEach( function () {
	// Reset global test variables.
	global $test_headers;
	$test_headers = array();

	// Initialize Engine to set up static properties.
	// This is required because many Engine methods depend on initialization.
	try {
		new Engine();
	} catch ( \Exception $e ) {
		// Silently catch any initialization errors in tests.
	}
} );

describe( 'Engine', function () {

	describe( 'get_settings', function () {
		it( 'returns settings array', function () {
			$engine = Engine::instance();
			$settings = $engine->get_settings();

			expect( $settings )->toBeArray();
		} );

		it( 'can retrieve settings for specific module', function () {
			$engine = Engine::instance();
			$settings = $engine->get_settings( 'cache' );

			expect( $settings )->toBeArray();
		} );

		it( 'returns consistent settings on multiple calls', function () {
			$engine = Engine::instance();
			$settings1 = $engine->get_settings();
			$settings2 = $engine->get_settings();

			expect( $settings1 )->toBe( $settings2 );
		} );
	} );

	describe( 'storage', function () {
		it( 'returns Storage instance', function () {
			$engine = Engine::instance();
			$storage = $engine->storage();

			expect( $storage )->toBeInstanceOf( Storage::class );
		} );

		it( 'returns same instance on multiple calls', function () {
			$engine = Engine::instance();
			$storage1 = $engine->storage();
			$storage2 = $engine->storage();

			expect( $storage1 )->toBe( $storage2 );
		} );
	} );

	describe( 'config', function () {
		it( 'returns Config instance', function () {
			$engine = Engine::instance();
			$config = $engine->config();

			expect( $config )->toBeInstanceOf( Config::class );
		} );

		it( 'returns same instance on multiple calls', function () {
			$engine = Engine::instance();
			$config1 = $engine->config();
			$config2 = $engine->config();

			expect( $config1 )->toBe( $config2 );
		} );

		it( 'config has expected properties', function () {
			$engine = Engine::instance();
			$config = $engine->config();

			expect( $config->ttl )->toBeInt();
			expect( $config->grace )->toBeInt();
			expect( $config->gzip )->toBeBool();
			expect( $config->debug )->toBeBool();
		} );
	} );

	describe( 'flags', function () {
		it( 'returns string prefix', function () {
			$engine = Engine::instance();
			$prefix = $engine->flags()->get_prefix();

			expect( $prefix )->toBeString();
		} );

		it( 'can accept site_id parameter', function () {
			$engine = Engine::instance();
			$prefix = $engine->flags()->get_prefix( 1 );

			expect( $prefix )->toBeString();
		} );

		it( 'can accept both site_id and network_id', function () {
			$engine = Engine::instance();
			$prefix = $engine->flags()->get_prefix( 1, 1 );

			expect( $prefix )->toBeString();
		} );

		it( 'returns string key for flag', function () {
			$engine = Engine::instance();
			$key = $engine->flags()->get_key( 'test' );

			expect( $key )->toBeString();
			expect( $key )->toContain( 'test' );
		} );

		it( 'can generate keys with site_id', function () {
			$engine = Engine::instance();
			$key = $engine->flags()->get_key( 'test', 1 );

			expect( $key )->toBeString();
			expect( $key )->toContain( 'test' );
		} );

		it( 'can generate keys with site_id and network_id', function () {
			$engine = Engine::instance();
			$key = $engine->flags()->get_key( 'test', 1, 1 );

			expect( $key )->toBeString();
			expect( $key )->toContain( 'test' );
		} );

		it( 'returns array of prefixed flags', function () {
			$engine = Engine::instance();
			$flags = array( 'flag1', 'flag2' );
			$result = $engine->flags()->prefix( $flags );

			expect( $result )->toBeArray();
			expect( count( $result ) )->toBe( 2 );
		} );

		it( 'can handle single flag as string', function () {
			$engine = Engine::instance();
			$result = $engine->flags()->prefix( 'flag1' );

			expect( $result )->toBeArray();
		} );

		it( 'can prefix flags with site_id', function () {
			$engine = Engine::instance();
			$flags = array( 'flag1', 'flag2' );
			$result = $engine->flags()->prefix( $flags, 1 );

			expect( $result )->toBeArray();
		} );

		it( 'returns empty array for empty input', function () {
			$engine = Engine::instance();
			$result = $engine->flags()->prefix( array() );

			expect( $result )->toBeArray();
			expect( $result )->toBeEmpty();
		} );

		it( 'can add a flag without errors', function () {
			$engine = Engine::instance();
			$engine->flags()->add( 'test-flag' );

			// Should not throw exception.
			expect( true )->toBeTrue();
		} );

		it( 'added flag appears in get_all', function () {
			$engine = Engine::instance();
			$engine->flags()->add( 'custom-flag' );
			$flags = $engine->flags()->get_all();

			expect( $flags )->toBeArray();
			expect( $flags )->toContain( 'custom-flag' );
		} );

		it( 'returns array of flags', function () {
			$engine = Engine::instance();
			$flags = $engine->flags()->get_all();

			expect( $flags )->toBeArray();
		} );

		it( 'can remove a flag without errors', function () {
			$engine = Engine::instance();
			$engine->flags()->add( 'temp-flag' );
			$engine->flags()->remove( 'temp-flag' );

			// Should not throw exception.
			expect( true )->toBeTrue();
		} );

		it( 'removed flag does not appear in get_all', function () {
			$engine = Engine::instance();
			$engine->flags()->add( 'removable-flag' );
			$flags_before = $engine->flags()->get_all();
			expect( $flags_before )->toContain( 'removable-flag' );

			$engine->flags()->remove( 'removable-flag' );
			$flags_after = $engine->flags()->get_all();
			expect( $flags_after )->not->toContain( 'removable-flag' );
		} );
	} );


	describe( 'cache clearing methods', function () {
		it( 'flags runs without error', function () {
			$engine = Engine::instance();
			$engine->clear()->flags( 'test-flag' );

			expect( true )->toBeTrue();
		} );

		it( 'flags accepts array of flags', function () {
			$engine = Engine::instance();
			$engine->clear()->flags( array( 'flag1', 'flag2' ) );

			expect( true )->toBeTrue();
		} );

		it( 'flags can expire instead of delete', function () {
			$engine = Engine::instance();
			$engine->clear()->flags( 'test-flag', true );

			expect( true )->toBeTrue();
		} );

		it( 'targets runs without error', function () {
			$engine = Engine::instance();
			$engine->clear()->targets( 'target1' );

			expect( true )->toBeTrue();
		} );

		it( 'urls runs without error', function () {
			$engine = Engine::instance();
			$engine->clear()->urls( 'https://example.com/test' );

			expect( true )->toBeTrue();
		} );

		it( 'urls accepts array', function () {
			$engine = Engine::instance();
			$engine->clear()->urls( array( 'https://example.com/page1', 'https://example.com/page2' ) );

			expect( true )->toBeTrue();
		} );

		it( 'posts runs without error', function () {
			$engine = Engine::instance();
			$engine->clear()->posts( 123 );

			expect( true )->toBeTrue();
		} );

		it( 'posts accepts array', function () {
			$engine = Engine::instance();
			$engine->clear()->posts( array( 123, 456, 789 ) );

			expect( true )->toBeTrue();
		} );

		it( 'sites runs without error', function () {
			$engine = Engine::instance();
			$engine->clear()->sites( 1 );

			expect( true )->toBeTrue();
		} );

		it( 'network runs without error', function () {
			$engine = Engine::instance();
			$engine->clear()->network( 1 );

			expect( true )->toBeTrue();
		} );

		it( 'all runs without error', function () {
			$engine = Engine::instance();
			$engine->clear()->all();

			expect( true )->toBeTrue();
		} );

		it( 'all can expire instead of delete', function () {
			$engine = Engine::instance();
			$engine->clear()->all( true );

			expect( true )->toBeTrue();
		} );
	} );

	describe( 'integration', function () {
		it( 'can manage flags through complete workflow', function () {
			$engine = Engine::instance();

			// Add multiple flags.
			$engine->flags()->add( 'workflow-flag-1' );
			$engine->flags()->add( 'workflow-flag-2' );

			// Get flags.
			$flags = $engine->flags()->get_all();
			expect( $flags )->toContain( 'workflow-flag-1' );
			expect( $flags )->toContain( 'workflow-flag-2' );

			// Remove one flag.
			$engine->flags()->remove( 'workflow-flag-1' );
			$flags = $engine->flags()->get_all();
			expect( $flags )->not->toContain( 'workflow-flag-1' );
			expect( $flags )->toContain( 'workflow-flag-2' );

			// Clear cache by flags.
			$engine->clear()->flags( 'workflow-flag-2' );

			expect( true )->toBeTrue();
		} );

		it( 'can configure cache settings', function () {
			$engine = Engine::instance();

			// Set TTL and grace using Options API.
			$options = $engine->options();
			$options->set_ttl( 7200 );
			$options->set_grace( 3600 );

			// Set cache decision.
			$options->set_cache_decision( true, 'Integration test' );
			$decision = $options->get_cache_decision();
			expect( $decision['decision'] )->toBeTrue();

			expect( true )->toBeTrue();
		} );

		it( 'storage and config remain consistent', function () {
			$engine = Engine::instance();

			$storage1 = $engine->storage();
			$config1 = $engine->config();

			$storage2 = $engine->storage();
			$config2 = $engine->config();

			expect( $storage1 )->toBe( $storage2 );
			expect( $config1 )->toBe( $config2 );
		} );
	} );

	describe( 'constructor', function () {
		it( 'can instantiate Engine', function () {
			$engine = new Engine();

			expect( $engine )->toBeInstanceOf( Engine::class );
		} );

		it( 'multiple instantiations work without error', function () {
			$engine1 = new Engine();
			$engine2 = new Engine();

			expect( $engine1 )->toBeInstanceOf( Engine::class );
			expect( $engine2 )->toBeInstanceOf( Engine::class );
		} );
	} );
} );
