<?php
/**
 * The file that defines the Redis class.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/includes
 */

namespace MilliCache;

use MilliCache\Predis\Autoloader;
use MilliCache\Predis\Client;
use MilliCache\Predis\PredisException;
use MilliCache\Predis\Connection\ConnectionException;

! defined( 'ABSPATH' ) && exit;

/**
 * Handles Redis operations for the MilliCache plugin.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @subpackage MilliCache/includes
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Redis {

	/**
	 * The Predis Client object.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var Client $redis    The Predis Client object.
	 */
	private Client $redis;

	/**
	 * The Redis host.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      string    $host    The Redis host.
	 */
	private string $host = '127.0.0.1';

	/**
	 * The Redis port.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      int    $port    The Redis port.
	 */
	private int $port = 6379;

	/**
	 * The Redis Server password.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      string    $password    The Redis auth.
	 */
	private string $password = '';

	/**
	 * The Redis database.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      int    $db    The Redis database.
	 */
	private int $db = 0;

	/**
	 * The Redis prefix.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      string    $prefix    The Redis prefix.
	 */
	private string $prefix = 'mll';

	/**
	 * Whether to use a persistent connection.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      bool    $persistent    Whether to use a persistent connection.
	 */
	private bool $persistent = true;

	/**
	 * The max TTL (Time to Live) of an entry in the Redis Cache to avoid stale data.
	 * Redis will automatically delete the cache after the max TTL.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var int The maximum time to live for the cache.
	 */
	private static $max_ttl = MONTH_IN_SECONDS; // 1 month.

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return void
	 */
	public function __construct() {
		$this->get_config();

		if ( ! $this->connect() ) {
			error_log( 'Unable to connect to Redis.' );
		}
	}

	/**
	 * Check if Redis is available.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool Whether Redis is available.
	 */
	public static function is_available(): bool {
		return class_exists( '\MilliCache\Predis\Autoloader' );
	}

	/**
	 * Load & set configuration from wp-config.php.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function get_config() {
		$keys = array(
			'host',
			'port',
			'password',
			'db',
			'prefix',
			'persistent',
			'max_ttl',
		);

		foreach ( $keys as $key ) {
			$constant = strtoupper( 'MC_REDIS_' . $key );

			if ( defined( $constant ) ) {
				$this->$key = constant( $constant );
			}
		}
	}

	/**
	 * Connect to Redis.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return bool Whether the connection was successful.
	 */
	private function connect(): bool {
		require_once dirname( __DIR__ ) . '/src/Predis/Autoloader.php';

		// Check if Predis is available.
		if ( ! self::is_available() ) {
			error_log( 'Predis is not available.' );
			return false;
		}

		try {
			// If Redis is already connected, return.
			if ( isset( $this->redis ) && $this->redis->isConnected() ) {
				return true;
			}

			// Register the autoloader.
			Autoloader::register();

			// Initialize Redis.
			$this->redis = new Client(
				array(
					'scheme' => 'tcp',
					'host' => $this->host,
					'port' => $this->port,
					'password' => $this->password,
					'database' => $this->db,
					'persistent' => $this->persistent,
				)
			);

			$this->redis->connect();

			$connect = $this->redis->isConnected();
		} catch ( ConnectionException $e ) {
			error_log( 'Unable to connect to Redis: ' . $e->getMessage() );
			return false;
		}

		return $connect;
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
			if ( ! isset( $this->redis ) ) {
				return false;
			}

			$this->redis->transaction(
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
			error_log( 'Unable to perform cache in Redis: ' . $e->getMessage() );
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

			$this->redis->transaction(
				function ( $tx ) use ( $key, &$cache, &$lock_status ) {
					// Get cache entry.
					$cache = $this->redis->hgetall( $key );

					// Get lock status.
					$lock_status = $this->redis->get( $key . '-lock' );
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
			error_log( 'Unable to get cache from Redis: ' . $e->getMessage() );
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
			 * Fires before a page cache is stored in Redis.
			 *
			 * @since 1.0.0
			 *
			 * @param string $hash The cache URL hash.
			 * @param string $key The cache key.
			 * @param array  $flags The flags associated with the cache.
			 * @param mixed  $data The data to cache.
			 */
			do_action( 'millicache_before_page_cache_stored', $hash, $key, $flags, $data );

			$this->redis->transaction(
				function ( $tx ) use ( $key, $flags, $data ) {

					// Store the data.
					$tx->hset( $key, 'data', serialize( $data ) );

					// Store the flags and add the key to the sets associated with the flags.
					foreach ( $flags as $flag ) {
						$flag = $this->get_flag_key( $flag );

						// Add the key to the set of the flag.
						$tx->hset( $key, $flag, '' );

						// Add the flag to the set of the key.
						$tx->sadd( $flag, array( $key ) );
					}

					// Set the max expiration time to avoid stale data.
					$tx->expire( $key, self::$max_ttl );
				}
			);

			/**
			 * Fires after a page cache is stored in Redis.
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
			error_log( 'Unable to set cache in Redis: ' . $e->getMessage() );
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
			$flags = $this->redis->hkeys( $key );

			// Check if the flags are an array.
			if ( ! is_array( $flags ) ) {
				return false;
			}

			/**
			 * Fires before a page cache is deleted in Redis.
			 *
			 * @param string $hash The cache URL hash.
			 * @param string $key The cache key.
			 * @param array  $flags The flags associated with the cache.
			 */
			do_action( 'millicache_before_page_cache_deleted', $hash, $key, $flags );

			$this->redis->transaction(
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
			 * Fires after a page cache is deleted in Redis.
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
			error_log( 'Unable to delete cache in Redis: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Set lock for a cache entry.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $hash The cache hash.
	 * @return bool Whether the lock operation was successful.
	 */
	public function lock( string $hash ): bool {
		try {
			$status = $this->redis->set(
				$this->get_cache_key( $hash . '-lock' ),
				true,
				'EX',
				30,
				'NX'
			);

			return (bool) $status;
		} catch ( PredisException $e ) {
			error_log( 'Unable to set lock in Redis: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Set lock for a cache entry.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $hash The cache hash.
	 * @return bool Whether the lock operation was successful.
	 */
	public function unlock( string $hash ): bool {
		try {
			return (bool) $this->redis->del( $this->get_cache_key( $hash . '-lock' ) );
		} catch ( PredisException $e ) {
			error_log( 'Unable to unlock in Redis: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Clear expired & deleted caches, running on shutdown.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array<array<string>> $sets The cache sets to clear.
	 * @param int                  $ttl The time-to-live for the cache.
	 * @return void
	 */
	public function clear_cache_by_flags( array $sets, int $ttl ): void {
		// Delete the Redis entries for the deleted flags.
		if ( isset( $sets['mll:deleted-flags'] ) ) {
			foreach ( array_unique( $sets['mll:deleted-flags'] ) as $flag ) {
				foreach ( $this->get_cache_keys_by_flag( $flag ) as $key ) {
					$this->delete_cache( $key );
				}
			}
		}

		// Expire the Redis entries for the expired flags.
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
			if ( ! isset( $this->redis ) ) {
				return array();
			}

			// Check if the flag contains any wildcard characters.
			if ( preg_match( '/[*?]/', $flag ) ) {
				$pattern = $this->get_flag_key( $flag );
				$members = array();

				// The flag contains wildcard, use SCAN to get all keys.
				$keys = array();
				foreach ( new Predis\Collection\Iterator\Keyspace( $this->redis, $pattern ) as $key ) {
					$keys[] = $key;
				}

				// Check if the keys are an array.
				if ( ! is_array( $keys ) ) {
					return array();
				}

				foreach ( $keys as $key ) {
					if ( is_string( $key ) ) {
						$key_members = $this->redis->smembers( $key );
						$members = array_merge( $members, $key_members );
					}
				}
			} else {
				// The flag does not contain wildcard, directly call sMembers.
				$members = $this->redis->smembers( $this->get_flag_key( $flag ) );
			}

			// Remove prefix from keys.
			return array_map(
				function ( $key ) {
					return $this->get_cache_key( $key );
				},
				array_unique( $members )
			);
		} catch ( PredisException $e ) {
			error_log( 'Unable to get entries with flag from Redis: ' . $e->getMessage() );
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
			// Get all flags.
			$flags = $this->redis->keys( $this->prefix . ':f:*' );

			foreach ( $flags as $flag ) {
				// Get all keys in the set associated with the flag.
				$keys = $this->redis->smembers( $flag );

				foreach ( $keys as $key ) {
					// If the key does not exist in Redis, remove it from the set.
					if ( ! $this->redis->exists( $key ) ) {
						$this->redis->srem( $flag, $key );
					}
				}

				// If the set of the flag is empty, delete the flag.
				if ( 0 === $this->redis->scard( $flag ) ) {
					$this->redis->del( $flag );
				}
			}

			return true;
		} catch ( PredisException $e ) {
			error_log( 'Unable to cleanup expired cache keys in Redis: ' . $e->getMessage() );
			return false;
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
		$prefix = $this->prefix . ':c:';
		if ( strpos( $hash, $prefix ) === 0 ) {
			return substr( $hash, strlen( $prefix ) );
		} else {
			return sprintf( '%s%s', $prefix, $hash );
		}
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
		$prefix = $this->prefix . ':f:';
		if ( strpos( $flag, $prefix ) === 0 ) {
			return substr( $flag, strlen( $prefix ) );
		} else {
			return sprintf( '%s%s', $prefix, $flag );
		}
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
			$keys = array();

			if ( ! empty( $flag ) ) {
				$keys = $this->get_cache_keys_by_flag( $flag );
			} else {
				foreach ( new Predis\Collection\Iterator\Keyspace( $this->redis, $this->prefix . ':c:*' ) as $key ) {
					$keys[] = $key;
				}
			}

			if ( ! is_array( $keys ) ) {
				return false;
			}

			$total_size = array_sum(
				array_map(
					function ( $key ) use ( $flag ) {
						return $this->redis->executeRaw( array( 'MEMORY', 'USAGE', ! empty( $flag ) ? $this->get_cache_key( $key ) : $key ) );
					},
					$keys
				)
			);

			return array(
				'index' => (int) count( $keys ),
				'size' => (int) round( $total_size / 1024 ),
			);
		} catch ( PredisException $e ) {
			error_log( 'Unable to get cache size from Redis: ' . $e->getMessage() );
			return false;
		}
	}
}
