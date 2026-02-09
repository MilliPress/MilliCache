---
title: 'PHP & REST API Reference'
post_excerpt: 'MilliCache developer API: PHP functions for cache clearing, flag management, and configuration. REST endpoints at /millicache/v1/ for status, clearing, and settings.'
menu_order: 40
---

# API Reference

This reference documents all public PHP functions and REST API endpoints.

## PHP Functions

All functions are defined in `functions.php` and are available when the MilliCache Engine loads, even before WordPress.

### Cache Clearing Functions

#### millicache_reset_cache()

Clear all cache entries.

```php
millicache_reset_cache( bool $expire = false ): bool
```

**Parameters:**
- `$expire` - If true, expire entries (serve stale during regen) instead of delete

**Returns:** `bool` - Success status

**Example:**
```php
// Delete all cache immediately
millicache_reset_cache();

// Expire all cache (serve stale while regenerating)
millicache_reset_cache( true );
```

---

#### millicache_clear_cache()

Clear cache by mixed targets.

```php
millicache_clear_cache( string|array $targets, bool $expire = false ): bool
```

**Parameters:**
- `$targets` - Flag(s), URL(s), or post ID(s) as string or array
- `$expire` - Expire instead of delete

**Returns:** `bool` - Success status

**Example:**
```php
// Clear by single flag
millicache_clear_cache( 'home' );

// Clear by multiple targets
millicache_clear_cache( [ 'post:123', 'home', 'archive:post' ] );

// Clear by URL
millicache_clear_cache( 'https://example.com/about/' );
```

---

#### millicache_clear_cache_by_urls()

Clear cache by URLs.

```php
millicache_clear_cache_by_urls( array $urls, bool $expire = false ): bool
```

**Parameters:**
- `$urls` - Array of URLs to clear
- `$expire` - Expire instead of delete

**Returns:** `bool` - Success status

**Example:**
```php
millicache_clear_cache_by_urls( [
    'https://example.com/',
    'https://example.com/about/',
    'https://example.com/contact/',
] );
```

---

#### millicache_clear_cache_by_post_ids()

Clear cache by post IDs.

```php
millicache_clear_cache_by_post_ids( array $post_ids, bool $expire = false ): bool
```

**Parameters:**
- `$post_ids` - Array of post IDs
- `$expire` - Expire instead of delete

**Returns:** `bool` - Success status

**Example:**
```php
millicache_clear_cache_by_post_ids( [ 1, 2, 3 ] );

// With expiration
millicache_clear_cache_by_post_ids( [ 123 ], true );
```

---

#### millicache_clear_cache_by_flags()

Clear cache by flags.

```php
millicache_clear_cache_by_flags(
    array $flags,
    bool $expire = false,
    bool $add_prefix = false
): bool
```

**Parameters:**
- `$flags` - Array of cache flags
- `$expire` - Expire instead of delete
- `$add_prefix` - Add site/network prefix (multisite)

**Returns:** `bool` - Success status

**Example:**
```php
// Clear by flags
millicache_clear_cache_by_flags( [ 'home', 'archive:post' ] );

// With site prefix (multisite)
millicache_clear_cache_by_flags( [ 'home' ], false, true );
```

---

#### millicache_clear_cache_by_site_ids()

Clear cache by site IDs (multisite).

```php
millicache_clear_cache_by_site_ids(
    array $site_ids,
    int $network_id = null,
    bool $expire = false
): bool
```

**Parameters:**
- `$site_ids` - Array of site IDs
- `$network_id` - Specific network ID (optional)
- `$expire` - Expire instead of delete

**Returns:** `bool` - Success status

**Example:**
```php
// Clear specific sites
millicache_clear_cache_by_site_ids( [ 1, 2, 3 ] );

// Clear sites in specific network
millicache_clear_cache_by_site_ids( [ 1, 2 ], 1 );
```

---

#### millicache_clear_cache_by_network_id()

Clear cache for entire network (multisite).

```php
millicache_clear_cache_by_network_id( int $network_id, bool $expire = false ): bool
```

**Parameters:**
- `$network_id` - Network ID to clear
- `$expire` - Expire instead of delete

**Returns:** `bool` - Success status

**Example:**
```php
millicache_clear_cache_by_network_id( 1 );
```

---

### Flag Management Functions

#### millicache_add_flag()

Add a flag to the current request.

```php
millicache_add_flag( string $flag ): void
```

**Parameters:**
- `$flag` - Flag to add

**Example:**
```php
// In theme or plugin
if ( is_product() ) {
    millicache_add_flag( 'woo:product' );
    millicache_add_flag( 'woo:product:' . get_the_ID() );
}
```

---

#### millicache_remove_flag()

Remove a flag from the current request.

```php
millicache_remove_flag( string $flag ): void
```

**Parameters:**
- `$flag` - Flag to remove

**Example:**
```php
// Remove home flag from the custom homepage
if ( is_front_page() && get_option( 'custom_homepage' ) ) {
    millicache_remove_flag( 'home' );
}
```

---

#### millicache_prefix_flags()

Add prefix to multiple flags.

