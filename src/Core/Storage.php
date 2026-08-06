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

use MilliBase\Logger;
use MilliCache\Engine;
use Predis;
use Predis\Client;
use Predis\Connection\ConnectionException;
use Predis\Connection\Resource\Exception\StreamInitException;
use Predis\PredisException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	 * The cache prefix.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @var      string    $prefix    The cache prefix.
	 */
	private string $prefix = 'mll';

	/**
	 * Whether the connection has failed during this request (Layer-1 fail-fast).
	 *
	 * @since    1.7.0
	 * @access   private
	 *
	 * @var      bool    $connection_failed
	 */
	private bool $connection_failed = false;

	/**
	 * The render-safe connection topology summary (from {@see Connection::describe()}).
	 *
	 * @since    1.7.0
	 * @access   private
	 *
	 * @var      array<string, mixed>    $connection_info
	 */
	private array $connection_info = array();

	/**
	 * Channel-prefixed logger for storage failures.
	 *
	 * @since 1.7.3
	 * @access private
	 *
	 * @var Logger $logger
	 */
	private $logger;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @since 1.7.3 Accepts an injected logger.
	 * @access public
	 *
	 * @param array<mixed> $settings The settings for the storage server connection.
	 * @param Logger|null  $logger   Shared logger instance; defaults to an own "MilliCache" channel.
	 *
	 * @return void
	 */
	public function __construct( array $settings, $logger = null ) {
		$this->logger = $logger ?? new Logger( 'MilliCache' );

		if ( isset( $settings['prefix'] ) && is_string( $settings['prefix'] ) && '' !== $settings['prefix'] ) {
			$this->prefix = $settings['prefix'];
		}

		$connection            = new Connection( $settings, $this->logger );
		$this->connection_info = $connection->describe();

		$client = $connection->client();

		if ( null === $client ) {
			// A disabled connection is a config error (already logged with its
			// precise reason during normalization); only a real connect failure
			// warrants this generic message here.
			if ( 'disabled' !== ( $this->connection_info['mode'] ?? '' ) ) {
				$this->logger->error( 'Unable to connect to the storage server.' );
			}
			return;
		}

		$this->client = $client;
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
	 * Check if the storage server's socket is currently open on this request.
	 *
	 * Predis uses lazy connections — the socket isn't opened until a command
	 * runs. This returns the current socket state and is best used as a
	 * "has this request talked to the cache yet?" breadcrumb, not as a
	 * health probe. For an active reachability check, use
	 * {@see self::ping()} (or {@see self::get_status()} for the full
	 * server diagnostic payload).
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool Whether the storage client socket is open.
	 */
	public function is_connected(): bool {
		return isset( $this->client ) && $this->client->isConnected();
	}

	/**
	 * Send a `PING` to the storage server and report whether it answered.
	 *
	 * Opens the lazy connection if needed and returns true on a `PONG`,
	 * false on any Predis exception. This is the correct signal for
	 * "is the server reachable right now?" — Site Health tests, the
	 * settings-page Status indicator, and any other place where the answer
	 * has to be accurate even on a request that hasn't yet read or written
	 * the cache.
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @return bool Whether the storage server responds to a PING.
	 */
	public function ping(): bool {
		if ( ! isset( $this->client ) ) {
			return false;
		}

		try {
			$this->client->ping();
			return true;
		} catch ( PredisException $e ) {
			unset( $e );
			return false;
		}
	}

	/**
	 * Run a storage operation through the single connection chokepoint.
	 *
	 * Skips the op (returning $fallback) when there is no client or the connection
	 * already failed this request; a connection failure trips the memoized flag.
	 *
	 * @since 1.7.0
	 * @access private
	 *
	 * @template TReturn
	 *
	 * @param callable(): TReturn $operation The storage operation to run.
	 * @param TReturn             $fallback  Returned when the op cannot run or fails.
	 * @param string              $message   Log prefix for command-level failures.
	 * @return TReturn The operation result, or the fallback.
	 */
	private function execute( callable $operation, $fallback, string $message = '' ) {
		if ( ! isset( $this->client ) || $this->connection_failed ) {
			return $fallback;
		}

		try {
			return $operation();
		} catch ( ConnectionException | StreamInitException $e ) {
			$this->connection_failed = true;
			$this->logger->error( 'Unable to connect to the storage server: ' . $e->getMessage() );
			return $fallback;
		} catch ( PredisException $e ) {
			if ( '' !== $message ) {
				$this->logger->error( $message . ': ' . $e->getMessage() );
			}
			return $fallback;
		}
	}

	/**
	 * Get cache entries (keyed by hash, flags always included). `$include_output`
	 * also transfers the dereferenced output body.
	 *
	 * @since 1.4.0
	 * @access public
	 *
	 * @param string $flag           Optional flag filter. Supports wildcards.
	 * @param bool   $include_output Whether to include the output body.
	 * @return array<string, array<string, mixed>> Entries keyed by cache hash.
	 */
	public function get_entries( string $flag = '', bool $include_output = false ): array {
		return $this->execute(
			function () use ( $flag, $include_output ) {
				$keys = empty( $flag )
					? $this->get_cache_keys_by_pattern( $this->toggle_cache_key( '*' ) )
					: $this->get_cache_keys_by_flag( $flag );

				if ( empty( $keys ) ) {
					return array();
				}

				$by_flag     = ! empty( $flag );
				$flag_prefix = $this->prefix . ':f:';

				$results = $this->client->pipeline(
					function ( $pipe ) use ( $keys, $by_flag ) {
						foreach ( $keys as $key ) {
							$full_key = $by_flag ? $this->toggle_cache_key( $key ) : $key;
							$pipe->hmget( $full_key, array( 'meta', 'output' ) );
							$pipe->hkeys( $full_key );
						}
					}
				);

				if ( ! is_array( $results ) ) {
					return array();
				}

				// Phase 1: collect output hashes per entry.
				$entries       = array();
				$output_hashes = array();
				foreach ( $keys as $i => $key ) {
					$values   = $results[ $i * 2 ] ?? null;
					$all_keys = $results[ $i * 2 + 1 ] ?? array();

					if ( ! is_array( $values ) || ! is_string( $values[0] ?? null ) ) {
						continue;
					}

					$entry         = $this->parse_meta( $values[0] );
					$output_hash   = isset( $values[1] ) && is_string( $values[1] ) ? $values[1] : '';
					$entry['size'] = 0;

					$entry['flags'] = array_map(
						array( $this, 'toggle_flag_key' ),
						is_array( $all_keys ) ? array_filter( $all_keys, fn( $k ) => is_string( $k ) && strpos( $k, $flag_prefix ) === 0 ) : array()
					);

					$hash             = $by_flag ? $key : $this->toggle_cache_key( $key );
					$entries[ $hash ] = $entry;

					if ( '' !== $output_hash ) {
						$output_hashes[ $hash ] = $output_hash;
					}
				}

				if ( empty( $output_hashes ) ) {
					return $entries;
				}

				// Phase 2: pipeline-fetch sizes (and bodies if requested) for unique hashes.
				$unique_hashes = array_values( array_unique( $output_hashes ) );
				$body_results  = $this->client->pipeline(
					function ( $pipe ) use ( $unique_hashes, $include_output ) {
						foreach ( $unique_hashes as $output_hash ) {
							$pipe->hstrlen( $this->output_key( $output_hash ), 'output' );
							if ( $include_output ) {
								$pipe->hget( $this->output_key( $output_hash ), 'output' );
							}
						}
					}
				);

				$stride       = $include_output ? 2 : 1;
				$hash_sizes   = array();
				$hash_outputs = array();
				if ( is_array( $body_results ) ) {
					foreach ( $unique_hashes as $i => $output_hash ) {
						$size                       = $body_results[ $i * $stride ] ?? 0;
						$hash_sizes[ $output_hash ] = is_numeric( $size ) ? (int) $size : 0;
						if ( $include_output ) {
							$body                         = $body_results[ $i * $stride + 1 ] ?? '';
							$hash_outputs[ $output_hash ] = is_string( $body ) ? $body : '';
						}
					}
				}

				foreach ( $output_hashes as $entry_hash => $output_hash ) {
					$entries[ $entry_hash ]['size'] = $hash_sizes[ $output_hash ] ?? 0;
					if ( $include_output ) {
						$entries[ $entry_hash ]['output'] = $hash_outputs[ $output_hash ] ?? '';
					}
				}

				return $entries;
			},
			array(),
			'Unable to get entries from the storage server'
		);
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
		return $this->execute(
			function () use ( $cache, $hash, $data, $flags ) {
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
			},
			false,
			'Unable to perform cache in the storage server'
		);
	}

	/**
	 * Get cache.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $hash The cache hash.
	 * @return null|array{array<string, mixed>, array<string>, string} The cached data.
	 */
	public function get_cache( string $hash ): ?array {
		return $this->execute(
			function () use ( $hash ) {
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
				$flags = array_map( array( $this, 'toggle_flag_key' ), $this->filter_flag_fields( array_keys( $cache ) ) );

				if ( empty( $cache ) || ! isset( $cache['output'] ) ) {
					return null;
				}

				// "output" field holds the output_hash; resolve to bytes.
				$output_hash = is_scalar( $cache['output'] ) ? (string) $cache['output'] : '';
				$output      = '';
				if ( '' !== $output_hash ) {
					$body = $this->client->hget( $this->output_key( $output_hash ), 'output' );
					if ( ! is_string( $body ) ) {
						// Body GC'd or missing — miss.
						return null;
					}
					$output = $body;
				}

				$data           = $this->parse_meta( $cache['meta'] ?? null );
				$data['output'] = $output;

				return array(
					$data,
					$flags,
					$lock_status,
				);
			},
			null,
			'Unable to get cache from the storage server'
		);
	}

	/**
	 * Set cache.
	 *
	 * Stores the response body in a content-addressable keyspace
	 * (`<prefix>:o:<output_hash>`) and points the request entry's `output`
	 * field at that hash. Multiple variants whose bodies hash to the same
	 * value automatically share storage; a Redis SET tracks referencing
	 * request keys so the body can be garbage-collected when the last
	 * referrer is removed.
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
		return $this->execute(
			function () use ( $hash, $data, $flags ) {
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

				// Hash the body for the content-addressable keyspace.
				$output      = isset( $data['output'] ) && is_string( $data['output'] ) ? $data['output'] : '';
				$output_hash = '' === $output ? '' : sha1( $output );

				// Read previous hash so we can release its reference if it's changing.
				$existing_output_hash = (string) ( $this->client->hget( $key, 'output' ) ?? '' );

				// Build metadata blob; output bytes live at output_key, not inline.
				$meta = array(
					'headers' => $data['headers'] ?? array(),
					'status'  => $data['status'] ?? 200,
					'gzip'    => ! empty( $data['gzip'] ),
					'updated' => $data['updated'] ?? time(),
				);

				if ( isset( $data['custom_ttl'] ) && is_numeric( $data['custom_ttl'] ) ) {
					$meta['custom_ttl'] = (int) $data['custom_ttl'];
				}

				if ( isset( $data['custom_grace'] ) && is_numeric( $data['custom_grace'] ) ) {
					$meta['custom_grace'] = (int) $data['custom_grace'];
				}

				$meta['url'] = $data['url'] ?? '';

				if ( isset( $data['variant'] ) && is_array( $data['variant'] ) ) {
					$meta['variant'] = $data['variant'];
				}

				// Surfaced top-level so get_cache_size can HMGET both without parsing every entry's meta JSON.
				$size_raw    = isset( $data['size_raw'] ) && is_numeric( $data['size_raw'] )
					? (int) $data['size_raw']
					: strlen( $output );
				$updated_top = is_numeric( $meta['updated'] ) ? (int) $meta['updated'] : time();

				$fields = array(
					'output'   => $output_hash,
					'meta'     => (string) wp_json_encode( $meta ),
					'updated'  => $updated_top,
					'size_raw' => $size_raw,
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

				// Get existing flag fields.
				$existing_flag_fields = $this->filter_flag_fields(
					array_map( 'strval', $this->client->hkeys( $key ) )
				);

				// Determine which ones to remove.
				$stale_flag_fields = array_diff( $existing_flag_fields, $flag_keys );

				$config = Engine::instance()->config();
				$ttl    = ( $meta['custom_ttl'] ?? $config->ttl ) + ( $meta['custom_grace'] ?? $config->grace ) + 3;

				$output_key   = '' === $output_hash ? '' : $this->output_key( $output_hash );
				$output_refs  = '' === $output_hash ? '' : $this->output_refs_key( $output_hash );

				// Execute the transaction.
				$this->client->transaction(
					function ( $tx ) use ( $key, $flag_keys, $fields, $stale_flag_fields, $ttl, $output_key, $output_refs, $output_hash, $output ) {
						// Remove stale flag fields and their flag-set memberships.
						foreach ( $stale_flag_fields as $stale_flag_field ) {
							$tx->hdel( $key, array( $stale_flag_field ) );
							$tx->srem( $stale_flag_field, array( $key ) );
						}

						// Store the request entry fields.
						$tx->hmset( $key, $fields );

						// Add key to flag sets.
						foreach ( $flag_keys as $flag_key ) {
							$tx->sadd( $flag_key, array( $key ) );
						}

						// Write the body keyspace if there is any output.
						if ( '' !== $output_hash ) {
							$tx->hsetnx( $output_key, 'output', $output );
							$tx->sadd( $output_refs, array( $key ) );
							$tx->expire( $output_key, $ttl );
							$tx->expire( $output_refs, $ttl );
						}

						$tx->expire( $key, $ttl );
					}
				);

				// Release the old body reference if the hash changed.
				if ( '' !== $existing_output_hash && $existing_output_hash !== $output_hash ) {
					$this->release_output( $existing_output_hash, $key );
				}

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
			},
			false,
			'Unable to set cache in the storage server'
		);
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
		return $this->execute(
			function () use ( $hash ) {
				$key = $this->toggle_cache_key( $hash );

				// Read fields up front so we can release the body reference after deletion.
				$entry_fields = $this->client->hgetall( $key );

				if ( empty( $entry_fields ) ) {
					return false;
				}

				$raw_flags   = $this->filter_flag_fields( array_keys( $entry_fields ) );
				$output_hash = isset( $entry_fields['output'] ) && is_scalar( $entry_fields['output'] ) ? (string) $entry_fields['output'] : '';

				// Canonical site-prefixed flags (e.g. 2:post:123).
				$flags = array_map( array( $this, 'toggle_flag_key' ), $raw_flags );

				// Decode the original request URL.
				$meta = $this->parse_meta( $entry_fields['meta'] ?? null );
				$url  = isset( $meta['url'] ) && is_string( $meta['url'] ) ? $meta['url'] : '';

				/**
				 * Fires before a cache entry is deleted in the storage server.
				 *
				 * @param string $hash The cache URL hash.
				 * @param string $key The cache key.
				 * @param array  $flags The canonical flags associated with the cache.
				 * @param string $url The original request URL.
				 */
				do_action( 'millicache_entry_deleting', $hash, $key, $flags, $url );

				$this->client->transaction(
					function ( $tx ) use ( $key, $raw_flags ) {

						// Delete flags and remove the key from the sets associated with the flags.
						foreach ( $raw_flags as $flag ) {
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

				// Release the body reference; GC if it was the last referrer.
				$this->release_output( $output_hash, $key );

				/**
				 * Fires after a cache entry is deleted in the storage server.
				 *
				 * @since 1.0.0
				 *
				 * @param string $hash The cache URL hash.
				 * @param string $key The cache key.
				 * @param array  $flags The canonical flags associated with the cache.
				 * @param string $url The original request URL.
				 */
				do_action( 'millicache_entry_deleted', $hash, $key, $flags, $url );

				return true;
			},
			false,
			'Unable to delete cache in the storage server'
		);
	}

	/**
	 * Build the Redis key for a body in the output keyspace.
	 *
	 * @since 1.6.0
	 * @access private
	 *
	 * @param string $output_hash The body's content hash.
	 * @return string The fully prefixed key.
	 */
	private function output_key( string $output_hash ): string {
		return $this->prefix . ':o:' . $output_hash;
	}

	/**
	 * Build the Redis key for a body's reference set.
	 *
	 * @since 1.6.0
	 * @access private
	 *
	 * @param string $output_hash The body's content hash.
	 * @return string The fully prefixed reference set key.
	 */
	private function output_refs_key( string $output_hash ): string {
		return $this->output_key( $output_hash ) . ':refs';
	}

	/**
	 * Release one referrer from a body and GC the body if it's the last one.
	 *
	 * Idempotent: a no-op when the hash is empty or the referrer isn't a member.
	 *
	 * @since 1.6.0
	 * @access private
	 *
	 * @param string $output_hash The body's content hash.
	 * @param string $referrer    The cache key that previously referenced it.
	 * @return void
	 */
	private function release_output( string $output_hash, string $referrer ): void {
		if ( '' === $output_hash ) {
			return;
		}

		$this->execute(
			function () use ( $output_hash, $referrer ) {
				$output_key  = $this->output_key( $output_hash );
				$output_refs = $this->output_refs_key( $output_hash );

				$this->client->srem( $output_refs, array( $referrer ) );
				$remaining = $this->client->scard( $output_refs );
				if ( 0 === (int) $remaining ) {
					$this->delete_prefixed( array( $output_key, $output_refs ) );
				}

				return null;
			},
			null,
			'Unable to release body reference in the storage server'
		);
	}

	/**
	 * Filter a list of field names to only those with the flag prefix.
	 *
	 * @since 1.4.0
	 * @access private
	 *
	 * @param array<string> $fields Field names.
	 * @return array<string> Fields matching the flag prefix.
	 */
	private function filter_flag_fields( array $fields ): array {
		$flag_prefix = $this->prefix . ':f:';

		return array_filter(
			$fields,
			fn( $field ) => strpos( $field, $flag_prefix ) === 0
		);
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
		return $this->execute(
			fn() => $this->client->sadd( $this->toggle_key( $key ), (array) $members ),
			0,
			'Storage::set_add failed'
		);
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
		return $this->execute(
			fn() => (array) $this->client->spop( $this->toggle_key( $key ), $count ),
			array(),
			'Storage::set_pop failed'
		);
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
		return $this->execute(
			fn() => $this->client->scard( $this->toggle_key( $key ) ),
			0,
			'Storage::set_count failed'
		);
	}

	/**
	 * Build a fully-prefixed key for the generic key/value surface.
	 *
	 * The caller owns the entire namespace after the storage prefix (e.g.
	 * `oc:group:id`); this only guarantees every key and scan pattern stays
	 * inside MilliCache's `<prefix>:` keyspace, so a pattern delete can never
	 * reach another application's keys and never needs `FLUSHDB`.
	 *
	 * @since 1.7.0
	 * @access private
	 *
	 * @param string $key The caller-namespaced key, without the storage prefix.
	 * @return string The fully-prefixed key.
	 */
	private function prefixed_key( string $key ): string {
		return $this->prefix . ':' . $key;
	}

	/**
	 * Delete already-prefixed keys with a single variadic `DEL`.
	 *
	 * @since 1.7.0
	 * @access private
	 *
	 * @param array<string> $keys Fully-prefixed keys.
	 * @return int The number of keys removed.
	 */
	private function delete_prefixed( array $keys ): int {
		if ( empty( $keys ) ) {
			return 0;
		}

		return (int) $this->client->del( ...array_values( $keys ) );
	}

	/**
	 * Read a raw string value through the generic key/value surface.
	 *
	 * The low-level counterpart to {@see self::get_cache()}: a plain `GET`
	 * with no body dereferencing, flags or metadata. Serialization is the
	 * caller's concern.
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @param string $key The caller-namespaced key.
	 * @return string|null The stored value, or null on a miss or unavailable storage.
	 */
	public function get( string $key ): ?string {
		return $this->execute(
			function () use ( $key ) {
				$value = $this->client->get( $this->prefixed_key( $key ) );
				return is_string( $value ) ? $value : null;
			},
			null,
			'Storage::get failed'
		);
	}

	/**
	 * Read many raw string values in a single round-trip (`MGET`).
	 *
	 * The batched counterpart to {@see self::get()}.
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @param array<int, string> $keys The caller-namespaced keys to read.
	 * @return array<string, string|null> Caller key => stored value (or null on a
	 *                                     miss). Empty when storage is unavailable.
	 */
	public function get_multiple( array $keys ): array {
		if ( empty( $keys ) ) {
			return array();
		}

		$keys = array_values( $keys );

		return $this->execute(
			function () use ( $keys ) {
				$prefixed = array_map( array( $this, 'prefixed_key' ), $keys );

				$values = $this->client->mget( ...$prefixed );

				$result = array();
				foreach ( $keys as $i => $key ) {
					$result[ $key ] = isset( $values[ $i ] ) && is_string( $values[ $i ] ) ? $values[ $i ] : null;
				}

				return $result;
			},
			array(),
			'Storage::get_multiple failed'
		);
	}

	/**
	 * Write a raw string value through the generic key/value surface.
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @param string $key   The caller-namespaced key.
	 * @param string $value The value to store. Serialization is the caller's concern.
	 * @param int    $ttl   Optional expiry in seconds; 0 stores without expiry.
	 * @return bool Whether the write succeeded.
	 */
	public function set( string $key, string $value, int $ttl = 0 ): bool {
		return $this->execute(
			function () use ( $key, $value, $ttl ) {
				$full   = $this->prefixed_key( $key );
				$result = $ttl > 0
					? $this->client->set( $full, $value, 'EX', $ttl )
					: $this->client->set( $full, $value );

				return (bool) $result;
			},
			false,
			'Storage::set failed'
		);
	}

	/**
	 * Delete one or more keys from the generic key/value surface.
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @param string ...$keys The caller-namespaced keys to delete.
	 * @return int The number of keys removed.
	 */
	public function delete( string ...$keys ): int {
		if ( empty( $keys ) ) {
			return 0;
		}

		return $this->execute(
			fn() => $this->delete_prefixed( array_map( array( $this, 'prefixed_key' ), $keys ) ),
			0,
			'Storage::delete failed'
		);
	}

	/**
	 * Delete every key matching a glob pattern within MilliCache's keyspace.
	 *
	 * SCANs `<prefix>:<pattern>` with a cursor (never the blocking `KEYS`) and
	 * removes matches in batches. The pattern is always scoped under the
	 * storage prefix, so a caller namespace (e.g. `oc:*`) can be flushed
	 * without ever touching page-cache keys or resorting to `FLUSHDB`.
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @param string $pattern The caller-namespaced glob pattern (e.g. `oc:*`).
	 * @return int The number of keys removed.
	 */
	public function delete_by_pattern( string $pattern ): int {
		return $this->execute(
			function () use ( $pattern ) {
				$deleted = 0;
				$batch   = array();

				foreach ( new Predis\Collection\Iterator\Keyspace( $this->client, $this->prefixed_key( $pattern ) ) as $key ) {
					if ( ! is_string( $key ) ) {
						continue;
					}

					$batch[] = $key;

					if ( count( $batch ) >= 512 ) {
						$deleted += $this->delete_prefixed( $batch );
						$batch    = array();
					}
				}

				if ( ! empty( $batch ) ) {
					$deleted += $this->delete_prefixed( $batch );
				}

				return $deleted;
			},
			0,
			'Storage::delete_by_pattern failed'
		);
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
		return $this->execute(
			function () use ( $hash ) {
				$status = $this->client->set(
					$this->toggle_cache_key( $hash . '-lock' ),
					true,
					'EX',
					30,
					'NX'
				);

				return (bool) $status;
			},
			false,
			'Unable to set lock in the storage server'
		);
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
		return $this->execute(
			fn() => (bool) $this->client->del( $this->toggle_cache_key( $hash . '-lock' ) ),
			false,
			'Unable to unlock in the storage server'
		);
	}

	/**
	 * Build the Redis hash key for a metrics bucket resolution —
	 * `<storage-prefix>:m:<site-prefix><resolution>` (e.g. `mll:m:h`, `mll:m:1:d`).
	 *
	 * @since 1.7.0
	 *
	 * @param string $prefix     Site/network prefix (`''`, `'1:'`, `'1:2:'`).
	 * @param string $resolution Bucket resolution (`h` or `d`).
	 * @return string
	 */
	private function metrics_key( string $prefix, string $resolution ): string {
		return $this->prefix . ':m:' . $prefix . $resolution;
	}

	/**
	 * Add deltas to metrics counter fields (HINCRBY batch).
	 *
	 * Best-effort: storage errors are swallowed so metrics can never disrupt
	 * a request.
	 *
	 * @since 1.7.0
	 *
	 * @param string             $prefix     Site/network prefix.
	 * @param string             $resolution Bucket resolution (`h` or `d`).
	 * @param array<string, int> $deltas     Field name => increment.
	 * @return void
	 */
	public function metrics_increment( string $prefix, string $resolution, array $deltas ): void {
		if ( empty( $deltas ) ) {
			return;
		}

		// Best-effort metrics: command-level errors are swallowed (no log message).
		$this->execute(
			function () use ( $prefix, $resolution, $deltas ) {
				$key = $this->metrics_key( $prefix, $resolution );
				$this->client->pipeline(
					function ( $pipe ) use ( $key, $deltas ) {
						foreach ( $deltas as $field => $delta ) {
							$pipe->hincrby( $key, (string) $field, $delta );
						}
					}
				);

				return null;
			},
			null
		);
	}

	/**
	 * Overwrite metrics counter fields with absolute values (HSET batch).
	 *
	 * Used by the daily rollup so re-running it is idempotent.
	 *
	 * @since 1.7.0
	 *
	 * @param string             $prefix     Site/network prefix.
	 * @param string             $resolution Bucket resolution (`h` or `d`).
	 * @param array<string, int> $values     Field name => absolute value.
	 * @return void
	 */
	public function metrics_set( string $prefix, string $resolution, array $values ): void {
		if ( empty( $values ) ) {
			return;
		}

		// Best-effort metrics: command-level errors are swallowed (no log message).
		$this->execute(
			function () use ( $prefix, $resolution, $values ) {
				$key = $this->metrics_key( $prefix, $resolution );
				$this->client->pipeline(
					function ( $pipe ) use ( $key, $values ) {
						foreach ( $values as $field => $value ) {
							$pipe->hset( $key, (string) $field, $value );
						}
					}
				);

				return null;
			},
			null
		);
	}

	/**
	 * Read every metrics counter field for a resolution (HGETALL).
	 *
	 * @since 1.7.0
	 *
	 * @param string $prefix     Site/network prefix.
	 * @param string $resolution Bucket resolution (`h` or `d`).
	 * @return array<string, int> Field name => value.
	 */
	public function metrics_read( string $prefix, string $resolution ): array {
		$raw = $this->execute(
			fn() => $this->client->hgetall( $this->metrics_key( $prefix, $resolution ) ),
			array()
		);

		$fields = array();
		foreach ( $raw as $field => $value ) {
			if ( is_numeric( $value ) ) {
				$fields[ (string) $field ] = (int) $value;
			}
		}

		return $fields;
	}

	/**
	 * Delete the named metrics counter fields (HDEL).
	 *
	 * @since 1.7.0
	 *
	 * @param string        $prefix     Site/network prefix.
	 * @param string        $resolution Bucket resolution (`h` or `d`).
	 * @param array<string> $fields     Field names to remove.
	 * @return void
	 */
	public function metrics_delete( string $prefix, string $resolution, array $fields ): void {
		if ( empty( $fields ) ) {
			return;
		}

		// Best-effort metrics: command-level errors are swallowed (no log message).
		$this->execute(
			fn() => $this->client->hdel( $this->metrics_key( $prefix, $resolution ), array_values( $fields ) ),
			null
		);
	}

	/**
	 * List the distinct site/network prefixes that have stored metrics
	 * (scans `<storage-prefix>:m:*`), for the nightly rollup.
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @return array<string> Distinct site/network prefixes.
	 */
	public function metrics_prefixes(): array {
		return $this->execute(
			function () {
				$base     = $this->prefix . ':m:';
				$base_len = strlen( $base );
				$prefixes = array();

				foreach ( new Predis\Collection\Iterator\Keyspace( $this->client, $base . '*' ) as $key ) {
					if ( ! is_string( $key ) ) {
						continue;
					}

					// Drop the base and the trailing resolution char (`h`/`d`).
					$prefix = substr( $key, $base_len, -1 );
					if ( ! in_array( $prefix, $prefixes, true ) ) {
						$prefixes[] = $prefix;
					}
				}

				return $prefixes;
			},
			array()
		);
	}

	/**
	 * Clear stale and deleted cache entries, running on shutdown.
	 *
	 * @since 1.0.0
	 * @since 1.8.0 Returns the number of entries removed.
	 * @access public
	 *
	 * @param array<array<string>> $sets The cache sets to clear.
	 * @param int                  $ttl The time-to-live for the cache.
	 * @return int Number of entries deleted or expired.
	 */
	public function clear_cache_by_sets( array $sets, int $ttl ): int {
		$cleared = 0;

		// Delete the stored entries for the deleted flags. Every flag is resolved
		// to its keys in one pipelined round-trip, de-duplicated so keys shared by
		// overlapping flags are deleted once rather than re-read per flag.
		if ( ! empty( $sets['mll:deleted-flags'] ) ) {
			foreach ( $this->get_cache_keys_by_flag( $sets['mll:deleted-flags'] ) as $key ) {
				if ( $this->delete_cache( $key ) ) {
					++$cleared;
				}
			}
		}

		// Expire the stored entries for the expired flags.
		if ( ! empty( $sets['mll:expired-flags'] ) ) {
			foreach ( $this->get_cache_keys_by_flag( $sets['mll:expired-flags'] ) as $key ) {
				$result = $this->get_cache( $key );
				if ( $result ) {
					list($data, $flags, $locked) = $result;
					if ( $data && ! $locked ) {
						$updated          = isset( $data['updated'] ) && is_numeric( $data['updated'] ) ? (int) $data['updated'] : time();
						$data['updated']  = $updated - $ttl;
						$this->set_cache( $key, $data, $flags );
						++$cleared;

						$url = isset( $data['url'] ) && is_string( $data['url'] ) ? $data['url'] : '';

						/**
						 * Fires after a cache entry is expired (aged out) in the storage server.
						 *
						 * Unlike deletion, the entry and its flag membership are preserved;
						 * only its freshness is reset. Edge/CDN mirrors should treat this as
						 * a purge signal since the origin will regenerate the response.
						 *
						 * @since 1.7.0
						 *
						 * @param string $hash The cache URL hash.
						 * @param string $key The cache key.
						 * @param array  $flags The canonical flags associated with the cache.
						 * @param string $url The original request URL, for edge/CDN mirrors.
						 */
						do_action( 'millicache_entry_expired', $key, $this->toggle_cache_key( $key ), $flags, $url );
					}
				}
			}
		}

		return $cleared;
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
		return $this->execute(
			function () use ( $pattern ) {
				$keys = array();
				foreach ( new Predis\Collection\Iterator\Keyspace( $this->client, $pattern ) as $key ) {
					if ( is_string( $key ) ) {
						$keys[] = $key;
					}
				}

				return $keys;
			},
			array(),
			'Unable to get keys by pattern from the storage server'
		);
	}

	/**
	 * Get cache keys for one or more flags in a single pipelined round-trip.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array<string> $flags The cache flag(s). Wildcards supported.
	 * @return array<string> The de-duplicated cache keys across all flags.
	 */
	public function get_cache_keys_by_flag( $flags ): array {
		$flags = is_array( $flags ) ? $flags : array( $flags );
		$flags = array_values( array_unique( array_filter( $flags, 'is_string' ) ) );

		if ( empty( $flags ) ) {
			return array();
		}

		return $this->execute(
			function () use ( $flags ) {
				// Expand every flag to the concrete flag-set key(s) to read.
				$set_keys = array();
				foreach ( $flags as $flag ) {
					if ( preg_match( '/[*?]/', $flag ) ) {
						// Wildcard: scan for the flag-set keys it matches.
						foreach ( $this->get_cache_keys_by_pattern( $this->toggle_flag_key( $flag ) ) as $set_key ) {
							$set_keys[] = $set_key;
						}
					} else {
						$set_keys[] = $this->toggle_flag_key( $flag );
					}
				}

				$set_keys = array_values( array_unique( $set_keys ) );

				if ( empty( $set_keys ) ) {
					return array();
				}

				// Read every flag set in one pipeline.
				$results = (array) $this->client->pipeline(
					function ( $pipe ) use ( $set_keys ) {
						foreach ( $set_keys as $set_key ) {
							$pipe->smembers( $set_key );
						}
					}
				);

				// Flatten members, keep strings, strip the cache-key prefix.
				$members = array_merge( array(), ...array_filter( $results, 'is_array' ) );
				$members = array_values( array_filter( $members, 'is_string' ) );

				return array_map(
					function ( string $key ) {
						return $this->toggle_cache_key( $key );
					},
					array_unique( $members )
				);
			},
			array(),
			'Unable to get entries with flags from the storage server'
		);
	}

	/**
	 * Resolve a flag pattern to the concrete flag names present in storage.
	 *
	 * @since 1.7.6
	 *
	 * @param string $pattern The flag pattern (may contain `*` / `?`).
	 * @return array<string> The concrete flag names matching the pattern.
	 */
	public function get_flags_by_pattern( string $pattern ): array {
		if ( ! preg_match( '/[*?]/', $pattern ) ) {
			return array( $pattern );
		}

		return $this->execute(
			function () use ( $pattern ) {
				return array_map(
					array( $this, 'toggle_flag_key' ),
					$this->get_cache_keys_by_pattern( $this->toggle_flag_key( $pattern ) )
				);
			},
			array(),
			'Unable to get flags by pattern from the storage server'
		);
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
		return $this->execute(
			function () {
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
			},
			false,
			'Unable to cleanup expired cache keys in the storage server'
		);
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
	 * @param mixed $json The raw meta value from storage (JSON string, or a
	 *                    non-string when the field is absent/corrupt).
	 * @return array<string, mixed> The parsed metadata.
	 */
	private function parse_meta( $json ): array {
		$decoded = is_string( $json ) ? json_decode( $json, true ) : null;
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
	 * Reports the headline size numbers, all derived from the same Redis
	 * pipeline pass:
	 *
	 * - `size` (net / physical): each unique body is counted once. Reflects how
	 *   much memory the storage server is actually holding for cached output.
	 *   This is the headline number shown in the UI.
	 * - `gross` (pre-dedup): each entry contributes the byte length of the
	 *   body it points to, even if multiple entries share the same body in
	 *   the deduplicated output keyspace. The ratio `gross / size` is the
	 *   dedup factor.
	 * - `raw`: like `gross` but in pre-compression bytes — each entry
	 *   contributes its original HTML length (the per-entry `size_raw`). Falls
	 *   back to compressed body size for entries cached before it was tracked.
	 * - `unique`: number of distinct stored bodies (denominator that explains
	 *   the dedup ratio).
	 * - `largest`: byte length of the biggest single stored body — surfaces
	 *   outliers and bloated pages.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $flag Get cache by flag. Supports wildcards.
	 * @return false|array{index: int, size: int, gross: int, raw: int, unique: int, largest: int} The entry count, net (physical) size, gross (pre-dedup) size, pre-dedup uncompressed size, unique-body count, and largest body.
	 */
	public function get_cache_size( string $flag = '' ) {
		return $this->execute(
			function () use ( $flag ) {
				$keys = empty( $flag )
					? $this->get_cache_keys_by_pattern( $this->toggle_cache_key( '*' ) )
					: $this->get_cache_keys_by_flag( $flag );

				$empty = array(
					'index'   => 0,
					'size'    => 0,
					'gross'   => 0,
					'raw'     => 0,
					'unique'  => 0,
					'largest' => 0,
				);

				if ( empty( $keys ) ) {
					return $empty;
				}

				// Entries written before `size_raw` existed return null for
				// that slot; we fall back to compressed body length downstream so
				// they contribute zero compression savings (graceful under-count).
				$by_flag = ! empty( $flag );
				$entries = $this->client->pipeline(
					function ( $pipe ) use ( $keys, $by_flag ) {
						foreach ( $keys as $key ) {
							$pipe->hmget(
								$by_flag ? $this->toggle_cache_key( $key ) : $key,
								array( 'output', 'size_raw' )
							);
						}
					}
				);

				if ( ! is_array( $entries ) ) {
					return array_merge( $empty, array( 'index' => count( $keys ) ) );
				}

				$entry_hashes   = array();
				$entry_orig_raw = array();
				foreach ( $entries as $row ) {
					$hash = '';
					$orig = null;
					if ( is_array( $row ) ) {
						$hash = isset( $row[0] ) && is_scalar( $row[0] ) ? (string) $row[0] : '';
						$orig = isset( $row[1] ) && is_numeric( $row[1] ) ? (int) $row[1] : null;
					}
					$entry_hashes[]   = $hash;
					$entry_orig_raw[] = $orig;
				}

				$unique = array_values( array_unique( array_filter( $entry_hashes ) ) );

				if ( empty( $unique ) ) {
					return $empty;
				}

				// Phase 2: fetch unique body strlens.
				$strlens = $this->client->pipeline(
					function ( $pipe ) use ( $unique ) {
						foreach ( $unique as $output_hash ) {
							$pipe->hstrlen( $this->output_key( $output_hash ), 'output' );
						}
					}
				);

				$hash_sizes = array();
				if ( is_array( $strlens ) ) {
					foreach ( $unique as $i => $output_hash ) {
						$hash_sizes[ $output_hash ] = is_numeric( $strlens[ $i ] ?? 0 ) ? (int) $strlens[ $i ] : 0;
					}
				}

				$gross       = 0;
				$raw         = 0;
				$valid_count = 0;
				foreach ( $entry_hashes as $i => $output_hash ) {
					if ( '' === $output_hash ) {
						continue;
					}
					$size = $hash_sizes[ $output_hash ] ?? 0;
					if ( $size <= 0 ) {
						continue;
					}
					$gross += $size;
					$raw   += $entry_orig_raw[ $i ] ?? $size;
					++$valid_count;
				}

				return array(
					'index'   => $valid_count,
					'size'    => array_sum( $hash_sizes ),
					'gross'   => $gross,
					'raw'     => $raw,
					'unique'  => count( $hash_sizes ),
					'largest' => $hash_sizes ? max( $hash_sizes ) : 0,
				);
			},
			false,
			'Unable to get cache size from the storage server'
		);
	}

	/**
	 * Get meaningful Storage Server config and info.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array{connected: bool, config: array<mixed>, info: array<string, array<string, mixed>>, error?: string} The Storage Server status.
	 */
	public function get_status(): array {
		$status = array(
			'connected' => false,
			'config'    => array_merge(
				array( 'prefix' => $this->prefix ),
				$this->connection_info
			),
			'info'      => array(),
		);

		if ( ! isset( $this->client ) ) {
			if ( 'disabled' === ( $this->connection_info['mode'] ?? '' ) ) {
				$reason = isset( $this->connection_info['reason'] ) && is_string( $this->connection_info['reason'] ) && '' !== $this->connection_info['reason']
					? $this->connection_info['reason']
					: 'MC_STORAGE_HOST is misconfigured.';

				$status['error'] = 'Storage is disabled: ' . $reason;
			}

			return $status;
		}

		// Try to connect if not already connected (Predis uses lazy connections).
		try {
			$this->client->ping();
			$status['connected'] = true;
		} catch ( PredisException $e ) {
			$status['error'] = $e->getMessage();
			return $status;
		}

		// INFO/CONFIG are node-specific and rejected on an aggregate
		// (replication/sentinel) connection, so target the master node for
		// diagnostics. Falls back to connected-only if a master can't be resolved.
		$node = $this->client;
		if ( 'single' !== ( $this->connection_info['mode'] ?? 'single' ) ) {
			try {
				$node = $this->client->getClientBy( 'role', 'master' );
			} catch ( \Throwable $e ) {
				unset( $e );
				return $status;
			}
		}

		// Get the storage server config.
		$config_keys = array(
			'databases',
			'maxmemory',
			'maxmemory-policy',
		);

		try {
			foreach ( $config_keys as $key ) {
				$status['config'] = array_merge( $status['config'], (array) $node->config( 'GET', $key ) );
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
			'Stats'  => array(
				'keyspace_hits',
				'keyspace_misses',
				'evicted_keys',
			),
		);

		$server_section = array();

		try {
			foreach ( $info_keys as $section => $keys ) {
				$info         = $node->info( $section );
				$section_data = isset( $info[ $section ] ) && is_array( $info[ $section ] ) ? $info[ $section ] : array();

				if ( 'Server' === $section ) {
					$server_section = $section_data;
				}

				foreach ( $keys as $key ) {
					if ( isset( $section_data[ $key ] ) ) {
						$status['info'][ $section ][ $key ] = $section_data[ $key ];
					}
				}
			}
		} catch ( PredisException $e ) {
			// INFO may be unavailable (e.g. restricted managed Redis). Skip gracefully.
			unset( $e );
		}

		// Add the server type and version.
		$types = array(
			'valkey_version' => 'Valkey',
			'keydb_version' => 'KeyDB',
			'dragonfly_version' => 'Dragonfly',
			'redis_version' => 'Redis',
		);

		foreach ( $types as $key => $type ) {
			if ( isset( $server_section[ $key ] ) && is_scalar( $server_section[ $key ] ) ) {
				$status['info']['Server']['version'] = $type . ' ' . $server_section[ $key ];
				break;
			}
		}

		return $status;
	}
}
