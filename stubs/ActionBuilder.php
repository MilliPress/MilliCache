<?php
/**
 * MilliRules ActionBuilder stubs for MilliCache actions
 *
 * This file provides type hints for MilliCache-specific actions
 * registered with MilliRules ActionBuilder.
 *
 * @package MilliCache
 */

namespace MilliCache\Deps\MilliRules\Builders;

/**
 * ActionBuilder with MilliCache action stubs
 *
 * @method ActionBuilder add_flag(string $flag) Add a cache flag
 * @method ActionBuilder remove_flag(string $flag) Remove a cache flag
 * @method ActionBuilder do_cache(bool $do_cache, string $reason = '') Allow page to be cached
 * @method ActionBuilder set_ttl(int $ttl) Set cache TTL in seconds
 * @method ActionBuilder set_grace(int $grace) Set the cache grace period in seconds
 * @method ActionBuilder flush_by_flag(string|array $flags) Flush cache by flag(s)
 * @method ActionBuilder flush_by_site(int|array|null $site_ids = null) Flush cache by site ID(s)
 *
 * Finalization:
 * @method ActionBuilder register() Register and activate the rule on the specified hook
 */
class ActionBuilder {}
