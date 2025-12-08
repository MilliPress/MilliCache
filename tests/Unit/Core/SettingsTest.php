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

	describe( 'get_settings', function () {
		it( 'returns merged settings with defaults', function () {
			$settings = new Settings();
			$result = $settings->get_settings();

			expect( $result )->toBeArray();
			expect( $result )->toHaveKey( 'storage' );
			expect( $result )->toHaveKey( 'cache' );
		} );

		it( 'returns specific module settings', function () {
			$settings = new Settings();
			$result = $settings->get_settings( 'cache' );

			expect( $result )->toBeArray();
			expect( $result )->toHaveKey( 'ttl' );
			expect( $result )->toHaveKey( 'grace' );
		} );

		it( 'can skip constants', function () {
			$settings = new Settings();
			$result = $settings->get_settings( null, true );

			expect( $result )->toBeArray();
		} );
	} );

	describe( 'add_config_file', function () {
		it( 'can be called without error', function () {
			$settings = new Settings();
			$testData = array( 'test' => 'value' );

			// Suppress file operation warnings in test environment.
			set_error_handler( function () {}, E_WARNING );
			$settings->add_config_file( 'millicache', $testData );
			restore_error_handler();

			// Just verify it doesn't throw.
			expect( true )->toBeTrue();
		} );
	} );

	describe( 'update_config_file', function () {
		it( 'can be called without error', function () {
			$settings = new Settings();
			$oldData = array( 'test' => 'old' );
			$newData = array( 'test' => 'new' );

			// Suppress file operation warnings in test environment.
			set_error_handler( function () {}, E_WARNING );
			$settings->update_config_file( $oldData, $newData );
			restore_error_handler();

			// Just verify it doesn't throw.
			expect( true )->toBeTrue();
		} );
	} );

	describe( 'delete_config_file', function () {
		it( 'ignores non-matching option names', function () {
			$settings = new Settings();

			// Should not throw when option name doesn't match.
			expect( fn() => $settings->delete_config_file( 'other_option' ) )
				->not->toThrow( Exception::class );
		} );

		it( 'can be called with matching option name', function () {
			$settings = new Settings();

			expect( fn() => $settings->delete_config_file( 'millicache' ) )
				->not->toThrow( Exception::class );
		} );
	} );

	describe( 'register_settings', function () {
		it( 'can be called without error', function () {
			$settings = new Settings();

			expect( fn() => $settings->register_settings() )
				->not->toThrow( Exception::class );
		} );
	} );

	describe( 'coerce_value', function () {
		it( 'coerces "true" to boolean true', function () {
			expect( Settings::coerce_value( 'true' ) )->toBe( true );
			expect( Settings::coerce_value( 'TRUE' ) )->toBe( true );
		} );

		it( 'coerces "false" to boolean false', function () {
			expect( Settings::coerce_value( 'false' ) )->toBe( false );
			expect( Settings::coerce_value( 'FALSE' ) )->toBe( false );
		} );

		it( 'coerces "null" to null', function () {
			expect( Settings::coerce_value( 'null' ) )->toBe( null );
			expect( Settings::coerce_value( 'NULL' ) )->toBe( null );
		} );

		it( 'coerces integer strings to integers', function () {
			expect( Settings::coerce_value( '42' ) )->toBe( 42 );
			expect( Settings::coerce_value( '0' ) )->toBe( 0 );
			expect( Settings::coerce_value( '-5' ) )->toBe( -5 );
		} );

		it( 'coerces float strings to floats', function () {
			expect( Settings::coerce_value( '3.14' ) )->toBe( 3.14 );
			expect( Settings::coerce_value( '0.5' ) )->toBe( 0.5 );
		} );

		it( 'keeps regular strings as strings', function () {
			expect( Settings::coerce_value( 'hello' ) )->toBe( 'hello' );
			expect( Settings::coerce_value( 'localhost' ) )->toBe( 'localhost' );
		} );
	} );

	describe( 'get', function () {
		it( 'gets nested value with dot notation', function () {
			$settings = new Settings();

			// MC_STORAGE_HOST is defined in this file, so the value comes from the constant.
			$value = $settings->get( 'storage.host' );
			expect( $value )->toBe( '192.168.1.1' );
		} );

		it( 'returns default for non-existent key', function () {
			$settings = new Settings();

			$value = $settings->get( 'nonexistent.key', 'default' );
			expect( $value )->toBe( 'default' );
		} );

		it( 'gets nested value from cache module', function () {
			$settings = new Settings();

			$value = $settings->get( 'cache.ttl' );
			expect( $value )->toBe( DAY_IN_SECONDS );
		} );
	} );

	describe( 'set', function () {
		it( 'returns false for invalid key format', function () {
			$settings = new Settings();

			$result = $settings->set( 'singlekey', 'value' );
			expect( $result )->toBeFalse();
		} );

		it( 'returns true for valid key', function () {
			$settings = new Settings();

			$result = $settings->set( 'cache.ttl', 3600 );
			expect( $result )->toBeTrue();
		} );
	} );

	describe( 'get_setting_source', function () {
		it( 'returns constant for constant-defined settings', function () {
			$settings = new Settings();

			// MC_STORAGE_HOST is defined earlier in this file.
			$source = $settings->get_setting_source( 'storage', 'host' );
			expect( $source )->toBe( 'constant' );
		} );

		it( 'returns default for non-constant settings', function () {
			$settings = new Settings();

			$source = $settings->get_setting_source( 'cache', 'ttl' );
			expect( $source )->toBe( 'default' );
		} );
	} );

	describe( 'export', function () {
		it( 'exports settings without encrypted fields by default', function () {
			$settings = new Settings();

			$exported = $settings->export();

			expect( $exported )->toBeArray();
			expect( $exported )->toHaveKey( 'storage' );
			expect( $exported )->toHaveKey( 'cache' );
			expect( $exported )->not->toHaveKey( 'host' );
		} );

		it( 'excludes enc_ prefixed fields when not including encrypted', function () {
			$settings = new Settings();

			$exported = $settings->export();

			expect( $exported['storage'] )->not->toHaveKey( 'enc_password' );
		} );
	} );

	describe( 'import', function () {
		it( 'imports valid settings', function () {
			$settings = new Settings();

			$data = array(
				'cache' => array( 'ttl' => 7200 ),
			);

			$result = $settings->import( $data );
			expect( $result )->toBeTrue();
		} );

		it( 'returns false for empty settings', function () {
			$settings = new Settings();

			$result = $settings->import( array() );
			expect( $result )->toBeFalse();
		} );

		it( 'filters out invalid modules', function () {
			$settings = new Settings();

			$data = array(
				'invalid_module' => array( 'key' => 'value' ),
			);

			$result = $settings->import( $data );
			expect( $result )->toBeFalse();
		} );
	} );

	describe( 'reset', function () {
		it( 'resets all settings', function () {
			$settings = new Settings();

			$result = $settings->reset();
			expect( $result )->toBeTrue();
		} );

		it( 'resets specific module settings', function () {
			$settings = new Settings();

			$result = $settings->reset( 'cache' );
			expect( $result )->toBeTrue();
		} );
	} );

	describe( 'encrypt_value static method', function () {
		it( 'encrypts a value', function () {
			$encrypted = Settings::encrypt_value( 'mysecret' );

			expect( $encrypted )->toBeString();
			expect( $encrypted )->toContain( 'ENC:' );
		} );

		it( 'is a public static method', function () {
			$method = new ReflectionMethod( Settings::class, 'encrypt_value' );
			expect( $method->isPublic() )->toBeTrue();
			expect( $method->isStatic() )->toBeTrue();
		} );
	} );

	describe( 'public accessor methods', function () {
		it( 'get_settings_from_constants is public', function () {
			$method = new ReflectionMethod( Settings::class, 'get_settings_from_constants' );
			expect( $method->isPublic() )->toBeTrue();
		} );

		it( 'get_settings_from_file is public', function () {
			$method = new ReflectionMethod( Settings::class, 'get_settings_from_file' );
			expect( $method->isPublic() )->toBeTrue();
		} );

		it( 'get_settings_from_db is public', function () {
			$method = new ReflectionMethod( Settings::class, 'get_settings_from_db' );
			expect( $method->isPublic() )->toBeTrue();
		} );
	} );
} );
