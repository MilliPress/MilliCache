<?php
/**
 * Base Action
 *
 * Abstract base class for all action implementations.
 *
 * @package MilliCache
 * @subpackage Rules\Actions
 * @since 1.0.0
 */

namespace MilliCache\Rules\Actions;

use MilliCache\Rules\PlaceholderResolver;

/**
 * Class BaseAction
 *
 * Provides common functionality for all the actions including placeholder resolution.
 *
 * @since 1.0.0
 */
abstract class BaseAction implements ActionInterface {
	/**
	 * Whether this is a trigger action (true) or stop action (false).
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected bool $is_trigger = true;

	/**
	 * Action value.
	 *
	 * @since 1.0.0
	 * @var mixed
	 */
	protected $value;

	/**
	 * Full action configuration.
	 *
	 * @since 1.0.0
	 * @var array<string, mixed>
	 */
	protected array $config;

	/**
	 * Placeholder resolver instance.
	 *
	 * @since 1.0.0
	 * @var PlaceholderResolver
	 */
	protected PlaceholderResolver $resolver;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $config  The action configuration.
	 * @param array<string, mixed> $context The execution context.
	 */
	public function __construct( array $config, array $context ) {
		$this->config   = $config;
		$this->value    = $config['value'] ?? null;
		$this->resolver = new PlaceholderResolver( $context );
	}

	/**
	 * Check if this is a trigger action.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if trigger action, false if stop action.
	 */
	public function is_trigger_action(): bool {
		return $this->is_trigger;
	}

	/**
	 * Resolve placeholders in a value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value The value to resolve.
	 * @return string The resolved value.
	 */
	protected function resolve_value( string $value ): string {
		return $this->resolver->resolve( $value );
	}
}
