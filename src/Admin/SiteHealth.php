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

		$payload = $this->status_builder->build( is_network_admin() );

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
	 * Add MilliCache test cards to Site Health → Status.
	 *
	 * Each card is a synchronous `direct` test — the underlying checks
	 * (file existence, Redis ping, constant lookup) all complete in
	 * single-digit milliseconds, so async REST trips aren't worth the
	 * complexity.
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

		$tests['direct']['millicache_dropin'] = array(
			'label' => __( 'MilliCache drop-in', 'millicache' ),
			'test'  => array( $this, 'test_dropin' ),
		);
		$tests['direct']['millicache_storage'] = array(
			'label' => __( 'MilliCache storage backend', 'millicache' ),
			'test'  => array( $this, 'test_storage' ),
		);
		$tests['direct']['millicache_wp_cache'] = array(
			'label' => __( 'MilliCache WP_CACHE constant', 'millicache' ),
			'test'  => array( $this, 'test_wp_cache' ),
		);

		return $tests;
	}

	/**
	 * Site Health test: is the advanced-cache.php drop-in present and current?
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, mixed>
	 */
	public function test_dropin(): array {
		$info    = Utils::validate_advanced_cache_file();
		$present = ! empty( $info );

		if ( ! $present ) {
			return $this->result(
				'millicache_dropin',
				'critical',
				__( 'MilliCache drop-in is not installed', 'millicache' ),
				__( 'The advanced-cache.php drop-in is required for MilliCache to intercept and serve cached pages. Without it, no pages are cached.', 'millicache' ),
				$this->settings_action_link( __( 'Open MilliCache settings', 'millicache' ) )
			);
		}

		if ( ! empty( $info['outdated'] ) ) {
			return $this->result(
				'millicache_dropin',
				'recommended',
				__( 'MilliCache drop-in is outdated', 'millicache' ),
				__( 'A newer version of advanced-cache.php is bundled with the plugin. Re-install it from the MilliCache settings to pick up improvements and bug fixes.', 'millicache' ),
				$this->settings_action_link( __( 'Re-install drop-in', 'millicache' ) )
			);
		}

		if ( ! empty( $info['custom'] ) ) {
			return $this->result(
				'millicache_dropin',
				'recommended',
				__( 'MilliCache drop-in has been customized', 'millicache' ),
				__( "The advanced-cache.php drop-in differs from the bundled version. MilliCache won't overwrite your changes, but plugin updates require manual merging.", 'millicache' ),
				''
			);
		}

		return $this->result(
			'millicache_dropin',
			'good',
			__( 'MilliCache drop-in is installed and current', 'millicache' ),
			__( 'The advanced-cache.php drop-in is in place and matches the bundled version.', 'millicache' ),
			''
		);
	}

	/**
	 * Site Health test: can MilliCache reach its storage backend?
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, mixed>
	 */
	public function test_storage(): array {
		$connected = \MilliCache\Engine::instance()->storage()->ping();

		if ( $connected ) {
			return $this->result(
				'millicache_storage',
				'good',
				__( 'MilliCache storage backend is reachable', 'millicache' ),
				__( 'MilliCache successfully connected to its configured storage server.', 'millicache' ),
				''
			);
		}

		return $this->result(
			'millicache_storage',
			'critical',
			__( 'MilliCache cannot reach its storage backend', 'millicache' ),
			__( 'MilliCache could not connect to the configured storage server (Redis, Valkey, KeyDB, or Dragonfly). Cached pages cannot be read or written until the connection is restored.', 'millicache' ),
			$this->settings_action_link( __( 'Open MilliCache settings', 'millicache' ) )
		);
	}

	/**
	 * Site Health test: is the WP_CACHE constant defined and truthy?
	 *
	 * @since 1.7.0
	 *
	 * @return array<string, mixed>
	 */
	public function test_wp_cache(): array {
		if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
			return $this->result(
				'millicache_wp_cache',
				'good',
				__( 'WP_CACHE constant is enabled', 'millicache' ),
				__( 'The WP_CACHE constant is defined and truthy, allowing WordPress to load the advanced-cache.php drop-in early in the request lifecycle.', 'millicache' ),
				''
			);
		}

		return $this->result(
			'millicache_wp_cache',
			'recommended',
			__( 'WP_CACHE constant is not enabled', 'millicache' ),
			__( "WordPress only loads the advanced-cache.php drop-in when WP_CACHE is defined and truthy in wp-config.php. Without it, MilliCache can't intercept page requests.", 'millicache' ),
			sprintf(
				/* translators: %s: literal PHP snippet to add to wp-config.php */
				__( 'Add %s to your wp-config.php, above the "happy blogging" line.', 'millicache' ),
				'<code>define( \'WP_CACHE\', true );</code>'
			)
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
	 * @param string $description Longer explanation rendered below the headline.
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
				'label' => __( 'Performance', 'millicache' ),
				'color' => $colors[ $status ] ?? 'gray',
			),
			'description' => sprintf( '<p>%s</p>', esc_html( $description ) ),
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
