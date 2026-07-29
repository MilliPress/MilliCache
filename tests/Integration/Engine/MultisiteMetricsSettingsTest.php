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

/**
 * Write a network config file into a fresh temp directory and return the
 * directory path, mimicking MilliBase's ConfigFile sync output.
 *
 * @param array<string, mixed> $settings The settings tree the file returns.
 */
function write_network_config_file( array $settings ): string {
	$dir = sys_get_temp_dir() . '/mc-metrics-test-' . uniqid();
	mkdir( $dir );

	file_put_contents(
		$dir . '/_network-1.php',
		'<?php return ' . var_export( $settings, true ) . ';'
	);

	return $dir;
}

beforeEach( function () {
	$GLOBALS['test_is_multisite'] = true;
} );

it( 'resolves metrics from the network scope, overriding the site toggle', function () {
	install_multisite_metrics(
		network_metrics: array(
			'active' => true,
			'hourly' => 10,
			'daily'  => 20,
		),
		site_metrics: array(
			'active' => false,
		),
	);

	$engine  = new Engine();
	$metrics = $engine->get_settings( 'metrics' );

	expect( $metrics['active'] )->toBeTrue();
	expect( $metrics['hourly'] )->toBe( 10 );
	expect( $metrics['daily'] )->toBe( 20 );
} );

it( 'declares every engine-read metrics key in the owning scope\'s defaults', function () {
	// The defaults-gate (MilliBase overlay_known) strips stored keys absent
	// from the scope's defaults, and the engine resolves settings before any
	// plugin can widen them via filters — so the keys Engine::metrics() reads
	// must be declared here or config-file values silently resolve to defaults.
	// Metrics is network-owned: declared once in Network::defaults(), reaching
	// Site::defaults() only through the single-site merge.
	expect( Network::defaults()['metrics'] )->toHaveKeys( array( 'active', 'hourly', 'daily' ) );

	expect( Site::defaults() )->not->toHaveKey( 'metrics' );

	$GLOBALS['test_is_multisite'] = false;
	expect( Site::defaults()['metrics'] )->toHaveKeys( array( 'active', 'hourly', 'daily' ) );
} );

it( 'keeps config-file metrics values through the real network defaults-gate', function () {
	$dir = write_network_config_file(
		array(
			'metrics' => array(
				'active' => true,
				'hourly' => 60,
				'daily'  => 730,
			),
		)
	);

	try {
		$settings = BaseSettings::standalone(
			array(
				'slug'        => 'millicache',
				'network'     => true,
				'defaults'    => Network::defaults(),
				'config_file' => array( 'directory' => $dir . '/' ),
			)
		);

		expect( $settings->get( 'metrics.active' ) )->toBeTrue();
		expect( $settings->get( 'metrics.hourly' ) )->toBe( 60 );
		expect( $settings->get( 'metrics.daily' ) )->toBe( 730 );
	} finally {
		unlink( $dir . '/_network-1.php' );
		rmdir( $dir );
	}
} );

it( 'passes retention through as null so the Recorder falls back when unconfigured', function () {
	install_multisite_metrics(
		network_metrics: Network::defaults()['metrics'],
		site_metrics: array(),
	);

	$engine  = new Engine();
	$metrics = $engine->get_settings( 'metrics' );

	// Free declares the keys but owns no values: Engine::metrics()'s
	// is_numeric guard skips null, so the Recorder uses its own constants.
	expect( $metrics['active'] )->toBeFalse();
	expect( $metrics['hourly'] )->toBeNull();
	expect( $metrics['daily'] )->toBeNull();
	expect( $engine->metrics() )->toBeInstanceOf( \MilliCache\Engine\Metrics\Manager::class );
} );
