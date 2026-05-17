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

use MilliCache\Engine;

! defined( 'ABSPATH' ) && exit;

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
	 *
	 * @param \WP_REST_Request $request The REST API request object.
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_site( \WP_REST_Request $request ) {
		$action = $request->get_param( 'action' );

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
						$this->engine->clear()->networks();
						$message = __( 'The network cache has been cleared.', 'millicache' );
					} else {
						$this->engine->clear()->sites();
						$message = __( 'The site cache has been cleared.', 'millicache' );
					}
					break;

				case 'clear_current':
					$flags = $this->parse_request_flags( $request );
					if ( empty( $flags ) ) {
						return $this->error( 'no_flags', __( 'No flags provided to clear cache.', 'millicache' ) );
					}
					// url: flag is stored unprefixed (even on multisite) —
					// clear as-is. Empty already handled above.
					$this->engine->clear()->flags( $flags, false, false );
					$message = __( 'The current page cache has been cleared.', 'millicache' );
					break;

				case 'clear_targets':
					$targets = $request->get_param( 'targets' );
					if ( ! is_string( $targets ) && ! is_array( $targets ) ) {
						return $this->error( 'invalid_targets', __( 'Invalid targets parameter. Must be a string or an array.', 'millicache' ) );
					}
					$this->engine->clear()->targets( $targets );
					$message = empty( $targets )
						? __( 'The site cache has been cleared.', 'millicache' )
						: __( 'Cache for the targets has been cleared.', 'millicache' );
					break;
			}

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

			return $this->response( $action, $message ?? '' );
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
	 *
	 * @param \WP_REST_Request $request The REST API request object.
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_network( \WP_REST_Request $request ) {
		$action = $request->get_param( 'action' );

		try {
			switch ( $action ) {
				case 'clear':
					$this->engine->clear()->networks()->execute_queue();
					$message = __( 'The network cache has been cleared.', 'millicache' );
					break;

				case 'clear_targets':
					$targets = $request->get_param( 'targets' );
					if ( ! is_string( $targets ) && ! is_array( $targets ) ) {
						return $this->error( 'invalid_targets', __( 'Invalid targets parameter. Must be a string or an array.', 'millicache' ) );
					}
					if ( empty( $targets ) ) {
						$this->engine->clear()->networks()->execute_queue();
						$message = __( 'The network cache has been cleared.', 'millicache' );
					} else {
						$this->clear_network_targets( $targets );
						$message = __( 'Cache for the targets has been cleared.', 'millicache' );
					}
					break;

				default:
					return $this->error( 'invalid_action', __( 'Invalid cache action.', 'millicache' ) );
			}

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

			return $this->response( is_string( $action ) ? $action : '', $message );
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
	 *
	 * @param string|array<int, mixed> $targets Targets from the REST request.
	 * @return void
	 */
	private function clear_network_targets( $targets ): void {
		$list  = is_array( $targets ) ? $targets : array( $targets );
		$clear = $this->engine->clear();

		foreach ( $list as $target ) {
			if ( ! is_string( $target ) || '' === $target ) {
				continue;
			}
			$clear->flags( $target, false, false );
		}

		$clear->execute_queue();
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
	 * Build the standard cache-action success response.
	 *
	 * @since 1.7.0
	 *
	 * @param string $action  Action that was performed.
	 * @param string $message User-facing message.
	 * @return \WP_REST_Response
	 */
	private function response( string $action, string $message ): \WP_REST_Response {
		return rest_ensure_response(
			array(
				'success'   => true,
				'message'   => $message,
				'action'    => $action,
				'timestamp' => time(),
			)
		);
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
