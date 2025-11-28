<?php

/**
 * Term Condition
 *
 * Checks the slug of the current queried term.
 *
 * @package     MilliCache\Deps\MilliRules
 * @author      Philipp Wellmer
 */

namespace MilliCache\Deps\MilliRules\Packages\WordPress\Conditions;

use MilliCache\Deps\MilliRules\Conditions\BaseCondition;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class Term
 *
 * Checks the slug of the current queried term on taxonomy/category/tag archive pages.
 * Returns the term slug (e.g., 'news', 'featured', 'electronics').
 * Returns empty string if not on a term archive.
 *
 * Supported operators:
 * - =: Exact term slug match (default)
 * - !=: Term slug doesn't match
 * - IN: Check if term slug is in array
 * - NOT IN: Check if term slug is not in array
 * - LIKE: Pattern matching with wildcards (* and ?)
 * - REGEXP: Regular expression matching
 *
 * Examples:
 * Array syntax:
 * - Check for term: ['type' => 'term', 'value' => 'featured']
 * - Multiple terms: ['type' => 'term', 'value' => ['news', 'featured'], 'operator' => 'IN']
 *
 * Builder syntax:
 * - ->term('featured') // exact match
 * - ->term(['news', 'featured'], 'IN') // multiple terms
 *
 * @since 0.1.0
 */
class Term extends BaseCondition
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
        return 'term';
    }

    /**
     * Get the actual value from context.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context.
     * @return string The current term slug.
     */
    protected function get_actual_value(Context $context): string
    {
        // Fall back to WordPress functions.
        if (function_exists('get_queried_object')) {
            $term = get_queried_object();
            if ($term && isset($term->slug)) {
                return (string) $term->slug;
            }
        }

        return '';
    }
}
