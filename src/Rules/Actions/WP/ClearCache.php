<?php
/**
 * Clear Cache Action
 *
 * Clears cache entries by flag(s), post-ID(s) or URL(s).
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package     MilliCache
 * @subpackage  Rules\Actions\WP
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Rules\Actions\WP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MilliRules\Actions\ActionMeta;
use MilliRules\Actions\BaseAction;
use MilliRules\Context;

/**
 * Class ClearCacheAction
 *
 * Clears all cache entries associated with specific flag(s), post-ID(s) or URL(s).
 *
 * @since 1.0.0
 */
class ClearCache extends BaseAction {
	/**
	 * Declare metadata for the clear_cache action.
	 *
	 * @since 1.5.0
	 *
	 * @param ActionMeta $meta The metadata object to configure.
	 * @return void
	 */
	public static function set_meta( ActionMeta $meta ): void {
		$meta
			->label( __( 'Clear Cache', 'millicache' ) )
			->description( __( 'Clear cache entries by flag(s), post-ID(s) or URL(s).', 'millicache' ) )
			->categories( 'caching' )
			->args()
				->string( 'targets' )
					->label( __( 'Targets', 'millicache' ) )
					->description( __( 'Cache entries to clear — flag patterns, post IDs, or URLs.', 'millicache' ) )
					->also_accepts( 'array' )
					->required()
				->boolean( 'expire' )
					->label( __( 'Expire Only', 'millicache' ) )
					->description( __( 'Mark entries as stale instead of deleting, allowing grace-period serving.', 'millicache' ) )
					->default( false );
	}

	/**
	 * Declare the builder argument order.
	 *
	 * Mirrors the keys declared in {@see self::set_meta()} so positional builder
	 * arguments and stored named arguments resolve to the same values.
	 *
	 * @since 1.8.3
	 *
	 * @return array<int, string> Argument names in positional order.
	 */
	public static function get_argument_mapping(): array {
		return array( 'targets', 'expire' );
	}

	/**
	 * Get the action type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The action type identifier.
	 */
	public function get_type(): string {
		return 'clear_cache';
	}

	/**
	 * Execute the action.
	 *
	 * @since 1.0.0
	 *
	 * @param Context $context The execution context.
	 * @return void
	 */
	public function execute( Context $context ): void {
		/**
		 * Cache clearing targets.
		 *
		 * @var array<string|int> $targets
		 */
		$targets = $this->get_arg( 0 )->array();
		$expire = $this->get_arg( 1, false )->bool();

		// Call Engine to clear cache for specified targets.
		millicache()->clear()->targets( $targets, $expire );
	}
}
