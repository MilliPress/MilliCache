---
title: 'Frequently Asked Questions'
post_excerpt: 'Answers to common questions about MilliCache: WooCommerce compatibility, CDN integration, membership sites, performance impact, security, migration, and best practices.'
menu_order: 20
---

# FAQ

Frequently asked questions about MilliCache.

## General Questions

### What is MilliCache?

MilliCache is a full-page caching plugin for WordPress that stores complete HTML pages in Redis (or compatible stores like ValKey, KeyDB, or Dragonfly). 
When a visitor requests a cached page, MilliCache serves it directly from memory without loading WordPress, resulting in sub-10ms response times.

### How is MilliCache different from other caching plugins?

| Feature                 | MilliCache            | Other Plugins    |
|-------------------------|-----------------------|------------------|
| Storage                 | Redis/ValKey/KeyDB    | Files/Database   |
| Speed                   | Sub-10ms              | 50-200ms         |
| Rules Engine            | MilliRules (powerful) | Basic conditions |
| Flag-based Invalidation | Yes                   | Limited          |
| Multisite               | Full support          | Varies           |
| License                 | GPL-2.0+              | Varies           |

### Does MilliCache require Redis?

Yes, MilliCache requires a Redis-compatible server. Supported options:
- Redis (original)
- ValKey (open-source fork)
- KeyDB (multithreaded)
- Dragonfly (high-performance)

### Is MilliCache free?

Yes, MilliCache is free and open-source under GPL-2.0+ license.

---

## Installation & Setup

### What are the requirements?

- PHP 7.4 or higher
- WordPress 5.6 or higher
- Redis, ValKey, KeyDB, or Dragonfly server

### How do I know if caching is working?

1. Enable debug mode:
   ```php
   define( 'MC_CACHE_DEBUG', true );
   ```

2. Visit a page (logged out)

3. Check response headers:
   - First visit: `X-MilliCache-Status: miss`
   - Second visit: `X-MilliCache-Status: hit`

### Do I need to configure anything after installation?

Basic setup requires:

1. Add to `wp-config.php`:
   ```php
   define( 'WP_CACHE', true );
   ```

2. Install the drop-in:
   ```bash
   wp millicache drop
   ```

If Redis runs on default settings (localhost:6379), no additional configuration is needed.

---

## Caching Behavior

### What gets cached?

**Cached:**
- GET and HEAD requests
- 200 OK responses
- Anonymous visitors (logged-out)
- Pages, posts, archives, taxonomies
- RSS feeds (by default)

**Not Cached:**
- POST, PUT, DELETE requests
- Logged-in users
- Non-200 responses
- AJAX, REST API, XML-RPC, Cron
- WP-CLI commands

### Why aren't logged-in users cached?

Logged-in users see personalized content (admin bar, user-specific data). Caching would show incorrect content. This is a security and UX best practice.

### Can I cache logged-in users?

It's not recommended. If you need it:

1. Remove the logged-in rule via filter
2. Add user-specific flags
3. Ensure no sensitive data leaks

> [!WARNING]
> Caching logged-in users can expose private data. Only do this if you fully understand the implications.

### What is the grace period?

The grace period allows stale cache to be served while fresh content generates in the background. 
This prevents visitors from waiting for page generation after cache expires.

Example with TTL=1 day, Grace=30 days:
- Day 1: Fresh cache served
- Day 2: Cache expired, but grace serves stale while regenerating
- Day 31: Grace expired, visitor waits for fresh content

### How does cache invalidation work?

MilliCache uses **flags** (tags) for targeted invalidation:

1. Each page is tagged with flags (e.g., `post:123`, `home`, `archive:post`)
2. When content changes, related flags are identified
3. Only pages with matching flags are cleared

This means updating one post doesn't clear the entire cache—only affected pages.

---

## Compatibility

### Does MilliCache work with WooCommerce?

Yes, with proper configuration:

```php
define( 'MC_CACHE_NOCACHE_COOKIES', [
    'wp-*pass*',
    'comment_author_*',
    'woocommerce_cart_hash',
    'woocommerce_items_in_cart',
] );

define( 'MC_CACHE_NOCACHE_PATHS', [
    '/my-account/*',
    '/checkout/*',
    '/cart/*',
] );
```

### Does MilliCache work with membership plugins?

Yes. Common configurations:

```php
// MemberPress
define( 'MC_CACHE_NOCACHE_COOKIES', [
    'wp-*pass*',
    'memberpress_*',
    'mepr_*',
] );

// Restrict Content Pro
define( 'MC_CACHE_NOCACHE_COOKIES', [
    'wp-*pass*',
    'rcp_*',
] );
```

