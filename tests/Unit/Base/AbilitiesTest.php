<?php
/**
 * Tests for the domain abilities exposed to the Abilities API.
 *
 * @link       https://www.millipress.com
 * @since      1.8.0
 *
 * @package    MilliCache
 */

use MilliCache\Base\Abilities;

/**
 * Build the site-scope abilities around recording test doubles.
 *
 * @param array<string, mixed> $status The payload the status provider returns.
 * @param mixed                $result What the clear handler returns.
 * @return array{abilities: array<int, array<string, mixed>>, seen: object}
 */
function make_cache_abilities( array $status = array(), $result = null, bool $is_network = false ): array {
	$seen = new class() {
		/** @var array<int, array<string, mixed>> */
		public array $requests = array();
	};

	$result = $result ?? new WP_REST_Response(
		array(
			'success' => true,
			'cleared' => 3,
			'message' => 'Cleared 3 entries.',
		)
	);

	$abilities = Abilities::cache(
		static function () use ( $status ): array {
			return $status;
		},
		static function ( WP_REST_Request $request ) use ( $seen, $result ) {
			$seen->requests[] = $request->get_params();

			return $result;
		},
		$is_network
	);

	return array( 'abilities' => $abilities, 'seen' => $seen );
}

/**
 * Pull one ability entry out of the list by id.
 *
 * @param array<int, array<string, mixed>> $abilities The ability entries.
 * @param string                           $id        The id to find.
 * @return array<string, mixed>
 */
function ability( array $abilities, string $id ): array {
	foreach ( $abilities as $entry ) {
		if ( ( $entry['id'] ?? '' ) === $id ) {
			return $entry;
		}
	}

	return array();
}

it( 'refuses an empty target list instead of clearing everything', function () {
	// No targets means "clear everything" to the invalidation manager.
	$built    = make_cache_abilities();
	$callback = ability( $built['abilities'], 'cache-clear' )['callback'];

	$result = $callback( array( 'targets' => array() ) );

	expect( $result )->toBeInstanceOf( WP_Error::class );
	expect( $result->get_error_code() )->toBe( 'millicache_no_targets' );
	expect( $built['seen']->requests )->toBe( array() );
} );

it( 'refuses missing targets when no scope was given', function () {
	$built    = make_cache_abilities();
	$callback = ability( $built['abilities'], 'cache-clear' )['callback'];

	expect( $callback( array() ) )->toBeInstanceOf( WP_Error::class );
	expect( $built['seen']->requests )->toBe( array() );
} );

it( 'clears everything only when scope is explicitly all', function () {
	$built    = make_cache_abilities();
	$callback = ability( $built['abilities'], 'cache-clear' )['callback'];

	$result = $callback( array( 'scope' => 'all' ) );

	expect( $built['seen']->requests )->toHaveCount( 1 );
	expect( $built['seen']->requests[0]['action'] )->toBe( 'clear' );
	expect( $built['seen']->requests[0] )->not->toHaveKey( 'targets' );
	expect( $result['cleared'] )->toBe( 3 );
} );

it( 'passes targets through as a clear_targets action', function () {
	$built    = make_cache_abilities();
	$callback = ability( $built['abilities'], 'cache-clear' )['callback'];

	$callback( array( 'targets' => array( '/blog/', 'home' ), 'expire' => true ) );

	expect( $built['seen']->requests[0]['action'] )->toBe( 'clear_targets' );
	expect( $built['seen']->requests[0]['targets'] )->toBe( array( '/blog/', 'home' ) );
	expect( $built['seen']->requests[0]['expire'] )->toBeTrue();
} );

it( 'drops non-string targets rather than forwarding them', function () {
	$built    = make_cache_abilities();
	$callback = ability( $built['abilities'], 'cache-clear' )['callback'];

	$callback( array( 'targets' => array( '/blog/', array( 'nested' ), null ) ) );

	expect( $built['seen']->requests[0]['targets'] )->toBe( array( '/blog/' ) );
} );

