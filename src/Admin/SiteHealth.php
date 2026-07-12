<?php
/**
 * Native WordPress Site Health integration for MilliCache.
 *
 * All data comes from {@see StatusBuilder::build()} — the same single
 * source of truth backing the settings-page Status tab, the footer
 * Status modal, and `wp millicache status`.
 *
 * @link       https://www.millipress.com
 * @since      1.7.0
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin
 * @author     Philipp Wellmer <hello@millipress.com>
 */

namespace MilliCache\Admin;

use MilliCache\Admin\UI\StatusBuilder;
use MilliCache\Core\Loader;

! defined( 'ABSPATH' ) && exit;

/**
 * Registers MilliCache's contributions to the WordPress Site Health screens.
 *
 * @package    MilliCache
 * @subpackage MilliCache/Admin
 * @author     Philipp Wellmer <hello@millipress.com>
 */
final class SiteHealth {

	/**
	 * The status payload builder.
	 *
	 * @since 1.7.0
	 * @var StatusBuilder
	 */
	private StatusBuilder $status_builder;

	/**
	 * Per-request memo of the built status payload, so the debug-info and
	 * status-test filters (both firing on the same Site Health page load)
	 * don't probe storage twice.
	 *
	 * @since 1.8.0
	 * @var ?array<string, mixed>
	 */
	private ?array $payload_cache = null;

	/**
	 * Construct the controller and register both Site Health filters via the loader.
	 *
	 * @since 1.7.0
	 *
	 * @param Loader        $loader         The hook loader.
	 * @param StatusBuilder $status_builder The status payload builder.
	 */
	public function __construct( Loader $loader, StatusBuilder $status_builder ) {
		$this->status_builder = $status_builder;

		$loader->add_filter( 'debug_information', $this, 'register_debug_info' );
		$loader->add_filter( 'site_status_tests', $this, 'register_status_tests' );
	}

	/**
	 * The built status payload for this request, memoized so the two Site
	 * Health filters share a single build (and a single storage probe).
	 *
	 * @since 1.8.0
	 *
	 * @return array<string, mixed>
	 */
	private function payload(): array {
		if ( null === $this->payload_cache ) {
			$this->payload_cache = $this->status_builder->build( is_network_admin() );
		}

		return $this->payload_cache;
	}

	/**
	 * Add a `millicache` section to the Site Health debug-info blob.
	 *
	 * Walks the same safe subset that `StatusBuilder::render_markdown()` uses
	 * — no host, port, credentials, prefix, or customer paths/cookies.
	 *
	 * @since 1.7.0
	 *
	 * @param mixed $info The existing debug-info array, as supplied by WordPress.
	 * @return mixed The augmented array (or original value when WP passed a non-array).
	 */
	public function register_debug_info( $info ) {
		if ( ! is_array( $info ) ) {
			return $info;
		}

		$payload = $this->payload();

		$info['millicache'] = array(
			'label'       => __( 'MilliCache', 'millicache' ),
			'description' => __( 'Page cache status, drop-in state, and storage backend.', 'millicache' ),
			'fields'      => $this->to_debug_fields( $payload ),
		);

		return $info;
	}

