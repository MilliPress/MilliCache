<?php

/**
 * User Context
 *
 * Provides WordPress current user data.
 *
 * @package     MilliRules
 * @subpackage  WordPress\Contexts
 * @author      Philipp Wellmer
 * @since       0.1.0
 */

namespace MilliCache\Deps\MilliRules\Packages\WordPress\Contexts;

use MilliCache\Deps\MilliRules\Contexts\BaseContext;

/**
 * Class User
 *
 * Provides 'user' context with current WordPress user data:
 * - id: User ID
 * - login: User login name
 * - email: User email
 * - roles: Array of user roles
 * - logged_in: Boolean indicating if user is logged in
 *
 * @since 0.1.0
 */
class User extends BaseContext
{
    /**
     * Get the context key.
     *
     * @since 0.1.0
     *
     * @return string The context key 'user'.
     */
    public function get_key(): string
    {
        return 'user';
    }

    /**
     * Build the user context data.
     *
     * @since 0.1.0
     *
     * @return array<string, mixed> The user context.
     */
    protected function build(): array
    {
        if (! function_exists('wp_get_current_user')) {
            return array(
                'user' => array(
                    'id'        => 0,
                    'login'     => '',
                    'email'     => '',
                    'roles'     => array(),
                    'logged_in' => false,
                ),
            );
        }

        $user = wp_get_current_user();

        return array(
            'user' => array(
                'id'        => $user->ID,
                'login'     => $user->user_login,
                'email'     => $user->user_email,
                'roles'     => $user->roles,
                'logged_in' => $user->ID > 0,
            ),
        );
    }

    /**
     * Check if WordPress user functions are available.
     *
     * @since 0.1.0
     *
     * @return bool True if available, false otherwise.
     */
    public function is_available(): bool
    {
        return function_exists('wp_get_current_user');
    }
}
