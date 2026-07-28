---
title: 'Action Hooks & Filters'
post_excerpt: 'Complete reference of all MilliCache WordPress hooks and filters: cache storage, clearing events, REST API, settings, flag assignment, and capability checks.'
menu_order: 30
---

# Hooks & Filters

This is a complete reference of all hooks and filters available in MilliCache.

## Action Hooks

### Cache Storage Events

#### millicache_entry_storing

Fires before a cache entry is stored.

```php
add_action( 'millicache_entry_storing', function( $hash, $key, $flags, $data ) {
    // $hash  - Cache hash
    // $key   - Cache key
    // $flags - Array of flags
    // $data  - Cache data array

    error_log( "Storing cache: $key with " . count( $flags ) . " flags" );
}, 10, 4 );
```

#### millicache_entry_stored

Fires after a cache entry is stored.

```php
add_action( 'millicache_entry_stored', function( $hash, $key, $flags, $data ) {
    // Notify external CDN
    cdn_notify_cached( $key );
}, 10, 4 );
```

#### millicache_entry_deleting

Fires before a cache entry is deleted.

```php
add_action( 'millicache_entry_deleting', function( $hash, $key, $flags, $url ) {
    error_log( "Deleting cache: $url" );
}, 10, 4 );
```

#### millicache_entry_deleted

Fires after a cache entry is deleted. The `$url` argument lets a listener mirror
the eviction elsewhere (for example, purging a CDN/edge cache by URL) without
having to resolve the hash back to a request.

```php
add_action( 'millicache_entry_deleted', function( $hash, $key, $flags, $url ) {
    // $hash  - Cache hash
    // $key   - Cache key
    // $flags - Array of canonical flags (e.g. "2:post:123"), as emitted by
    //          millicache_cache_cleared_by_flags
    // $url   - The original request URL of the deleted entry

    cdn_purge_url( $url );
}, 10, 4 );
```

#### millicache_entry_expired

Fires after a cache entry is expired (aged out) by flag, e.g. via
`millicache()->clear()->flags( ..., $expire = true )`. Unlike deletion, the
entry and its flag membership are preserved; only its freshness is reset, so
the origin regenerates the response on the next request. Edge/CDN mirrors
should treat this as a purge signal too, since the cached body is now stale.

The payload matches `millicache_entry_deleted` exactly: `$hash`, `$key`,
canonical `$flags`, and `$url`.

```php
add_action( 'millicache_entry_expired', function( $hash, $key, $flags, $url ) {
    cdn_purge_url( $url );
}, 10, 4 );
```

---

### Cache Clearing Events

#### millicache_delete_flags / millicache_expire_flags

Fire when the clearing queue executes — once per request, with the complete,
deduplicated set of canonical flags collected from **every** clear, no matter
the trigger. The `millicache_cache_cleared_by_*` actions below are
trigger-scoped and carry the caller's vocabulary (post IDs, URLs, raw flags);
these two are the content-level counterpart: the final list as it goes to
storage — site-prefixed on multisite (`2:post:123`), raw patterns kept raw
(`*:test`, `5:*`). They fire immediately before the entries are removed;
per-entry outcomes are reported by `millicache_entry_deleted` /
`millicache_entry_expired`.

`millicache_delete_flags` carries the flags being deleted,
`millicache_expire_flags` the ones being expired (soft-cleared). Hook both
with the same callback when the distinction doesn't matter.

These are the hooks to use when you mirror MilliCache's invalidation
elsewhere (edge caches, external systems) and need the complete, canonical
picture from a single listener.

```php
$purge = function( $flags ) {
    // $flags - The complete batch of canonical flags being cleared.
    external_cache_purge_by_tags( $flags );
};

add_action( 'millicache_delete_flags', $purge );
add_action( 'millicache_expire_flags', $purge );
```

#### millicache_cache_cleared_by_urls

Fires after cache is cleared by URLs.

```php
add_action( 'millicache_cache_cleared_by_urls', function( $urls, $expire ) {
    // $urls   - Array of cleared URLs
    // $expire - Whether expire mode was used

    foreach ( $urls as $url ) {
        error_log( "Cleared cache for URL: $url" );
    }
}, 10, 2 );
```

#### millicache_cache_cleared_by_posts

Fires after cache is cleared by post IDs — both explicit clears (`clear()->posts()`,
WP-CLI `--id`) and the automatic invalidation that runs when a post is published,
updated, unpublished, or deleted. The related-content flags cleared alongside a
post (archives, author, taxonomies, dates) are reported separately by
`millicache_cache_cleared_by_flags`.

