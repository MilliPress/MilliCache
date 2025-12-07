<?php

/**
 * Template Condition
 *
 * Checks the current page template.
 *
 * @package     MilliRules
 * @author      Philipp Wellmer
 */

namespace MilliCache\Deps\MilliRules\Packages\WordPress\Conditions;

use MilliCache\Deps\MilliRules\Conditions\BaseCondition;
use MilliCache\Deps\MilliRules\Context;

/**
 * Class Template
 *
 * Checks the current page template slug/filename.
 * For posts/pages that use custom templates, returns the template filename.
 * Returns empty string if no custom template is used or if the function is unavailable.
 *
 * Supported operators:
 * - =: Exact template match (default)
 * - !=: Template doesn't match
 * - IN: Check if template is in array
 * - NOT IN: Check if template is not in array
 * - LIKE: Pattern matching with wildcards (* and ?)
 * - REGEXP: Regular expression matching
 *
 * Examples:
 * Array syntax:
 * - Check template: ['type' => 'template', 'value' => 'page-full-width.php']
 * - Multiple templates: ['type' => 'template', 'value' => ['template-a.php', 'template-b.php'], 'operator' => 'IN']
 *
 * Builder syntax:
 * - ->template('page-full-width.php') // exact match
 * - ->template(['template-a.php', 'template-b.php'], 'IN') // multiple templates
 *
 * @since 0.1.0
 */
class Template extends BaseCondition
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
        return 'template';
    }

    /**
     * Get the actual value from context.
     *
     * @since 0.1.0
     *
     * @param Context $context The execution context.
     * @return string The current template slug/filename.
     */
    protected function get_actual_value(Context $context): string
    {
        if (function_exists('get_page_template_slug')) {
            $template = get_page_template_slug();
            return is_string($template) ? $template : '';
        }

        return '';
    }
}
