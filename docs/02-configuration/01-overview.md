---
title: 'Configuration Overview'
description: 'Configure MilliCache through wp-config.php constants, the WordPress admin UI, or WP-CLI. Covers priority order, setting categories, and common setups.'
menu_order: 10
---

# Configuration Overview

MilliCache can be configured through multiple sources. This guide covers the basics — see the [Reference](02-reference.md) for all available constants.

## Quick Start

Add this to your `wp-config.php` before `"That's all, stop editing!"`:

```php
// Required - enables WordPress caching
define( 'WP_CACHE', true );

// Optional - Connect to Redis (adjust for your setup)
define( 'MC_STORAGE_HOST', '127.0.0.1' );
define( 'MC_STORAGE_PORT', 6379 );
```

That's it! MilliCache will use the defaults for everything else.

## Configuration Sources

Settings are resolved in priority order:

| Priority        | Source                       | Best For                    |
|-----------------|------------------------------|-----------------------------|
| **1 (highest)** | Constants in `wp-config.php` | Production, version control |
| **2**           | Database (Admin UI, WP-CLI)  | Easy management             |
| **3**           | Defaults                     | Fallback values             |

Higher-priority sources always win. A constant overrides the database value.

### Using Constants

Define in `wp-config.php` with the format `MC_<MODULE>_<KEY>`:

```php
define( 'MC_CACHE_TTL', 86400 );       // 1 day
define( 'MC_CACHE_DEBUG', true );      // Debug headers
define( 'MC_STORAGE_HOST', 'redis' );  // Redis hostname
```

Defining a constant sets the value and locks it in the admin UI; removing the constant unlocks the setting but keeps its last value. Changing a constant behaves exactly like changing the setting in the admin UI: any side effects (such as rescheduling background jobs) are applied automatically on the next admin visit.

### Using Admin UI

Navigate to **Settings → MilliCache** to configure via the WordPress admin.

> [!NOTE]
> Settings defined via constants are shown but cannot be modified in the admin UI.

### Using WP-CLI

```bash
# View all settings
wp millicache config get

# Set a value
wp millicache config set cache.ttl 86400

# See where values come from
wp millicache config get --show-source
```

## Setting Categories

### Storage Settings

Connection to your Redis-compatible server:

| Constant              | Default     | Description                           |
|-----------------------|-------------|---------------------------------------|
| `MC_STORAGE_HOST`     | `127.0.0.1` | Hostname, IP, socket, or `tls://host` |
| `MC_STORAGE_PORT`     | `6379`      | TCP port                              |
| `MC_STORAGE_USERNAME` | `''`        | Redis ACL username                    |
| `MC_STORAGE_PASSWORD` | `''`        | Redis AUTH password                   |
| `MC_STORAGE_DB`       | `0`         | Database number (0-15)                |
| `MC_STORAGE_PREFIX`   | `mll`       | Key prefix                            |

### Cache Settings

Caching behavior:

| Constant         | Default   | Description            |
|------------------|-----------|------------------------|
| `MC_CACHE_TTL`   | `86400`   | Time-to-live (1 day)   |
| `MC_CACHE_GRACE` | `2592000` | Grace period (30 days) |
| `MC_CACHE_DEBUG` | `false`   | Debug headers          |
| `MC_CACHE_GZIP`  | `true`    | Compression            |

### Exclusion Settings

What to skip:

| Constant                       | Default           | Description               |
|--------------------------------|-------------------|---------------------------|
| `MC_CACHE_NOCACHE_PATHS`       | `[]`              | URL paths to exclude      |
| `MC_CACHE_NOCACHE_COOKIES`     | `[...]`           | Cookies that bypass cache |
| `MC_CACHE_IGNORE_COOKIES`      | `['_*']`          | Cookies stripped from key |
| `MC_CACHE_IGNORE_REQUEST_KEYS` | `['_*', 'utm_*']` | Query params to ignore    |

### Key Composition Settings

How cache entries differentiate from each other:

| Constant            | Default | Description                                                              |
|---------------------|---------|--------------------------------------------------------------------------|
| `MC_CACHE_UNIQUE`   | `[]`    | Static deployment-level keys folded into every request hash              |
| `MC_CACHE_BUCKETS`  | `[]`    | Per-request signal → token map (Accept negotiation, language, device, …) |

### Update Settings

Plugin update behavior:

| Constant               | Default | Description                        |
|------------------------|---------|------------------------------------|
| `MC_UPDATE_PRERELEASE` | `false` | Opt in to prerelease (beta) builds |

Update checks can be disabled with the `millicache_updates` filter. See the
[Constants Reference](02-reference.md#update-constants) and
[Hooks & Filters](../07-developers/02-hooks-filters.md#millicache_updates) for details.

## Common Configurations

### High-Traffic Site

```php
define( 'MC_CACHE_TTL', 604800 );     // 7 days
define( 'MC_CACHE_GRACE', 2592000 );  // 30 days grace
define( 'MC_CACHE_GZIP', true );
```

### Frequently Updated Content

```php
define( 'MC_CACHE_TTL', 3600 );       // 1 hour
define( 'MC_CACHE_GRACE', 86400 );    // 1 day grace
```

### Development Environment

```php
define( 'MC_CACHE_TTL', 60 );         // 1 minute
define( 'MC_CACHE_DEBUG', true );     // Show debug headers
```

### WooCommerce Site

WooCommerce already marks its dynamic pages (cart, checkout, my account) with the
`DONOTCACHEPAGE` constant, which MilliCache respects out of the box. What you should
configure is cookie handling: WooCommerce sets several cookies for ordinary browsing
visitors, and any cookie MilliCache does not ignore becomes part of the cache key.

Only ignore a cookie that cannot change the HTML your server builds. The tracking,
store-notice and recently-viewed cookies never do in a standard shop. The cart and
session cookies usually do not either, but a theme can render cart contents
server-side: open a product page in two private windows with different carts and
compare the sources before ignoring them.

```php
define( 'MC_CACHE_IGNORE_COOKIES', [
    '_*',                        // Keep the default (analytics cookies)
    'sbjs_*',                    // Order Attribution tracking (every visitor)
    'store_notice*',             // Dismissed store notices (hidden markup, revealed by JS)
    'woocommerce_*',             // Recently viewed, and cart state after the comparison above
    'wp_woocommerce_session_*',  // Customer session, same condition
] );
```

Do **not** put `woocommerce_*` or `sbjs_*` into `MC_CACHE_NOCACHE_COOKIES`.
`sbjs_*` is set for every visitor, and with the classic Recently Viewed Products
widget active `woocommerce_recently_viewed` is set on the first product view, so a
bypass on either silently turns most browsing traffic into cache misses. See the
[FAQ](../09-troubleshooting/02-faq.md#does-millicache-work-with-woocommerce) for
details.

## Viewing Current Configuration

```bash
# All settings with sources
wp millicache config get --show-source

# Test connection
wp millicache test

# Cache statistics
wp millicache stats
```

## Next Steps

- [Constants Reference](02-reference.md) — Complete list of all constants
- [Storage Backends](../08-storage-backends/01-overview.md) — Redis/ValKey setup
- [WP-CLI Commands](../06-wp-cli/01-commands.md) — Command reference
