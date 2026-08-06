---
title: 'Built-in Cache Flags'
description: 'Reference of cache flags MilliCache assigns automatically: home, post IDs, post type, taxonomy, author and date archives, feeds, and multisite prefixes.'
menu_order: 20
---

# Built-in Flags

MilliCache automatically assigns flags based on the type of page being cached. These built-in flags cover common WordPress content types.

## Homepage Flags

| Flag   | Applied When             |
|--------|--------------------------|
| `home` | Front page or blog index |

The `home` flag is added to:
- The site's front page (whether static or showing latest posts)
- The blog posts page (if using a static front page)

## Singular Content Flags

| Flag Format | Applied When         | Example    |
|-------------|----------------------|------------|
| `post:{id}` | Single post/page/CPT | `post:123` |

Every singular page (posts, pages, custom post types) receives a flag with its post-ID. 
This enables precise invalidation when that specific content is updated.

## Archive Flags

Archives receive flags based on their type:

### Post-Type Archives

| Flag Format           | Applied When             | Example           |
|-----------------------|--------------------------|-------------------|
| `archive:post`        | Blog/post archive        | `archive:post`    |
| `archive:{post_type}` | Custom post type archive | `archive:product` |

### Taxonomy Archives

| Flag Format                 | Applied When            | Example               |
|-----------------------------|-------------------------|-----------------------|
| `archive:category:{id}`     | Category archive        | `archive:category:5`  |
| `archive:post_tag:{id}`     | Tag archive             | `archive:post_tag:12` |
| `archive:{taxonomy}:{id}`   | Custom taxonomy archive | `archive:genre:8`     |

### Author Archives

| Flag Format            | Applied When   | Example            |
|------------------------|----------------|--------------------|
| `archive:author:{id}`  | Author archive | `archive:author:1` |

### Date Archives

| Flag Format                    | Applied When   | Example              |
|--------------------------------|----------------|----------------------|
| `archive:{year}`               | Year archive   | `archive:2026`       |
| `archive:{year}:{month}`       | Month archive  | `archive:2026:01`    |
| `archive:{year}:{month}:{day}` | Day archive    | `archive:2026:01:15` |

## Feed Flags

| Flag   | Applied When        |
|--------|---------------------|
| `feed` | RSS/Atom feed pages |

## Flag Prefixes in Multisite

In multisite installations, flags are automatically prefixed to ensure cache isolation between sites:

| Environment   | Format                          | Example        |
|---------------|---------------------------------|----------------|
| Single site   | `{flag}`                        | `post:123`     |
| Multisite     | `{site_id}:{flag}`              | `2:post:123`   |
| Multi-network | `{network_id}:{site_id}:{flag}` | `1:2:post:123` |

This ensures Site A's `post:123` doesn't conflict with Site B's `post:123`.

### Working with Prefixes

Use the helper function to handle prefixes correctly:

```php
// Prefix flags with the current site's prefix
$prefix = millicache_get_flag_prefix( ['home', 'post:123'] );
// Returns: ['home', 'post:123'] (single site), ['2:home', '2:post:123'] (multisite), or ['1:2:home', '1:2:post:123'] (multi-network)

// Prefix flags for a specific site
$flags = millicache_prefix_flags( ['home', 'post:123'], $site_id = 2 );
// Returns: ['2:home', '2:post:123'] in multisite
```

## Viewing Assigned Flags

### Debug Headers

Enable debug mode in the Settings UI or via constant to see flags in response headers:

```php
define( 'MC_CACHE_DEBUG', true );
```

Then check the `X-MilliCache-Flags` header:

```
X-MilliCache-Flags: home,post:1,post:2,post:3
```

>[!TIP]
> Use the MilliCache browser extension for easy flag inspection:
> [Get the MilliCache Browser Extension](https://github.com/MilliPress/millicache-browser-ext/releases/latest)

### WP-CLI

```bash
# View stats for entries with a specific flag
wp millicache stats --flag="post:*"

# View all entries with the home flag
wp millicache stats --flag="home"
```

## Automatic Cache Clearing

When content changes, MilliCache automatically identifies and clears related cache entries:

| Event                  | Flags Cleared                         |
|------------------------|---------------------------------------|
| Post published/updated | `post:{id}`, `home`, related archives |
| Post deleted           | `post:{id}`, `home`, related archives |
| Category updated       | `archive:category:{id}`               |
| Site option changed    | All site cache (configurable)         |

### Customizing Related Flags

Use the `millicache_flags_related_to_post` filter to customize which flags are cleared when a post changes:

```php
add_filter( 'millicache_flags_related_to_post', function( $flags, $post ) {
    // Also clear featured products when a product is updated
    if ( $post->post_type === 'product' && get_post_meta( $post->ID, 'featured', true ) ) {
        $flags[] = 'featured';
    }
    return $flags;
}, 10, 2 );
```

## Next Steps

- [Custom Flags](03-custom-flags.md) — Create your own flags
- [Cache Clearing](../05-usage/20-cache-clearing.md) — Methods for clearing by flags