```php
millicache_prefix_flags(
    array $flags,
    int $site_id = null,
    int $network_id = null
): array
```

**Parameters:**
- `$flags` - Array of flags to prefix
- `$site_id` - Site ID
- `$network_id` - Network ID

**Returns:** `array` - Prefixed flags

**Example:**
```php
$flags = [ 'post:123', 'home' ];
$prefixed = millicache_prefix_flags( $flags, 2 );
// Result: [ '2:post:123', '2:home' ]
```

---

### Cache Configuration Functions

#### millicache_set_ttl()

Override TTL for current request.

```php
millicache_set_ttl( int $ttl ): void
```

**Parameters:**
- `$ttl` - Time-to-live in seconds

**Example:**
```php
// Short TTL for dynamic page
if ( is_page( 'live-scores' ) ) {
    millicache_set_ttl( 60 );  // 1 minute
}
```

---

#### millicache_set_grace()

Override grace period for current request.

```php
millicache_set_grace( int $grace ): void
```

**Parameters:**
- `$grace` - Grace period in seconds

**Example:**
```php
// Long grace for important pages
if ( is_front_page() ) {
    millicache_set_grace( 86400 * 7 );  // 7 days
}
```

---

## REST API

All endpoints require authentication via nonce (`X-WP-Nonce` header).

### GET /wp-json/millicache/v1/status

Get plugin and cache status.

**Capability Required:** `manage_options`

**Parameters:**
- `network` (optional) - Set to "true" for network stats in multisite

**Response:**
```json
{
    "plugin_name": "millicache",
    "version": "1.0.0",
    "cache": {
        "entries": 142,
        "size": 2856432,
        "size_h": "2.7 MB"
    },
    "storage": {
        "connected": true,
        "version": "7.2.4",
        "memory": {
            "used": 12500000,
            "max": 268435456
        }
    },
    "dropin": {
        "status": "symlink",
        "outdated": false
    },
    "settings": {
        "has_defaults": false,
        "has_backup": true
    }
}
```

---

### POST /wp-json/millicache/v1/cache

Perform cache actions.

**Capability Required:** Filter `millicache_clear_cache_capability` (default: `publish_pages`)

**Actions:**

#### clear

Clear all cache.

```json
{
    "action": "clear",
    "is_network_admin": false
}
```

#### clear_current

Clear current page by flags.

```json
{
    "action": "clear_current",
    "request_flags": ["post:123", "home"]
}
```

#### clear_targets

Clear by mixed targets.

```json
{
    "action": "clear_targets",
    "targets": ["post:123", "https://example.com/page/"]
}
```

**Response:**
```json
{
    "success": true,
    "message": "The site cache has been cleared.",
    "action": "clear",
    "timestamp": 1699900000
}
```

---

### POST /wp-json/millicache/v1/settings

Perform settings actions.

**Capability Required:** `manage_options`

**Actions:**

#### reset

Reset settings to defaults.

```json
{
    "action": "reset"
}
```

#### restore

Restore from backup.

```json
{
    "action": "restore"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Settings reset successfully.",
    "action": "reset",
    "timestamp": 1699900000
}
```

---

## Code Examples

### Complete Cache Integration

```php
<?php
/**
 * Custom cache integration for a membership site.
 */

// Add custom flags for membership content
add_filter( 'millicache_flags_for_request', function( $flags ) {
    if ( is_singular() && has_membership_content() ) {
        $flags[] = 'membership:content';
        $flags[] = 'membership:level:' . get_membership_level();
    }
    return $flags;
} );

// Clear membership content when the level changes
add_action( 'membership_level_changed', function( $user_id, $new_level ) {
    millicache_clear_cache_by_flags( [
        'membership:level:' . $new_level
    ] );
}, 10, 2 );

// Short TTL for dynamic membership pages
add_action( 'template_redirect', function() {
    if ( is_page( 'member-dashboard' ) ) {
        millicache_set_ttl( 300 );  // 5 minutes
    }
} );

// Clear cache via REST API
add_action( 'rest_api_init', function() {
    register_rest_route( 'my-plugin/v1', '/clear-membership-cache', [
        'methods'  => 'POST',
        'callback' => function() {
            millicache_clear_cache_by_flags( [ 'membership:*' ] );
            return [ 'success' => true ];
        },
        'permission_callback' => function() {
            return current_user_can( 'manage_options' );
        },
    ] );
} );
```

### Bulk Operations

```php
<?php
/**
 * Bulk cache operations.
 */
function clear_category_cache( $category_id ) {
    $flags = [
        "archive:category:{$category_id}",
        'archive:post',
        'home',
    ];

    // Get posts in category
    $posts = get_posts( [
        'category'       => $category_id,
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ] );

    foreach ( $posts as $post_id ) {
        $flags[] = "post:{$post_id}";
    }

    millicache_clear_cache_by_flags( array_unique( $flags ) );
}
```

## Next Steps

- [Hooks & Filters](30-hooks-filters.md) - Extension points
- [Architecture](10-architecture.md) - Internal structure
- [Custom Rules](20-custom-rules.md) - Rules engine
