<?php
/**
 * Plugin Name: Reset helper
 * Description: Allows clearing caches (OPCache, object cache, APCu) visiting example.com?reset_helper
 *              and enables performance mode with longer TTL via ?perf_mode=1
 * Version: 0.2.0
 * Author: Pascal Birchler
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Performance mode filter - extends cache TTL during performance tests.
 * This prevents cache regeneration during test runs.
 */
add_filter(
    'millicache_settings_defaults',
    static function ( array $defaults ): array {
        if ( get_option( 'mc_perf_mode' ) ) {
            $defaults['ttl'] = 3600; // 1 hour TTL during performance tests.
        }
        return $defaults;
    }
);

add_action(
    'plugins_loaded',
    static function () {
        // Handle performance mode toggle.
        if ( isset( $_GET['perf_mode'] ) ) {
            $enable = filter_var( $_GET['perf_mode'], FILTER_VALIDATE_BOOLEAN );

            if ( $enable ) {
                update_option( 'mc_perf_mode', true );
            } else {
                delete_option( 'mc_perf_mode' );
            }

            // If not combined with reset_helper, respond immediately.
            if ( ! isset( $_GET['reset_helper'] ) ) {
                status_header( 200 );
                echo $enable ? 'Performance mode enabled' : 'Performance mode disabled';
                die;
            }
        }

        // Handle cache reset.
        if ( isset( $_GET['reset_helper'] ) ) {
            if ( function_exists( 'opcache_reset' ) ) {
                opcache_reset();
            }

            if ( function_exists( 'apcu_clear_cache' ) ) {
                apcu_clear_cache();
            }

            wp_cache_flush();
            delete_expired_transients( true );

            clearstatcache( true );

            status_header( 202 );
            die;
        }
    },
    1
);