```php
add_action( 'millicache_cache_cleared_by_posts', function( $post_ids, $expire ) {
    // $post_ids - Array of post IDs
    // $expire   - Whether expire mode was used

    foreach ( $post_ids as $post_id ) {
        error_log( "Cleared cache for post: $post_id" );
    }
}, 10, 2 );
```

#### millicache_cache_cleared_by_flags

Fires after cache is cleared explicitly by flags (`clear()->flags()`, WP-CLI
`--flag`, the settings-page target clears). Carries the flags as the caller
passed them — normalized but un-prefixed. For the canonical, complete flag
batch of every clear, listen to `millicache_delete_flags` /
`millicache_expire_flags` instead.

```php
add_action( 'millicache_cache_cleared_by_flags', function( $flags, $expire ) {
    // $flags  - Array of flags as requested by the caller
    // $expire - Whether expire mode was used

    error_log( 'Flag clear requested: ' . implode( ', ', $flags ) );
}, 10, 2 );
```

#### millicache_cache_cleared_by_sites

Fires after cache is cleared by site IDs (multisite).

```php
add_action( 'millicache_cache_cleared_by_sites', function( $site_ids, $network_id, $expire ) {
    // $site_ids   - Array of site IDs
    // $network_id - Network ID (if specified)
    // $expire     - Whether expire mode was used
}, 10, 3 );
```

#### millicache_cache_cleared_by_networks

Fires after cache is cleared by network IDs.

```php
add_action( 'millicache_cache_cleared_by_networks', function( $network_ids, $expire ) {
    // $network_ids - Array of network IDs
    // $expire      - Whether expire mode was used
}, 10, 2 );
```

#### millicache_cache_cleared

Fires after any cache clearing operation.

```php
add_action( 'millicache_cache_cleared', function( $expire ) {
    // $expire - Whether expire mode was used

    // Log clearing event
    error_log( 'MilliCache cleared at ' . current_time( 'mysql' ) );

    // Clear external caches
    if ( function_exists( 'wp_cache_flush' ) ) {
        wp_cache_flush();
    }
} );
```

---

### REST API Events

#### millicache_rest_cache_action_performed

Fires after a REST API cache action is performed.

```php
add_action( 'millicache_rest_cache_action_performed', function( $action, $params, $request ) {
    // $action  - Action performed (clear, clear_current, clear_targets)
    // $params  - Action parameters
    // $request - WP_REST_Request object

    // Audit log
    audit_log( "Cache action: $action by user " . get_current_user_id() );
}, 10, 3 );
```

#### millicache_rest_settings_action_performed

Fires after a REST API settings action is performed.

```php
add_action( 'millicache_rest_settings_action_performed', function( $action, $params, $request ) {
    // $action - Action performed (reset, restore)

    audit_log( "Settings action: $action" );
}, 10, 3 );
```

---

## Filter Hooks

### Settings Filters

#### millicache_settings_defaults

Filter default settings.

```php
add_filter( 'millicache_settings_defaults', function( $defaults ) {
    // Modify defaults
    $defaults['cache']['ttl'] = 3600;  // 1 hour default

    return $defaults;
} );
```

---

### Cache Entry Filters

#### millicache_entry_headers

Filter the response headers stored with a cache entry. Runs on every store: a fresh cache miss and a stale-while-revalidate background regeneration alike. Because it runs on regeneration too, headers derived from the entry's flags (such as edge cache tags or `Cache-Control: s-maxage`) always match the flags being persisted and cannot drift.

```php
add_filter( 'millicache_entry_headers', function( $headers, $flags, $context ) {
    // Leave private (per-cookie/variant) entries off shared caches.
    if ( null !== $context['variant'] ) {
        return $headers;
    }

    // Replace it, don't append: strip the header we own before re-adding it.
    $headers = array_values( array_filter(
        $headers,
        fn( $h ) => stripos( $h, 'Cache-Tag:' ) !== 0
    ) );

    $headers[] = 'Cache-Tag: ' . implode( ',', $flags );

    return $headers;
}, 10, 3 );
```

**Parameters:**

- `$headers` (`string[]`) — Response headers as `"Key: Value"` strings, already scrubbed of MilliCache's own and per-hop headers.
- `$flags` (`string[]`) — Canonical flags persisted with the entry, in the same form Redis stores minus the `<storage_prefix>:f:` key prefix. On multisite these carry the site/network prefix (e.g. `2:post:9`); on single-site they are unprefixed (e.g. `post:9`).
- `$context` (`array`) — Store-time context: `url` (string), `variant` (array|null; non-null marks a private entry), `status` (int), `ttl` (int seconds), `grace` (int seconds).

