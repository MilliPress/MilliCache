---
title: 'Configuration Overview'
post_excerpt: 'Configure MilliCache via wp-config.php constants, the admin UI, or WP-CLI. Constants override database settings, which override defaults — giving you full control.'
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

| Constant              | Default     | Description                    |
|-----------------------|-------------|--------------------------------|
| `MC_STORAGE_HOST`     | `127.0.0.1` | Server hostname, IP, or socket |
| `MC_STORAGE_PORT`     | `6379`      | TCP port                       |
| `MC_STORAGE_PASSWORD` | `''`        | Redis AUTH password            |
| `MC_STORAGE_DB`       | `0`         | Database number (0-15)         |
| `MC_STORAGE_PREFIX`   | `mll`       | Key prefix                     |
| `MC_STORAGE_SCHEME`   | `tcp`       | Connection scheme (`tcp` or `tls`) |

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

```php
define( 'MC_CACHE_NOCACHE_COOKIES', [
    'wp-*pass*',
    'comment_author_*',
    'woocommerce_*',
    'sbjs_*'
] );
```

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
