<?php

/**
 * Base Condition
 *
 * Abstract base class for all condition implementations.
 *
 * @package     MilliCache\Deps\MilliRules
 * @author      Philipp Wellmer
 * @since 		0.1.0
 */

namespace MilliCache\Deps\MilliRules\Conditions;

use MilliCache\Deps\MilliRules\Logger;
use MilliCache\Deps\MilliRules\Context;
use MilliCache\Deps\MilliRules\PlaceholderResolver;

/**
 * Class BaseCondition
 *
 * Provides common functionality for all conditions, including operator logic
 * and multi-value matching.
 *
 * @since 0.1.0
 */
abstract class BaseCondition implements ConditionInterface
{
    /**
     * Comparison operator.
     *
     * @since 0.1.0
     * @var string
     */
    protected string $operator;

    /**
     * Expected value(s) to compare against.
     *
     * @since 0.1.0
     * @var mixed
     */
    protected $value;

    /**
     * Match type for array values (all/any/none).
     *
     * @since 0.1.0
     * @var string|null
     */
    protected ?string $match_type;

    /**
     * Full condition configuration.
     *
     * @since 0.1.0
     * @var array<string, mixed>
     */
    protected array $config;

    /**
     * Placeholder resolver instance.
     *
     * @since 0.1.0
     * @var PlaceholderResolver
     */
    protected PlaceholderResolver $resolver;

    /**
     * Define how builder arguments map to condition config keys.
     *
     * This method controls how the ConditionBuilder interprets method arguments
     * and maps them to the condition configuration array.
     *
     * Common Patterns:
     *
     * Value-Based Conditions (default: ['value']):
     * - Check a single, predetermined field in the context
     * - First argument = the VALUE to compare against
     * - Examples:
     *   - RequestMethod: ->request_method('POST') → ['value' => 'POST']
     *   - PostType: ->post_type('page') → ['value' => 'page']
     *   - UserRole: ->user_role('admin') → ['value' => 'admin']
     *
     * Name-Based Conditions (['name', 'value']):
     * - Require specifying WHICH field/key to check from a collection
     * - First argument = the field NAME to check
     * - Second argument = the VALUE to compare (optional, defaults to EXISTS check)
     * - Examples:
     *   - Cookie: ->cookie('session_id') → ['name' => 'session_id', 'operator' => 'EXISTS']
     *   - Cookie: ->cookie('session_id', 'abc') → ['name' => 'session_id', 'value' => 'abc']
     *   - RequestHeader: ->request_header('User-Agent') → ['name' => 'User-Agent', 'operator' => 'EXISTS']
     *   - RequestParam: ->request_param('action', 'edit') → ['name' => 'action', 'value' => 'edit']
     *
     * Custom Handling (empty array: []):
     * - Returns empty array to signal "pass through all arguments as 'args'"
     * - Condition handles argument interpretation in its constructor
     * - Example: IsConditional with variable argument patterns
     *
     * Override this method in your condition class to define custom argument mapping.
     *
     * @since 0.1.0
     *
     * @return array<int, string> Array of config keys that arguments map to, or empty for custom handling.
     */
    public static function get_argument_mapping(): array
    {
        return ['value'];  // Default: value-based condition
    }

    /**
     * Constructor.
     *
     * @since 0.1.0
     *
     * @param array<string, mixed> $config  The condition configuration.
     * @param Context     $context The execution context.
     */
    public function __construct(array $config, Context $context)
    {
        $this->config     = $config;
        $operator_value   = $config['operator'] ?? '=';
        $this->operator   = $this->normalize_operator(is_string($operator_value) ? $operator_value : '=');

        if (array_key_exists('value', $config)) {
            $this->value = $config['value'];
        } else {
            $this->value = '';
        }

        $match_type_value = $config['match_type'] ?? null;
        $this->match_type = is_string($match_type_value) ? $match_type_value : null;
        $this->resolver   = new PlaceholderResolver($context);
    }

    /**
     * Normalize operator to uppercase.
     *
     * @since 0.1.0
     *
     * @param string $operator The operator to normalize.
     * @return string The normalized operator.
     */
    private function normalize_operator(string $operator): string
    {
        return strtoupper(trim($operator));
    }

    /**
     * Check if the condition matches.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context.
     * @return bool True if the condition matches, false otherwise.
     */
    public function matches(Context $context): bool
    {
        $actual_value = $this->get_actual_value($context);

        // Handle array values with match_type logic.
        if (is_array($this->value)) {
            return $this->check_multiple_values($actual_value, $this->value);
        }

        // Resolve placeholders in the expected value if it's a string.
        $expected_value = is_string($this->value) ? $this->resolver->resolve($this->value) : $this->value;

        return $this->compare($actual_value, $expected_value);
    }

