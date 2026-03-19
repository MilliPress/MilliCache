<?php
/**
 * The file that defines the Storage class.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage Core
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Core;

use MilliBase\Settings as BaseSettings;
use MilliCache\Engine;
use Predis;
use Predis\Autoloader;
use Predis\Client;
use Predis\Connection\ConnectionException;
use Predis\PredisException;

! defined( 'ABSPATH' ) && exit;

/**
 * The Storage class for interacting with in-memory cache servers.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @subpackage Core
 * @author     Philipp Wellmer <hello@millipress.com>
 */
class Storage {

	/**
	 * The Predis Client object.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var Client $client    The Predis Client object.
	 */
	private Client $client;

	/**
	 * The storage server host.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      string    $host    The storage server host.
	 */
	private string $host;

	/**
	 * The storage server port.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      int    $port    The storage server port.
	 */
	private int $port;

	/**
	 * The storage server password.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      string    $enc_password   The storage server auth.
	 */
	private string $enc_password;

	/**
	 * The storage server database.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      int    $db    The storage server database.
	 */
	private int $db;

	/**
	 * The cache prefix.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      string    $prefix    The cache prefix.
	 */
	private string $prefix;

	/**
	 * Whether to use a persistent connection.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      bool    $persistent    Whether to use a persistent connection.
	 */
	private bool $persistent;

	/**
	 * Whether the host is a Unix socket path.
	 *
	 * @since    1.2.0
	 * @access   private
	 *
	 * @var      bool    $is_socket    Whether the host is a Unix socket path.
	 */
	private bool $is_socket;

	/**
	 * The connection scheme (tcp, tls, or unix).
	 *
	 * Derived from the host value: `tls://host` sets tls,
	 * `/path/to/socket` sets unix, otherwise defaults to tcp.
	 *
	 * @since    1.3.0
	 * @access   private
	 *
	 * @var      string    $scheme    The connection scheme.
	 */
	private string $scheme = 'tcp';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array<mixed> $settings The settings for the storage server connection.
	 *
	 * @return void
	 */
	public function __construct( array $settings ) {
		$this->config( $settings );

		if ( ! $this->connect() ) {
			error_log( 'Unable to connect to the storage server.' );
		}
	}

	/**
	 * Check if the storage server is available.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool Whether the storage server is available.
	 */
	public static function is_available(): bool {
		return class_exists( '\Predis\Autoloader' );
	}

	/**
	 * Check if the storage server is connected.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool Whether the storage server is connected.
	 */
	public function is_connected(): bool {
		return isset( $this->client ) && $this->client->isConnected();
	}

	/**
	 * Configure the storage server connection.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array<mixed> $settings The settings for the storage server connection.
	 *
	 * @return void
	 *
	 * @throws \SodiumException If the decryption fails.
	 */
	private function config( array $settings ): void {
		foreach ( $settings as $key => $value ) {
			if ( property_exists( $this, $key ) ) {
				if ( is_string( $value ) && strpos( $value, 'ENC:' ) === 0 ) {
					$value = BaseSettings::decrypt_value( $value );
				}

				$this->$key = $value;
			}
		}

		// Extract scheme from host if present (e.g. "tls://hostname").
		if ( preg_match( '#^(tls|tcp)://#', $this->host, $matches ) ) {
			$this->scheme = $matches[1];
			$this->host   = (string) substr( $this->host, strlen( $matches[0] ) );
		}

		$this->is_socket = strpos( $this->host, '/' ) === 0;

		if ( $this->is_socket ) {
			$this->scheme = 'unix';
		}
	}

