<?php
/**
 * Add Flag Action
 *
 * Adds a cache flag.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package     MilliCache
 * @subpackage  Rules\Actions\WP
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Rules\Actions\WP;

use MilliCache\Deps\MilliRules\Actions\BaseAction;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class AddFlagAction
 *
 * Adds a flag to the cached page for later bulk invalidation (trigger action).
 *
 * @since 1.0.0
 */
class AddFlag extends BaseAction {
	/**
	 * Get the action type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The action type identifier.
	 */
	public function get_type(): string {
		return 'add_flag';
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
		$flag = $this->get_arg( 0 )->string();

		// Call Engine's flag manager to add the flag.
		millicache()->flags()->add( $flag );
	}
}
