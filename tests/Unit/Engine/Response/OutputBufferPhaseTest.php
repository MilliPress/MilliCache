<?php
/**
 * Tests for the outermost output buffer's phase guards and storage gating.
 *
 * Covers Processor::process_output_buffer()'s abort semantics (CLEAN,
 * missing FINAL, chunk-size overflow), the sticky-negative sentinel, the
 * in-shutdown gate against third-party fastcgi_finish_request(), flush-time
 * option application, and the Content-Encoding storage bypass.
 *
 * @link       https://www.millipress.com
 * @since      1.8.0
 *
 * @package    MilliCache
 */

use MilliCache\Core\Storage;
use MilliCache\Engine;
use MilliCache\Engine\Cache\Manager as CacheManager;
use MilliCache\Engine\Flags;
use MilliCache\Engine\Request\Processor as RequestProcessor;
use MilliCache\Engine\Response\Headers;
use MilliCache\Engine\Response\Processor;
use MilliCache\Engine\Response\State;
use MilliCache\Engine\Utilities\Multisite;

/**
 * Create a Processor without running its constructor.
 *
 * The abort paths touch only the state property, so an instance without
 * manager dependencies suffices; any accidental reach into an uninitialized
 * typed property throws and fails the test, proving the path stayed cold.
 *
 * @param State|null $state Optional state to inject.
 * @return Processor
 */
function millicache_bare_processor( ?State $state = null ): Processor {
	$processor = ( new ReflectionClass( Processor::class ) )->newInstanceWithoutConstructor();
	if ( null !== $state ) {
		millicache_set_processor_prop( $processor, 'state', $state );
	}
	return $processor;
}

/**
 * Set a private Processor property.
 *
 * @param Processor $processor The instance.
 * @param string    $prop      Property name.
 * @param mixed     $value     Value to set.
 * @return void
 */
function millicache_set_processor_prop( Processor $processor, string $prop, $value ): void {
	$ref = new ReflectionProperty( Processor::class, $prop );
	$ref->setAccessible( true );
	$ref->setValue( $processor, $value );
}

/**
 * Simulate that PHP entered shutdown (normally set by the shutdown function
 * registered in start_output_buffer()).
 *
 * @param Processor $processor The instance.
 * @return void
 */
function millicache_enter_shutdown( Processor $processor ): void {
	millicache_set_processor_prop( $processor, 'in_shutdown', true );
}

/**
 * Invoke the private sentinel recorder (normally fired via the
 * template_redirect closure registered in start_output_buffer()).
 *
 * @param Processor $processor The instance.
 * @param bool      $storable  The sentinel decision to record.
 * @return void
 */
function millicache_mark_storable( Processor $processor, bool $storable ): void {
	$method = new ReflectionMethod( Processor::class, 'mark_storable' );
	$method->setAccessible( true );
	$method->invoke( $processor, $storable );
}

/**
 * Build a fully wired Processor around a mocked Storage.
 *
 * @param Storage $storage Mocked storage instance.
 * @return array{0: Processor, 1: RequestProcessor} Processor and its request manager.
 */
function millicache_full_processor( $storage ): array {
	$config  = create_test_config();
	$request = new RequestProcessor( $config );
	$request->process();

	$processor = new Processor(
		$config,
		new Flags( new Multisite() ),
		new Headers( $config ),
		new CacheManager( $config, $storage ),
		$request
	);

	return array( $processor, $request );
}

uses()->beforeEach( function () {
	$this->ob_base = ob_get_level();

	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_SERVER['HTTP_HOST']      = 'example.test';
	$_SERVER['REQUEST_URI']    = '/buffer-test/';

	Engine::instance()->options()->reset();
} );

uses()->afterEach( function () {
	while ( ob_get_level() > $this->ob_base ) {
		@ob_end_clean();
	}

	Engine::instance()->options()->reset();
	unset( $_SERVER['HTTP_USER_AGENT'] );
} );

