---
title: 'Cache Clearing & Invalidation'
description: 'Clear or purge the WordPress page cache in MilliCache: automatic invalidation on content changes, plus targeted clearing by cache flags, URLs, or post IDs.'
menu_order: 20
---

# Cache Clearing

MilliCache provides multiple methods to clear cached content, from automatic invalidation to targeted manual clearing.

## Automatic Invalidation

MilliCache automatically clears cache when content changes:

### Post Updates

When a post is created, updated, unpublished (trashed, set to draft or private), or deleted:

- The post's URL is cleared
- Related archives are cleared (category, tag, author, date)
- The homepage is cleared
- RSS feeds are cleared

**Hooks triggering post-cache clearing:**
- `clean_post_cache`
- `before_delete_post`
- `transition_post_status`

### Site-Wide Events

These events clear the entire site cache:

- Theme switching (`switch_theme`)
- Menu updates (`wp_update_nav_menu`)
- Widget updates (`widget_update_callback`)
- Customizer saves (`customize_save_after`)
- Template part updates (`save_post_wp_template_part`)
- Permalink structure changes (`update_option_permalink_structure`)
- Active plugins changes (`update_option_active_plugins`)

[MilliCache Pro](https://www.millipress.com/millicache-pro/)'s [Block Editor module](https://www.millipress.com/docs/millicache-pro/02-modules/04-block-editor/) makes several of these full clears surgical: when a synced pattern, Query Loop source, or Site Editor template changes, exactly the pages using it are cleared instead of the whole site.

## Manual Clearing

### Admin Bar

For logged-in users with the `publish_pages` capability, hover the **Cache** button in the admin bar:

- **Clear Current View Cache** clears only the page you are viewing. On post edit screens this becomes **Clear {Post Type} Cache** for the edited post.
- **Clear Website Cache** clears the current site.
- **Clear Network Cache** (network admin only) clears every site and asks for a confirming second click.

Clicking the **Cache** button itself never clears anything: in wp-admin on WordPress 7.0+ it opens the command palette, elsewhere it toggles the submenu.

### Command Palette

In wp-admin on WordPress 7.0 or newer, click the **Cache** admin bar button (or press <kbd>Cmd</kbd>+<kbd>K</kbd> / <kbd>Ctrl</kbd>+<kbd>K</kbd> and search for "MilliCache"):

- **MilliCache: Clear website cache** (or **MilliCache: Clear network cache** in the network admin)
- **MilliCache: Clear cache for `<targets>`**: type flags, post IDs or URLs into the palette search, comma-separated, e.g. `post:123, https://example.com/pricing/`
- **MilliCache: Status & Settings**

In the network admin, typed targets are raw flag patterns cleared across all sites: `*:home` clears every site's home page, `5:*` everything on site 5.

### WP-CLI

```bash
# Clear all cache
wp millicache clear

# Clear specific posts
wp millicache clear --id=1,2,3

# Clear by URIs (paths or full URLs)
wp millicache clear --uri="/page-1/,/page-2/"

# Or with absolute URLs
wp millicache clear --uri="https://example.com/page-1/,https://example.com/page-2/"

# Clear by flags
wp millicache clear --flag="post:123,home,archive:*"

# Clear specific sites (multisite)
wp millicache clear --site=1,2,3

# Clear entire network (multisite)
wp millicache clear --network=1
```

### Expire vs Delete

By default, `wp millicache clear` deletes cache entries immediately. Use `--expire` to mark them as expired instead:

```bash
wp millicache clear --expire
```

**Expire behavior:**
- Entries remain in cache but marked expired
- Next request serves stale content (grace)
- Content regenerates in background
- Visitors never wait for regeneration

**Delete behavior:**
- Entries removed immediately
- Next request generates fresh content
- First visitor waits for generation

> [!TIP]
> Use `--expire` for non-critical updates to maintain performance. Use deletion for urgent content corrections.

## Flag-Based Clearing

MilliCache uses **flags** (tags) to enable targeted cache clearing. Each cached page is tagged with relevant flags.

### Built-in Flags

| Flag Format                    | Applied To        | Example              |
|--------------------------------|-------------------|----------------------|
| `home`                         | Homepage/blog     | `home`               |
| `post:{id}`                    | Single post/page  | `post:123`           |
| `archive:post`                 | Post archive      | `archive:post`       |
| `archive:{post_type}`          | Custom Post Type  | `archive:book`       |
| `archive:{taxonomy}:{term_id}` | Term archive      | `archive:category:5` |
| `archive:author:{id}`          | Author archive    | `archive:author:1`   |
| `archive:{year}`               | Year archive      | `archive:2026`       |
| `archive:{year}:{month}`       | Month archive     | `archive:2026:01`    |
| `feed`                         | RSS/Atom feeds    | `feed`               |

### Multisite Flag Prefixes

In multisite, flags are prefixed with site/network IDs:

| Environment   | Flag Format                       |
|---------------|-----------------------------------|
| Single site   | `post:123`                        |
| Multisite     | `{site_id}:post:123`              |
| Multi-network | `{network_id}:{site_id}:post:123` |

### Clearing by Flags

```bash
# Clear homepage
wp millicache clear --flag="home"

# Clear post and its archives
wp millicache clear --flag="post:123,archive:post,home"

# Clear all category archives
wp millicache clear --flag="archive:category:*"
```

## Programmatic Clearing

### PHP Functions

MilliCache provides helper functions in `functions.php`:

```php
// Clear all cache
millicache_reset_cache();
millicache_reset_cache( true ); // Expire instead of delete

// Clear by post-IDs
millicache_clear_cache_by_post_ids( [ 1, 2, 3 ] );
millicache_clear_cache_by_post_ids( [ 1, 2, 3 ], true ); // Expire

// Clear by URLs
millicache_clear_cache_by_urls( [
    'https://example.com/page-1/',
    'https://example.com/page-2/',
] );

// Clear by flags
millicache_clear_cache_by_flags( [ 'post:123', 'home' ] );
millicache_clear_cache_by_flags( [ 'post:123' ], true ); // Expire
millicache_clear_cache_by_flags( [ 'post:123' ], false, true ); // Add site prefix

// Clear by mixed targets (Post-IDs, Flags & URLs)
millicache_clear_cache( 'post:123' ); // Single flag
millicache_clear_cache( [ 1, 'home', 'https://example.com/page-1/' ] ); // Multiple flags

// Multisite: Clear by site IDs
millicache_clear_cache_by_site_ids( [ 1, 2 ] );
millicache_clear_cache_by_site_ids( [ 1, 2 ], 1 ); // Specific network

// Multisite: Clear by network
millicache_clear_cache_by_network_id( 1 );
```

### Hooks for Custom Clearing

Clear cache in response to custom events:

```php
// Clear cache when a custom option changes
add_action( 'update_option_my_custom_option', function() {
    millicache_reset_cache();
} );

// Clear specific posts when the ACF field updates
add_action( 'acf/save_post', function( $post_id ) {
    millicache_clear_cache_by_post_ids( [ $post_id ] );
} );
```

## Custom Flags

Add custom flags to enable targeted clearing:

```php
// Add a custom flag based on content
add_filter( 'millicache_flags_for_request', function( $flags ) {
    // Add a flag for WooCommerce shop
    if ( function_exists( 'is_shop' ) && is_shop() ) {
        $flags[] = 'woo:shop';
    }

    return $flags;
} );
```

Then clear by your custom flag:

```bash
wp millicache clear --flag="woo:shop"
```

## Clearing Actions (Hooks)

Hook into clearing events:

```php
// After clearing by post-IDs
add_action( 'millicache_cache_cleared_by_posts', function( $post_ids, $expire ) {
    error_log( 'Cleared cache for posts: ' . implode( ', ', $post_ids ) );
}, 10, 2 );

// After clearing by flags
add_action( 'millicache_cache_cleared_by_flags', function( $flags, $expire ) {
    // Notify external CDN
    notify_cdn_purge( $flags );
}, 10, 2 );
```

## Best Practices

### 1. Use Targeted Clearing

Clear only what's necessary:

```php
// Good: Clear specific content
millicache_clear_cache_by_post_ids( [ $post_id ] );

// Avoid: Clear everything
millicache_reset_cache();
```

### 2. Prefer Expire Over Delete

For non-critical updates, expire instead of delete:

```php
millicache_clear_cache_by_post_ids( [ $post_id ], true ); // expire = true
```

After a full clear, the first visitor to each page pays the render cost. With [Cache Preloading](https://www.millipress.com/docs/millicache-pro/02-modules/05-cache-preloading/) in [MilliCache Pro](https://www.millipress.com/millicache-pro/), the cache refills itself from your XML sitemap in the background instead.

### 3. Batch Related Clears

Clear related items together:

```php
// Clear post and its relationships
$flags = [
    "post:{$post_id}",
    'home',
    'archive:post',
];
millicache_clear_cache_by_flags( $flags );
```

### 4. Use Custom Flags for Cross-Cutting Concerns

Add flags for content that should clear together:

```php
// Tag all pages showing a promotion
add_filter( 'millicache_flags_for_request', function( $flags ) {
    if ( is_promotion_active() ) {
        $flags[] = 'promo:summer-sale';
    }
    return $flags;
} );

// Clear all promotion pages when the sale ends
millicache_clear_cache_by_flags( [ 'promo:summer-sale' ] );
```

## Next Steps

- [WP-CLI Commands](../06-wp-cli/01-commands.md) - Complete CLI reference
- [Cache Flags](../03-cache-flags/01-introduction.md) - Understanding flags
- [Hooks & Filters](../07-developers/02-hooks-filters.md) - All available hooks