	/**
	 * Connect to the storage server.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return bool Whether the connection was successful.
	 */
	private function connect(): bool {
		if ( ! self::is_available() ) {
			require_once dirname( __DIR__, 2 ) . '/deps/predis/predis/src/Autoloader.php';
		}

		try {
			// If the storage server is already connected, return.
			if ( $this->is_connected() ) {
				return true;
			}

			// Register the autoloader.
			Autoloader::register();

			// Initialize the storage server.
			$this->client = new Client(
				array(
					'scheme'     => $this->scheme,
					'host'       => $this->is_socket ? null : $this->host,
					'port'       => $this->is_socket ? null : $this->port,
					'path'       => $this->is_socket ? $this->host : null,
					'password'   => $this->enc_password,
					'database'   => $this->db,
					'persistent' => $this->persistent,
				)
			);

			return true;
		} catch ( ConnectionException $e ) {
			error_log( 'Unable to connect to the storage server: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Get cache entries with metadata and optional output.
	 *
	 * Returns per-entry data keyed by cache hash, with flags always included.
	 * By default, only reads the small meta-field; set $include_output to also
	 * transfer the (potentially large) output field.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @param string $flag           Optional flag filter. Supports wildcards.
	 * @param bool   $include_output Whether to include the output body.
	 * @return array<string, array<string, mixed>> Entries keyed by cache hash.
	 */
	public function get_entries( string $flag = '', bool $include_output = false ): array {
		try {
			if ( ! isset( $this->client ) ) {
				return array();
			}

			$keys = empty( $flag )
				? $this->get_cache_keys_by_pattern( $this->toggle_cache_key( '*' ) )
				: $this->get_cache_keys_by_flag( $flag );

			if ( empty( $keys ) ) {
				return array();
			}

			$by_flag      = ! empty( $flag );
			$hmget_fields = $include_output ? array( 'meta', 'output' ) : array( 'meta' );
			$flag_prefix  = $this->prefix . ':f:';

			$results = $this->client->pipeline(
				function ( $pipe ) use ( $keys, $hmget_fields, $by_flag ) {
					foreach ( $keys as $key ) {
						$full_key = $by_flag ? $this->toggle_cache_key( $key ) : $key;
						$pipe->hmget( $full_key, $hmget_fields );
						$pipe->hstrlen( $full_key, 'output' );
						$pipe->hkeys( $full_key );
					}
				}
			);

			if ( ! is_array( $results ) ) {
				return array();
			}

			$entries = array();

			foreach ( $keys as $i => $key ) {
				$values   = $results[ $i * 3 ] ?? null;
				$size     = $results[ $i * 3 + 1 ] ?? 0;
				$all_keys = $results[ $i * 3 + 2 ] ?? array();

				if ( ! is_array( $values ) || ! is_string( $values[0] ?? null ) ) {
					continue;
				}

				$entry         = $this->parse_meta( $values[0] );
				$entry['size'] = is_numeric( $size ) ? (int) $size : 0;

				if ( $include_output ) {
					$entry['output'] = $values[1] ?? '';
				}

				// Extract flags from hash keys.
				$entry['flags'] = array_map(
					array( $this, 'toggle_flag_key' ),
					is_array( $all_keys ) ? array_filter( $all_keys, fn( $k ) => is_string( $k ) && strpos( $k, $flag_prefix ) === 0 ) : array()
				);

				// Keys from the pattern scan are full keys; from the flag lookup are hashes.
				$hash             = $by_flag ? $key : $this->toggle_cache_key( $key );
				$entries[ $hash ] = $entry;
			}

			return $entries;
		} catch ( PredisException $e ) {
			error_log( 'Unable to get entries from the storage server: ' . $e->getMessage() );
			return array();
		}
	}

	/**
	 * Perform cache operations.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string        $hash The cache hash.
	 * @param mixed[]       $data The data to cache.
	 * @param array<string> $flags The flags associated with the cache.
	 * @param bool          $cache Whether to cache or delete the data.
	 * @return bool Whether the cache operation was successful.
	 */
	public function perform_cache( string $hash, array $data, array $flags = array(), bool $cache = true ): bool {
		try {
			if ( ! isset( $this->client ) ) {
				return false;
			}

			$this->client->transaction(
				function ( $tx ) use ( $cache, $hash, $data, $flags ) {
					if ( $cache ) {
						// Set cache entry.
						$this->set_cache( $hash, $data, $flags );
					} else {
						// Delete cache entry.
						$this->delete_cache( $hash );
					}

					// Unlock the cache entry.
					$this->unlock( $hash );
				}
			);

			return true;
		} catch ( PredisException $e ) {
			error_log( 'Unable to perform cache in the storage server: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Get cache.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $hash The cache hash.
	 * @return null|array{mixed[], array<string>, string} The cached data.
	 */
	public function get_cache( string $hash ): ?array {
		try {
			// Get the cache entry and lock status.
			$key = $this->toggle_cache_key( $hash );

			$results = $this->client->transaction(
				function ( $tx ) use ( $key ) {
					// Get cache entry.
					$tx->hgetall( $key );

					// Get lock status.
					$tx->get( $key . '-lock' );
				}
			);

			if ( ! is_array( $results ) || ! is_array( $results[0] ?? null ) ) {
				return null;
			}

			$cache       = $results[0];
			$lock_status = $results[1] ?? '';
			$flags = array_filter(
				array_keys( $cache ),
				fn( $key ) => strpos( (string) $key, $this->prefix . ':f:' ) === 0
			);
			$flags = array_map( array( $this, 'toggle_flag_key' ), $flags );

			if ( empty( $cache ) ) {
				return null;
			}

			// MC <1.4.0 backward compat: old entries stored in a serialized 'data' blob.
			if ( isset( $cache['data'] ) ) {
				return array(
					(array) unserialize( $cache['data'] ),
					$flags,
					$lock_status,
				);
			}

			// MC >=1.4.0: output + meta JSON blob.
			if ( ! isset( $cache['output'] ) ) {
				return null;
			}

			$data           = $this->parse_meta( $cache['meta'] ?? '{}' );
			$data['output'] = $cache['output'];

			return array(
				$data,
				$flags,
				$lock_status,
			);
		} catch ( PredisException $e ) {
			error_log( 'Unable to get cache from the storage server: ' . $e->getMessage() );
			return null;
		}
	}

	/**
	 * Set cache.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string        $hash The cache hash.
	 * @param mixed[]       $data The data to cache.
	 * @param array<string> $flags The flags associated with the cache.
	 * @return bool Whether the cache operation was successful.
	 */
	public function set_cache( string $hash, array $data, array $flags ): bool {
		try {
			// Get the cache key.
			$key = $this->toggle_cache_key( $hash );

			/**
			 * Fires before a cache entry is stored in the storage server.
			 *
			 * @since 1.0.0
			 *
			 * @param string $hash The cache URL hash.
			 * @param string $key The cache key.
			 * @param array  $flags The flags associated with the cache.
			 * @param mixed  $data The data to cache.
			 */
			do_action( 'millicache_entry_storing', $hash, $key, $flags, $data );

			// Build metadata blob.
			$meta = array(
				'headers' => $data['headers'] ?? array(),
				'status'  => $data['status'] ?? 200,
				'gzip'    => ! empty( $data['gzip'] ),
				'updated' => $data['updated'] ?? time(),
			);

			if ( isset( $data['custom_ttl'] ) ) {
				$meta['custom_ttl'] = (int) $data['custom_ttl'];
			}

			if ( isset( $data['custom_grace'] ) ) {
				$meta['custom_grace'] = (int) $data['custom_grace'];
			}

			$meta['url'] = $data['url'] ?? '';

			if ( isset( $data['variant'] ) && is_array( $data['variant'] ) ) {
				$meta['variant'] = $data['variant'];
			}

			$fields = array(
				'output' => $data['output'] ?? '',
				'meta'   => (string) wp_json_encode( $meta ),
			);

			// Prepare flag keys and add them to fields.
			$flag_keys = array_map(
				function ( $flag ) use ( &$fields ) {
					$flag_key = $this->toggle_flag_key( $flag );
					$fields[ $flag_key ] = 1;
					return $flag_key;
				},
				$flags
			);

			// Execute the transaction.
			$this->client->transaction(
				function ( $tx ) use ( $key, $flag_keys, $fields ) {
					// Store the fields.
					$tx->hmset( $key, $fields );

					// Add key to flag sets.
					foreach ( $flag_keys as $flag_key ) {
						$tx->sadd( $flag_key, array( $key ) );
					}

					// Set the max expiration time.
					$config = Engine::instance()->config();
					$tx->expire( $key, $config->ttl + $config->grace );
				}
			);

			/**
			 * Fires after a cache entry is stored in the storage server.
			 *
			 * @since 1.0.0
			 *
			 * @param string $hash The cache URL hash.
			 * @param string $key The cache key.
			 * @param array  $flags The flags associated with the cache.
			 * @param mixed  $data The data to cache.
			 */
			do_action( 'millicache_entry_stored', $hash, $key, $flags, $data );

			return true;
		} catch ( PredisException $e ) {
			error_log( 'Unable to set cache in the storage server: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Delete cache.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $hash The cache hash.
	 * @return bool Whether the cache operation was successful.
	 */
	public function delete_cache( string $hash ): bool {
		try {
			$key = $this->toggle_cache_key( $hash );

			// Get all fields of the key and filter to flag fields only.
			$fields = $this->client->hkeys( $key );

			if ( ! is_array( $fields ) ) {
				return false;
			}

			$flags = array_filter(
				$fields,
				fn( $field ) => is_string( $field ) && strpos( $field, $this->prefix . ':f:' ) === 0
			);

			/**
			 * Fires before a cache entry is deleted in the storage server.
			 *
			 * @param string $hash The cache URL hash.
			 * @param string $key The cache key.
			 * @param array  $flags The flags associated with the cache.
			 */
			do_action( 'millicache_entry_deleting', $hash, $key, $flags );

			$this->client->transaction(
				function ( $tx ) use ( $key, $flags ) {

					// Delete flags and remove the key from the sets associated with the flags.
					foreach ( $flags as $flag ) {
						// Remove the key from the set of the flag.
						$tx->srem( $flag, $key );

						// If the set of the flag is empty, delete the flag.
						$n = $tx->scard( $flag );
						if ( is_int( $n ) && 0 == $n ) {
							$tx->del( $flag );
						}
					}

					// Delete the key.
					$tx->del( $key );
				}
			);

			/**
			 * Fires after a cache entry is deleted in the storage server.
			 *
			 * @since 1.0.0
			 *
			 * @param string $hash The cache URL hash.
			 * @param string $key The cache key.
			 * @param array  $flags The flags associated with the cache.
			 */
			do_action( 'millicache_entry_deleted', $hash, $key, $flags );

			return true;
		} catch ( PredisException $e ) {
			error_log( 'Unable to delete cache in the storage server: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Add one or more members to a set (deduplicated).
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $key The key of the set.
	 * @param mixed  $members The member(s) to add to the set.
	 * @return int The number of members that were added to the set, not including all the members already present in the set.
	 */
	public function set_add( string $key, $members ): int {
		try {
			return $this->client->sadd( $this->toggle_key( $key ), (array) $members );
		} catch ( PredisException $e ) {
			error_log( 'Storage::set_add failed: ' . $e->getMessage() );
			return 0;
		}
	}

	/**
	 * Remove one or more members from a set.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $key The key of the set.
	 * @param int    $count The number of members to remove.
	 * @return array|string[] The removed members.
	 */
	public function set_pop( string $key, int $count = 1 ): array {
		try {
			return (array) $this->client->spop( $this->toggle_key( $key ), $count );
		} catch ( PredisException $e ) {
			error_log( 'Storage::set_pop failed: ' . $e->getMessage() );
			return array();
		}
	}

	/**
	 * Get the count of members in a set.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $key The key of the set.
	 * @return int The number of members in the set.
	 */
	public function set_count( string $key ): int {
		try {
			return $this->client->scard( $this->toggle_key( $key ) );
		} catch ( PredisException $e ) {
			error_log( 'Storage::set_count failed: ' . $e->getMessage() );
			return 0;
		}
	}

	/**
	 * Set a lock for a cache entry.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $hash The cache hash.
	 * @return bool Whether the lock operation was successful.
	 */
	public function lock( string $hash ): bool {
		try {
			$status = $this->client->set(
				$this->toggle_cache_key( $hash . '-lock' ),
				true,
				'EX',
				30,
				'NX'
			);

			return (bool) $status;
		} catch ( PredisException $e ) {
			error_log( 'Unable to set lock in the storage server: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Unlock a storage entry.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $hash The cache hash.
	 * @return bool True if the lock was released.
	 */
	public function unlock( string $hash ): bool {
		try {
			return (bool) $this->client->del( $this->toggle_cache_key( $hash . '-lock' ) );
		} catch ( PredisException $e ) {
			error_log( 'Unable to unlock in the storage server: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Clear stale and deleted cache entries, running on shutdown.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array<array<string>> $sets The cache sets to clear.
	 * @param int                  $ttl The time-to-live for the cache.
	 * @return void
	 */
	public function clear_cache_by_sets( array $sets, int $ttl ): void {
		// Delete the stored entries for the deleted flags.
		if ( isset( $sets['mll:deleted-flags'] ) ) {
			foreach ( array_unique( $sets['mll:deleted-flags'] ) as $flag ) {
				foreach ( $this->get_cache_keys_by_flag( $flag ) as $key ) {
					$this->delete_cache( $key );
				}
			}
		}

		// Expire the stored entries for the expired flags.
		if ( isset( $sets['mll:expired-flags'] ) ) {
			foreach ( array_unique( $sets['mll:expired-flags'] ) as $flag ) {
				foreach ( $this->get_cache_keys_by_flag( $flag ) as $key ) {
					$result = $this->get_cache( $key );
					if ( $result ) {
						list($data, , $locked) = $result;
						if ( $data && ! $locked ) {
							$data['updated'] -= $ttl;
							$this->set_cache( $key, $data, array() );
						}
					}
				}
			}
		}
	}

	/**
	 * SCAN cache keys that match a specific pattern.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $pattern The pattern to match keys against.
	 * @return array<string> The cache keys that match the pattern.
	 */
	private function get_cache_keys_by_pattern( string $pattern ): array {
		try {
			if ( ! isset( $this->client ) ) {
				return array();
			}

			$keys = array();
			foreach ( new Predis\Collection\Iterator\Keyspace( $this->client, $pattern ) as $key ) {
				if ( is_string( $key ) ) {
					$keys[] = $key;
				}
			}

			// Check if the keys are an array.
			if ( ! is_array( $keys ) ) {
				return array();
			}

			return $keys;
		} catch ( PredisException $e ) {
			error_log( 'Unable to get keys by pattern from the storage server: ' . $e->getMessage() );
			return array();
		}
	}

	/**
	 * Get cache keys by a given flag.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $flag The cache flag. Wildcards supported.
	 * @return array<string> The cache keys associated with the flag.
	 */
	public function get_cache_keys_by_flag( string $flag ): array {
		try {
			if ( ! isset( $this->client ) ) {
				return array();
			}

			// Get all keys in the set associated with the flag with wildcard support.
			$members = preg_match( '/[*?]/', $flag )
				? array_merge(
					array(),
					...array_filter(
						(array) $this->client->pipeline(
							function ( $pipe ) use ( $flag ) {
								foreach ( $this->get_cache_keys_by_pattern( $this->toggle_flag_key( $flag ) ) as $key ) {
									if ( is_string( $key ) ) {
										$pipe->smembers( $key );
									}
								}
							}
						),
						'is_array'
					)
				)
				: $this->client->smembers( $this->toggle_flag_key( $flag ) );

			// Remove prefix from keys.
			return array_map(
				function ( $key ) {
					return $this->toggle_cache_key( $key );
				},
				array_unique( $members )
			);
		} catch ( PredisException $e ) {
			error_log( 'Unable to get entries with flag from the storage server: ' . $e->getMessage() );
			return array();
		}
	}

	/**
	 * Clean up expired flags.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool Whether the cleanup was successful.
	 */
	public function cleanup_expired_flags(): bool {
		try {
			$this->client->pipeline(
				function ( $pipe ) {
					// Get all flag keys matching the prefix.
					$flags = $this->client->keys( $this->toggle_flag_key( '*' ) );

					foreach ( $flags as $flag ) {
						// Get all members of the flag's set.
						$keys = $this->client->smembers( $flag );

						foreach ( $keys as $key ) {
							// Remove non-existent keys from the set.
							if ( ! $this->client->exists( $key ) ) {
								$pipe->srem( $flag, $key );
							}
						}

						// If the flag's set is empty, delete the flag.
						if ( ! $this->client->scard( $flag ) ) {
							$pipe->del( $flag );
						}
					}
				}
			);

			return true;
		} catch ( PredisException $e ) {
			error_log( 'Unable to cleanup expired cache keys in the storage server: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Toggles a key prefix: adds it if missing, strips it if present.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $key  The key.
	 * @param string $type The type prefix (e.g. 'c' or 'f').
	 * @return string The toggled key.
	 */
	private function toggle_key( string $key, string $type = '' ): string {
		$prefix = $this->prefix . ':' . ( '' !== $type ? $type . ':' : '' );
		if ( strpos( $key, $prefix ) === 0 ) {
			return substr( $key, strlen( $prefix ) );
		} else {
			return sprintf( '%s%s', $prefix, $key );
		}
	}

	/**
	 * Toggle the cache key prefix: adds it if missing, strips it if present.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $hash The cache hash.
	 * @return string The toggled cache key.
	 */
	public function toggle_cache_key( string $hash ): string {
		return $this->toggle_key( $hash, 'c' );
	}

	/**
	 * Toggle the flag key prefix: adds it if missing, strips it if present.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $flag The flag name.
	 * @return string The toggled flag key.
	 */
	public function toggle_flag_key( string $flag ): string {
		return $this->toggle_key( $flag, 'f' );
	}

	/**
	 * Parse a meta-JSON string into a typed data array.
	 *
	 * @since 1.4.0
	 * @access private
	 *
	 * @param string $json The raw JSON meta-string.
	 * @return array<string, mixed> The parsed metadata.
	 */
	private function parse_meta( string $json ): array {
		$decoded = json_decode( $json, true );
		// @var array<string, mixed> $meta
		$meta = is_array( $decoded ) ? $decoded : array();

		$data = array(
			'headers' => isset( $meta['headers'] ) && is_array( $meta['headers'] ) ? $meta['headers'] : array(),
			'status'  => isset( $meta['status'] ) && is_numeric( $meta['status'] ) ? (int) $meta['status'] : 200,
			'gzip'    => ! empty( $meta['gzip'] ),
			'updated' => isset( $meta['updated'] ) && is_numeric( $meta['updated'] ) ? (int) $meta['updated'] : time(),
		);

		if ( isset( $meta['custom_ttl'] ) && is_numeric( $meta['custom_ttl'] ) ) {
			$data['custom_ttl'] = (int) $meta['custom_ttl'];
		}

		if ( isset( $meta['custom_grace'] ) && is_numeric( $meta['custom_grace'] ) ) {
			$data['custom_grace'] = (int) $meta['custom_grace'];
		}

		$data['url'] = isset( $meta['url'] ) && is_string( $meta['url'] ) ? $meta['url'] : '';

		if ( isset( $meta['variant'] ) && is_array( $meta['variant'] ) ) {
			$data['variant'] = $meta['variant'];
		}

		return $data;
	}

	/**
	 * Get the size of the cache.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $flag Get cache by flag. Supports wildcards.
	 * @return false|array{index: int, size: int} The number of cache keys and the size of the cache in bytes.
	 */
	public function get_cache_size( string $flag = '' ) {
		try {
			$keys = empty( $flag )
				? $this->get_cache_keys_by_pattern( $this->toggle_cache_key( '*' ) )
				: $this->get_cache_keys_by_flag( $flag );

			if ( empty( $keys ) ) {
				return array(
					'index' => 0,
					'size' => 0,
				);
			}

			$sizes = $this->client->pipeline(
				function ( $pipe ) use ( $keys, $flag ) {
					foreach ( $keys as $key ) {
						$pipe->hstrlen( ! empty( $flag ) ? $this->toggle_cache_key( $key ) : $key, 'output' );
					}
				}
			);

			if ( ! is_array( $sizes ) ) {
				return array(
					'index' => count( $keys ),
					'size' => 0,
				);
			}

			$valid_sizes = array_filter( $sizes, 'is_numeric' );

			return array(
				'index' => count( $valid_sizes ),
				'size' => (int) array_sum( $valid_sizes ),
			);
		} catch ( PredisException $e ) {
			error_log( 'Unable to get cache size from the storage server: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Get meaningful Storage Server config and info.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array<mixed> The Storage Server status.
	 */
	public function get_status(): array {
		$status = array(
			'connected' => false,
			'config' => array(
				'host'       => $this->host,
				'port'       => $this->port,
				'scheme'     => $this->scheme,
				'database'   => $this->db,
				'prefix'     => $this->prefix,
				'persistent' => $this->persistent,
			),
			'info' => array(),
		);

		// Try to connect if not already connected (Predis uses lazy connections).
		try {
			$this->client->ping();
			$status['connected'] = true;
		} catch ( PredisException $e ) {
			$status['error'] = $e->getMessage();
			return $status;
		}

		// Get the storage server config.
		$config_keys = array(
			'databases',
			'maxmemory',
			'maxmemory-policy',
		);

		try {
			foreach ( $config_keys as $key ) {
				$status['config'] = array_merge( $status['config'], (array) $this->client->config( 'GET', $key ) );
			}
		} catch ( PredisException $e ) {
			// CONFIG may be disabled on managed Redis. Skip gracefully.
			unset( $e );
		}

		// Get the storage server info.
		$info_keys = array(
			'Memory' => array(
				'used_memory',
				'used_memory_peak',
				'used_memory_human',
				'maxmemory',
				'maxmemory_human',
				'maxmemory_policy',
			),
			'Server' => array(
				'redis_version',
				'valkey_version',
				'keydb_version',
				'dragonfly_version',
				'tcp_port',
			),
		);

		foreach ( $info_keys as $section => $keys ) {
			$info = $this->client->info( $section );

			if ( ! is_array( $info ) ) {
				continue;
			}

			foreach ( $keys as $key ) {
				if ( isset( $info[ $section ][ $key ] ) ) {
					$status['info'][ $section ][ $key ] = $info[ $section ][ $key ];
				}
			}
		}

		// Add the server type and version.
		$types = array(
			'valkey_version' => 'Valkey',
			'keydb_version' => 'KeyDB',
			'dragonfly_version' => 'Dragonfly',
			'redis_version' => 'Redis',
		);

		foreach ( $types as $key => $type ) {
			if ( isset( $info['Server'][ $key ] ) ) {
				$status['info']['Server']['version'] = "$type {$info[ 'Server' ][ $key ]}";
				break;
			}
		}

		return $status;
	}
}