	/**
	 * Translate the unified status payload into Site Health debug fields.
	 *
	 * @since 1.7.0
	 *
	 * @param array<string, mixed> $payload The unified status payload.
	 * @return array<string, array{label: string, value: string, debug?: string}>
	 */
	private function to_debug_fields( array $payload ): array {
		$debug    = is_array( $payload['debug'] ?? null ) ? $payload['debug'] : array();
		$plugin   = is_array( $debug['plugin'] ?? null ) ? $debug['plugin'] : array();
		$versions = is_array( $debug['versions'] ?? null ) ? $debug['versions'] : array();
		$storage  = is_array( $payload['storage'] ?? null ) ? $payload['storage'] : array();
		$info     = is_array( $storage['info'] ?? null ) ? $storage['info'] : array();
		$memory   = is_array( $info['Memory'] ?? null ) ? $info['Memory'] : array();
		$server   = is_array( $info['Server'] ?? null ) ? $info['Server'] : array();
		$dropin   = is_array( $payload['dropin'] ?? null ) ? $payload['dropin'] : null;
		$cache    = is_array( $payload['cache'] ?? null ) ? $payload['cache'] : array();
		$flags    = is_array( $debug['flags'] ?? null ) ? $debug['flags'] : array();

		$install_mode = $this->as_string( $plugin['install_mode'] ?? null, 'unknown' );
		$dropin_state = null === $dropin
			? 'n/a (per-site multisite)'
			: ( empty( $dropin ) ? 'missing' : $this->describe_dropin( $dropin ) );

		return array(
			'version'           => array(
				'label' => __( 'Version', 'millicache' ),
				'value' => sprintf( '%s (%s)', $this->as_string( $plugin['version'] ?? null ), $install_mode ),
			),
			'millibase'         => array(
				'label' => __( 'MilliBase version', 'millicache' ),
				'value' => $this->as_string( $versions['millibase'] ?? null, 'dev' ),
			),
			'millirules'        => array(
				'label' => __( 'MilliRules version', 'millicache' ),
				'value' => $this->as_string( $versions['millirules'] ?? null, 'dev' ),
			),
			'predis'            => array(
				'label' => __( 'Predis version', 'millicache' ),
				'value' => $this->as_string( $versions['predis'] ?? null, 'not installed' ),
			),
			'wp_cache'          => array(
				'label' => __( 'WP_CACHE constant', 'millicache' ),
				'value' => defined( 'WP_CACHE' ) && WP_CACHE
					? __( 'Enabled', 'millicache' )
					: __( 'Disabled', 'millicache' ),
				'debug' => defined( 'WP_CACHE' ) && WP_CACHE ? 'true' : 'false',
			),
			'dropin'            => array(
				'label' => __( 'advanced-cache.php', 'millicache' ),
				'value' => $dropin_state,
			),
			'storage_connected' => array(
				'label' => __( 'Storage backend connected', 'millicache' ),
				'value' => ! empty( $storage['connected'] )
					? __( 'Yes', 'millicache' )
					: __( 'No', 'millicache' ),
				'debug' => ! empty( $storage['connected'] ) ? 'yes' : 'no',
			),
			'storage_server'    => array(
				'label' => __( 'Storage backend', 'millicache' ),
				'value' => $this->as_string( $server['version'] ?? null, 'n/a' ),
			),
			'storage_memory'    => array(
				'label' => __( 'Storage memory usage', 'millicache' ),
				'value' => sprintf(
					'%s / %s (policy: %s)',
					$this->as_string( $memory['used_memory_human'] ?? null, 'n/a' ),
					$this->as_string( $memory['maxmemory_human'] ?? null, 'n/a' ),
					$this->as_string( $memory['maxmemory_policy'] ?? null, 'n/a' )
				),
			),
			'cache_entries'     => array(
				'label' => __( 'Cached pages', 'millicache' ),
				'value' => isset( $cache['index'] ) && is_numeric( $cache['index'] )
					? (string) (int) $cache['index']
					: '0',
			),
			'cache_size'        => array(
				'label' => __( 'Cache size', 'millicache' ),
				'value' => $this->as_string( $cache['size_human'] ?? null, 'n/a' ),
			),
			'flags_registered'  => array(
				'label' => __( 'Registered cache flags', 'millicache' ),
				'value' => isset( $flags['registered_count'] ) && is_numeric( $flags['registered_count'] )
					? (string) (int) $flags['registered_count']
					: '0',
			),
		);
	}

	/**
	 * Compose a human-readable description of the drop-in shape.
	 *
	 * @since 1.7.0
	 *
	 * @param array<mixed> $dropin The dropin block from the payload (non-empty).
	 * @return string
	 */
	private function describe_dropin( array $dropin ): string {
		$parts = array( $this->as_string( $dropin['type'] ?? null, 'file' ) );

		if ( ! empty( $dropin['outdated'] ) ) {
			$parts[] = 'outdated';
		}

		if ( ! empty( $dropin['custom'] ) ) {
			$parts[] = 'customized';
		}

		return implode( ', ', $parts );
	}

	/**
	 * Register Site Health tests from the unified checks array, so any Pro
	 * check surfaces automatically. All good → one "MilliCache is healthy"
	 * entry; otherwise one `direct` test per outstanding issue.
	 *
	 * @since 1.7.0
	 *
	 * @param mixed $tests The existing test registry, as supplied by WordPress.
	 * @return mixed
	 */
	public function register_status_tests( $tests ) {
		if ( ! is_array( $tests ) ) {
			return $tests;
		}

		if ( ! isset( $tests['direct'] ) || ! is_array( $tests['direct'] ) ) {
			$tests['direct'] = array();
		}

		$issues = $this->outstanding_checks();

		if ( array() === $issues ) {
			$tests['direct']['millicache_health'] = array(
				'label' => __( 'MilliCache is healthy', 'millicache' ),
				'test'  => array( $this, 'test_health' ),
			);

			return $tests;
		}

		foreach ( $issues as $check ) {
			$id = is_string( $check['id'] ?? null ) ? $check['id'] : '';
			if ( '' === $id ) {
				continue;
			}

			$slug = 'millicache_' . $id;

			$tests['direct'][ $slug ] = array(
				'label' => $this->headline( $check ),
				'test'  => function () use ( $check, $slug ) {
					return $this->result_from_check( $check, $slug );
				},
			);
		}

		return $tests;
	}

