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
add_action( 'millicache_entry_deleting', function( $hash, $key, $flags ) {
    error_log( "Deleting cache: $key" );
}, 10, 3 );
```

#### millicache_entry_deleted

Fires after a cache entry is deleted.

```php
add_action( 'millicache_entry_deleted', function( $hash, $key, $flags ) {
    // Notify external systems
    cdn_notify_purged( $key );
}, 10, 3 );
```

---

### Cache Clearing Events

#### millicache_cache_cleared_by_posts

Fires after cache is cleared by post IDs.

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

Fires after cache is cleared by flags.

```php
add_action( 'millicache_cache_cleared_by_flags', function( $flags, $expire ) {
    // $flags  - Array of cleared flags
    // $expire - Whether expire mode was used

    // Notify external cache
    external_cache_purge_by_tags( $flags );
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

#### millicache_cleared_by_networks

Fires after cache is cleared by network IDs.

```php
add_action( 'millicache_cleared_by_networks', function( $network_ids, $expire ) {
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
        millipress_clear_cache_by_flags( [ 'acf:dynamic' ] );
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

- [API Reference](40-api-reference.md) - Function documentation
- [Custom Rules](20-custom-rules.md) - Extend the rules engine
- [Architecture](10-architecture.md) - Internal structure
