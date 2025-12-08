<?php

/**
 * Cookie Context
 *
 * Provides cookie data from HTTP request.
 *
 * @package     MilliRules
 * @subpackage  PHP\Contexts
 * @author      Philipp Wellmer
 * @since       0.1.0
 */

namespace MilliCache\Deps\MilliRules\Packages\PHP\Contexts;

use MilliCache\Deps\MilliRules\Contexts\BaseContext;

/**
 * Class Cookie
 *
 * Provides 'cookie' context with cookie data from the HTTP request.
 *
 * @since 0.1.0
 */
class Cookie extends BaseContext
{
    /**
     * Get the context key.
     *
     * @since 0.1.0
     *
     * @return string The context key 'cookie'.
     */
    public function get_key(): string
    {
        return 'cookie';
    }

    /**
     * Build the cookie context data.
     *
     * Captures $_COOKIE at execution time (when context is actually needed)
     * rather than at construction time.
     *
     * @since 0.1.0
     *
     * @return array<string, mixed> The cookie context.
     */
    protected function build(): array
    {
        return array(
            'cookie' => $_COOKIE,
        );
    }
}
