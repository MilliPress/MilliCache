---
title: 'Built-in Rules'
post_excerpt: 'Reference for all built-in caching rules in MilliCache, including bootstrap and WordPress phase rules.'
menu_order: 20
---

# Built-in Rules

MilliCache includes default rules that handle common caching scenarios. All built-in rules have negative or zero priority, so your custom rules (`->order({10+})`) run after them and can override their decisions.

## Bootstrap Phase Rules

Execute **before WordPress loads** via `advanced-cache.php`.

### Core Bypass Rules (Order -10)

These run first and bypass caching for non-cacheable scenarios:

| Rule ID                           | Condition                    | Result |
|-----------------------------------|------------------------------|--------|
| `millicache:const:wp-cache`       | `WP_CACHE !== true`          | Bypass |
| `millicache:request:check-method` | Method not GET/HEAD          | Bypass |
| `millicache:request:cli`          | Running in WP-CLI            | Bypass |
| `millicache:request:rest`         | `REST_REQUEST` is true       | Bypass |
| `millicache:request:xmlrpc`       | `XMLRPC_REQUEST` is true     | Bypass |
| `millicache:request:file`         | URL ends with file extension | Bypass |
| `millicache:request:wp-json`      | URL contains `wp-json`       | Bypass |
| `millicache:config:ttl-not-set`   | TTL is 0 or negative         | Bypass |

### Configuration-Based Rules (Order 0)

Apply settings from your configuration:

| Rule ID                             | Condition                 | Result |
|-------------------------------------|---------------------------|--------|
| `millicache:config:nocache-cookies` | Excluded cookie present   | Bypass |
| `millicache:config:nocache-paths`   | URL matches excluded path | Bypass |

**Default excluded cookies:**
- `wp-*pass*` — WordPress password-protected content
- `comment_author_*` — Comment author cookies

## WordPress Phase Rules

Execute **after WordPress loads** on the `template_redirect` hook.

### Context-Based Rules (Hook Priority 20)

| Rule ID                              | Condition                | Result |
|--------------------------------------|--------------------------|--------|
| `millicache:wp:logged-in`            | User is logged in        | Bypass |
| `millicache:wp:response:code`        | HTTP status ≠ 200        | Bypass |
| `millicache:wp:const:donotcachepage` | `DONOTCACHEPAGE` defined | Bypass |
| `millicache:wp:const:doing-cron`     | `DOING_CRON` defined     | Bypass |
| `millicache:wp:const:doing-ajax`     | `DOING_AJAX` defined     | Bypass |

## Rule Priority System

Rules execute in priority order (lower numbers first):

| Priority  | Who Uses It         | Purpose                                 |
|-----------|---------------------|-----------------------------------------|
| -10       | Core bypass rules   | Fundamental checks (methods, CLI, etc.) |
| 0         | Configuration rules | Apply user settings                     |
| 10+       | Your custom rules   | Override or extend defaults             |

### All Rules Run

MilliRules evaluates **all rules** in order — there's no short-circuit behavior. Later rules can override earlier rules' decisions.

```php
// Built-in rule (order -10) sets do_cache(false) for POST requests
// Your rule (order 10) runs AFTER and can override it
Rules::create( 'mysite:cache-post-search', 'php' )
    ->order( 10 )
    ->when()
        ->request_method( 'POST' )
        ->request_url( '/search/*' )
    ->then()
        ->do_cache( true )  // Overrides the earlier bypass
    ->register();
```

This means:
- All your rules **always run** after built-in rules
- Your rules can override built-in decisions (the last `do_cache()` wins)
- To completely replace a built-in rule, use the same rule ID (see below)

## Available Actions

### Bootstrap Phase Actions

Available in `php` rules (before WordPress):

| Action                       | Description            | Example                          |
|------------------------------|------------------------|----------------------------------|
| `do_cache( $bool, $reason )` | Enable/disable caching | `->do_cache( false, 'Preview' )` |
| `set_ttl( $seconds )`        | Override TTL           | `->set_ttl( 3600 )`              |
| `set_grace( $seconds )`      | Override grace period  | `->set_grace( 86400 )`           |

### WordPress Phase Actions

Available in `wp` rules (after WordPress loads):

