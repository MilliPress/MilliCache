<?php
/**
 * Set Grace Action
 *
 * Sets the cache grace period.
 *
 * @link       https://www.millipress.com
 * @since      1.0.0
 *
 * @package     MilliCache
 * @subpackage Rules\Actions\PHP
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Rules\Actions\PHP;

use MilliCache\Engine;
use MilliCache\Deps\MilliRules\Actions\BaseAction;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class SetGraceAction
 *
 * Sets a custom grace period for stale cache serving (trigger action).
 *
 * @since 1.0.0
 */
class SetGrace extends BaseAction {
	/**
	 * Get the action type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The action type identifier.
	 */
	public function get_type(): string {
		return 'set_grace';
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
		$grace = $this->get_arg( 0 )->int();

		// Call Engine to set the grace period.
		Engine::set_grace( $grace );
	}
}
