<?php

/**
 * Tag Condition
 *
 * Checks the queried tag by ID, slug, or name.
 *
 * @package     MilliRules
 * @author      Philipp Wellmer
 */

namespace MilliCache\Deps\MilliRules\Packages\WordPress\Conditions;

use MilliCache\Deps\MilliRules\Conditions\BaseCondition;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class Tag
 *
 * Unified tag condition that matches against ID, slug, or name.
 * Works on tag archive pages, allowing flexible matching against
 * any tag identifier.
 *
 * The condition automatically matches the expected value against:
 * - Tag ID (numeric values)
 * - Tag slug (string values)
 * - Tag name (string values)
 *
 * Supported operators:
 * - =: Exact match against any field (default)
 * - !=: Does not match any field
 * - IN: Check if any value matches any field
 * - NOT IN: Check if no values match any field
 * - LIKE: Pattern matching against slug/name
 * - REGEXP: Regular expression matching against slug/name
 *
 * Examples:
 * Array syntax:
 * - Match by ID: ['type' => 'tag', 'value' => 10]
 * - Match by slug: ['type' => 'tag', 'value' => 'featured']
 * - Match by name: ['type' => 'tag', 'value' => 'Featured Posts']
 * - Multiple values: ['type' => 'tag', 'value' => [10, 'popular', 'featured'], 'operator' => 'IN']
 * - Pattern match: ['type' => 'tag', 'value' => 'wp-*', 'operator' => 'LIKE']
 *
 * Builder syntax:
 * - ->tag(10) // match by ID
 * - ->tag('featured') // match by slug or name
 * - ->tag([10, 'popular', 'featured'], 'IN') // match any
 *
 * @since 0.1.0
 */
class Tag extends BaseCondition
{
    /**
     * Get the condition type.
     *
     * @since 0.1.0
     *
     * @return string The condition type identifier.
     */
    public function get_type(): string
    {
        return 'tag';
    }

    /**
     * Get the actual value from context.
     *
     * Returns an associative array with tag information.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context.
     * @return array<string, mixed>|null Tag data array or null if not a tag archive.
     */
    protected function get_actual_value(Context $context)
    {
        // Try to get from context first.
        $context->load('term');
        $term_data = $context->get('term');

        if (is_array($term_data)) {
            // Verify this is a tag (taxonomy = 'post_tag').
            if (isset($term_data['taxonomy']) && 'post_tag' === $term_data['taxonomy']) {
                return array(
                    'id'   => isset($term_data['id']) && is_numeric($term_data['id']) ? (int) $term_data['id'] : 0,
                    'slug' => isset($term_data['slug']) && is_string($term_data['slug']) ? $term_data['slug'] : '',
                    'name' => isset($term_data['name']) && is_string($term_data['name']) ? $term_data['name'] : '',
                );
            }
        }

        // Fall back to WordPress functions.
        if (function_exists('is_tag') && function_exists('get_queried_object') && is_tag()) {
            $term = get_queried_object();

            if ($term && isset($term->term_id, $term->slug, $term->name)) {
                return array(
                    'id'   => (int) $term->term_id,
                    'slug' => (string) $term->slug,
                    'name' => (string) $term->name,
                );
            }
        }

        return null;
    }

    /**
     * Compare actual and expected values.
     *
     * Overrides parent to handle matching against multiple fields (id, slug, name).
     *
     * @since 0.1.0
     *
     * @param mixed $actual   The actual value from context (array with id/slug/name or null).
     * @param mixed $expected The expected value from config (scalar or array).
     * @return bool True if comparison matches, false otherwise.
     */
    protected function compare($actual, $expected): bool
    {
        // If no tag data, handle based on operator.
        if (null === $actual || ! is_array($actual)) {
            // For negative operators, not having a tag can be considered a match.
            if (in_array($this->operator, array( '!=', 'IS NOT', 'NOT IN', 'NOT EXISTS' ), true)) {
                return true;
            }
            return false;
        }

        $actual_id   = $actual['id'] ?? 0;
        $actual_slug = $actual['slug'] ?? '';
        $actual_name = $actual['name'] ?? '';

        // Convert expected to array for unified handling.
        $expected_values = is_array($expected) ? $expected : array( $expected );

        // Check if any expected value matches any actual field.
        $has_match = false;

        foreach ($expected_values as $expected_value) {
            // Resolve placeholders if needed.
            $resolved_value = is_string($expected_value) ? $this->resolver->resolve($expected_value) : $expected_value;

            // Try matching against ID (if expected value is numeric).
            if (is_numeric($resolved_value)) {
                if (parent::compare($actual_id, (int) $resolved_value)) {
                    $has_match = true;
                    break;
                }
            }

            // Try matching against slug (for all operators).
            if (parent::compare($actual_slug, $resolved_value)) {
                $has_match = true;
                break;
            }

            // Try matching against name (for string-based operators).
            if (in_array($this->operator, array( '=', 'EQUALS', 'IN', 'LIKE', 'REGEXP' ), true)) {
                if (parent::compare($actual_name, $resolved_value)) {
                    $has_match = true;
                    break;
                }
            }
        }

        // Apply operator logic.
        switch ($this->operator) {
            case '!=':
            case 'IS NOT':
            case 'NOT IN':
            case 'NOT LIKE':
                return ! $has_match;

            default:
                return $has_match;
        }
    }
}
