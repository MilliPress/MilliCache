<?php
/**
 * Plugin Name: Server-Timing Enhancements
 * Description: Expose various additional metrics via the Server-Timing header, using Performance Lab's' Server Timing API.
 * Version: 0.1.0
 * Author: Pascal Birchler
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

// Enable output buffer to also capture Server-Timing metrics during template rendering.
add_filter('perflab_server_timing_use_output_buffer', '__return_true');

// Send Server-Timing header in /wp-admin/.
add_action(
    'wp_loaded',
    static function () {
        if (! function_exists('perflab_server_timing')) {
            return;
        }

        $server_timing = perflab_server_timing();

        add_filter('admin_init', array( $server_timing, 'on_template_include' ), PHP_INT_MIN);
    },
    100
);

add_action(
    'plugins_loaded',
    static function () {
        if (! function_exists('perflab_server_timing_register_metric')) {
            return;
        }

        // Add total number of db queries.
        perflab_server_timing_register_metric(
            'db-queries',
            array(
                'measure_callback' => function (Perflab_Server_Timing_Metric $metric) {
                    add_action(
                        'perflab_server_timing_send_header',
                        static function () use ($metric) {
                            global $wpdb;
                            $metric->set_value($wpdb->num_queries);
                        }
                    );
                },
                'access_cap'       => 'exist',
            )
        );

        // Add memory usage.
        perflab_server_timing_register_metric(
            'memory-usage',
            array(
                'measure_callback' => function (Perflab_Server_Timing_Metric $metric) {
                    add_action(
                        'perflab_server_timing_send_header',
                        static function () use ($metric) {
                            $metric->set_value(memory_get_usage());
                        }
                    );
                },
                'access_cap'       => 'exist',
            )
        );
    }
);
