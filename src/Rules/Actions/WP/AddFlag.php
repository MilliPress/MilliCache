<?php
/**
 * Add Flag Action
 *
 * Adds a cache flag.
 *
 * @package MilliCache
 * @subpackage Rules\Actions
 * @since 1.0.0
 */

namespace MilliCache\Rules\Actions\WP;

use MilliCache\Engine;
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
		$flag = $this->args[0] ?? null;

		// Resolve placeholders.
		if ( is_string( $flag ) ) {
			$flag = $this->resolve_value( $flag );
		}

		if ( empty( $flag ) || ! is_string( $flag ) ) {
			return;
		}

		// Call Engine to add the flag.
		if ( class_exists( '\\MilliCache\\Engine' ) ) {
			Engine::add_flag( $flag );
		}
	}
}
