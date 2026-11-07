<?php
/**
 * Set Bucket Action
 *
 * Adds a bucket to the request hash for the current request.
 *
 * @link        https://www.millipress.com
 * @since       1.7.0
 *
 * @package     MilliCache
 * @subpackage  Rules\Actions\PHP
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Rules\Actions\PHP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MilliRules\Actions\ActionMeta;
use MilliRules\Actions\BaseAction;
use MilliRules\Context;

/**
 * Class SetBucket
 *
 * Registers a bucket (name + token) on the request resolver. The bucket
 * folds into the request hash so that variants sharing the same bucket
 * map to the same cache entry.
 *
 * @since 1.7.0
 */
class SetBucket extends BaseAction {

	/**
	 * Highest rule order seen per bucket name.
	 *
	 * Higher-order rules win, mirroring the SetGrace/SetTtl pattern.
	 * Tracked per-name so unrelated buckets don't suppress each other.
	 *
	 * @since 1.7.0
	 * @var array<string, int>
	 */
	private static array $last_orders = array();

	/**
	 * Declare metadata for the set_bucket action.
	 *
	 * @since 1.7.0
	 *
	 * @param ActionMeta $meta The metadata object to configure.
	 * @return void
	 */
	public static function set_meta( ActionMeta $meta ): void {
		$meta
			->label( __( 'Set Bucket', 'millicache' ) )
			->description( __( 'Add a bucket (name + token) to the request hash so matching requests share a cache entry.', 'millicache' ) )
			->categories( 'caching' )
			->args()
				->string( 'name' )
					->label( __( 'Bucket Name', 'millicache' ) )
					->description( __( 'Identifier for the bucket dimension (e.g. device, tenant, ab).', 'millicache' ) )
					->default( '' )
				->string( 'token' )
					->label( __( 'Token', 'millicache' ) )
					->description( __( 'Short canonical value folded into the request hash.', 'millicache' ) )
					->default( '' );
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
		return array( 'name', 'token' );
	}

	/**
	 * Get the action type.
	 *
	 * @since 1.7.0
	 *
	 * @return string The action type identifier.
	 */
	public function get_type(): string {
		return 'set_bucket';
	}

	/**
	 * Execute the action.
	 *
	 * @since 1.7.0
	 *
	 * @param Context $context The execution context.
	 * @return void
	 */
	public function execute( Context $context ): void {
		$name  = $this->usable_arg( 0 );
		$token = $this->usable_arg( 1 );

		if ( null === $name || null === $token ) {
			return;
		}

		$order = $context->get( 'rule.order' );
		$order = is_int( $order ) ? $order : 10;

		// Skip if a higher-order rule already set this bucket.
		if ( isset( self::$last_orders[ $name ] ) && $order < self::$last_orders[ $name ] ) {
			return;
		}
		self::$last_orders[ $name ] = $order;

		millicache()
			->request()
			->buckets()
			->add( $name, $token );
	}
}