	/**
	 * The checks that need attention — `recommended` or `critical`. Neutral
	 * `info` checks and passing `good` checks are filtered out.
	 *
	 * @since 1.8.0
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function outstanding_checks(): array {
		$debug  = is_array( $this->payload()['debug'] ?? null ) ? $this->payload()['debug'] : array();
		$checks = is_array( $debug['checks'] ?? null ) ? $debug['checks'] : array();

		$issues = array();
		foreach ( $checks as $check ) {
			if ( ! is_array( $check ) ) {
				continue;
			}
			$status = is_string( $check['status'] ?? null ) ? $check['status'] : '';
			if ( 'recommended' === $status || 'critical' === $status ) {
				$issues[] = $check;
			}
		}

		return $issues;
	}

	/**
	 * Site Health test: the all-clear card shown when no check needs attention.
	 *
	 * @since 1.8.0
	 *
	 * @return array<string, mixed>
	 */
	public function test_health(): array {
		return $this->result(
			'millicache_health',
			'good',
			__( 'MilliCache is healthy', 'millicache' ),
			__( 'Every MilliCache status check passes: the drop-in, the WP_CACHE constant, the storage backend, and any active Pro modules are configured correctly.', 'millicache' ),
			$this->settings_action_link( __( 'Open MilliCache status', 'millicache' ) )
		);
	}

	/**
	 * Turn a single unified status check into a Site Health test result.
	 *
	 * @since 1.8.0
	 *
	 * @param array<string, mixed> $check A check from the unified `debug.checks` array.
	 * @param string               $slug  The `direct` test key this result answers to.
	 * @return array<string, mixed>
	 */
	private function result_from_check( array $check, string $slug ): array {
		$status = is_string( $check['status'] ?? null ) ? $check['status'] : 'recommended';
		if ( ! in_array( $status, array( 'good', 'recommended', 'critical' ), true ) ) {
			$status = 'recommended';
		}

		$description = $this->as_string( $check['description'] ?? null );
		$url         = $this->as_string( $check['url'] ?? null );

		$actions = $this->settings_action_link( __( 'Open MilliCache status', 'millicache' ) );
		if ( '' !== $url ) {
			$actions .= sprintf(
				'<p><a href="%s" target="_blank" rel="noopener noreferrer">%s</a></p>',
				esc_url( $url ),
				esc_html__( 'Learn more', 'millicache' )
			);
		}

		return $this->result( $slug, $status, $this->headline( $check ), $description, $actions );
	}

	/**
	 * Fold a check's subject label and verdict value into one Site Health
	 * headline, e.g. "Storage backend: Disconnected".
	 *
	 * @since 1.8.0
	 *
	 * @param array<string, mixed> $check A check from the unified `debug.checks` array.
	 * @return string
	 */
	private function headline( array $check ): string {
		$label = $this->as_string( $check['label'] ?? null, __( 'MilliCache check', 'millicache' ) );
		$value = $this->as_string( $check['value'] ?? null );

		if ( '' === $value ) {
			return $label;
		}

		return sprintf(
			/* translators: 1: check subject (e.g. "Storage backend"), 2: its verdict (e.g. "Disconnected"). */
			__( '%1$s: %2$s', 'millicache' ),
			$label,
			$value
		);
	}

	/**
	 * Compose the standard Site Health test-result shape.
	 *
	 * @since 1.7.0
	 *
	 * @param string $slug        Test identifier (matches the `direct` key).
	 * @param string $status      One of `good`, `recommended`, `critical`.
	 * @param string $label       Short headline shown on the card.
	 * @param string $description Longer explanation rendered below the headline (may contain <code>).
	 * @param string $actions     Optional HTML for the actions row.
	 * @return array<string, mixed>
	 */
	private function result( string $slug, string $status, string $label, string $description, string $actions ): array {
		$colors = array(
			'good'        => 'blue',
			'recommended' => 'orange',
			'critical'    => 'red',
		);

		return array(
			'label'       => $label,
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'MilliCache', 'millicache' ),
				'color' => $colors[ $status ] ?? 'gray',
			),
			'description' => sprintf( '<p>%s</p>', wp_kses( $description, array( 'code' => array() ) ) ),
			'actions'     => $actions,
			'test'        => $slug,
		);
	}

	/**
	 * Build the standard "Open MilliCache settings" actions-row link.
	 *
	 * @since 1.7.0
	 *
	 * @param string $label The visible link label.
	 * @return string
	 */
	private function settings_action_link( string $label ): string {
		return sprintf(
			'<p><a class="button button-secondary" href="%s">%s</a></p>',
			esc_url( admin_url( 'options-general.php?page=millicache' ) ),
			esc_html( $label )
		);
	}

	/**
	 * Narrow a `mixed` value to a string, falling back to `$default` when not a string.
	 *
	 * @since 1.7.0
	 *
	 * @param mixed  $value   The candidate value.
	 * @param string $default Fallback when `$value` isn't a string.
	 * @return string
	 */
	private function as_string( $value, string $default = '' ): string {
		return is_string( $value ) ? $value : $default;
	}
}
