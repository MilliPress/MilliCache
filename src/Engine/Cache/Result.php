<?php
/**
 * Cache retrieval result value object.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package     MilliCache
 * @subpackage  Engine\Cache
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Engine\Cache;

! defined( 'ABSPATH' ) && exit;

/**
 * Value object representing the result of a cache retrieval operation.
 *
 * Encapsulates the cache entry along with metadata about the retrieval
 * such as flags and lock status.
 *
 * @since      1.0.0
 * @package    MilliCache
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class Result {

	/**
	 * The cache entry (null if cache miss).
	 *
	 * @var Entry|null
	 */
	public ?Entry $entry;

	/**
	 * Flags associated with this cache entry.
	 *
	 * @var array<string>
	 */
	public array $flags;

	/**
	 * Whether the cache entry is locked for regeneration.
	 *
	 * @var bool
	 */
	public bool $locked;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Entry|null    $entry  The cache entry or null for cache miss.
	 * @param array<string> $flags  Associated flags.
	 * @param bool          $locked Whether entry is locked.
	 */
	public function __construct( $entry, array $flags = array(), $locked = false ) {
		$this->entry  = $entry;
		$this->flags  = $flags;
		$this->locked = (bool) $locked;
	}

	/**
	 * Create cache miss result.
	 *
	 * @since 1.0.0
	 *
	 * @return self Cache miss result.
	 */
	public static function miss(): self {
		return new self( null, array(), false );
	}

	/**
	 * Create cache hit result.
	 *
	 * @since 1.0.0
	 *
	 * @param Entry         $entry  The cache entry.
	 * @param array<string> $flags  Associated flags.
	 * @param bool          $locked Whether entry is locked.
	 * @return self Cache hit result.
	 */
	public static function hit( Entry $entry, array $flags = array(), bool $locked = false ): self {
		return new self( $entry, $flags, $locked );
	}

	/**
	 * Check if this is a cache hit.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if cache was found.
	 */
	public function is_hit(): bool {
		return null !== $this->entry;
	}

	/**
	 * Check if this is a cache miss.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if cache was not found.
	 */
	public function is_miss(): bool {
		return null === $this->entry;
	}
}
