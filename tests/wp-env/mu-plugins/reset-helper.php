<?php
/**
 * Plugin Name: Reset helper
 * Description: Allows clearing caches (OPCache, object cache, APCu) visiting example.com?reset_helper
 * Version: 0.1.0
 * Author: Pascal Birchler
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

add_action(
    'plugins_loaded',
    static function () {
        if (isset($_GET['reset_helper'])) {
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }

            if (function_exists('apcu_clear_cache')) {
                apcu_clear_cache();
            }

            wp_cache_flush();
            delete_expired_transients(true);

            clearstatcache(true);

            status_header(202);
            die;
        }
    },
    1
);