describe( 'phase guard order', function () {

	it( 'treats CLEAN|FINAL (ob_end_clean) as an abort, not a store', function () {
		$processor = millicache_bare_processor( State::create( 'hash' ) );
		millicache_mark_storable( $processor, true );
		millicache_enter_shutdown( $processor );

		$out = $processor->process_output_buffer( 'x', PHP_OUTPUT_HANDLER_CLEAN | PHP_OUTPUT_HANDLER_FINAL );

		expect( $out )->toBe( 'x' );
		expect( $processor->is_storable() )->toBeFalse();
	} );

	it( 'aborts and returns the chunk as a string when FINAL is absent', function () {
		$processor = millicache_bare_processor( State::create( 'hash' ) );
		millicache_mark_storable( $processor, true );

		$out = $processor->process_output_buffer( 'chunk', PHP_OUTPUT_HANDLER_START | PHP_OUTPUT_HANDLER_FLUSH );

		expect( $out )->toBe( 'chunk' );
		expect( $out )->toBeString();
		expect( $processor->is_storable() )->toBeFalse();
	} );

	it( 'refuses storage on FINAL before shutdown (third-party fastcgi_finish_request)', function () {
		$processor = millicache_bare_processor( State::create( 'hash' ) );
		millicache_mark_storable( $processor, true );

		$out = $processor->process_output_buffer( 'page', PHP_OUTPUT_HANDLER_FINAL );

		expect( $out )->toBe( 'page' );
	} );

	it( 'returns null on FINAL for an aborted background regeneration', function () {
		$state     = State::create( 'hash' )->with_fcgi_regenerate( true );
		$processor = millicache_bare_processor( $state );
		millicache_mark_storable( $processor, true );
		millicache_enter_shutdown( $processor );

		// Abort first (e.g. a mid-request flush), then the final flush.
		$processor->process_output_buffer( 'chunk', PHP_OUTPUT_HANDLER_START | PHP_OUTPUT_HANDLER_FLUSH );
		$out = $processor->process_output_buffer( 'page', PHP_OUTPUT_HANDLER_FINAL );

		expect( $out )->toBeNull();
	} );
} );

describe( 'sentinel stickiness', function () {

	it( 'never flips a negative back to positive', function () {
		$processor = millicache_bare_processor();
		millicache_mark_storable( $processor, false );
		millicache_mark_storable( $processor, true );

		expect( $processor->is_storable() )->toBeFalse();
	} );

	it( 'lets a later negative win over an earlier positive', function () {
		$processor = millicache_bare_processor();
		millicache_mark_storable( $processor, true );
		millicache_mark_storable( $processor, false );

		expect( $processor->is_storable() )->toBeFalse();
	} );
} );

describe( 'real buffer interactions', function () {

	it( 'survives a third-party while(ob_get_level()) ob_end_clean() loop', function () {
		$processor = millicache_bare_processor();
		millicache_mark_storable( $processor, true );
		$processor->start_output_buffer( State::create( 'hash' ) );

		echo 'partial output';

		while ( ob_get_level() > $this->ob_base ) {
			ob_end_clean();
		}

		expect( $processor->is_storable() )->toBeFalse();

		// A sentinel firing after the abort cannot resurrect storability.
		millicache_mark_storable( $processor, true );
		expect( $processor->is_storable() )->toBeFalse();
	} );

	it( 'streams mid-request ob_flush() output intact and aborts storage', function () {
		ob_start();

		$processor = millicache_bare_processor();
		millicache_mark_storable( $processor, true );
		millicache_enter_shutdown( $processor );
		$processor->start_output_buffer( State::create( 'hash' ) );

		echo 'chunk-one ';
		ob_flush();
		echo 'chunk-two';
		ob_end_flush();

		$streamed = ob_get_clean();

		expect( $streamed )->toBe( 'chunk-one chunk-two' );
		expect( $processor->is_storable() )->toBeFalse();
	} );

	it( 'streams responses over the cache limit without storing (chunk overflow)', function () {
		ob_start();

		$processor = millicache_bare_processor();
		// Sentinel positive and in shutdown: if the overflow abort failed,
		// the storable path would hit an uninitialized manager and throw.
		millicache_mark_storable( $processor, true );
		millicache_enter_shutdown( $processor );
		$processor->start_output_buffer( State::create( 'hash' ) );

		$megabyte = str_repeat( 'a', 1048576 );
		for ( $i = 0; $i < 6; $i++ ) {
			echo $megabyte;
		}
		ob_end_flush();

		$streamed = ob_get_clean();

		expect( strlen( $streamed ) )->toBe( 6 * 1048576 );
		expect( $processor->is_storable() )->toBeFalse();
	} );
} );

describe( 'flush-time decisions', function () {

	it( 'honors a late do_cache(false) set during rendering', function () {
		Engine::instance()->options()->set_cache_decision( false, 'late-bypass' );

		$processor = millicache_bare_processor( State::create( 'hash' ) );
		millicache_mark_storable( $processor, true );
		millicache_enter_shutdown( $processor );

		$out = $processor->process_output_buffer( '<html>page</html>', PHP_OUTPUT_HANDLER_FINAL );

		// Passed through unstored: the bare instance would have thrown on
		// any reach into the cache manager.
		expect( $out )->toBe( '<html>page</html>' );
	} );

	it( 'still refuses a late do_cache(true) after a sentinel negative', function () {
		Engine::instance()->options()->set_cache_decision( true, 'too-late' );

		$processor = millicache_bare_processor( State::create( 'hash' ) );
		millicache_mark_storable( $processor, false );
		millicache_enter_shutdown( $processor );

		$out = $processor->process_output_buffer( '<html>page</html>', PHP_OUTPUT_HANDLER_FINAL );

		expect( $out )->toBe( '<html>page</html>' );
		expect( $processor->is_storable() )->toBeFalse();
	} );
} );

