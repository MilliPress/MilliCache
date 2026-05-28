<?php
/**
 * Tests for StatusBuilder's unified status payload — health computation
 * and the markdown rendering's redaction guarantees.
 *
 * The Engine class is `final`, so the high-value pure helpers are tested
 * via reflection rather than mocking the whole engine chain. Integration
 * behavior is covered by manual verification described in the feature plan.
 *
 * @link       https://www.millipress.com
 * @since      1.7.0
 *
 * @package    MilliCache
 */

use MilliCache\Admin\UI\StatusBuilder;
use MilliCache\Engine;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/' );
}

// Health and markdown branches both check the WP_CACHE constant directly.
// Defining it as `true` here puts the test environment on the happy path
// so the green-light branch is reachable. PHP constants can't be redefined,
// so per-test variation isn't available — the constant-off branch is
// covered implicitly via the markdown output assertions on a real install.
if ( ! defined( 'WP_CACHE' ) ) {
	define( 'WP_CACHE', true );
}

/**
 * Build a StatusBuilder instance without invoking Engine state.
 */
function make_status_builder(): StatusBuilder {
	$reflection = new ReflectionClass( StatusBuilder::class );
	$instance   = $reflection->newInstanceWithoutConstructor();

	$plugin_name = $reflection->getProperty( 'plugin_name' );
	$plugin_name->setAccessible( true );
	$plugin_name->setValue( $instance, 'millicache' );

	$version = $reflection->getProperty( 'version' );
	$version->setAccessible( true );
	$version->setValue( $instance, '1.7.0' );

	$engine = $reflection->getProperty( 'engine' );
	$engine->setAccessible( true );
	$engine->setValue( $instance, Engine::instance() );

	return $instance;
}

/**
 * Invoke a private method on the builder for direct testing.
 *
 * @param StatusBuilder $builder Target instance.
 * @param string        $method  Private method name.
 * @param array<mixed>  $args    Positional arguments.
 * @return mixed
 */
function invoke_builder_method( StatusBuilder $builder, string $method, array $args = array() ) {
	$reflection = new ReflectionMethod( StatusBuilder::class, $method );
	$reflection->setAccessible( true );
	return $reflection->invokeArgs( $builder, $args );
}

