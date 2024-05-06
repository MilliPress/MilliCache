<?php
/**
 * Plugin Name: Link Header Fix
 * Description: Add Link header to set rest route for WordPress while WordPress is not loaded
 * Version: 1.0
 */

/**
 * Add Link header to set rest route for WordPress
 * This is needed for our Playwright tests to work
 * MilliCache plugin skips WordPress loading, so we need to add this header manually
 */
// Add Link header to set rest route for WordPress
if (!function_exists('rest_output_link_header')) {
    $isSecure         = ( ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ) || $_SERVER['SERVER_PORT'] == 443;
    $protocol         = $isSecure ? "https://" : "http://";
    $serverRequestURL = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    header("Link: <{$serverRequestURL}index.php?rest_route=/>; rel=\"https://api.w.org/\"");
}
