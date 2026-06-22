<?php
/**
 * Integration test for network-scoped metrics settings on multisite.
 *
 * On multisite the Detailed Metrics activation toggle and retention live in
 * the network scope, so every subsite's Recorder must resolve `metrics.*`
 * from Network::settings() (exactly as `storage` already does), even though
 * collection itself stays per-blog.
 *
 * @package MilliCache
 */

use MilliBase\Settings as BaseSettings;
use MilliCache\Base\Network;
use MilliCache\Base\Site;
use MilliCache\Engine;

if ( ! function_exists( 'is_multisite' ) ) {
	function is_multisite() {
		global $test_is_multisite;
		return $test_is_multisite ?? false;
	}
}

/**
 * Install standalone Settings for both scopes with distinct metrics blocks.
 *
 * The site scope toggles detailed metrics off; the network scope toggles it
 * on with explicit retention. A correct merge resolves to the network values.
 *
 * @param array<string, mixed> $network_metrics
 * @param array<string, mixed> $site_metrics
 */
function install_multisite_metrics( array $network_metrics, array $site_metrics ): void {
	$cache_defaults = array(
		'ttl'             => 3600,
		'grace'           => 600,
		'nocache_paths'   => array(),
		'nocache_cookies' => array(),
		'ignore_cookies'  => array(),
		'unique'          => array(),
	);

	Site::inject_settings(
		BaseSettings::standalone(
			array(
				'slug'     => 'millicache',
				'defaults' => array(
					'cache'   => $cache_defaults,
					'metrics' => $site_metrics,
				),
			)
		)
	);

	Network::inject_settings(
		BaseSettings::standalone(
			array(
				'slug'     => 'millicache-network',
				'defaults' => array(
					'storage' => array(),
					'metrics' => $network_metrics,
				),
			)
		)
	);
}

beforeEach( function () {
	$GLOBALS['test_is_multisite'] = true;
} );

it( 'resolves metrics from the network scope, overriding the site toggle', function () {
	install_multisite_metrics(
		network_metrics: array(
			'active'           => true,
			'retention_hourly' => 10,
			'retention_daily'  => 20,
		),
		site_metrics: array(
			'active' => false,
		),
	);

	$engine  = new Engine();
	$metrics = $engine->get_settings( 'metrics' );

	expect( $metrics['active'] )->toBeTrue();
	expect( $metrics['retention_hourly'] )->toBe( 10 );
	expect( $metrics['retention_daily'] )->toBe( 20 );
} );
