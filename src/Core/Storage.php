<?php
/**
 * The file that defines the Storage class.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/includes
 */

namespace MilliCache\Core;

use MilliCache\Engine;
use MilliCache\Deps\Predis;
use MilliCache\Deps\Predis\Autoloader;
use MilliCache\Deps\Predis\Client;
use MilliCache\Deps\Predis\Connection\ConnectionException;
use MilliCache\Deps\Predis\PredisException;

! defined( 'ABSPATH' ) && exit;

/**
 * The Storage class for interacting with in-memory cache servers.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @subpackage MilliCache/includes
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Storage {

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
	 * Initialize the class and set its properties.
	 *
	 * @param array<mixed> $settings The settings for the storage server connection.
	 *
	 * @return void
	 * @since    1.0.0
	 * @access   public
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
		return class_exists( '\MilliCache\Deps\Predis\Autoloader' );
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
					$value = Settings::decrypt_value( $value );
				}

				$this->$key = $value;
			}
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
			require_once dirname( __DIR__, 2 ) . '/src/Deps/Predis/Autoloader.php';
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
					'scheme' => 'tcp',
					'host' => $this->host,
					'port' => $this->port,
					'password' => $this->enc_password,
					'database' => $this->db,
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
			$cache = null;
			$lock_status = '';

			// Get the cache entry and lock status.
			$key = $this->get_cache_key( $hash );

			$this->client->transaction(
				function ( $tx ) use ( $key, &$cache, &$lock_status ) {
					// Get cache entry.
					$cache = $this->client->hgetall( $key );

					// Get lock status.
					$lock_status = $this->client->get( $key . '-lock' );
				}
			);

			if ( ! $cache ) {
				return null;
			}

			// Sort out the flags.
			$flags = array();
			foreach ( array_keys( $cache ) as $key ) {
				if ( strpos( (string) $key, $this->prefix . ':f:' ) === 0 ) {
					$flags[] = $this->get_flag_key( (string) $key );
				}
			}

			// Return the data, the flags and lock status.
			return isset( $cache['data'] ) ? array(
				(array) unserialize( $cache['data'] ),
				$flags,
				$lock_status ?? '',
			) : null;
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
			$key = $this->get_cache_key( $hash );

			/**
			 * Fires before a page cache is stored in the storage server.
			 *
			 * @since 1.0.0
			 *
			 * @param string $hash The cache URL hash.
			 * @param string $key The cache key.
			 * @param array  $flags The flags associated with the cache.
			 * @param mixed  $data The data to cache.
			 */
			do_action( 'millicache_before_page_cache_stored', $hash, $key, $flags, $data );

			// Serialize the data and calculate its size.
			$serialized_data = serialize( $data );
			$fields = array(
				'data' => $serialized_data,
				'size' => strlen( $serialized_data ),
			);

			// Prepare flag keys and add them to fields.
			$flag_keys = array_map(
				function ( $flag ) use ( &$fields ) {
					$flag_key = $this->get_flag_key( $flag );
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
					$tx->expire( $key, Engine::$ttl + Engine::$grace );
				}
			);

			/**
			 * Fires after a page cache is stored in the storage server.
			 *
			 * @since 1.0.0
			 *
			 * @param string $hash The cache URL hash.
			 * @param string $key The cache key.
			 * @param array  $flags The flags associated with the cache.
			 * @param mixed  $data The data to cache.
			 */
			do_action( 'millicache_after_page_cache_stored', $hash, $key, $flags, $data );

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
			$key = $this->get_cache_key( $hash );

			// Get all flags of the key.
			$flags = $this->client->hkeys( $key );

			// Check if the flags are an array.
			if ( ! is_array( $flags ) ) {
				return false;
			}

			/**
			 * Fires before a page cache is deleted in the storage server.
			 *
			 * @param string $hash The cache URL hash.
			 * @param string $key The cache key.
			 * @param array  $flags The flags associated with the cache.
			 */
			do_action( 'millicache_before_page_cache_deleted', $hash, $key, $flags );

			$this->client->transaction(
				function ( $tx ) use ( $key, $flags ) {

					// Delete flags and remove the key from the sets associated with the flags.
					foreach ( $flags as $flag ) {
						if ( strpos( $flag, $this->prefix . ':f:' ) === 0 ) {
							// Remove the key from the set of the flag.
							$tx->srem( $flag, $key );

							// If the set of the flag is empty, delete the flag.
							$n = $tx->scard( $flag );
							if ( is_int( $n ) && 0 == $n ) {
								$tx->del( $flag );
							}
						}
					}

					// Delete the key.
					$tx->del( $key );
				}
			);

			/**
			 * Fires after a page cache is deleted in the storage server.
			 *
			 * @since 1.0.0
			 *
			 * @param string $hash The cache URL hash.
			 * @param string $key The cache key.
			 * @param array  $flags The flags associated with the cache.
			 */
			do_action( 'millicache_after_page_cache_deleted', $hash, $key, $flags );

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
			return $this->client->sadd( $this->get_key( $key ), (array) $members );
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
			return (array) $this->client->spop( $this->get_key( $key ), $count );
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
			return $this->client->scard( $this->get_key( $key ) );
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
				$this->get_cache_key( $hash . '-lock' ),
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
			return (bool) $this->client->del( $this->get_cache_key( $hash . '-lock' ) );
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
	 * @param string $flag The cache flag. Supports wildcards.
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
								foreach ( $this->get_cache_keys_by_pattern( $this->get_flag_key( $flag ) ) as $key ) {
									if ( is_string( $key ) ) {
										$pipe->smembers( $key );
									}
								}
							}
						),
						'is_array'
					)
				)
				: $this->client->smembers( $this->get_flag_key( $flag ) );

			// Remove prefix from keys.
			return array_map(
				function ( $key ) {
					return $this->get_cache_key( $key );
				},
				array_unique( $members )
			);
		} catch ( PredisException $e ) {
			error_log( 'Unable to get entries with flag from the storage server: ' . $e->getMessage() );
			return array();
		}
	}

	/**
	 * Cleanup expired flags.
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
					$flags = $this->client->keys( $this->get_flag_key( '*' ) );

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
	 * Generates a key with prefix, but removes it if already present.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $key  The key.
	 * @param string $type The type prefix (e.g. 'c' or 'f').
	 * @return string The full key with the prefix.
	 */
	public function get_key( string $key, string $type = '' ): string {
		$prefix = $this->prefix . ':' . ( '' !== $type ? $type . ':' : '' );
		if ( strpos( $key, $prefix ) === 0 ) {
			return substr( $key, strlen( $prefix ) );
		} else {
			return sprintf( '%s%s', $prefix, $key );
		}
	}

	/**
	 * Get and convert the cache key.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $hash The cache hash.
	 * @return string The cache key.
	 */
	private function get_cache_key( string $hash ): string {
		return $this->get_key( $hash, 'c' );
	}

	/**
	 * Get and convert the flag key.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $flag The flag name.
	 * @return string The flag key.
	 */
	private function get_flag_key( string $flag ): string {
		return $this->get_key( $flag, 'f' );
	}

	/**
	 * Get the size of the cache.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $flag Get cache by flag. Supports wildcards.
	 * @return false|array{index: int, size: int} The number of cache keys & the size of the cache in kilobytes.
	 */
	public function get_cache_size( string $flag = '' ) {
		try {
			$keys = empty( $flag )
				? $this->get_cache_keys_by_pattern( $this->get_cache_key( '*' ) )
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
						$pipe->hget( ! empty( $flag ) ? $this->get_cache_key( $key ) : $key, 'size' );
					}
				}
			);

			if ( ! is_array( $sizes ) ) {
				return array(
					'index' => count( $keys ),
					'size' => 0,
				);
			}

			$valid_sizes = array_filter(
				$sizes,
				function ( $size ) {
					return is_numeric( $size );
				}
			);

			return array(
				'index' => count( $valid_sizes ),
				'size' => (int) round( array_sum( $valid_sizes ) / 1024 ),
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
			'connected' => $this->is_connected(),
			'config' => array(
				'host' => $this->host,
				'port' => $this->port,
				'database' => $this->db,
				'prefix' => $this->prefix,
				'persistent' => $this->persistent,
			),
			'info' => array(),
		);

		if ( ! $status['connected'] ) {
			try {
				$this->client->ping();
			} catch ( PredisException $e ) {
				$status['error'] = $e->getMessage();
			}
		} else {
			// Get the storage server config.
			$config_keys = array(
				'databases',
				'maxmemory',
				'maxmemory-policy',
			);

			foreach ( $config_keys as $key ) {
				$status['config'] = array_merge( $status['config'], (array) $this->client->config( 'GET', $key ) );
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
		}

		return $status;
	}
}