| Action                       | Description            | Example                        |
|------------------------------|------------------------|--------------------------------|
| `do_cache( $bool, $reason )` | Enable/disable caching | `->do_cache( false, 'Admin' )` |
| `set_ttl( $seconds )`        | Override TTL           | `->set_ttl( 300 )`             |
| `set_grace( $seconds )`      | Override grace period  | `->set_grace( 3600 )`          |
| `add_flag( $flag )`          | Add cache flag         | `->add_flag( 'custom:flag' )`  |
| `remove_flag( $flag )`       | Remove cache flag      | `->remove_flag( 'home' )`      |
| `clear_cache( $targets )`    | Clear cache entries    | `->clear_cache( ['post:*'] )`  |
| `clear_site_cache()`         | Clear entire site      | `->clear_site_cache()`         |

## Available Conditions

### Core Conditions

Available in both `php` (bootstrap) and `wp` (WordPress) phases:

| Condition                    | Description          | Example                               |
|------------------------------|----------------------|---------------------------------------|
| `constant( $name, $value )`  | Check constant value | `->constant( 'WP_DEBUG', true )`      |
| `custom( $id, $callback )`   | Custom callback      | `->custom( 'my-check', fn() => ... )` |
| `request_method( $methods )` | HTTP method          | `->request_method( 'POST' )`          |
| `request_url( $pattern )`    | URL pattern match    | `->request_url( '/shop/*' )`          |
| `cookie( $name, $value )`    | Cookie check         | `->cookie( 'currency', 'EUR' )`       |

### WordPress `is_*` Conditions

All `is_*` methods are available in `wp` phase rules (require WordPress context):

| Condition                        | Description          |
|----------------------------------|----------------------|
| `is_singular( $post_types )`     | Single post/page/CPT |
| `is_front_page()`                | Front page           |
| `is_home()`                      | Blog homepage        |
| `is_post_type_archive( $types )` | Post type archive    |
| `is_category( $category )`       | Category archive     |
| `is_tag( $tag )`                 | Tag archive          |
| `is_tax( $taxonomy, $term )`     | Taxonomy archive     |
| `is_author( $author )`           | Author archive       |
| `is_date()`                      | Date archive         |
| `is_feed( $feeds )`              | Feed page            |
| `is_user_logged_in()`            | User logged in       |

### WordPress `has_*` Conditions

All `has_*` methods are available in `wp` phase rules (require WordPress context):

| Condition                      | Description             |
|--------------------------------|-------------------------|
| `has_term( $term, $taxonomy )` | Post has specific term  |
| `has_block( $block_name )`     | Post contains block     |
| `has_tag( $tag )`              | Post has tag            |
| `has_category( $category )`    | Post has category       |
| `has_post_thumbnail()`         | Post has featured image |
| `has_excerpt()`                | Post has excerpt        |
| `has_post_format( $format )`   | Post has format         |
| `has_nav_menu( $location )`    | Menu location has menu  |
| `has_custom_logo()`            | Site has custom logo    |

## Debugging Rules

### Enable Debug Mode

```php
define( 'MC_CACHE_DEBUG', true );
```

### Check Response Headers

The `X-MilliCache-Status` header shows the caching result:

| Value    | Meaning                          |
|----------|----------------------------------|
| `hit`    | Served from cache                |
| `miss`   | Not cached, will be stored       |
| `bypass` | A rule prevented caching         |
| `grace`  | Serving stale while regenerating |

### List Registered Rules

```php
use MilliCache\Deps\MilliRules\Rules;

// Log all rules
foreach ( Rules::all() as $rule ) {
    error_log( sprintf(
        'Rule: %s (order: %d, phase: %s)',
        $rule['id'],
        $rule['order'],
        $rule['phase']
    ) );
}
```

## Override / Unregister Built-in Rules

To change the behavior of a built-in rule, you can unregister them or create your own with the **same ID**:

```php
add_action('template_redirect', function() {
    // Unregister a built-in rule to completely remove its behavior
    Rules::unregister( 'millicache:wp:const:doing-ajax' );
});

// Override the logged-in user bypass to allow caching for subscribers
Rules::create( 'millicache:wp:logged-in' )  // Same ID as built-in
    ->on( 'template_redirect', 20 )         // Same hook & priority as built-in
    ->order( 10 )                           // Higher order to run after built-in
    ->when()
        ->is_user_logged_in()
        ->custom( 'is-editor-or-higher', function() {
            return current_user_can( 'edit_posts' );
        })
    ->then()
        ->do_cache( false, 'Editor role or above' )
    ->register();
```

Now subscribers can see cached pages, but editors and admins still bypass.

## Next Steps

- [Examples](03-examples.md) — Practical rule examples
- [MilliRules Conditions](https://millipress.com/docs/millirules/05-reference/01-conditions/) — Full conditions reference
- [MilliRules Actions](https://millipress.com/docs/millirules/05-reference/02-actions/) — Full actions reference
