<?php
/**
 * Subprocess probes for the outermost output buffer.
 *
 * These run real PHP processes because the scenarios cannot be simulated
 * inside the test runner: a boot fatal must happen WITHOUT the WordPress
 * function mocks loaded (proving the handler's abort path is strictly
 * WP-function-free), and the phase-semantics probe asserts engine-level
 * ob_* behavior the capture buffer relies on.
 *
 * @package MilliCache
 */

/**
 * Run a probe script in a fresh PHP process.
 *
 * @param string $script Absolute path to the probe script.
 * @param string $args   Extra CLI arguments.
 * @param bool   $stderr Whether to capture stderr too.
 * @return array{output: string, code: int} Captured output and exit code.
 */
function millicache_run_probe( string $script, string $args = '', bool $stderr = false ): array {
	$redirect = $stderr ? '2>&1' : '2>/dev/null';
	$command  = sprintf(
		'%s -d display_errors=1 -d error_reporting=E_ALL %s %s %s',
		escapeshellarg( PHP_BINARY ),
		escapeshellarg( $script ),
		$args,
		$redirect
	);

	exec( $command, $lines, $code );

	return array(
		'output' => implode( "\n", $lines ),
		'code'   => $code,
	);
}

describe( 'boot-fatal abort path', function () {
	$probe = dirname( __DIR__, 2 ) . '/probe/boot-fatal-probe.php';

	it( 'passes the error page through intact when boot dies via exit (dead-DB wp_die)', function () use ( $probe ) {
		$result = millicache_run_probe( $probe, 'exit' );

		expect( $result['code'] )->toBe( 0 );
		expect( $result['output'] )->toBe( 'BOOT ERROR PAGE' );
	} );

	it( 'streams the page without a secondary fatal when boot fatals (broken drop-in)', function () use ( $probe ) {
		$result = millicache_run_probe( $probe, 'fatal' );

		// The client still receives the error page.
		expect( $result['output'] )->toContain( 'BOOT ERROR PAGE' );

		// Exactly ONE fatal: the simulated boot fatal. A second one would
		// mean the output handler itself called into WordPress.
		expect( substr_count( $result['output'], 'Fatal error' ) )->toBe( 1 );
		expect( $result['output'] )->toContain( 'totally_undefined_wordpress_function' );
		expect( $result['code'] )->toBe( 255 );
	} );
} );

describe( 'output-buffer phase semantics', function () {
	it( 'hold on the PHP version running the suite', function () {
		$probe  = dirname( __DIR__, 2 ) . '/probe/ob-phase-semantics.php';
		$result = millicache_run_probe( $probe, '', true );

		expect( $result['code'] )->toBe( 0, 'Probe deviations: ' . $result['output'] );
		expect( $result['output'] )->toContain( 'OK: output-buffer phase semantics verified' );
	} );
} );
