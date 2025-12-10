<?php
/**
 * Set TTL Action
 *
 * Sets the cache time-to-live.
 *
 * @link        https://www.millipress.com
 * @since       1.0.0
 *
 * @package     MilliCache
 * @subpackage  Rules\Actions\PHP
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Rules\Actions\PHP;

use MilliCache\Deps\MilliRules\Actions\BaseAction;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class SetTtlAction
 *
 * Sets a custom TTL (time-to-live) for the cached page (trigger action).
 *
 * @since 1.0.0
 */
class SetTtl extends BaseAction {
	/**
	 * Get the action type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The action type identifier.
	 */
	public function get_type(): string {
		return 'set_ttl';
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
		$ttl = $this->get_arg( 0 )->int();

		// Set TTL override.
		millicache()->options()->set_ttl( $ttl );
	}
}
