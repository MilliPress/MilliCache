<?php
/**
 * The file that defines the Redis class.
 *
 * @link       https://www.milli.press
 * @since      1.0.0
 *
 * @package    Millicache
 * @subpackage Millicache/includes
 */

! defined( 'ABSPATH' ) && exit;

/**
 * Handles Redis operations for the Millicache plugin.
 *
 * @since      1.0.0
 * @package    Millicache
 * @subpackage Millicache/includes
 * @author     Philipp Wellmer <hello@milli.press>
 */
final class Millicache_Redis {

	/**
	 * The Redis object.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      Redis    $redis    The Redis object.
	 */
	private $redis;

	/**
	 * The Redis host.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      string    $host    The Redis host.
	 */
	private $host = '127.0.0.1';

	/**
	 * The Redis port.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      int    $port    The Redis port.
	 */
	private $port = 6379;

	/**
	 * The Redis Server password.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      string    $password    The Redis auth.
	 */
	private $password = '';

	/**
	 * The Redis database.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      int    $db    The Redis database.
	 */
	private $db = 0;

	/**
	 * The Redis prefix.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      string    $prefix    The Redis prefix.
	 */
	private $prefix = 'mll';

	/**
	 * Whether to use a persistent connection.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      bool    $persistent    Whether to use a persistent connection.
	 */
	private $persistent = true;

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
		$this->connect();
	}

	/**
	 * Check if Redis is available.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool Whether Redis is available.
	 */
	public static function is_available() {
		return class_exists( 'Redis' );
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
	private function connect() {
		// Check if Redis is available.
		if ( ! self::is_available() ) {
			error_log( 'Redis class does not exist.' );
			return false;
		}

		try {
			// If Redis is already connected, return.
			if ( is_object( $this->redis ) && $this->redis->isConnected() ) {
				return true;
			}

			// Initialize Redis.
			$this->redis = new Redis();

			// Connect to Redis.
			$connect = $this->persistent ?
				$this->redis->pconnect( $this->host, $this->port ) :
				$this->redis->connect( $this->host, $this->port );

			// Authenticate and select database.
			if ( ! empty( $this->password ) ) {
				$this->redis->auth( $this->password );
			}

			$this->redis->select( $this->db );
			$this->redis->setOption( Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP );
		} catch ( RedisException $e ) {
			error_log( 'Unable to connect to Redis: ' . $e->getMessage() );
			return false;
		}

		if ( ! $connect ) {
			error_log( 'Unable to connect to Redis.' );
			return false;
		}

		return true;
	}

	/**
	 * Perform cache operations.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $hash The cache hash.
	 * @param mixed  $data The data to cache.
	 * @param array  $flags The flags associated with the cache.
	 * @param bool   $cache Whether to cache or delete the data.
	 * @return bool Whether the cache operation was successful.
	 */
	public function perform_cache( $hash, $data, $flags = array(), $cache = true ) {
		try {
			// Start a transaction.
			$this->redis->multi();

			if ( $cache ) {
				// Set cache entry.
				$this->set_cache( $hash, $data, $flags );
			} else {
				// Delete cache entry.
				$this->delete_cache( $hash );
			}

			// Unlock the cache entry.
			$this->unlock( $hash );

			// Execute the transaction.
			$this->redis->exec();

			return true;
		} catch ( RedisException $e ) {
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
	 * @return false|array The cached data.
	 */
	public function get_cache( $hash ) {
		try {
			// Get the cache entry and lock status.
			$key = $this->get_cache_key( $hash );

			// Start a transaction.
			$this->redis->multi();

			// Get entries and lock status.
			$this->redis->hGetAll( $key );
			$this->redis->get( $key . '-lock' );

			// Execute the transaction.
			list($cache, $lock_status) = $this->redis->exec();

			if ( ! $cache ) {
				return false;
			}

			// Sort out the flags.
			$flags = array();
			foreach ( array_keys( $cache ) as $key ) {
				if ( strpos( $key, $this->prefix . ':f:' ) === 0 ) {
					$flags[] = $this->get_flag_key( $key );
				}
			}

			// Return the data, flags and lock status.
			return isset( $cache['data'] ) ? array(
				$cache['data'],
				$flags,
				$lock_status,
			) : false;
		} catch ( RedisException $e ) {
			error_log( 'Unable to get cache from Redis: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Set cache.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $hash The cache hash.
	 * @param mixed  $data The data to cache.
	 * @param array  $flags The flags associated with the cache.
	 * @return bool Whether the cache operation was successful.
	 */
	public function set_cache( $hash, $data, $flags ) {
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

			// Start a transaction.
			$this->redis->multi();

			// Store the data.
			$this->redis->hSet( $key, 'data', $data );

			// Store the flags and add the key to the sets associated with the flags.
			foreach ( $flags as $flag ) {
				$flag = $this->get_flag_key( $flag );

				// Add the key to the set of the flag.
				$this->redis->hSet( $key, $flag, 1 );

				// Add the flag to the set of the key.
				$this->redis->sAdd( $flag, $key );
			}

			// Set the max expiration time to avoid stale data.
			$this->redis->expire( $key, self::$max_ttl );

			// Execute the transaction.
			$this->redis->exec();

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
		} catch ( RedisException $e ) {
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
	public function delete_cache( $hash ) {
		try {
			$key = $this->get_cache_key( $hash );

			// Get all flags of the key.
			$flags = $this->redis->hKeys( $key );

			/**
			 * Fires before a page cache is deleted in Redis.
			 *
			 * @param string $hash The cache URL hash.
			 * @param string $key The cache key.
			 * @param array  $flags The flags associated with the cache.
			 */
			do_action( 'millicache_before_page_cache_deleted', $hash, $key, $flags );

			// Start a transaction.
			$this->redis->multi();

			// Delete flags and remove the key from the sets associated with the flags.
			foreach ( $flags as $flag ) {
				if ( strpos( $flag, $this->prefix . ':f:' ) === 0 ) {
					// Remove the key from the set of the flag.
					$this->redis->sRem( $flag, $key );

					// If the set of the flag is empty, delete the flag.
					$n = $this->redis->sCard( $flag );
					if ( is_int( $n ) && 0 == $n ) {
						$this->redis->del( $flag );
					}
				}
			}

			// Delete the key.
			$this->redis->del( $key );

			// Execute the transaction.
			$this->redis->exec();

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
		} catch ( RedisException $e ) {
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
	public function lock( $hash ) {
		try {
			return $this->redis->set(
				$this->get_cache_key( $hash . '-lock' ),
				true,
				array(
					'nx',
					'ex' => 30,
				)
			);
		} catch ( RedisException $e ) {
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
	public function unlock( $hash ) {
		try {
			return (bool) $this->redis->del( $this->get_cache_key( $hash . '-lock' ) );
		} catch ( RedisException $e ) {
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
	 * @param array $sets The cache sets to clear.
	 * @param int   $ttl The time-to-live for the cache.
	 * @return void
	 */
	public function clear_cache_by_flags( $sets, $ttl ) {
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
					list($data, $flags, $locked) = $this->get_cache( $key );
					if ( $data && ! $locked ) {
						$data['updated'] -= $ttl;
						$this->set_cache( $key, $data, array() );
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
	 * @return array The cache keys associated with the flag.
	 */
	public function get_cache_keys_by_flag( $flag ) {
		try {
			// Check if flag contains any wildcard characters.
			if ( preg_match( '/[\*\?\[\]]/', $flag ) ) {
				$pattern = $this->get_flag_key( $flag );
				$iterator = null;
				$members = array();

				// Flag contains wildcard, use SCAN to get all keys.
				$keys = $this->redis->scan( $iterator, $pattern );
				foreach ( $keys as $key ) {
					$key_members = $this->redis->sMembers( $key );
					$members = array_merge( $members, $key_members );
				}
			} else {
				// Flag does not contain wildcard, directly call sMembers.
				$members = $this->redis->sMembers( $this->get_flag_key( $flag ) );
			}

			// Remove prefix from keys.
			return array_map(
				function ( $key ) {
					return $this->get_cache_key( $key );
				},
				array_unique( $members )
			);
		} catch ( RedisException $e ) {
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
	public function cleanup_expired_flags() {
		try {
			// Get all flags.
			$flags = $this->redis->keys( $this->prefix . ':f:*' );

			foreach ( $flags as $flag ) {
				// Get all keys in the set associated with the flag.
				$keys = $this->redis->sMembers( $flag );

				foreach ( $keys as $key ) {
					// If the key does not exist in Redis, remove it from the set.
					if ( ! $this->redis->exists( $key ) ) {
						$this->redis->sRem( $flag, $key );
					}
				}

				// If the set of the flag is empty, delete the flag.
				if ( 0 === $this->redis->sCard( $flag ) ) {
					$this->redis->del( $flag );
				}
			}

			return true;
		} catch ( RedisException $e ) {
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
	private function get_cache_key( $hash ) {
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
	private function get_flag_key( $flag ) {
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
	 * @return false|array The amount of cache keys & size of the cache in kilobytes.
	 */
	public function get_cache_size( $flag = '' ) {
		try {
			$keys = ! empty( $flag ) ? $this->get_cache_keys_by_flag( $flag ) : $this->redis->scan( $iterator, $this->prefix . ':c:*' );
			$total_size = array_sum(
				array_map(
					function ( $key ) use ( $flag ) {
						return $this->redis->rawCommand( 'MEMORY', 'USAGE', ! empty( $flag ) ? $this->get_cache_key( $key ) : $key );
					},
					$keys
				)
			);

			return array(
				'index' => count( $keys ),
				'size' => round( $total_size / 1024 ),
			);
		} catch ( RedisException $e ) {
			error_log( 'Unable to get cache size from Redis: ' . $e->getMessage() );
			return false;
		}
	}
}
