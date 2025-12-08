<?php

/**
 * Callback Condition
 *
 * Wrapper class for callback-based conditions registered via Rules::register_condition().
 *
 * @package     MilliRules
 * @author      Philipp Wellmer
 */

namespace MilliCache\Deps\MilliRules\Conditions;

use MilliCache\Deps\MilliRules\Logger;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class Callback
 *
 * Allows developers to use closures or callables as condition logic without
 * creating dedicated condition classes.
 *
 * @since 0.1.0
 */
class Callback implements ConditionInterface
{
    /**
     * The callback function to execute.
     *
     * @since 0.1.0
     * @var callable
     */
    private $callback;

    /**
     * The condition type identifier.
     *
     * @since 0.1.0
     * @var string
     */
    private string $type;

    /**
     * Full condition configuration.
     *
     * @since 0.1.0
     * @var array<string, mixed>
     */
    private array $config;

    /**
     * Execution context.
     *
     * @since 0.1.0
     * @var Context
     */
    private Context $context;

    /**
     * Constructor.
     *
     * @since 0.1.0
     *
     * @param string               $type     The condition type identifier.
     * @param callable             $callback The callback function to execute.
     * @param array<string, mixed> $config   The condition configuration.
     * @param Context     $context  The execution context.
     */
    public function __construct(string $type, callable $callback, array $config, Context $context)
    {
        $this->type     = $type;
        $this->callback = $callback;
        $this->config   = $config;
        $this->context  = $context;
    }

    /**
     * Check if the condition matches by calling the callback.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context (unused, already stored in constructor).
     * @return bool True if the callback returns true, false otherwise.
     */
    public function matches(Context $context): bool
    {
        try {
            // Call the callback with config and context object.
            $result = call_user_func($this->callback, $this->config, $this->context);

            // Ensure boolean return value.
            return (bool) $result;
        } catch (\Exception $e) {
            Logger::error(
                sprintf(
                    'Error in callback condition "%s": %s',
                    $this->type,
                    $e->getMessage()
                )
            );
            return false;
        } catch (\Throwable $e) {
            Logger::error(
                sprintf(
                    'Fatal error in callback condition "%s": %s',
                    $this->type,
                    $e->getMessage()
                )
            );
            return false;
        }
    }

    /**
     * Get the condition type identifier.
     *
     * @since 0.1.0
     *
     * @return string The condition type.
     */
    public function get_type(): string
    {
        return $this->type;
    }
}
