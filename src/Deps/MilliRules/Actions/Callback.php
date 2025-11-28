<?php

/**
 * Callback Action
 *
 * Wrapper class for callback-based actions registered via Rules::register_action().
 *
 * @package MilliCache\Deps\MilliRules
 * @author  Philipp Wellmer
 * @since 	0.1.0
 */

namespace MilliCache\Deps\MilliRules\Actions;

use MilliCache\Deps\MilliRules\Logger;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class Callback
 *
 * Allows developers to use closures or callables as action logic without
 * creating dedicated action classes.
 *
 * @since 0.1.0
 */
class Callback implements ActionInterface
{
    /**
     * The callback function to execute.
     *
     * @since 0.1.0
     * @var callable
     */
    private $callback;

    /**
     * The action type identifier.
     *
     * @since 0.1.0
     * @var string
     */
    private string $type;

    /**
     * Full action configuration.
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
     * @param string               $type     The action type identifier.
     * @param callable             $callback The callback function to execute.
     * @param array<string, mixed> $config   The action configuration.
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
     * Execute the action by calling the callback.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context (unused, already stored in constructor).
     * @return void
     */
    public function execute(Context $context): void
    {
        try {
            // Call the callback with config and context object.
            call_user_func($this->callback, $this->config, $this->context);
        } catch (\Exception $e) {
            Logger::error(
                sprintf(
                    'Error in callback action "%s": %s',
                    $this->type,
                    $e->getMessage()
                )
            );
        } catch (\Throwable $e) {
            Logger::error(
                sprintf(
                    'Fatal error in callback action "%s": %s',
                    $this->type,
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Get the action type identifier.
     *
     * @since 0.1.0
     *
     * @return string The action type.
     */
    public function get_type(): string
    {
        return $this->type;
    }
}
