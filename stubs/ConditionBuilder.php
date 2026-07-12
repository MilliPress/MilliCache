<?php
/**
 * MilliRules ConditionBuilder stubs for WordPress conditions
 *
 * This file provides type hints for WordPress-specific conditions
 * registered with MilliRules ConditionBuilder.
 *
 * @package MilliCache
 */

namespace MilliRules\Builders;

/**
 * ConditionBuilder with WordPress condition stubs
 *
 * Core Conditions:
 * @method ConditionBuilder constant(string $name, mixed $value = null, string $operator = '==') Check constant value
 * @method ConditionBuilder custom(string $id, callable $callback) Execute custom condition callback
 * @method ConditionBuilder request_method(string|array<string> $methods) Check HTTP request method
 * @method ConditionBuilder request_url(string $pattern, string $operator = 'matches') Check request URL
 * @method ConditionBuilder cookie(string $name, mixed $value = null, string $operator = '==') Check cookie value
 *
 * WordPress Conditional Tags:
 * @method ConditionBuilder is_singular(string|array<string>|null $post_types = null) Check if viewing a single post
 * @method ConditionBuilder is_front_page(bool|null $value = null) Check if on the front page
 * @method ConditionBuilder is_home(bool|null $value = null) Check if on the blog homepage
 * @method ConditionBuilder is_post_type_archive(string|array<string>|null $post_types = null) Check if on a post type archive
 * @method ConditionBuilder is_category(int|string|array<int|string>|null $category = null) Check if on a category archive
 * @method ConditionBuilder is_tag(int|string|array<int|string>|null $tag = null) Check if on a tag archive
 * @method ConditionBuilder is_tax(string|array<string>|null $taxonomy = null, int|string|array<int|string>|null $term = null) Check if on a taxonomy archive
 * @method ConditionBuilder is_author(int|string|array<int|string>|null $author = null) Check if on an author archive
 * @method ConditionBuilder is_date() Check if on a date archive
 * @method ConditionBuilder is_feed(string|array<string>|null $feeds = null) Check if viewing a feed
 * @method ConditionBuilder is_search(bool|null $value = null) Check if on a search results page
 * @method ConditionBuilder is_user_logged_in(bool|null $value = null) Check if user is logged in
 *
 * Condition Groups (chained after and()):
 * @method ConditionBuilder when(array<string, mixed>[]|null $conditions = null) Start a new condition group (alias of when_all)
 * @method ConditionBuilder when_all(array<string, mixed>[]|null $conditions = null) New condition group, all must match
 * @method ConditionBuilder when_any(array<string, mixed>[]|null $conditions = null) New condition group, any may match
 * @method ConditionBuilder when_none(array<string, mixed>[]|null $conditions = null) New condition group, none may match
 *
 * Delegation to Rules:
 * @method ConditionBuilder and() Close this condition group and start chaining the next
 *
 * Then/Finalization:
 * @method ActionBuilder then() Proceed to the action builder
 */
class ConditionBuilder {}
