<?php
/**
 * Output-buffer phase-semantics probe.
 *
 * Asserts the PHP output-handler phase behavior MilliCache's capture buffer
 * relies on. Runs against the current PHP as part of the test suite (see
 * OutputBufferProbesTest); standalone on purpose (no Composer, no Pest,
 * PHP 7.4-compatible syntax) so it can be run against any interpreter.
 *
 * The supported floor is settled: all probes pass on PHP 7.4.33 (the final
 * 7.4 release, verified 2026-08-03), matching 8.4.16.
 *
 * Run: php tests/probe/ob-phase-semantics.php
 * Exits non-zero listing every deviation.
 *
 * @package MilliCache
 */

$failures = array();

/**
 * Run a single probe, collecting assertion failures instead of aborting.
 *
 * @param string   $name The probe name.
 * @param callable $fn   The probe body; throws RuntimeException on failure.
 * @return void
 */
function probe( $name, callable $fn ) {
	global $failures;

	$level = ob_get_level();
	try {
		$fn();
	} catch ( Throwable $e ) {
		$failures[] = $name . ': ' . $e->getMessage();
	}
	while ( ob_get_level() > $level ) {
		@ob_end_clean();
	}
}

/**
 * Minimal assertion helper.
 *
 * @param bool   $condition The condition that must hold.
 * @param string $message   Failure message.
 * @return void
 */
function check( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

probe(
	'ob_clean invokes the handler with CLEAN, without FINAL',
	function () {
		$phases = array();
		ob_start(
			function ( $output, $phase ) use ( &$phases ) {
				$phases[] = $phase;
				return $output;
			}
		);
		echo 'x';
		ob_clean();

		check( 1 === count( $phases ), 'handler was not called exactly once on ob_clean' );
		check( 0 !== ( $phases[0] & PHP_OUTPUT_HANDLER_CLEAN ), 'CLEAN flag missing on ob_clean' );
		check( 0 === ( $phases[0] & PHP_OUTPUT_HANDLER_FINAL ), 'FINAL flag unexpectedly set on ob_clean' );
	}
);

probe(
	'ob_end_clean invokes the handler with CLEAN|FINAL and emits nothing',
	function () {
		$phases = array();
		ob_start();
		ob_start(
			function ( $output, $phase ) use ( &$phases ) {
				$phases[] = $phase;
				return $output;
			}
		);
		echo 'x';
		ob_end_clean();
		$emitted = ob_get_clean();

		check( 1 === count( $phases ), 'handler was not called exactly once on ob_end_clean' );
		check( 0 !== ( $phases[0] & PHP_OUTPUT_HANDLER_CLEAN ), 'CLEAN flag missing on ob_end_clean' );
		check( 0 !== ( $phases[0] & PHP_OUTPUT_HANDLER_FINAL ), 'FINAL flag missing on ob_end_clean' );
		check( '' === $emitted, 'ob_end_clean emitted output' );
	}
);

probe(
	'chunk-size overflow invokes the handler without FINAL and without CLEAN',
	function () {
		$phases = array();
		ob_start();
		ob_start(
			function ( $output, $phase ) use ( &$phases ) {
				$phases[] = $phase;
				return $output;
			},
			8
		);
		echo '0123456789';

		check( count( $phases ) >= 1, 'handler was not called on chunk-size overflow' );
		check( 0 === ( $phases[0] & PHP_OUTPUT_HANDLER_FINAL ), 'FINAL flag unexpectedly set on chunk overflow' );
		check( 0 === ( $phases[0] & PHP_OUTPUT_HANDLER_CLEAN ), 'CLEAN flag unexpectedly set on chunk overflow' );
	}
);

probe(
	'mid-request ob_flush invokes the handler with FLUSH, without FINAL',
	function () {
		$phases = array();
		ob_start();
		ob_start(
			function ( $output, $phase ) use ( &$phases ) {
				$phases[] = $phase;
				return $output;
			}
		);
		echo 'x';
		ob_flush();

		check( 1 === count( $phases ), 'handler was not called exactly once on ob_flush' );
		check( 0 !== ( $phases[0] & PHP_OUTPUT_HANDLER_FLUSH ), 'FLUSH flag missing on ob_flush' );
		check( 0 === ( $phases[0] & PHP_OUTPUT_HANDLER_FINAL ), 'FINAL flag unexpectedly set on ob_flush' );
	}
);

probe(
	'null return suppresses output on FINAL',
	function () {
		ob_start();
		ob_start(
			function ( $output, $phase ) {
				return null;
			}
		);
		echo 'SHOULD NOT APPEAR';
		ob_end_flush();
		$emitted = ob_get_clean();

		check( '' === $emitted, 'null handler return did not suppress output on FINAL' );
	}
);

if ( ! empty( $failures ) ) {
	fwrite( STDERR, 'Output-buffer phase semantics DEVIATE on PHP ' . PHP_VERSION . ":\n" );
	foreach ( $failures as $failure ) {
		fwrite( STDERR, ' - ' . $failure . "\n" );
	}
	exit( 1 );
}

echo 'OK: output-buffer phase semantics verified on PHP ' . PHP_VERSION . "\n";
exit( 0 );
