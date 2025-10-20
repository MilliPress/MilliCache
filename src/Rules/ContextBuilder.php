<?php
/**
 * Context Builder
 *
 * Builds the execution context for rule evaluation.
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Rules;

use MilliCache\Engine;

/**
 * Class ContextBuilder
 *
 * Builds a structured context array for rule evaluation with categories like request, post, user.
 *
 * @since 1.0.0
 */
class ContextBuilder {
	/**
	 * Build the complete context array.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed> The structured context array.
	 */
	public static function build(): array {
		$context = array(
			'request' => self::build_request_context(),
			'post'    => self::build_post_context(),
			'user'    => self::build_user_context(),
		);

		return apply_filters( 'millicache_rules_context', $context );
	}

	/**
	 * Build request-related context.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed> Request context data.
	 */
	private static function build_request_context(): array {
		return array(
			'method'     => Engine::get_server_var( 'REQUEST_METHOD' ),
			'uri'        => Engine::get_server_var( 'REQUEST_URI' ),
			'scheme'     => 'on' === Engine::get_server_var( 'HTTPS' ) ? 'https' : 'http',
			'host'       => Engine::get_server_var( 'HTTP_HOST' ),
			'path'       => parse_url( Engine::get_server_var( 'REQUEST_URI' ), PHP_URL_PATH ),
			'query'      => Engine::get_server_var( 'QUERY_STRING' ),
			'referer'    => Engine::get_server_var( 'HTTP_REFERER' ),
			'user_agent' => Engine::get_server_var( 'HTTP_USER_AGENT' ),
			'header'     => self::get_request_headers(),
			'ip'         => self::get_client_ip(),
			'cookie'     => $_COOKIE,
			'param'      => $_GET,
		);
	}

	/**
	 * Build post-related context.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed> Post context data.
	 */
	private static function build_post_context(): array {
		$post = self::get_current_post();

		if ( ! $post || ! is_a( $post, 'WP_Post' ) ) {
			return array();
		}

		return array(
			'id'     => $post->ID,
			'type'   => $post->post_type,
			'status' => $post->post_status,
			'author' => $post->post_author,
			'parent' => $post->post_parent,
			'name'   => $post->post_name,
			'title'  => $post->post_title,
		);
	}

	/**
	 * Get the current post in any context.
	 *
	 * Handles frontend, classic editor, Gutenberg/REST, and save hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return \WP_Post|null
	 */
	private static function get_current_post(): ?\WP_Post {
		// 1. Try queried object (frontend)
		if ( ! is_admin() ) {
			$post = get_queried_object();
			if ( $post && is_a( $post, 'WP_Post' ) ) {
				return $post;
			}
		}

		// 2. Try global $post
		global $post;
		if ( $post && is_a( $post, 'WP_Post' ) ) {
			return $post;
		}

		// 3. Try REST API context (Gutenberg saves)
		$rest_post = self::get_current_rest_post();
		if ( $rest_post ) {
			return $rest_post;
		}

		return null;
	}

	/**
	 * Get post from REST API context (Gutenberg).
	 *
	 * @return \WP_Post|null
	 */
	private static function get_current_rest_post(): ?\WP_Post {
		// Check if this is a REST request.
		if ( ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return null;
		}

		// Try to get post-ID from REST route
		// Routes like: /wp-json/wp/v2/posts/123 or /wp-json/wp/v2/pages/456.
		$uri = Engine::get_server_var( 'REQUEST_URI' );
		if ( ! empty( $uri ) ) {
			// Match: /wp-json/wp/v2/{post_type}/{id}.
			if ( preg_match( '#/wp-json/wp/v2/[^/]+/(\d+)#', $uri, $matches ) ) {
				$post_id = (int) $matches[1];
				$post = get_post( $post_id );
				if ( $post && is_a( $post, 'WP_Post' ) ) {
					return $post;
				}
			}
		}

		// Try global wp object.
		if ( isset( $GLOBALS['wp'] ) && ! empty( $GLOBALS['wp']->request ) ) {
			$path = $GLOBALS['wp']->request;

			if ( preg_match( '#wp/v2/[^/]+/(\d+)#', $path, $matches ) ) {
				$post_id = (int) $matches[1];
				$post = get_post( $post_id );
				if ( $post && is_a( $post, 'WP_Post' ) ) {
					return $post;
				}
			}
		}

		// Try from REST request object.
		if ( function_exists( 'rest_get_server' ) ) {
			$server = rest_get_server();
			if ( method_exists( $server, 'get_raw_data' ) ) {
				$data = json_decode( $server->get_raw_data(), true );
				if ( is_array( $data ) && ! empty( $data['id'] ) ) {
					$id_value = $data['id'];
					if ( is_numeric( $id_value ) ) {
						$post = get_post( (int) $id_value );
						if ( $post && is_a( $post, 'WP_Post' ) ) {
							return $post;
						}
					}
				}
			}
		}

		return null;
	}

	/**
	 * Build user-related context.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed> User context data.
	 */
	private static function build_user_context(): array {
		if ( ! function_exists( 'wp_get_current_user' ) ) {
			return array(
				'id'        => 0,
				'logged_in' => false,
				'roles'     => array(),
			);
		}

		$user = wp_get_current_user();

		if ( ! $user->exists() ) {
			return array(
				'id'        => 0,
				'logged_in' => false,
				'roles'     => array(),
			);
		}

		return array(
			'id'           => $user->ID,
			'logged_in'    => true,
			'roles'        => $user->roles,
			'login'        => $user->user_login,
			'email'        => $user->user_email,
			'display_name' => $user->display_name,
		);
	}

	/**
	 * Get all request headers.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed> Request headers (normalized to lowercase keys).
	 */
	private static function get_request_headers(): array {
		$headers = array();

		if ( function_exists( 'getallheaders' ) ) {
			$result = getallheaders();
			$headers = $result ? $result : array();
		} else {
			// Fallback for servers that don't have getallheaders().
			foreach ( $_SERVER as $key => $value ) {
				if ( strpos( $key, 'HTTP_' ) === 0 ) {
					$header = str_replace( ' ', '-', ucwords( str_replace( '_', ' ', strtolower( substr( $key, 5 ) ) ) ) );
					$headers[ $header ] = $value;
				}
			}
		}

		// Normalize all keys to the lowercase for case-insensitive access.
		// array_change_key_case requires an array, already ensured above.
		return array_change_key_case( $headers );
	}

	/**
	 * Get the client IP address.
	 *
	 * @since 1.0.0
	 *
	 * @return string Client IP address.
	 */
	private static function get_client_ip(): string {
		$ip_keys = array(
			'HTTP_CF_CONNECTING_IP', // CloudFlare.
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
			'REMOTE_ADDR',
		);

		foreach ( $ip_keys as $key ) {
			$value = Engine::get_server_var( $key );
			if ( $value ) {
				// Handle comma-separated IPs (X-Forwarded-For).
				if ( strpos( $value, ',' ) !== false ) {
					return trim( explode( ',', $value )[0] );
				}
				return $value;
			}
		}

		return '';
	}
}
