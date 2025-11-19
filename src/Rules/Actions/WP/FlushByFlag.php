<?php
/**
 * Flush By Flag Action
 *
 * Flushes cache entries by flag.
 *
 * @package MilliCache
 * @subpackage Rules\Actions
 * @since 1.0.0
 */

namespace MilliCache\Rules\Actions\WP;

use MilliCache\Engine;
use MilliRules\Actions\BaseAction;
use MilliRules\Context;

/**
 * Class FlushByFlagAction
 *
 * Flushes all cache entries associated with a specific flag (stop action).
 *
 * @since 1.0.0
 */
class FlushByFlag extends BaseAction {
	/**
	 * Get the action type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The action type identifier.
	 */
	public function get_type(): string {
		return 'flush_by_flag';
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
		$flags = $this->args[0] ?? null;

		// Resolve placeholders.
		if ( is_string( $flags ) ) {
			$flags = $this->resolve_value( $flags );
		}

		// Convert to array if a single flag.
		if ( ! is_array( $flags ) ) {
			$flags = array( $flags );
		}

		// Call Engine to clear the cache by flags.
		if ( class_exists( '\\MilliCache\\Engine' ) ) {
			Engine::clear_cache_by_flags( $flags );
		}
	}
}
