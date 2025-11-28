<?php

/**
 * Request Context
 *
 * Provides HTTP request data including method, URI, headers, and client IP.
 *
 * @package     MilliCache\Deps\MilliRules
 * @subpackage  PHP\Contexts
 * @author      Philipp Wellmer
 * @since       0.1.0
 */

namespace MilliCache\Deps\MilliRules\Packages\PHP\Contexts;

use MilliCache\Deps\MilliRules\Contexts\BaseContext;

/**
 * Class Request
 *
 * Provides 'request' context with HTTP request metadata:
 * - method: HTTP method (GET, POST, etc.)
 * - uri: Request URI
 * - scheme: http or https
 * - host: Host name
 * - path: URL path
 * - query: Query string
 * - referer: HTTP referer
 * - user_agent: User agent string
 * - headers: Request headers (lowercase keys)
 * - ip: Client IP address
 *
 * @since 0.1.0
 */
class Request extends BaseContext
{
    /**
     * Get the context key.
     *
     * @since 0.1.0
     *
     * @return string The context key 'request'.
     */
    public function get_key(): string
    {
        return 'request';
    }

    /**
     * Build the request context data.
     *
     * Captures $_SERVER at execution time (when context is actually needed)
     * rather than at construction time.
     *
     * @since 0.1.0
     *
     * @return array<string, mixed> The request context.
     */
    protected function build(): array
    {
        return array(
            'request' => array(
                'method'     => $this->get_request_method(),
                'uri'        => $this->get_request_uri(),
                'scheme'     => $this->get_request_scheme(),
                'host'       => $this->get_host(),
                'path'       => $this->get_path(),
                'query'      => $this->get_query_string(),
                'referer'    => $this->get_referer(),
                'user_agent' => $this->get_user_agent(),
                'headers'    => $this->get_headers(),
                'ip'         => $this->get_client_ip(),
            ),
        );
    }

    /**
     * Get server variable with fallback.
     *
     * @since 0.1.0
     *
     * @param string $key     The server variable key.
     * @param string $default Default value if not found.
     * @return string The server variable value.
     */
    protected function get_server_var(string $key, string $default = ''): string
    {
        $value = $_SERVER[ $key ] ?? $default;
        return is_string($value) ? $value : $default;
    }

    /**
     * Get the HTTP request method.
     *
     * @since 0.1.0
     *
     * @return string The request method (e.g., GET, POST).
     */
    protected function get_request_method(): string
    {
        return strtoupper($this->get_server_var('REQUEST_METHOD', 'GET'));
    }

    /**
     * Get the request URI.
     *
     * @since 0.1.0
     *
     * @return string The request URI.
     */
    protected function get_request_uri(): string
    {
        return $this->get_server_var('REQUEST_URI');
    }

    /**
     * Get the request scheme (http or https).
     *
     * @since 0.1.0
     *
     * @return string The request scheme.
     */
    protected function get_request_scheme(): string
    {
        $https = $this->get_server_var('HTTPS');
        return 'on' === strtolower($https) ? 'https' : 'http';
    }

    /**
     * Get the host name.
     *
     * @since 0.1.0
     *
     * @return string The host name.
     */
    protected function get_host(): string
    {
        return $this->get_server_var('HTTP_HOST');
    }

    /**
     * Get the URL path from the request URI.
     *
     * @since 0.1.0
     *
     * @return string The URL path.
     */
    protected function get_path(): string
    {
        $uri = $this->get_request_uri();
        if (empty($uri)) {
            return '';
        }

        $parsed = parse_url($uri, PHP_URL_PATH);
        return is_string($parsed) ? $parsed : '';
    }

    /**
     * Get the query string.
     *
     * @since 0.1.0
     *
     * @return string The query string.
     */
    protected function get_query_string(): string
    {
        return $this->get_server_var('QUERY_STRING');
    }

    /**
     * Get the referer.
     *
     * @since 0.1.0
     *
     * @return string The HTTP referer.
     */
    protected function get_referer(): string
    {
        return $this->get_server_var('HTTP_REFERER');
    }

    /**
     * Get the user agent.
     *
     * @since 0.1.0
     *
     * @return string The user agent string.
     */
    protected function get_user_agent(): string
    {
        return $this->get_server_var('HTTP_USER_AGENT');
    }

    /**
     * Get all HTTP request headers.
     *
     * @since 0.1.0
     *
     * @return array<string, string> Request headers (normalized to lowercase keys).
     */
    protected function get_headers(): array
    {
        $headers = array();

        // Try getallheaders() if available.
        if (function_exists('getallheaders')) {
            $result = getallheaders();
            $headers = is_array($result) ? $result : array();
        } else {
            // Fallback: Parse headers from $_SERVER.
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $header = str_replace(
                        ' ',
                        '-',
                        ucwords(str_replace('_', ' ', strtolower(substr($key, 5))))
                    );
                    if (is_string($value)) {
                        $headers[ $header ] = $value;
                    }
                }
            }
        }

        // Normalize all keys to lowercase for case-insensitive access.
        return array_change_key_case($headers, CASE_LOWER);
    }

    /**
     * Get the client IP address.
     *
     * @since 0.1.0
     *
     * @return string The client IP address.
     */
    protected function get_client_ip(): string
    {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP', // CloudFlare.
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        );

        foreach ($ip_keys as $key) {
            $value = $this->get_server_var($key);
            if (! empty($value)) {
                // Handle comma-separated IPs (X-Forwarded-For).
                if (strpos($value, ',') !== false) {
                    $parts = explode(',', $value);
                    return trim($parts[0]);
                }
                return $value;
            }
        }

        return '';
    }
}
