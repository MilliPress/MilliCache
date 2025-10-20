<?php
/**
 * Action Interface
 *
 * Contract for all action classes in the rules system.
 *
 * @package MilliCache
 * @subpackage Rules\Actions
 * @since 1.0.0
 */

namespace MilliCache\Rules\Actions;

/**
 * Interface ActionInterface
 *
 * Defines the contract that all action classes must implement.
 *
 * @since 1.0.0
 */
interface ActionInterface {
	/**
	 * Execute the action based on the provided context.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context containing request and WordPress data.
	 * @return void
	 */
	public function execute( array $context ): void;

	/**
	 * Check if this is a trigger action (executes without stopping rule processing).
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if trigger action, false if stop action.
	 */
	public function is_trigger_action(): bool;

	/**
	 * Get the type identifier for this action.
	 *
	 * @since 1.0.0
	 *
	 * @return string The action type (e.g., 'cache_page', 'add_flag').
	 */
	public function get_type(): string;
}