describe( 'storable path', function () {

	it( 'stores on the shutdown FINAL flush when the sentinel fired positive', function () {
		// Internal UA short-circuits metrics recording.
		$_SERVER['HTTP_USER_AGENT'] = 'MilliCache/tests';

		$captured = null;
		$storage  = Mockery::mock( Storage::class );
		$storage->shouldReceive( 'is_available' )->andReturn( true );
		$storage->shouldReceive( 'perform_cache' )->once()->andReturnUsing(
			function ( $hash, $data, $flags, $cacheable ) use ( &$captured ) {
				$captured = compact( 'hash', 'data', 'flags', 'cacheable' );
				return true;
			}
		);

		list( $processor, $request ) = millicache_full_processor( $storage );
		millicache_set_processor_prop( $processor, 'state', State::create( $request->get_hasher()->get_hash() ?? '' ) );
		millicache_mark_storable( $processor, true );
		millicache_enter_shutdown( $processor );

		$out = $processor->process_output_buffer( '<html>fresh page</html>', PHP_OUTPUT_HANDLER_FINAL );

		expect( $out )->toBe( '<html>fresh page</html>' );
		expect( $captured )->not->toBeNull();
		expect( $captured['cacheable'] )->toBeTrue();
	} );

	it( 'regenerates unchanged: null return and stored-header reuse', function () {
		$_SERVER['HTTP_USER_AGENT'] = 'MilliCache/tests';

		$captured = null;
		$storage  = Mockery::mock( Storage::class );
		$storage->shouldReceive( 'is_available' )->andReturn( true );
		$storage->shouldReceive( 'perform_cache' )->once()->andReturnUsing(
			function ( $hash, $data, $flags, $cacheable ) use ( &$captured ) {
				$captured = $data;
				return true;
			}
		);

		list( $processor, $request ) = millicache_full_processor( $storage );
		$state = State::create( $request->get_hasher()->get_hash() ?? '' )
			->with_fcgi_regenerate( true )
			->with_regen_headers( array( 'Content-Type: text/html', 'X-Custom: kept' ) );
		millicache_set_processor_prop( $processor, 'state', $state );
		millicache_mark_storable( $processor, true );
		millicache_enter_shutdown( $processor );

		$out = $processor->process_output_buffer( '<html>regenerated</html>', PHP_OUTPUT_HANDLER_FINAL );

		expect( $out )->toBeNull();
		expect( $captured )->not->toBeNull();
		expect( $captured['headers'] )->toContain( 'X-Custom: kept' );
	} );

	it( 'never stores responses carrying Content-Encoding', function () {
		$storage = Mockery::mock( Storage::class );
		$storage->shouldReceive( 'is_available' )->andReturn( true );
		$storage->shouldNotReceive( 'perform_cache' );

		list( $processor, $request ) = millicache_full_processor( $storage );
		// Inject the encoded header set via the regen-header channel, which
		// replaces headers_list() as the header source.
		$state = State::create( $request->get_hasher()->get_hash() ?? '' )
			->with_regen_headers( array( 'Content-Type: text/html', 'Content-Encoding: gzip' ) );
		millicache_set_processor_prop( $processor, 'state', $state );
		millicache_mark_storable( $processor, true );
		millicache_enter_shutdown( $processor );

		$out = $processor->process_output_buffer( "\x1f\x8bencoded", PHP_OUTPUT_HANDLER_FINAL );

		expect( $out )->toBe( "\x1f\x8bencoded" );
	} );

	it( 'store() middleware path rejects Content-Encoding with a reason', function () {
		$storage = Mockery::mock( Storage::class );
		$storage->shouldReceive( 'is_available' )->andReturn( true );
		$storage->shouldNotReceive( 'perform_cache' );

		list( $processor ) = millicache_full_processor( $storage );

		$result = $processor->store(
			'pre-compressed body',
			array( 'Content-Type: text/html', 'Content-Encoding: br' ),
			200
		);

		expect( $result['cached'] )->toBeFalse();
		expect( $result['reason'] )->toContain( 'Content-Encoding' );
		expect( $result['reason'] )->toContain( 'br' );
	} );
} );
