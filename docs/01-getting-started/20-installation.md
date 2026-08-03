---
title: 'Installation & Quick Start'
description: 'Install MilliCache, the Redis page cache for WordPress, in under 5 minutes: enable WP_CACHE, connect Redis, and verify cache hits via headers or WP-CLI.'
menu_order: 20
---

# Installation & Quick Start

Get MilliCache installed and caching your WordPress site in 5 minutes.

## Requirements

| Component   | Requirement                               |
|-------------|-------------------------------------------|
| PHP         | 7.4 or higher                             |
| WordPress   | 5.6 or higher                             |
| Storage     | Redis, ValKey, Dragonfly, or KeyDB server |

> [!TIP]
> The optional `ext-zlib` PHP extension enables gzip compression of cached content, reducing memory usage significantly.

## Installation Methods

### Method 1: GitHub Release (Recommended)

1. Download the latest release from [GitHub Releases](https://github.com/millipress/millicache/releases)
2. Upload the ZIP file via **Plugins → Add New → Upload Plugin**
3. Activate the plugin

The release package includes all dependencies pre-bundled. Internal dependencies are prefixed to avoid conflicts; the MilliRules rule engine keeps its own `MilliRules\` namespace, so custom rules use the same classes on every install type.

When activated, MilliCache automatically creates the `advanced-cache.php` drop-in file required for early request interception.

### Method 2: Composer

For developers managing WordPress with Composer:

```bash
composer require millipress/millicache
```

Dependencies (`predis/predis` and `millipress/millirules`) are installed as regular Composer packages, without prefixing.

## Setup (2 Steps)

### Step 1: Enable WP_CACHE

Add to your `wp-config.php` **before** the "That's all, stop editing!" line:

```php
define( 'WP_CACHE', true );
```

> [!IMPORTANT]
> The `WP_CACHE` constant must be set to `true` for MilliCache to intercept requests early in the WordPress bootstrap process. Without this, caching will not work.

### Step 2: Configure Redis Connection (If Needed)

By default, MilliCache connects to Redis at `127.0.0.1:6379`. If your Redis server uses different settings:

**Option A: Via Admin UI**

Go to **Settings → MilliCache → Settings Tab → Storage Server** and enter your Redis credentials.

**Option B: Via wp-config.php**

```php
define( 'MC_STORAGE_HOST', '127.0.0.1' );
define( 'MC_STORAGE_PORT', 6379 );
define( 'MC_STORAGE_USERNAME', 'your-redis-username' );
define( 'MC_STORAGE_PASSWORD', 'your-redis-password' );
define( 'MC_STORAGE_DB', 0 );
```

Settings defined as constants override those in the admin UI.

## Verify It's Working

### Via Admin UI

Navigate to **Settings → MilliCache → Stats Tab** to see:

- Cache entries count
- Total cache size
- Cache hit ratio
- Recent cache activity

You can also check the admin bar: **MilliCache** should show cache statistics.

### Via Browser (Easiest)

1. **Enable debug headers** in `wp-config.php`:
   ```php
   define( 'MC_CACHE_DEBUG', true );
   ```

2. **Visit your homepage** (logged out)

3. **Open browser developer tools** (F12) → Network tab

4. **Refresh the page** and check the response headers:

   | Header                 | First Visit | Second Visit         |
   |------------------------|-------------|----------------------|
   | `X-MilliCache-Status`  | `miss`      | `hit`                |
   | `X-MilliCache-Flags`   | not set     | Shows assigned flags |
   | `X-MilliCache-Expires` | not set     | Seconds until expiry |

   **Success**: Second visit shows `X-MilliCache-Status: hit` ✓

> [!TIP]
> The [MilliCache Browser Extension](https://github.com/MilliPress/millicache-browser-ext/) adds a dedicated panel to your browser's developer tools for easier debugging. 
> Debug mode (`MC_CACHE_DEBUG`) must be enabled for the extension to display cache information.

### Via WP-CLI (Optional)

If you have WP-CLI installed, you can run comprehensive tests:

**Check status:**
```bash
wp millicache status
```

Expected output:
```
+-------------------+------------------+
| property          | status           |
+-------------------+------------------+
| plugin_version    | 1.0.0            |
| wp_cache          | enabled          |
| advanced_cache    | symlink          |
| storage_connected | yes              |
| storage_version   | 7.2.4            |
| cache_entries     | 1                |
| cache_size        | 15 KB            |
+-------------------+------------------+
```

**Test Redis connection:**
```bash
wp millicache test
```

This performs connection, ping, write, read, and delete tests. All should show `PASS`.

**View cache statistics:**
```bash
wp millicache stats
```

## That's It!

Your site is now caching. Here's what happens automatically by default:

- **Anonymous visitors** receive cached pages (fast!)
- **Logged-in users** bypass the cache (personalized content)
- **Content updates** automatically clear related cache entries
- **Expired cache** serves stale content while regenerating (grace period)

## Default Behavior

Out of the box, MilliCache:

| Setting         | Default  | Meaning                               |
|-----------------|----------|---------------------------------------|
| TTL             | 1 day    | Cache expires after 24 hours          |
| Grace           | 1 month  | Stale cache served while regenerating |
| Gzip            | Enabled  | Compressed storage saves memory       |
| Logged-in users | Bypassed | No caching for authenticated users    |
| POST requests   | Bypassed | Only GET/HEAD requests cached         |
| Admin/REST/AJAX | Bypassed | Backend requests never cached         |

## Common Tasks

### Clear All Cache

**Via Admin Bar:**
**MilliCache → Clear Site Cache**

**Via Admin UI:**
**Settings → MilliCache → Stats Tab → Clear Cache Button**

**Via WP-CLI** (optional):
```bash
wp millicache clear
```

### Clear Specific Post

**Via Admin Bar:**
When viewing a post, click **MilliCache → Clear Post Cache**

**Via WP-CLI** (optional):
```bash
wp millicache clear --id=123
```

### View Configuration

**Via Admin UI:**
**Settings → MilliCache → Settings Tab**

**Via WP-CLI** (optional):
```bash
wp millicache config get
```

## Troubleshooting

### advanced-cache.php Issues

The drop-in is created automatically on activation. If it needs repair (e.g., after deployment or manual deletion):

**Via WP-CLI:**
```bash
wp millicache drop --force
```

> [!WARNING]
> If another caching plugin installed its own `advanced-cache.php`, you must deactivate that plugin first.

### Permission Issues

The `wp-content/settings` directory must be writable by the web server user.

### Redis Connection Failed

1. Verify Redis is running: `redis-cli ping` (should return `PONG`)
2. Check connection settings in **Settings → MilliCache → Settings Tab → Storage Server**
3. Test connectivity via **Settings → MilliCache → Stats Tab** or `wp millicache test`

### WP_CACHE Not Enabled

Check **Settings → MilliCache** for warnings. Ensure `define( 'WP_CACHE', true );` is in `wp-config.php` **before** the "That's all" line.

### No Cache Headers Showing

Make sure you're:
- Logged out (logged-in users bypass cache)
- Using a GET request (not POST)
- Not on an admin/login/REST/AJAX page
- Have `MC_CACHE_DEBUG` set to `true`

## Multisite Installation

For WordPress Multisite:

1. Network-activate the plugin
2. The `WP_CACHE` constant applies to all sites
3. Each site's cache is automatically isolated
4. Network admins can clear cache for all sites via **Network Admin → MilliCache**

See [Multisite](../05-usage/30-multisite.md) for detailed configuration.

## Next Steps

- [Configuration](../02-configuration/01-overview.md) - Customize caching behavior
- [Cache Clearing](../05-usage/20-cache-clearing.md) - Learn invalidation strategies
- [WP-CLI Commands](../06-wp-cli/01-commands.md) - Master command-line tools (optional)
