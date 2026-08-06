<?php
/**
 * Plugin Name: Init-Phase Output-Buffer Post-Processor
 * Description: Simulates plugins like TranslatePress that open an output buffer on init and transform the page HTML in their flush callback. MilliCache must capture the transformed HTML, so the marker has to appear in cached entries too.
 * Version: 1.0.0
 *
 * @package MilliCacheTests
 */

add_action(
    'init',
    static function () {
        // Frontend page renders only — mirrors TranslatePress' own scoping.
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }

        ob_start(
            static function (string $output): string {
                return str_replace('</body>', '<!-- mc-e2e-init-ob --></body>', $output);
            }
        );
    },
    0
);
