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

use MilliCache\Deps\MilliRules\Actions\BaseAction;
use MilliCache\Deps\MilliRules\Context;
use MilliCache\Engine;

/**
 * Class ClearCacheAction
 *
 * Clears all cache entries associated with specific flag(s), post-ID(s) or URL(s).
 *
 * @since 1.0.0
 */
class ClearCache extends BaseAction {
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
		Engine::instance()->clear()->targets( $targets, $expire );
	}
}