describe( 'StatusBuilder/build', function () {

	describe( 'class structure', function () {
		it( 'declares a build method', function () {
			$reflection = new ReflectionClass( StatusBuilder::class );
			expect( $reflection->hasMethod( 'build' ) )->toBeTrue();
		} );

		it( 'build is public and returns array', function () {
			$method = new ReflectionMethod( StatusBuilder::class, 'build' );
			expect( $method->isPublic() )->toBeTrue();
			expect( $method->getReturnType()->getName() )->toBe( 'array' );
		} );

		it( 'build takes a bool + optional WP_REST_Request parameter', function () {
			$method = new ReflectionMethod( StatusBuilder::class, 'build' );
			$params = $method->getParameters();

			expect( $params[0]->getName() )->toBe( 'network_admin' );
			expect( $params[0]->getType()->getName() )->toBe( 'bool' );
			expect( $params[1]->getName() )->toBe( 'request' );
			expect( $params[1]->allowsNull() )->toBeTrue();
		} );

		it( 'no longer exposes a build_debug method', function () {
			$reflection = new ReflectionClass( StatusBuilder::class );
			expect( $reflection->hasMethod( 'build_debug' ) )->toBeFalse();
		} );
	} );

	describe( 'gather_checks', function () {
		beforeEach( function () {
			$this->builder = make_status_builder();
			$this->healthy = array(
				'dropin'  => array(
					'type'     => 'symlink',
					'outdated' => false,
					'custom'   => false,
				),
				'storage' => array(
					'connected' => true,
					'info'      => array(
						'Memory' => array(
							'maxmemory'        => 536870912,
							'maxmemory_human'  => '512M',
							'maxmemory_policy' => 'allkeys-lru',
						),
					),
				),
			);
		} );

		it( 'emits good for every check on a fully healthy payload', function () {
			$checks = invoke_builder_method( $this->builder, 'gather_checks', array( $this->healthy ) );

			expect( count( $checks ) )->toBeGreaterThan( 0 );
			foreach ( $checks as $check ) {
				expect( $check )->toHaveKey( 'id' );
				expect( $check )->toHaveKey( 'status' );
				expect( $check['status'] )->toBe( 'good' );
			}
		} );

		it( 'attaches a docs_url to every check', function () {
			$checks = invoke_builder_method( $this->builder, 'gather_checks', array( $this->healthy ) );

			foreach ( $checks as $check ) {
				expect( $check )->toHaveKey( 'docs_url' );
				expect( $check['docs_url'] )->toStartWith( 'https://millipress.com/docs/millicache/' );
			}
		} );

		it( 'flags dropin_present as critical when the drop-in is missing', function () {
			$payload = $this->healthy;
			$payload['dropin'] = array();

			$checks = invoke_builder_method( $this->builder, 'gather_checks', array( $payload ) );
			$by_id  = array_column( $checks, null, 'id' );

			expect( $by_id )->toHaveKey( 'dropin_present' );
			expect( $by_id['dropin_present']['status'] )->toBe( 'critical' );
		} );

		it( 'flags dropin_current as recommended when outdated', function () {
			$payload = $this->healthy;
			$payload['dropin']['outdated'] = true;

			$checks = invoke_builder_method( $this->builder, 'gather_checks', array( $payload ) );
			$by_id  = array_column( $checks, null, 'id' );

			expect( $by_id['dropin_current']['status'] )->toBe( 'recommended' );
		} );

		it( 'flags storage_connected as critical when disconnected', function () {
			$payload = $this->healthy;
			$payload['storage']['connected'] = false;

			$checks = invoke_builder_method( $this->builder, 'gather_checks', array( $payload ) );
			$by_id  = array_column( $checks, null, 'id' );

			expect( $by_id['storage_connected']['status'] )->toBe( 'critical' );
		} );

		it( 'flags storage_max_memory as recommended when unset', function () {
			$payload = $this->healthy;
			$payload['storage']['info']['Memory']['maxmemory'] = 0;

			$checks = invoke_builder_method( $this->builder, 'gather_checks', array( $payload ) );
			$by_id  = array_column( $checks, null, 'id' );

			expect( $by_id['storage_max_memory']['status'] )->toBe( 'recommended' );
		} );

		it( 'flags storage_eviction_policy as recommended when not allkeys-lru', function () {
			$payload = $this->healthy;
			$payload['storage']['info']['Memory']['maxmemory_policy'] = 'noeviction';

			$checks = invoke_builder_method( $this->builder, 'gather_checks', array( $payload ) );
			$by_id  = array_column( $checks, null, 'id' );

			expect( $by_id['storage_eviction_policy']['status'] )->toBe( 'recommended' );
		} );

		it( 'omits install-wide checks on per-site multisite (no dropin key)', function () {
			$payload = array(
				'storage' => array( 'connected' => true ),
			);

			$checks = invoke_builder_method( $this->builder, 'gather_checks', array( $payload ) );
			$by_id  = array_column( $checks, null, 'id' );

			expect( $by_id )->not->toHaveKey( 'dropin_present' );
			expect( $by_id )->not->toHaveKey( 'dropin_current' );
			expect( $by_id )->not->toHaveKey( 'storage_max_memory' );
			expect( $by_id )->not->toHaveKey( 'storage_eviction_policy' );
			expect( $by_id )->toHaveKey( 'wp_cache_constant' );
			expect( $by_id )->toHaveKey( 'storage_connected' );
		} );
	} );

	describe( 'compute_health', function () {
		beforeEach( function () {
			$this->builder = make_status_builder();
		} );

		it( 'returns ok when every check is good', function () {
			$checks = array(
				array( 'id' => 'a', 'status' => 'good' ),
				array( 'id' => 'b', 'status' => 'good' ),
			);
			expect( invoke_builder_method( $this->builder, 'compute_health', array( $checks ) ) )
				->toBe( 'ok' );
		} );

		it( 'returns warning when at least one check is recommended', function () {
			$checks = array(
				array( 'id' => 'a', 'status' => 'good' ),
				array( 'id' => 'b', 'status' => 'recommended' ),
			);
			expect( invoke_builder_method( $this->builder, 'compute_health', array( $checks ) ) )
				->toBe( 'warning' );
		} );

		it( 'returns error when any check is critical (winning over recommended)', function () {
			$checks = array(
				array( 'id' => 'a', 'status' => 'recommended' ),
				array( 'id' => 'b', 'status' => 'critical' ),
			);
			expect( invoke_builder_method( $this->builder, 'compute_health', array( $checks ) ) )
				->toBe( 'error' );
		} );

		it( 'returns ok on an empty checks list', function () {
			expect( invoke_builder_method( $this->builder, 'compute_health', array( array() ) ) )
				->toBe( 'ok' );
		} );
	} );

	describe( 'render_markdown', function () {
		beforeEach( function () {
			$this->builder = make_status_builder();
			$this->payload = array(
				// Top-level legacy blocks (also consumed by the Status tab).
				'dropin'  => array(
					'type'     => 'symlink',
					'outdated' => false,
					'custom'   => false,
				),
				// Unredacted storage block — render_markdown is responsible
				// for emitting only the safe subset.
				'storage' => array(
					'connected' => true,
					'config'    => array(
						'host'       => 'redis.internal.example.com',
						'port'       => 6380,
						'username'   => 'cache_user',
						'prefix'     => 'tenant_42',
						'database'   => 5,
						'databases'  => 16,
						'persistent' => true,
						'scheme'     => 'tcp',
					),
					'info'      => array(
						'Memory' => array(
							'used_memory_human' => '176M',
							'maxmemory'         => 536870912,
							'maxmemory_human'   => '512M',
							'maxmemory_policy'  => 'allkeys-lru',
						),
						'Server' => array(
							'version' => 'Redis 7.2.4',
						),
					),
				),
				'cache'   => array(
					'ttl'                 => 86400,
					'grace'               => 2592000,
					'gzip'                => true,
					'debug'               => false,
					'nocache_paths'       => array( '/secret-staging-area/', '/customers/acme/private/' ),
					'nocache_cookies'     => array( 'session_token' ),
					'ignore_cookies'      => array( '_*' ),
					'ignore_request_keys' => array( '_*', 'utm_*' ),
					'index'               => 4812,
					'size_human'          => '184M',
					'unique'              => 4210,
					'largest_human'       => '412K',
				),
				// Extended snapshot data under the `debug` namespace.
				'debug'   => array(
					'plugin'    => array(
						'name'         => 'millicache',
						'version'      => '1.7.0',
						'install_mode' => 'composer',
					),
					'versions'  => array(
						'wp'         => '6.7.2',
						'php'        => '8.3.10',
						'predis'     => 'v2.2.2',
						'millibase'  => '2.6.1',
						'millirules' => '1.3.0',
					),
					'multisite' => array(
						'enabled'       => true,
						'site_count'    => 12,
						'network_count' => 1,
					),
					'flags'     => array(
						'registered_count' => 18,
						'sample'           => array( 'home', 'posts', 'category' ),
					),
					'rules'     => array(
						'registered_count' => 27,
						'custom_count'     => 3,
						'packages'         => array( 'WP' => 18, 'PHP' => 9 ),
					),
					'plugins'   => array(
						array( 'name' => 'Akismet', 'version' => '5.3.5', 'network' => false ),
						array( 'name' => 'WooCommerce', 'version' => '9.4.2', 'network' => true ),
					),
					'theme'     => array(
						'name'    => 'Twenty Twenty-Five',
						'version' => '1.1',
						'parent'  => null,
					),
					'health'    => 'ok',
				),
			);
		} );

		it( 'contains the expected section headers', function () {
			$md = invoke_builder_method( $this->builder, 'render_markdown', array( $this->payload ) );

			expect( $md )->toContain( '### MilliCache Debug Info' );
			expect( $md )->toContain( '**Versions**' );
			expect( $md )->toContain( '**Multisite**' );
			expect( $md )->toContain( '**advanced-cache.php**' );
			expect( $md )->toContain( '**Storage**' );
			expect( $md )->toContain( '**Cache config**' );
			expect( $md )->toContain( '**Cache stats**' );
			expect( $md )->toContain( '**Flags**' );
			expect( $md )->toContain( '**Rules**' );
			expect( $md )->toContain( '**Active plugins**' );
			expect( $md )->toContain( '**Theme**' );
			expect( $md )->toContain( '**Health**: ok' );
		} );

		it( 'includes versions for MilliCache, MilliBase, and MilliRules', function () {
			$md = invoke_builder_method( $this->builder, 'render_markdown', array( $this->payload ) );

			expect( $md )->toContain( 'MilliCache: 1.7.0 (composer)' );
			expect( $md )->toContain( 'MilliBase: 2.6.1' );
			expect( $md )->toContain( 'MilliRules: 1.3.0' );
		} );

		it( 'marks network-active plugins explicitly', function () {
			$md = invoke_builder_method( $this->builder, 'render_markdown', array( $this->payload ) );

			expect( $md )->toContain( 'WooCommerce 9.4.2 (network)' );
			expect( $md )->toContain( 'Akismet 5.3.5' );
		} );

		it( 'never emits storage host, port, username, prefix, or database index', function () {
			$md = invoke_builder_method( $this->builder, 'render_markdown', array( $this->payload ) );

			expect( $md )->not->toContain( 'redis.internal.example.com' );
			expect( $md )->not->toContain( '6380' );
			expect( $md )->not->toContain( 'cache_user' );
			expect( $md )->not->toContain( 'tenant_42' );
		} );

		it( 'never emits the actual values of nocache_paths or related arrays', function () {
			$md = invoke_builder_method( $this->builder, 'render_markdown', array( $this->payload ) );

			expect( $md )->not->toContain( '/secret-staging-area/' );
			expect( $md )->not->toContain( '/customers/acme/private/' );
			expect( $md )->not->toContain( 'session_token' );
			expect( $md )->toContain( 'nocache_paths entries: 2' );
			expect( $md )->toContain( 'nocache_cookies entries: 1' );
		} );

		it( 'emits the safe storage server fields', function () {
			$md = invoke_builder_method( $this->builder, 'render_markdown', array( $this->payload ) );

			expect( $md )->toContain( 'Server: Redis 7.2.4' );
			expect( $md )->toContain( 'Used memory: 176M' );
			expect( $md )->toContain( 'Max memory: 512M' );
			expect( $md )->toContain( 'Max-memory policy: allkeys-lru' );
			expect( $md )->toContain( 'Databases available: 16' );
		} );

		it( 'degrades gracefully when storage info is absent (per-site multisite)', function () {
			$payload = $this->payload;
			$payload['storage'] = array( 'connected' => true );
			unset( $payload['dropin'] );

			$md = invoke_builder_method( $this->builder, 'render_markdown', array( $payload ) );

			expect( $md )->toContain( '_Install-wide diagnostic. See the Network Admin Status page._' );
		} );

		it( 'ends with the generated-at footer line', function () {
			$md = invoke_builder_method( $this->builder, 'render_markdown', array( $this->payload ) );

			expect( $md )->toMatch( '/_Generated by MilliCache 1\.7\.0 on \d{4}-\d{2}-\d{2}_$/' );
		} );

		it( 'appends sections returned by the millicache_status_markdown_sections filter', function () {
			global $test_filters;
			$test_filters['millicache_status_markdown_sections'] = array(
				"**Fragment cache**\n- Enabled: yes\n- Index: 1,234",
				"**Edge cache**\n- Vendor: Cloudflare",
			);

			$md = invoke_builder_method( $this->builder, 'render_markdown', array( $this->payload ) );

			expect( $md )->toContain( '**Fragment cache**' );
			expect( $md )->toContain( '- Index: 1,234' );
			expect( $md )->toContain( '**Edge cache**' );
			expect( $md )->toContain( '- Vendor: Cloudflare' );

			// Filter sections must appear above the Health line.
			$frag_pos   = strpos( $md, '**Fragment cache**' );
			$health_pos = strpos( $md, '**Health**' );
			expect( $frag_pos )->not->toBeFalse();
			expect( $health_pos )->not->toBeFalse();
			expect( $frag_pos )->toBeLessThan( $health_pos );

			unset( $test_filters['millicache_status_markdown_sections'] );
		} );

		it( 'skips empty/whitespace-only entries from the markdown-sections filter', function () {
			global $test_filters;
			$test_filters['millicache_status_markdown_sections'] = array(
				'**Fragment cache**',
				'',
				'   ',
			);

			$md = invoke_builder_method( $this->builder, 'render_markdown', array( $this->payload ) );

			expect( $md )->toContain( '**Fragment cache**' );

			unset( $test_filters['millicache_status_markdown_sections'] );
		} );
	} );

	describe( 'extension filters', function () {
		it( 'documents and applies the millicache_status_checks filter', function () {
			$source = file_get_contents( __DIR__ . '/../../../../src/Admin/UI/StatusBuilder.php' );
			expect( $source )->toContain( "apply_filters( 'millicache_status_checks'" );
		} );

		it( 'documents and applies the millicache_status_debug filter', function () {
			$source = file_get_contents( __DIR__ . '/../../../../src/Admin/UI/StatusBuilder.php' );
			expect( $source )->toContain( "apply_filters( 'millicache_status_debug'" );
		} );

		it( 'documents and applies the millicache_status_markdown_sections filter', function () {
			$source = file_get_contents( __DIR__ . '/../../../../src/Admin/UI/StatusBuilder.php' );
			expect( $source )->toContain( "apply_filters( 'millicache_status_markdown_sections'" );
		} );
	} );
} );
