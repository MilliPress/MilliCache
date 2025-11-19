<?php
/**
 * Flush By Site Action
 *
 * Flushes cache for a specific site or current site.
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
 * Class FlushBySiteAction
 *
 * Flushes cache entries for a specific site (stop action).
 * Accepts optional site_id parameter, defaults to current site.
 *
 * @since 1.0.0
 */
class FlushBySite extends BaseAction {
	/**
	 * Get the action type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The action type identifier.
	 */
	public function get_type(): string {
		return 'flush_by_site';
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
		$site_id = $this->args[0] ?? null;

		// Resolve placeholders in site_id if it's a string.
		if ( is_string( $site_id ) ) {
			$site_id = $this->resolve_value( $site_id );
		}

		// Convert to array if single site_id provided.
		$site_ids = null;
		if ( null !== $site_id ) {
			$site_ids = is_array( $site_id ) ? $site_id : array( $site_id );
		}

		// Call Engine to clear cache for specified site(s) or current site.
		if ( class_exists( '\\MilliCache\\Engine' ) ) {
			Engine::clear_cache_by_site_ids( $site_ids );
		}
	}
}
