<?php
/**
 * Custom rule registration from MilliBase Settings.
 *
 * @link        https://www.millipress.com
 * @since       1.7.0
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Rules;

use MilliCache\Base\Network;
use MilliCache\Base\Site;
use MilliCache\Engine\Utilities\Multisite;
use MilliRules\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Custom
 *
 * Called at PHP-phase alongside {@see Bootstrap}, {@see WordPress}, and
 * {@see RequestFlags}. PHP-typed settings rules register immediately;
 * WP-typed rules queue as pending and finalize when MilliRules loads the
 * WP package at plugins_loaded:1. Because this class runs *after*
 * {@see WordPress::register()}, its WP-typed rules sit later in the pending
 * queue and override unlocked same-ID built-ins on flush. Locked built-ins
 * still reject overrides.
 *
 * On multisite, network-scope rules register first and site-scope rules
 * second. Lock authority flows network → site: a network-locked rule cannot
 * be overridden by a site rule with the same ID, while site-only same-ID
 * rules without a network counterpart proceed normally. Reordering the
 * registration calls is a silent break of this authority model.
 *
 * On single-site, only the site path runs (the network scope delegates to
 * the site scope via {@see Network::settings()}).
 *
 * @since       1.7.0
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millipress.com>
 */
final class Custom {

	/**
	 * Register custom rules from network and site Settings stores.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public static function register(): void {
		if ( Multisite::is_enabled() ) {
			self::register_rule_list( (array) Network::settings()->get( 'rules.items', array() ) );
		}

		self::register_rule_list( (array) Site::settings()->get( 'rules.items', array() ) );
	}

	/**
	 * Register a list of rules.
	 *
	 * Each item is an array forwarded as-is to {@see Rules::register_rule()},
	 * which owns the schema (id, title, order, enabled, locked, hook,
	 * priority, match_type, conditions, actions; per-action `locked`
	 * translated to `_locked`), validation, and PackageManager handoff.
	 *
	 * @since 1.7.0
	 *
	 * @param array<array-key, mixed> $rules The rule items to register.
	 * @return void
	 */
	private static function register_rule_list( array $rules ): void {
		foreach ( $rules as $rule_data ) {
			if ( ! is_array( $rule_data ) ) {
				continue;
			}

			// Skip explicit no-ops and obvious garbage at the boundary.
			if ( array_key_exists( 'enabled', $rule_data ) && ! $rule_data['enabled'] ) {
				continue;
			}

			if ( '' === (string) ( $rule_data['id'] ?? '' ) ) {
				continue;
			}

			Rules::register_rule( $rule_data );
		}
	}
}