it( 'constrains the input schema so an empty list cannot be requested', function () {
	$schema = ability( make_cache_abilities()['abilities'], 'cache-clear' )['input_schema'];

	expect( $schema['properties']['targets']['minItems'] )->toBe( 1 );
	expect( $schema['properties']['scope']['enum'] )->toBe( array( 'targets', 'all' ) );
	expect( $schema['additionalProperties'] )->toBeFalse();
} );

it( 'reports only checks that need attention', function () {
	$status = array(
		'debug' => array(
			'health' => 'warning',
			'checks' => array(
				array( 'id' => 'a', 'label' => 'A', 'status' => 'good', 'description' => 'fine' ),
				array( 'id' => 'b', 'label' => 'B', 'status' => 'info', 'description' => 'neutral' ),
				array( 'id' => 'c', 'label' => 'C', 'status' => 'recommended', 'description' => 'do this' ),
				array( 'id' => 'd', 'label' => 'D', 'status' => 'critical', 'description' => 'broken' ),
			),
		),
	);

	$callback = ability( make_cache_abilities( $status )['abilities'], 'cache-status' )['callback'];
	$result   = $callback();

	expect( $result['health'] )->toBe( 'warning' );
	expect( array_column( $result['issues'], 'id' ) )->toBe( array( 'c', 'd' ) );
} );

it( 'omits the plugin and theme inventory from the status summary', function () {
	$status = array(
		'debug' => array(
			'health'  => 'ok',
			'plugins' => array( 'woocommerce' => '9.0' ),
			'theme'   => array( 'name' => 'Twenty Twenty-Five' ),
		),
	);

	$result = ability( make_cache_abilities( $status )['abilities'], 'cache-status' )['callback']();

	expect( array_keys( $result ) )->toBe( array( 'health', 'multisite', 'storage', 'cache', 'issues' ) );
} );

it( 'survives a status payload whose fields are the wrong type', function () {
	$status = array(
		'debug'   => array( 'health' => array( 'nope' ), 'checks' => 'not-a-list' ),
		'storage' => 'broken',
		'cache'   => array( 'index' => '12', 'ttl' => null ),
	);

	$result = ability( make_cache_abilities( $status )['abilities'], 'cache-status' )['callback']();

	expect( $result['health'] )->toBe( 'ok' );
	expect( $result['issues'] )->toBe( array() );
	expect( $result['cache']['entries'] )->toBe( 12 );
	expect( $result['cache']['size_bytes'] )->toBe( 0 );
	expect( $result['cache']['ttl'] )->toBe( 0 );
} );

it( 'prefixes network ids so the two scopes do not collide', function () {
	$ids = array_column( make_cache_abilities( array(), null, true )['abilities'], 'id' );

	expect( $ids )->toBe( array( 'network-cache-status', 'network-cache-clear' ) );
} );

it( 'marks clear destructive and status readonly', function () {
	$abilities = make_cache_abilities()['abilities'];

	expect( ability( $abilities, 'cache-status' )['meta']['annotations']['readonly'] )->toBeTrue();
	expect( ability( $abilities, 'cache-clear' )['meta']['annotations']['destructive'] )->toBeTrue();
} );

it( 'omits the storage mode when the subsite cannot see it', function () {
	$subsite = ability( make_cache_abilities( array( 'storage' => array( 'connected' => true ) ) )['abilities'], 'cache-status' )['callback']();
	$network = ability( make_cache_abilities( array( 'storage' => array( 'connected' => true, 'config' => array( 'mode' => 'single' ) ) ) )['abilities'], 'cache-status' )['callback']();

	expect( $subsite['storage'] )->toBe( array( 'connected' => true ) );
	expect( $network['storage']['mode'] )->toBe( 'single' );
} );

it( 'documents skipped targets so cleared zero is not read as an empty cache', function () {
	$schema = ability( make_cache_abilities()['abilities'], 'cache-clear' )['output_schema'];

	expect( $schema['properties']['skipped']['type'] )->toBe( 'array' );
	expect( $schema['required'] )->not->toContain( 'skipped' );
} );
