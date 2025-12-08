<?php

/**
 * Generic Is Condition
 *
 * Generic fallback for WordPress is_* conditional functions.
 *
 * @package     MilliRules
 * @author      Philipp Wellmer
 */

namespace MilliCache\Deps\MilliRules\Packages\WordPress\Conditions;

use MilliCache\Deps\MilliRules\Conditions\BaseCondition;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class IsConditional
 *
 * Provides a generic fallback for WordPress conditional functions that start with "is_".
 * This class dynamically calls the corresponding WordPress function when no specific
 * condition class exists.
 *
 * Modes:
 * - Boolean mode (no args): calls the is_* function with no arguments and compares
 *   the boolean result to the configured value (default TRUE) using the configured
 *   operator (default 'IS').
 *   Examples:
 *     ->is_404()                    // is_404() IS TRUE
 *     ['type' => 'is_404']          // is_404() IS TRUE
 *     ['type' => 'is_404','value'=>false,'operator'=>'IS']
 *
 * - Function-call mode (with args): uses the raw method arguments to call the
 *   is_* function, and compares the resulting boolean value to TRUE using an
 *   optional operator taken from the last argument if it looks like an operator.
 *   Examples:
 *     ->is_singular('page')                 // is_singular('page') IS TRUE
 *     ->is_tax('genre', 'sci-fi')          // is_tax('genre','sci-fi') IS TRUE
 *     ->is_tax('genre', 'sci-fi', '!=')    // is_tax('genre','sci-fi') != TRUE
 *
 * Raw arguments are provided by ConditionBuilder via the 'args' key.
 * Other packages can reuse this convention for their own condition types.
 *
 * @since 0.1.0
 */
class IsConditional extends BaseCondition
{
    /**
     * Define argument mapping for IsConditional.
     *
     * IsConditional uses custom argument handling - returns empty array
     * to signal that all args should be passed through for custom interpretation.
     * See BaseCondition::get_argument_mapping() for detailed explanation.
     *
     * @since 0.1.0
     *
     * @return array<int, string>
     */
    public static function get_argument_mapping(): array
    {
        return [];  // Empty = pass through as 'args' for custom handling
    }

    /**
     * Constructor.
     *
     * Sets up comparison defaults and interprets builder arguments
     * for WordPress is_* conditionals.
     *
     * @since 0.1.0
     *
     * @param array<string, mixed> $config  The condition configuration.
     * @param Context              $context The execution context.
     */
    public function __construct(array $config, Context $context)
    {
        // Default operator/value fallbacks (will be refined below).
        if (! isset($config['operator'])) {
            $config['operator'] = 'IS';
        }

        // If no explicit value provided, start from TRUE (boolean conditionals).
        if (! isset($config['value'])) {
            $config['value'] = true;
        }

        // If we have raw arguments from the builder, interpret them.
        if (isset($config['args']) && is_array($config['args']) && ! empty($config['args'])) {
            $raw_args = $config['args'];
            $first    = $raw_args[0];

            // Mode A: boolean mode when first arg is boolean (->is_404(false), ->is_404(true, 'IS NOT')).
            if (is_bool($first)) {
                $config['value'] = $first;

                // Optional operator as second arg if it's a string.
                if (isset($raw_args[1]) && is_string($raw_args[1])) {
                    $config['operator'] = $raw_args[1];
                } else {
                    // Infer operator for boolean values (IS / IS NOT).
                    $config['operator'] = self::normalize_operator($config['operator'], $first);
                }
            } else {
                // Mode B: function-call mode (arguments for the is_* function).
                $args = $raw_args;

                // Inspect last raw arg as potential operator.
                $maybe_op = end($args);
                $operator = null;

                if (is_string($maybe_op) && self::looks_like_operator($maybe_op)) {
                    $operator = $maybe_op;
                    array_pop($args);
                }

                // Store remaining arguments as function args.
                $config['fn_args'] = $args;

                // We always compare the result of the is_* function to TRUE.
                $config['value'] = true;

                // Operator for comparison: explicit operator from args or default to 'IS'.
                $config['operator'] = $operator !== null ? $operator : 'IS';
            }
        }

        parent::__construct($config, $context);
    }

    /**
     * Get the condition type.
     *
     * @since 0.1.0
     *
     * @return string The condition type identifier.
     */
    public function get_type(): string
    {
        $type_value = $this->config['type'] ?? '';
        return is_string($type_value) ? $type_value : '';
    }

    /**
     * Get the actual value from context.
     *
     * Dynamically calls the WordPress conditional function corresponding to the type.
     * For example, type 'is_home' will call the is_home() function.
     *
     * If 'fn_args' is present in config, calls the function with those arguments:
     *   is_tax('genre','sci-fi')
     * Otherwise, calls the function with no arguments.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context.
     * @return bool The result of the WordPress conditional function, or false if it doesn't exist.
     */
    protected function get_actual_value(Context $context): bool
	{
        $fn = $this->get_type();

        // Check if the function exists.
        if (empty($fn) || ! function_exists($fn)) {
            return false;
        }

        // Call with or without function arguments.
        if (isset($this->config['fn_args']) && is_array($this->config['fn_args']) && ! empty($this->config['fn_args'])) {
            return (bool) call_user_func_array($fn, $this->config['fn_args']);
        }

        return (bool) call_user_func($fn);
    }

    /**
     * Check if a string looks like a comparison operator we support.
     *
     * Supported operators for this condition:
     * - '='
     * - '!='
     * - 'IS'
     * - 'IS NOT'
	 *
	 * @since 0.1.0
     *
     * @param string $value Candidate operator string.
     * @return bool
     */
    private function looks_like_operator(string $value): bool
    {
        $upper = strtoupper(trim($value));
        $supported = array('=', '!=', 'IS', 'IS NOT');
        return in_array($upper, $supported, true);
    }

    /**
     * Normalize operator for boolean comparisons.
     *
     * For now, this simply uppercases known operators and falls back to 'IS'
     * if the provided operator is not recognized.
	 *
	 * @since 0.1.0
     *
     * @param string $operator Provided operator.
     * @param bool   $value    The boolean value (currently unused, but may be used in future).
     * @return string
     */
    private function normalize_operator(string $operator, bool $value): string
    {
        $upper = strtoupper(trim($operator));
        if ($this->looks_like_operator($upper)) {
            return $upper;
        }
        return 'IS';
    }
}