> [!IMPORTANT]
> Listeners must be replace-not-append: strip any header they own before re-adding it, so the filter stays idempotent across the miss and regeneration passes.

---

### Cache Clearing Filters

#### millicache_flags_related_to_post

Filter flags that should be cleared when a post is updated.

```php
add_filter( 'millicache_flags_related_to_post', function( $flags, $post ) {
    // Add custom flags based on post content
    if ( has_block( 'myblock/featured', $post ) ) {
        $flags[] = 'featured-content';
    }

    // Add taxonomy-based flags
    $categories = get_the_category( $post->ID );
    foreach ( $categories as $cat ) {
        $flags[] = 'category:' . $cat->term_id;
    }

    return $flags;
}, 10, 2 );
```

#### millicache_settings_clear_site_hooks

Filter hooks that trigger full site cache clearing.

```php
add_filter( 'millicache_settings_clear_site_hooks', function( $hooks ) {
    // Add a custom hook that should clear the cache
    $hooks[] = 'my_custom_global_update';

    // Remove a default hook
    $key = array_search( 'switch_theme', $hooks );
    if ( $key !== false ) {
        unset( $hooks[ $key ] );
    }

    return $hooks;
} );
```

**Default hooks:**
- `save_post_wp_template_part`
- `customize_save_after`
- `wp_update_nav_menu`
- `switch_theme`
- `update_option_permalink_structure`
- `update_option_active_plugins`

#### millicache_settings_clear_site_options

Filter options that trigger full site cache clearing when updated.

```php
add_filter( 'millicache_settings_clear_site_options', function( $options ) {
    // Add a custom option
    $options[] = 'my_global_setting';

    return $options;
} );
```

---

### Bucket Extension

Buckets are short canonical tokens folded into the cache key to differentiate per-request signals. MilliCache does **not** expose a runtime hook filter for buckets — `advanced-cache.php` runs before any plugin or mu-plugin loads, so a `add_filter()` callback registered on `plugins_loaded` would never run during cache lookup. The lookup hash and the write hash would diverge, producing a permanent cache miss.

Buckets are extended through two paths whose timing matches MilliCache's boot order:

#### Static bucket configuration