### Can I use MilliCache with a CDN?

Yes! MilliCache caches at the origin server. CDN caches on edge servers. They work together:

1. CDN checks its cache
2. CDN miss → Request reaches origin
3. MilliCache serves from Redis (fast!)
4. CDN caches the response

For cache clearing, you may need to:
- Clear MilliCache (origin)
- Clear CDN (edge)

### Does MilliCache work with Cloudflare/other proxies?

Yes. MilliCache operates at the origin level, before any proxy/CDN.

### Does MilliCache conflict with other caching plugins?

Yes, you should only use one full-page caching plugin. Deactivate others before using MilliCache:

- WP Super Cache
- W3 Total Cache
- WP Fastest Cache
- LiteSpeed Cache
- etc.

Object caching plugins (Redis Object Cache) are fine — they cache different things.

---

## Multisite

### Does MilliCache support multisite?

Yes, fully. Features include:

- Per-site cache isolation
- Multi-network support
- Site and network-level clearing
- Per-site configuration options

### How do I clear cache for one site only?

```bash
# By site URL
wp millicache clear --url=site1.example.com

# By site ID
wp millicache clear --site=2
```

### How do I clear cache for all sites?

```bash
# All sites in network
wp millicache clear --network=1

# All cache (all networks)
wp millicache clear
```

---

## Performance

### How fast is MilliCache?

| Scenario               | Typical Response Time  |
|------------------------|------------------------|
| Cache hit              | 5-15ms                 |
| Cache miss (WordPress) | 200-2000ms             |

That's 10-200x faster than uncached WordPress.

### How much memory does MilliCache use?

Memory usage depends on:
- Number of cached pages
- Average page size
- Compression enabled

Typical usage with 1000 pages:
- Without compression: ~50-100MB
- With compression: ~15-30MB

### Should I enable compression?

Yes, for most sites:

```php
define( 'MC_CACHE_GZIP', true );
```

Benefits:
- 60-80% smaller cache entries
- Lower memory usage
- Faster cache retrieval

Requires `ext-zlib` PHP extension.

---

## WP-CLI

### What commands are available?

| Command                | Description     |
|------------------------|-----------------|
| `wp millicache clear`  | Clear cache     |
| `wp millicache stats`  | View statistics |
| `wp millicache status` | Check status    |
| `wp millicache test`   | Test connection |
| `wp millicache drop`   | Fix drop-in     |
| `wp millicache cli`    | Open Redis CLI  |
| `wp millicache config` | Manage settings |

### How do I clear cache via cron?

```bash
# Add to crontab
0 3 * * * cd /path/to/wordpress && wp millicache clear --expire
```

The `--expire` flag serves stale content while regenerating (gentler).

---

## Security

### Does MilliCache cache sensitive data?

MilliCache does not cache:
- Logged-in users
- POST requests
- Pages with excluded cookies

This prevents accidental caching of sensitive data.

### Is the Redis connection secure?

For security:
1. Use authentication: `MC_STORAGE_USERNAME` and `MC_STORAGE_PASSWORD`
2. Bind Redis to localhost if on same server
3. Use private networks for remote Redis
4. Consider Redis TLS for sensitive environments

### Does MilliCache modify WordPress core?

No. MilliCache uses WordPress's official drop-in caching API (`advanced-cache.php`). It doesn't modify core files.

---

## Troubleshooting

### Why isn't my page being cached?

Check debug headers (`MC_CACHE_DEBUG = true`):

| Status     | Meaning                   |
|------------|---------------------------|
| No headers | Drop-in not installed     |
| `bypass`   | Rule prevented caching    |
| `miss`     | First request, will cache |
| `hit`      | Cached and serving        |

Common bypass reasons:
- Logged in
- Excluded cookie
- Excluded path
- POST request
- Non-200 response
- Response larger than 5MB

### Why is cache not clearing?

1. Clear manually: `wp millicache clear`
2. Check Redis connection: `wp millicache test`
3. Verify clearing hooks: Check `millicache_cache_cleared` action
4. Clear external caches (CDN, proxy)

### Where can I get help?

- [Troubleshooting Guide](01-common-issues.md)
- [GitHub Issues](https://github.com/millipress/millicache/issues)
- [Documentation](../01-getting-started/10-introduction.md)

## Next Steps

- [Troubleshooting](01-common-issues.md) - Detailed problem solving
- [Installation & Quick Start](../01-getting-started/20-installation.md) - Get started fast
- [WP-CLI Commands](../06-wp-cli/01-commands.md) - Command reference
