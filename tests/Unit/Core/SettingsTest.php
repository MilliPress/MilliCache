<?php
/**
 * Tests for Settings class.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 */

use MilliCache\Core\Settings;

// Mock WordPress constants.
if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}
if ( ! defined( 'MONTH_IN_SECONDS' ) ) {
	define( 'MONTH_IN_SECONDS', 2592000 );
}
if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}
if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', '/tmp/wp-content' );
}
if ( ! defined( 'AUTH_KEY' ) ) {
	define( 'AUTH_KEY', 'test-auth-key' );
}
if ( ! defined( 'SECURE_AUTH_KEY' ) ) {
	define( 'SECURE_AUTH_KEY', 'test-secure-auth-key' );
}

// Mock WordPress functions.
if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $hook, $value ) {
		return $value;
	}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( $hook, $callback, $priority = 10, $args = 1 ) {
		return true;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $hook, $callback, $priority = 10, $args = 1 ) {
		return true;
	}
}

if ( ! function_exists( 'register_setting' ) ) {
	function register_setting( $group, $name, $args ) {
		return true;
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $name, $default = false ) {
		return $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( $name, $value ) {
		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	function delete_option( $name ) {
		return true;
	}
}

if ( ! function_exists( 'set_transient' ) ) {
	function set_transient( $name, $value, $expiration ) {
		return true;
	}
}

if ( ! function_exists( 'get_transient' ) ) {
	function get_transient( $name ) {
		return false;
	}
}

if ( ! function_exists( 'delete_transient' ) ) {
	function delete_transient( $name ) {
		return true;
	}
}

if ( ! function_exists( 'wp_mkdir_p' ) ) {
	function wp_mkdir_p( $path ) {
		return true;
	}
}

if ( ! function_exists( 'sanitize_file_name' ) ) {
	function sanitize_file_name( $name ) {
		return $name;
	}
}

if ( ! function_exists( 'do_action' ) ) {
	function do_action( $hook, ...$args ) {
		return true;
	}
}

describe( 'Settings', function () {

	describe( 'get_default_settings', function () {
		it( 'returns all default settings', function () {
			$settings = new Settings();
			$defaults = $settings->get_default_settings();

			expect( $defaults )->toBeArray();
			expect( $defaults )->toHaveKeys( array( 'storage', 'cache', 'rules', 'host' ) );
		} );

		it( 'returns storage module defaults', function () {
			$settings = new Settings();
			$defaults = $settings->get_default_settings( 'storage' );

			expect( $defaults )->toBeArray();
			expect( $defaults )->toHaveKey( 'host' );
			expect( $defaults )->toHaveKey( 'port' );
			expect( $defaults )->toHaveKey( 'db' );
			expect( $defaults['host'] )->toBe( '127.0.0.1' );
			expect( $defaults['port'] )->toBe( 6379 );
		} );

		it( 'returns cache module defaults', function () {
			$settings = new Settings();
			$defaults = $settings->get_default_settings( 'cache' );

			expect( $defaults )->toBeArray();
			expect( $defaults )->toHaveKey( 'ttl' );
			expect( $defaults )->toHaveKey( 'grace' );
			expect( $defaults )->toHaveKey( 'gzip' );
			expect( $defaults['ttl'] )->toBe( DAY_IN_SECONDS );
			expect( $defaults['grace'] )->toBe( MONTH_IN_SECONDS );
			expect( $defaults['gzip'] )->toBeTrue();
		} );

		it( 'returns empty array for non-existent module', function () {
			$settings = new Settings();
			$defaults = $settings->get_default_settings( 'non_existent' );

			expect( $defaults )->toBe( array() );
		} );
	} );

	describe( 'get_settings_schema', function () {
		it( 'generates schema from settings', function () {
			$settings = new Settings();
			$testSettings = array(
				'storage' => array(
					'host' => '127.0.0.1',
					'port' => 6379,
					'db' => 0,
				),
				'cache' => array(
					'ttl' => 3600,
					'gzip' => true,
				),
			);

			$schema = $settings->get_settings_schema( $testSettings );

			expect( $schema )->toBeArray();
			expect( $schema )->toHaveKey( 'type' );
			expect( $schema['type'] )->toBe( 'object' );
			expect( $schema )->toHaveKey( 'properties' );
			expect( $schema['properties'] )->toHaveKey( 'storage' );
			expect( $schema['properties']['storage']['properties']['host']['type'] )->toBe( 'string' );
			expect( $schema['properties']['storage']['properties']['port']['type'] )->toBe( 'integer' );
			expect( $schema['properties']['cache']['properties']['gzip']['type'] )->toBe( 'boolean' );
		} );
	} );

	describe( 'filter_settings_by_constants', function () {
		it( 'filters out settings defined as constants', function () {
			// Define a constant to test filtering.
			if ( ! defined( 'MC_STORAGE_HOST' ) ) {
				define( 'MC_STORAGE_HOST', '192.168.1.1' );
			}

			$settings = new Settings();
			$testSettings = array(
				'storage' => array(
					'host' => '127.0.0.1',
					'port' => 6379,
				),
				'cache' => array(
					'ttl' => 3600,
				),
			);

			$filtered = $settings->filter_settings_by_constants( $testSettings );

			expect( $filtered )->toBeArray();
			// Host should be removed because it's defined as a constant.
			expect( $filtered['storage'] )->not->toHaveKey( 'host' );
			// Port should still be present.
			expect( $filtered['storage'] )->toHaveKey( 'port' );
		} );

		it( 'returns empty array for false input', function () {
			$settings = new Settings();
			$result = $settings->filter_settings_by_constants( false );

			expect( $result )->toBe( array() );
		} );

		it( 'adds default settings back when constants are removed', function () {
			$settings = new Settings();
			$testSettings = array(
				'storage' => array(),
				'cache' => array(),
			);

			$filtered = $settings->filter_settings_by_constants( $testSettings );

			expect( $filtered )->toBeArray();
			expect( $filtered['storage'] )->toHaveKey( 'port' );
			expect( $filtered['cache'] )->toHaveKey( 'ttl' );
		} );
	} );

	describe( 'encrypt_value and decrypt_value', function () {
		it( 'encrypts and decrypts a value correctly', function () {
			$original = 'my-secret-password';

			// Use reflection to access the private encrypt_value method.
			$settings = new Settings();
			$reflection = new ReflectionClass( $settings );
			$encryptMethod = $reflection->getMethod( 'encrypt_value' );
			$encryptMethod->setAccessible( true );

			$encrypted = $encryptMethod->invoke( $settings, $original );

			expect( $encrypted )->toBeString();
			expect( $encrypted )->toContain( 'ENC:' );
			expect( $encrypted )->not->toBe( $original );

			// Decrypt it back.
			$decrypted = Settings::decrypt_value( $encrypted );

			expect( $decrypted )->toBe( $original );
		} );

		it( 'does not re-encrypt already encrypted value', function () {
			$settings = new Settings();
			$reflection = new ReflectionClass( $settings );
			$encryptMethod = $reflection->getMethod( 'encrypt_value' );
			$encryptMethod->setAccessible( true );

			$encrypted = 'ENC:already-encrypted';
			$result = $encryptMethod->invoke( $settings, $encrypted );

			expect( $result )->toBe( $encrypted );
		} );

		it( 'returns value as-is if not encrypted', function () {
			$plain = 'not-encrypted';
			$result = Settings::decrypt_value( $plain );

			expect( $result )->toBe( $plain );
		} );

		it( 'handles empty string encryption', function () {
			$settings = new Settings();
			$reflection = new ReflectionClass( $settings );
			$encryptMethod = $reflection->getMethod( 'encrypt_value' );
			$encryptMethod->setAccessible( true );

			$result = $encryptMethod->invoke( $settings, '' );

			expect( $result )->toBe( '' );
		} );
	} );

	describe( 'encrypt_sensitive_settings_data', function () {
		it( 'encrypts fields with enc_ prefix', function () {
			$settings = new Settings();
			$testSettings = array(
				'storage' => array(
					'host' => '127.0.0.1',
					'enc_password' => 'secret',
				),
			);

			$encrypted = $settings->encrypt_sensitive_settings_data( $testSettings );

			expect( $encrypted )->toBeArray();
			expect( $encrypted['storage']['host'] )->toBe( '127.0.0.1' );
			expect( $encrypted['storage']['enc_password'] )->toContain( 'ENC:' );
			expect( $encrypted['storage']['enc_password'] )->not->toBe( 'secret' );
		} );

		it( 'does not encrypt non-string values', function () {
			$settings = new Settings();
			$testSettings = array(
				'storage' => array(
					'enc_password' => 123,
				),
			);

			$encrypted = $settings->encrypt_sensitive_settings_data( $testSettings );

			expect( $encrypted['storage']['enc_password'] )->toBe( 123 );
		} );
	} );

	describe( 'decrypt_sensitive_settings_data', function () {
		it( 'decrypts fields with enc_ prefix', function () {
			$settings = new Settings();

			// First encrypt a value.
			$reflection = new ReflectionClass( $settings );
			$encryptMethod = $reflection->getMethod( 'encrypt_value' );
			$encryptMethod->setAccessible( true );
			$encrypted = $encryptMethod->invoke( $settings, 'secret' );

			$testSettings = array(
				'storage' => array(
					'host' => '127.0.0.1',
					'enc_password' => $encrypted,
				),
			);

			$decrypted = $settings->decrypt_sensitive_settings_data( $testSettings );

			expect( $decrypted )->toBeArray();
			expect( $decrypted['storage']['host'] )->toBe( '127.0.0.1' );
			expect( $decrypted['storage']['enc_password'] )->toBe( 'secret' );
		} );
	} );

	describe( 'has_default_settings', function () {
		it( 'returns true when settings are default', function () {
			// This test assumes get_settings returns defaults.
			$result = Settings::has_default_settings();

			expect( $result )->toBeTrue();
		} );

		it( 'returns true for specific module with defaults', function () {
			$result = Settings::has_default_settings( 'storage' );

			expect( $result )->toBeTrue();
		} );
	} );

	describe( 'has_backup', function () {
		it( 'returns false when no backup exists', function () {
			$result = Settings::has_backup();

			expect( $result )->toBeFalse();
		} );
	} );
} );
