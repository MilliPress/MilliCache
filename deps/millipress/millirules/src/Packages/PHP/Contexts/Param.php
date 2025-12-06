<?php

/**
 * Param Context
 *
 * Provides request parameters (GET/POST data).
 *
 * @package     MilliRules
 * @subpackage  PHP\Contexts
 * @author      Philipp Wellmer
 * @since       0.1.0
 */

namespace MilliCache\Deps\MilliRules\Packages\PHP\Contexts;

use MilliCache\Deps\MilliRules\Contexts\BaseContext;

/**
 * Class Param
 *
 * Provides 'param' context with request parameters from GET/POST.
 *
 * @since 0.1.0
 */
class Param extends BaseContext
{
    /**
     * Get the context key.
     *
     * @since 0.1.0
     *
     * @return string The context key 'param'.
     */
    public function get_key(): string
    {
        return 'param';
    }

    /**
     * Build the param context data.
     *
     * Captures $_GET at execution time (when context is actually needed)
     * rather than at construction time.
     *
     * @since 0.1.0
     *
     * @return array<string, mixed> The param context.
     */
    protected function build(): array
    {
        return array(
            'param' => $_GET,
        );
    }
}