Lookup tables for resolvers go in the [`MC_CACHE_BUCKETS`](../02-configuration/02-reference.md#mc_cache_buckets) constant. Read during Config construction, available when the cache key is generated.

```php
define( 'MC_CACHE_BUCKETS', [
    'tenant' => [ 'acme' => 'acme', 'globex' => 'glx' ],
    'ab'     => [ 'control' => 'a', 'variant' => 'b' ],
] );
```

Defining a lookup table doesn't bucket anything by itself; a *resolver* still has to read the table and call `add_bucket()`.

#### Runtime bucket extension via the rules engine

For per-request bucket resolution that depends on conditions (URL match, header presence, cookie value, etc.), use the **rules engine**. Rules are evaluated during the early PHP phase, in time to influence the request hash.

The `set_bucket` PHP-phase action calls into:

```php
\MilliCache\Engine\Request\Bucket\Resolver::add_bucket( string $name, string $token ): void
```

`add_bucket()` is the programmatic extension point. Programmatic additions take precedence over built-in resolutions when names collide.

Example: bucket A/B test arms from a cookie:

```
Condition: cookie ab_arm matches /^[ab]$/
Action:    set_bucket name="ab" token="{cookie:ab_arm}"
```

The rule fires per request; the action calls `$resolver->add_bucket('ab', $cookie_value)`. Cache entries for arm A and arm B stay distinct, but if the rendered HTML happens to be byte-identical they automatically share storage via the content-addressable output keyspace.

> [!NOTE]
> Regular WordPress plugins cannot extend buckets at the cache-lookup phase because they load after `advanced-cache.php`. To influence cache differentiation, ship a settings update (extending `MC_CACHE_BUCKETS`) or register rules.

---

### Request Flags Filters

#### millicache_flags_for_request

Filter flags assigned to the current request.

```php
add_filter( 'millicache_flags_for_request', function( $flags ) {
    // Add flags based on content
    if ( has_block( 'myblock/weather' ) ) {
        $flags[] = 'block:weather';
    }

    // Add flags for WooCommerce
    if ( function_exists( 'is_shop' ) && is_shop() ) {
        $flags[] = 'woo:shop';
    }

    // Add a template-based flag
    if ( is_page_template( 'templates/landing.php' ) ) {
        $flags[] = 'template:landing';
    }

    return $flags;
} );
```

---

### Capability Filters

#### millicache_clear_cache_capability

Filter the capability required to clear cache.

```php
add_filter( 'millicache_clear_cache_capability', function( $capability ) {
    // Require higher capability
    return 'manage_options';  // Only administrators

    // Or lower for specific sites
    // return 'edit_posts';  // Editors can clear
} );
```

**Default:** `publish_pages`

---

### REST API Filters

#### millicache_rest_cache_allowed_actions

Filter allowed cache actions via REST API.

```php
add_filter( 'millicache_rest_cache_allowed_actions', function( $actions ) {
    // Add custom action
    $actions[] = 'my_custom_action';

    // Remove an action
    $key = array_search( 'clear', $actions );
    if ( $key !== false ) {
        unset( $actions[ $key ] );
    }

    return $actions;
} );
```

**Default:** `['clear', 'clear_current', 'clear_targets']`

#### millicache_rest_settings_allowed_actions

Filter allowed settings actions via REST API.

```php
add_filter( 'millicache_rest_settings_allowed_actions', function( $actions ) {
    // Remove restore action
    $key = array_search( 'restore', $actions );
    if ( $key !== false ) {
        unset( $actions[ $key ] );
    }

    return $actions;
} );
```

**Default:** `['reset', 'restore']`

#### millicache_rest_status_response

Filter the REST API status response.

```php
add_filter( 'millicache_rest_status_response', function( $status ) {
    // Add custom data
    $status['custom_metric'] = get_custom_cache_metric();

    // Remove sensitive data
    unset( $status['storage']['password'] );

    return $status;
} );
```

---

### Update Filters

#### millicache_updates

Filter whether MilliCache checks MilliPress.com for plugin updates. Return
`false` to disable update checks entirely, which stops the remote request and
hides the update notice on the Plugins screen.

The filter is evaluated at update-check time (when WordPress refreshes the
`update_plugins` transient), so it can be added from a theme's `functions.php`
or another plugin and still be honored.

```php
add_filter( 'millicache_updates', '__return_false' );
```

**Default:** `true`

---

## Hook Usage Examples

### Notify CDN on Cache Clear

```php
add_action( 'millicache_cache_cleared_by_flags', function( $flags, $expire ) {
    // Convert MilliCache flags to CDN tags
    $cdn_tags = array_map( function( $flag ) {
        return 'millicache-' . sanitize_title( $flag );
    }, $flags );

    // Purge CDN
    cdn_purge_by_tags( $cdn_tags );
}, 10, 2 );
```

### Audit Logging

```php
add_action( 'millicache_cache_cleared', function( $expire ) {
    $user = wp_get_current_user();
    $method = $expire ? 'expired' : 'deleted';

    log_audit_event( 'cache_cleared', [
        'user_id'   => $user->ID,
        'user_name' => $user->user_login,
        'method'    => $method,
        'timestamp' => current_time( 'mysql' ),
    ] );
} );
```

### Custom Flags for ACF

```php
add_filter( 'millicache_flags_for_request', function( $flags ) {
    if ( ! function_exists( 'get_field' ) ) {
        return $flags;
    }

    // Add a flag for pages with a specific ACF field
    if ( is_singular() && get_field( 'enable_dynamic_content' ) ) {
        $flags[] = 'acf:dynamic';
    }

    return $flags;
} );

// Clear when the ACF field changes
add_action( 'acf/save_post', function( $post_id ) {
    if ( get_field( 'enable_dynamic_content', $post_id ) ) {
        millicache_clear_cache_by_flags( [ 'acf:dynamic' ] );
    }
} );
```

### Restrict Cache Clearing by Role

```php
add_filter( 'millicache_clear_cache_capability', function( $capability ) {
    // Only allow on staging/development
    if ( defined( 'WP_ENV' ) && WP_ENV === 'production' ) {
        return 'manage_options';  // Admins only in production
    }

    return 'edit_posts';  // Editors can clear on staging
} );
```

## Next Steps

- [API Reference](03-api-reference.md) - Function documentation
- [Architecture](01-architecture.md) - Internal structure
- [Rules Introduction](../04-rules/01-introduction.md) - Extend the rules engine
