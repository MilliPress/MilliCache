<?php
/**
 * Condition Interface
 *
 * Contract for all condition classes in the rules system.
 *
 * @package     MilliCache
 * @subpackage  Rules
 * @author      Philipp Wellmer <hello@millicache.com>
 */

namespace MilliCache\Rules\Conditions;

/**
 * Interface ConditionInterface
 *
 * Defines the contract that all condition classes must implement.
 *
 * @since 1.0.0
 */
interface ConditionInterface {
	/**
	 * Check if the condition matches based on the provided context.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $context The execution context containing request and WordPress data.
	 * @return bool True if the condition matches, false otherwise.
	 */
	public function matches( array $context ): bool;

	/**
	 * Get the type identifier for this condition.
	 *
	 * @since 1.0.0
	 *
	 * @return string The condition type (e.g., 'request_url', 'post_type').
	 */
	public function get_type(): string;
}
