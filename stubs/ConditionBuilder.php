<?php
/**
 * MilliRules ConditionBuilder stubs for WordPress conditions
 *
 * This file provides type hints for WordPress-specific conditions
 * registered with MilliRules ConditionBuilder.
 *
 * @package MilliCache
 */

namespace MilliCache\Deps\MilliRules\Builders;

/**
 * ConditionBuilder with WordPress condition stubs
 *
 * Core Conditions:
 * @method ConditionBuilder constant(string $name, mixed $value = null, string $operator = '==') Check constant value
 * @method ConditionBuilder custom(string $id, callable $callback) Execute custom condition callback
 * @method ConditionBuilder request_method(string|array $methods) Check HTTP request method
 * @method ConditionBuilder request_url(string $pattern, string $operator = 'matches') Check request URL
 * @method ConditionBuilder cookie(string $name, mixed $value = null, string $operator = '==') Check cookie value
 *
 * WordPress Conditional Tags:
 * @method ConditionBuilder is_singular(string|array|null $post_types = null) Check if viewing a single post
 * @method ConditionBuilder is_front_page(bool|null $value = null) Check if on the front page
 * @method ConditionBuilder is_home(bool|null $value = null) Check if on the blog homepage
 * @method ConditionBuilder is_post_type_archive(string|array|null $post_types = null) Check if on a post type archive
 * @method ConditionBuilder is_category(int|string|array|null $category = null) Check if on a category archive
 * @method ConditionBuilder is_tag(int|string|array|null $tag = null) Check if on a tag archive
 * @method ConditionBuilder is_tax(string|array|null $taxonomy = null, int|string|array|null $term = null) Check if on a taxonomy archive
 * @method ConditionBuilder is_author(int|string|array|null $author = null) Check if on an author archive
 * @method ConditionBuilder is_date() Check if on a date archive
 * @method ConditionBuilder is_feed(string|array|null $feeds = null) Check if viewing a feed
 * @method ConditionBuilder is_user_logged_in(bool|null $value = null) Check if user is logged in
 *
 * Then/Finalization:
 * @method ActionBuilder then() Proceed to the action builder
 */
class ConditionBuilder {}