    /**
     * Public static helper to compare values using operators.
     *
     * @since 0.1.0
     *
     * @param mixed  $actual   The actual value from context.
     * @param mixed  $expected The expected value from config.
     * @param string $operator The comparison operator (=, !=, >, >=, <, <=, IN, LIKE, REGEXP, etc.).
     * @return bool True if comparison matches, false otherwise.
     */
    public static function compare_values($actual, $expected, string $operator = '='): bool
    {
        // Normalize operator to uppercase.
        $operator = strtoupper(trim($operator));

        // Convert to strings for string operations.
        $actual_str   = is_scalar($actual) ? (string) $actual : '';
        $expected_str = is_scalar($expected) ? (string) $expected : '';

        switch ($operator) {
            // Equality operators.
            case '=':
                return $actual_str === $expected_str;

            case '!=':
                return $actual_str !== $expected_str;

            // Numeric comparison operators.
            case '>':
                return is_numeric($actual) && is_numeric($expected) && (float) $actual > (float) $expected;

            case '>=':
                return is_numeric($actual) && is_numeric($expected) && (float) $actual >= (float) $expected;

            case '<':
                return is_numeric($actual) && is_numeric($expected) && (float) $actual < (float) $expected;

            case '<=':
                return is_numeric($actual) && is_numeric($expected) && (float) $actual <= (float) $expected;

            // Pattern matching operators.
            case 'LIKE':
                return self::like_match($actual_str, $expected_str);

            case 'NOT LIKE':
                return ! self::like_match($actual_str, $expected_str);

            case 'REGEXP':
                return self::regexp_match($actual_str, $expected_str);

            // Array operators.
            case 'IN':
                $expected_array = is_array($expected) ? $expected : array( $expected );
                return in_array($actual_str, array_map('strval', $expected_array), true);

            case 'NOT IN':
                $expected_array = is_array($expected) ? $expected : array( $expected );
                return ! in_array($actual_str, array_map('strval', $expected_array), true);

            // Existence operators.
            case 'EXISTS':
                return ! empty($actual) || '0' === $actual || 0 === $actual;

            case 'NOT EXISTS':
                return empty($actual) && '0' !== $actual && 0 !== $actual;

            // Boolean operators.
            case 'IS':
                return (bool) $actual === (bool) $expected;

            case 'IS NOT':
                return (bool) $actual !== (bool) $expected;

            default:
                Logger::error('Unknown operator: ' . $operator);
                return false;
        }
    }

    /**
     * Compare actual and expected values based on the operator.
     *
     * @since 0.1.0
     *
     * @param mixed $actual   The actual value from context.
     * @param mixed $expected The expected value from config.
     * @return bool True if comparison matches, false otherwise.
     */
    protected function compare($actual, $expected): bool
    {
        return self::compare_values($actual, $expected, $this->operator);
    }

    /**
     * Static helper for LIKE pattern matching with wildcard support.
     *
     * @since 0.1.0
     *
     * @param string $string  The string to test.
     * @param string $pattern The pattern to match against (* and ? wildcards).
     * @return bool True if the pattern matches, false otherwise.
     */
    protected static function like_match(string $string, string $pattern): bool
    {
        // Convert LIKE wildcards to regex: * = .*, ? = .
        $regex = '/^' . str_replace(
            array( '\\*', '\\?' ),
            array( '.*', '.' ),
            preg_quote($pattern, '/')
        ) . '$/i';

        return preg_match($regex, $string) === 1;
    }

    /**
     * Static helper for REGEXP pattern matching.
     *
     * @since 0.1.0
     *
     * @param string $string  The string to test.
     * @param string $pattern The pattern to match against.
     * @return bool True if the pattern matches, false otherwise.
     */
    protected static function regexp_match(string $string, string $pattern): bool
    {
        // Check if the pattern is a regex (enclosed in /).
        if (preg_match('/^\/.*\/$/', $pattern)) {
            return @preg_match($pattern, $string) === 1;
        }

        // Wildcard support: convert * to regex.
        $regex = '/^' . str_replace(array( '\\*', '\\?' ), array( '.*', '.' ), preg_quote($pattern, '/')) . '$/i';
        return preg_match($regex, $string) === 1;
    }

    /**
     * Check multiple values with match_type logic.
     *
     * @since 0.1.0
     *
     * @param mixed                    $actual_value    The actual value from context.
     * @param array<int|string, mixed> $expected_values Array of expected values.
     * @return bool True if the match_type condition is met, false otherwise.
     */
    protected function check_multiple_values($actual_value, array $expected_values): bool
    {
        $match_type = $this->match_type ?? 'any';
        $matches    = array();

        foreach ($expected_values as $expected) {
            $resolved_expected = is_string($expected) ? $this->resolver->resolve($expected) : $expected;
            $matches[]         = $this->compare($actual_value, $resolved_expected);
        }

        switch ($match_type) {
            case 'all':
                return ! in_array(false, $matches, true);

            case 'none':
                return ! in_array(true, $matches, true);

            case 'any':
            default:
                return in_array(true, $matches, true);
        }
    }

    /**
     * Get the actual value from context.
     *
     * Must be implemented by concrete condition classes.
     * Subclasses can call $context->load() to trigger lazy loading.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context.
     * @return mixed The actual value to compare.
     */
    abstract protected function get_actual_value(Context $context);
}
