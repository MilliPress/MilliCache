<?php
/**
 * REST handlers for the MilliCache settings pages' cache action endpoint.
 *
 * @link       https://www.millipress.com
 * @since      1.7.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/UI
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Admin\UI;

use MilliCache\Admin\Utils;
use MilliCache\Engine;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles `clear`, `clear_current`, and `clear_targets` cache actions from
 * the per-site and Network Admin settings pages.
 *
 * Both pages register a `cache` REST endpoint (under their own namespace);
 * the per-site flavour serves all three actions while the network flavour
 * serves the two network-aware ones (`clear`, `clear_targets`).
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin/UI
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class CacheActions {

	/**
	 * The MilliCache engine instance.
	 *
	 * @since 1.7.0
	 * @var Engine
	 */
	private Engine $engine;

	/**
	 * Construct a CacheActions handler.
	 *
	 * @since 1.7.0
	 *
	 * @param Engine $engine The cache engine.
	 */
	public function __construct( Engine $engine ) {
		$this->engine = $engine;
	}

	/**
	 * Handle cache clear actions from the per-site REST API.
	 *
	 * @since 1.7.0
	 * @since 1.8.0 The boolean `expire` param marks entries stale instead
	 *              of deleting them (stale-while-revalidate regeneration).
	 *
	 * @param \WP_REST_Request $request The REST API request object.
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_site( \WP_REST_Request $request ) {
		$action  = $request->get_param( 'action' );
		$expire  = (bool) $request->get_param( 'expire' );
		$skipped = array();

		/**
		 * Filters allowed REST cache actions.
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $allowed_actions Array of allowed REST cache action slugs.
		 */
		$allowed_actions = apply_filters(
			'millicache_rest_cache_allowed_actions',
			array( 'clear', 'clear_current', 'clear_targets' )
		);

		if ( ! is_string( $action ) || ! in_array( $action, $allowed_actions, true ) ) {
			return $this->error( 'invalid_action', __( 'Invalid cache action.', 'millicache' ) );
		}

		try {
			switch ( $action ) {
				case 'clear':
					if ( (bool) $request->get_param( 'is_network_admin' ) ) {
						$this->engine->clear()->networks( null, $expire );
					} else {
						$this->engine->clear()->sites( null, null, $expire );
					}
					break;

				case 'clear_current':
					$flags = $this->parse_request_flags( $request );
					if ( empty( $flags ) ) {
						return $this->error( 'no_flags', __( 'No flags provided to clear cache.', 'millicache' ) );
					}
					// url: flag is stored unprefixed (even on multisite).
					$this->engine->clear()->flags( $flags, $expire, false );
					break;

				case 'clear_targets':
					$targets = $this->read_targets( $request );
					if ( null === $targets ) {
						return $this->error( 'invalid_targets', __( 'Invalid targets parameter. Must be a string or an array.', 'millicache' ) );
					}
					$this->engine->clear()->targets( $targets, $expire );
					$skipped = $this->engine->clear()->skipped();
					break;
			}

			$cleared = $this->engine->clear()->execute_queue();

			/**
			 * Fires after a MilliCache cache action has been processed.
			 *
			 * @since 1.0.0
			 *
			 * @param string           $action  The action that was processed.
			 * @param array            $params  The parameters passed to the action.
			 * @param \WP_REST_Request $request The REST API request object.
			 */
			do_action( 'millicache_rest_cache_action_performed', $action, $request->get_params(), $request );

			return $this->response( $action, $cleared, $expire, $skipped );
		} catch ( \Exception $e ) {
			return $this->error(
				'cache_action_failed',
				__( 'Failed to perform cache action: ', 'millicache' ) . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Handle network-scoped cache-clear actions.
	 *
	 * Mirrors {@see self::handle_site()} but the empty-targets fallback
	 * clears every site instead of the current one, and targets are queued
	 * as raw flag patterns (no per-site prefix prepended).
	 *
	 * @since 1.7.0
	 * @since 1.8.0 The boolean `expire` param marks entries stale instead
	 *              of deleting them (stale-while-revalidate regeneration).
	 *
	 * @param \WP_REST_Request $request The REST API request object.
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_network( \WP_REST_Request $request ) {
		$action = $request->get_param( 'action' );
		$expire = (bool) $request->get_param( 'expire' );

		try {
			switch ( $action ) {
				case 'clear':
					$this->engine->clear()->networks( null, $expire );
					break;

				case 'clear_targets':
					$targets = $this->read_targets( $request );
					if ( null === $targets ) {
						return $this->error( 'invalid_targets', __( 'Invalid targets parameter. Must be a string or an array.', 'millicache' ) );
					}
					if ( empty( $targets ) ) {
						$this->engine->clear()->networks( null, $expire );
					} else {
						$this->clear_network_targets( $targets, $expire );
					}
					break;

				default:
					return $this->error( 'invalid_action', __( 'Invalid cache action.', 'millicache' ) );
			}

			$cleared = $this->engine->clear()->execute_queue();

			/**
			 * Fires after a MilliCache network cache action has been processed.
			 *
			 * @since 1.7.0
			 *
			 * @param string           $action  The action that was processed.
			 * @param array            $params  The parameters passed to the action.
			 * @param \WP_REST_Request $request The REST API request object.
			 */
			do_action(
				'millicache_rest_network_cache_action_performed',
				is_string( $action ) ? $action : '',
				$request->get_params(),
				$request
			);

			return $this->response( is_string( $action ) ? $action : '', $cleared, $expire );
		} catch ( \Exception $e ) {
			return $this->error(
				'cache_action_failed',
				__( 'Failed to perform cache action: ', 'millicache' ) . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Queue and execute network-admin target clears.
	 *
	 * Each target is queued as a raw flag pattern (no per-site prefix
	 * prepended) so `5:*` clears site 5, `*:posts` clears every site's
	 * `posts` flag, and `5:posts` clears one specific flag.
	 *
	 * @since 1.7.0
	 * @since 1.8.0 Added the `$expire` parameter.
	 *
	 * @param string|array<int, mixed> $targets Targets from the REST request.
	 * @param bool                     $expire  Expire (true) or delete (false).
	 * @return void
	 */
	private function clear_network_targets( $targets, bool $expire = false ): void {
		$list  = is_array( $targets ) ? $targets : array( $targets );
		$clear = $this->engine->clear();

		foreach ( $list as $target ) {
			if ( ! is_string( $target ) || '' === $target ) {
				continue;
			}
			$clear->flags( $target, $expire, false );
		}
	}

	/**
	 * Extract a string-only list of flag names from the `request_flags` param.
	 *
	 * Accepts either a JSON-encoded string or an array; non-string entries
	 * are dropped.
	 *
	 * @since 1.7.0
	 *
	 * @param \WP_REST_Request $request The REST API request object.
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 * @return array<int, string>
	 */
	private function parse_request_flags( \WP_REST_Request $request ): array {
		$raw = $request->get_param( 'request_flags' );

		if ( null === $raw ) {
			return array();
		}

		$list = is_string( $raw )
			? (array) json_decode( $raw, true )
			: ( is_array( $raw ) ? $raw : array() );

		return array_values( array_filter( $list, 'is_string' ) );
	}

	/**
	 * Read the untrusted `targets` REST param.
	 *
	 * The param arrives as `mixed`; this is the single place that validates
	 * it. Array entries are reduced to scalar (string/int) targets.
	 *
	 * @since 1.7.0
	 *
	 * @param \WP_REST_Request $request The REST API request object.
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 * @return array<int|string>|string|null Filtered list, raw string, or null when invalid.
	 */
	private function read_targets( \WP_REST_Request $request ) {
		$raw = $request->get_param( 'targets' );

		if ( is_string( $raw ) ) {
			return $raw;
		}

		if ( is_array( $raw ) ) {
			return array_values( array_filter( $raw, static fn ( $t ) => is_string( $t ) || is_int( $t ) ) );
		}

		return null;
	}

	/**
	 * Build the standard cache-action success response.
	 *
	 * @since 1.7.0
	 * @since 1.8.0 Reports the number of entries actually removed and
	 *              words the message for expire vs delete.
	 *
	 * @param string             $action  Action that was performed.
	 * @param int                $cleared Number of entries deleted or expired.
	 * @param bool               $expire  Whether entries were expired instead of deleted.
	 * @param array<int, string> $skipped Targets that belong to another site.
	 * @return \WP_REST_Response
	 */
	private function response( string $action, int $cleared, bool $expire = false, array $skipped = array() ): \WP_REST_Response {
		$payload = array(
			'success'   => true,
			'message'   => Utils::cleared_entries_message( $cleared, $expire ),
			'cleared'   => $cleared,
			'action'    => $action,
			'timestamp' => time(),
		);

		// Without this a caller cannot tell an empty cache from a target that
		// never applied, since both report zero cleared entries.
		if ( array() !== $skipped ) {
			$payload['skipped'] = array_values( $skipped );
			$payload['message'] = sprintf(
				/* translators: 1: the result message; 2: comma-separated list of URLs that belong to another site. */
				__( '%1$s Skipped, not on this site: %2$s', 'millicache' ),
				$payload['message'],
				implode( ', ', $skipped )
			);
		}

		return rest_ensure_response( $payload );
	}

	/**
	 * Build a cache-action error response.
	 *
	 * @since 1.7.0
	 *
	 * @param string $code    Error slug.
	 * @param string $message User-facing message.
	 * @param int    $status  HTTP status (default 400).
	 * @return \WP_Error
	 */
	private function error( string $code, string $message, int $status = 400 ): \WP_Error {
		return new \WP_Error( $code, $message, array( 'status' => $status ) );
	}
}
