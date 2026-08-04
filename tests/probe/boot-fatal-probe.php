<?php
/**
 * Boot-fatal probe: simulates a request that dies before
 * wp-includes/functions.php loads (dead-DB wp_die, broken object-cache
 * drop-in) AFTER MilliCache opened its capture buffer in the drop-in phase.
 *
 * Deliberately loads NO WordPress function mocks: only ABSPATH and the
 * Composer autoloader exist, so any WordPress function call on the output
 * handler's abort path fatals and fails the parent test's assertions.
 *
 * Modes (argv[1]):
 *  - 'exit'  wp_die-style: prints the error page, then exits cleanly.
 *  - 'fatal' drop-in style: undefined-function Error during rendering.
 *
 * @package MilliCache
 */

define( 'ABSPATH', __DIR__ . '/../../' );

require __DIR__ . '/../../vendor/autoload.php';

/**
 * Stand-in for wp-includes/plugin.php, which wp-settings.php always loads
 * BEFORE advanced-cache.php; it is the only WordPress API available when
 * the capture buffer opens. Everything else stays undefined on purpose.
 *
 * @param string   $hook     Hook name.
 * @param callable $callback Callback.
 * @param int      $priority Priority.
 * @return bool
 */
function add_action( $hook, $callback, $priority = 10 ) {
	return true;
}

use MilliCache\Engine\Response\Processor;
use MilliCache\Engine\Response\State;

$mode = isset( $argv[1] ) ? $argv[1] : 'exit';

$processor = ( new ReflectionClass( Processor::class ) )->newInstanceWithoutConstructor();

// Mirror Engine::run(): the buffer opens in the drop-in phase (this also
// registers the in-shutdown marker). The template_redirect sentinel never
// fires in this scenario, so the handler must pass the error page through
// on its WP-function-free path.
$processor->start_output_buffer( State::create( 'boot-fatal-hash' ) );

echo 'BOOT ERROR PAGE';

if ( 'fatal' === $mode ) {
	totally_undefined_wordpress_function();
}

exit( 0 );
