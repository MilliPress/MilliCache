<?php
/**
 * Remove Flag Action
 *
 * Removes a cache flag.
 *
 * @package MilliCache
 * @subpackage Rules\Actions
 * @since 1.0.0
 */

namespace MilliCache\Rules\Actions;

use MilliCache\Engine;

/**
 * Class RemoveFlagAction
 *
 * Removes a flag from the cached page (trigger action).
 *
 * @since 1.0.0
 */
class RemoveFlag extends BaseAction {
	/**
	 * Whether this is a trigger action.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected bool $is_trigger = true;

	/**
	 * Get the action type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The action type identifier.
	 */
	public function get_type(): string {
		return 'remove_flag';
	}

	/**
	 * Execute the action.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context.
	 * @return void
	 */
	public function execute( array $context ): void {
		$flag = $this->value;

		// Resolve placeholders.
		if ( is_string( $flag ) ) {
			$flag = $this->resolve_value( $flag );
		}

		if ( empty( $flag ) || ! is_string( $flag ) ) {
			return;
		}

		// Call Engine to remove the flag.
		if ( class_exists( '\\MilliCache\\Engine' ) ) {
			Engine::remove_flag( $flag );
		}
	}
}
