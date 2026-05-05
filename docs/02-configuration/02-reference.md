---
title: 'Configuration Constants Reference'
post_excerpt: 'Complete reference of all MilliCache PHP constants for wp-config.php — Redis connection, TTL, cache exclusions, stale-while-revalidate, and storage settings.'
menu_order: 20
---

# Constants Reference

This is a complete reference of all configuration constants available in MilliCache. Define these in `wp-config.php` before the line `"That's all, stop editing!"`.
Alternatively, use the admin UI (Settings -> MilliCache) or WP-CLI to manage settings stored in the database.

## Required Constants

### WP_CACHE

```php
define( 'WP_CACHE', true );
```

**Required.** Enables WordPress drop-in caching. Without this, MilliCache cannot intercept requests early.

## Storage Constants

Connection settings for your Redis-compatible server.

### MC_STORAGE_HOST

```php
define( 'MC_STORAGE_HOST', '127.0.0.1' );
```

| Property  | Value       |
|-----------|-------------|
| Default   | `127.0.0.1` |
| Type      | `string`    |

Server hostname, IP address, or Unix socket path. Supports an optional `tls://` or `tcp://` scheme prefix for encrypted connections.

**Examples:**

```php
// IP address
define( 'MC_STORAGE_HOST', '10.0.0.5' );

// Hostname
define( 'MC_STORAGE_HOST', 'redis.example.com' );

// Docker container name
define( 'MC_STORAGE_HOST', 'redis' );

// Unix socket
define( 'MC_STORAGE_HOST', '/var/run/redis/redis.sock' );

// TLS connection (e.g. AWS ElastiCache with in-transit encryption)
define( 'MC_STORAGE_HOST', 'tls://master.example.cache.amazonaws.com' );
```

### MC_STORAGE_PORT

```php
define( 'MC_STORAGE_PORT', 6379 );
```

| Property   | Value     |
|------------|-----------|
| Default    | `6379`    |
| Type       | `integer` |

TCP port number. Ignored when using Unix sockets.

### MC_STORAGE_USERNAME

```php
define( 'MC_STORAGE_USERNAME', 'your-username' );
```

| Property  | Value        |
|-----------|--------------|
| Default   | `''` (empty) |
| Type      | `string`     |

Redis ACL username. Leave empty to use the default user. Required when your Redis server is configured with ACL users (Redis 6+).

> [!NOTE]
> When using the admin UI or WP-CLI, the setting key is `storage.username`.

### MC_STORAGE_PASSWORD

```php
define( 'MC_STORAGE_PASSWORD', 'your-password' );
```

| Property  | Value        |
|-----------|--------------|
| Default   | `''` (empty) |
| Type      | `string`     |

Redis AUTH password. Leave empty if no authentication required.

> [!NOTE]
> When using the admin UI or WP-CLI, the password is stored encrypted as `enc_password`.

### MC_STORAGE_DB

```php
define( 'MC_STORAGE_DB', 0 );
```

| Property   | Value     |
|------------|-----------|
| Default    | `0`       |
| Type       | `integer` |

Redis database number (0-15). Use different databases to isolate cache data.

### MC_STORAGE_PERSISTENT

```php
define( 'MC_STORAGE_PERSISTENT', true );
```

| Property  | Value     |
|-----------|-----------|
| Default   | `true`    |
| Type      | `boolean` |

Enable persistent connections. Reduces connection overhead but requires proper server configuration.

### MC_STORAGE_PREFIX

```php
define( 'MC_STORAGE_PREFIX', 'mll' );
```

| Property   | Value    |
|------------|----------|
| Default    | `mll`    |
| Type       | `string` |

Prefix for all cache keys in Redis. Use different prefixes to share Redis between sites.

## Cache Constants

Settings that control caching behavior.

### MC_CACHE_TTL

```php
define( 'MC_CACHE_TTL', 86400 );
```

| Property   | Value           |
|------------|-----------------|
| Default    | `86400` (1 day) |
| Type       | `integer`       |

Time-to-live in seconds. How long cached content remains fresh.

| Value     | Duration  |
|-----------|-----------|
| `3600`    | 1 hour    |
| `86400`   | 1 day     |
| `604800`  | 1 week    |
| `2592000` | 30 days   |

### MC_CACHE_GRACE

```php
define( 'MC_CACHE_GRACE', 2592000 );
```

| Property   | Value               |
|------------|---------------------|
| Default    | `2592000` (30 days) |
| Type       | `integer`           |

Grace period in seconds. How long stale content can be served while regenerating.

### MC_CACHE_DEBUG

```php
define( 'MC_CACHE_DEBUG', false );
```

| Property  | Value     |
|-----------|-----------|
| Default   | `false`   |
| Type      | `boolean` |

Enable debug response headers. Useful for troubleshooting, disable in production.

### MC_CACHE_GZIP

```php
define( 'MC_CACHE_GZIP', true );
```

| Property  | Value     |
|-----------|-----------|
| Default   | `true`    |
| Type      | `boolean` |

Enable gzip compression of cached content. Requires `ext-zlib`.

### MC_CACHE_UNIQUE

```php
define( 'MC_CACHE_UNIQUE', [] );
```

| Property  | Value   |
|-----------|---------|
| Default   | `[]`    |
| Type      | `array` |

Static cache namespace — values folded into every request hash on this deployment. Use for deploy-time cache busting or multi-site isolation.

```php
define( 'MC_CACHE_UNIQUE', [ 'version' => '2.1', 'site_id' => 5 ] );
```

> [!TIP]
> For per-request differentiation, use `MC_CACHE_BUCKETS` instead.

### MC_CACHE_BUCKETS

```php
define( 'MC_CACHE_BUCKETS', [] );
```

| Property  | Value                                       |
|-----------|---------------------------------------------|
| Default   | `[]`                                        |
| Type      | `array<string, array<string, string>>`      |

Shared lookup tables for bucket resolvers. Each top-level key names a request *dimension*; the inner map translates raw request values into compact bucket tokens.

```php
define( 'MC_CACHE_BUCKETS', [
    'accept' => [ 'text/markdown' => 'md' ],
    'tenant' => [ 'acme' => 'acme', 'globex' => 'glx' ],
] );
```

Two built-in resolvers ship in MilliCache:

- **`auth`** — Authorization header. Always-on correctness primitive: each unique bearer token gets its own cache entry. No config needed.
- **`accept`** — Accept header content negotiation. Dormant unless `MC_CACHE_BUCKETS['accept']` is configured. Parses with q-values and looks up the top-preferred MIME type.

Other dimensions need a resolver implementation. See [Bucket Extension](../07-developers/02-hooks-filters.md#bucket-extension) — the rules engine's `set_bucket` action is the no-code way to add them.

> [!NOTE]
> Bucket tokens should be short — they're folded into the cache key. `md`, `de`, `mobile` are good; full MIME types or full UA strings are not.

### MC_CACHE_NOCACHE_PATHS

```php
define( 'MC_CACHE_NOCACHE_PATHS', [] );
```

| Property  | Value   |
|-----------|---------|
| Default   | `[]`    |
| Type      | `array` |

URL paths to exclude from caching. Supports wildcards.

```php
define( 'MC_CACHE_NOCACHE_PATHS', [
    '/my-account/*',
    '/checkout/*',
    '/cart/*',
] );
```

### MC_CACHE_NOCACHE_COOKIES

```php
define( 'MC_CACHE_NOCACHE_COOKIES', [ 'wp-*pass*', 'comment_author_*' ] );
```

| Property   | Value                               |
|------------|-------------------------------------|
| Default    | `['wp-*pass*', 'comment_author_*']` |
| Type       | `array`                             |

Cookies that cause cache bypass. Supports wildcards.

```php
define( 'MC_CACHE_NOCACHE_COOKIES', [
    'wp-*pass*',
    'comment_author_*',
    'woocommerce_cart_hash',
] );
```

### MC_CACHE_IGNORE_COOKIES

```php
define( 'MC_CACHE_IGNORE_COOKIES', [ '_*' ] );
```

| Property   | Value    |
|------------|----------|
| Default    | `['_*']` |
| Type       | `array`  |

Cookies stripped from cache key calculation. Supports wildcards.

```php
define( 'MC_CACHE_IGNORE_COOKIES', [
    '_*',       // Analytics
    '__utm*',   // UTM tracking
] );
```

### MC_CACHE_IGNORE_REQUEST_KEYS

```php
define( 'MC_CACHE_IGNORE_REQUEST_KEYS', [ '_*', 'utm_*' ] );
```

| Property  | Value             |
|-----------|-------------------|
| Default   | `['_*', 'utm_*']` |
| Type      | `array`           |

Query parameters stripped from cache key. Supports wildcards.

```php
define( 'MC_CACHE_IGNORE_REQUEST_KEYS', [
    '_*',
    'utm_*',
    'fbclid',
    'gclid',
] );
```

## WordPress Cache Constants

Standard WordPress constants that affect MilliCache behavior.

### DONOTCACHEPAGE

```php
define( 'DONOTCACHEPAGE', true );
```

Set dynamically in themes/plugins to skip caching for the current request.

```php
// In your template
if ( some_condition() ) {
    define( 'DONOTCACHEPAGE', true );
}
```

### DOING_CRON

```php
define( 'DOING_CRON', true );
```

Automatically set by WordPress during cron execution. MilliCache skips caching.

### DOING_AJAX

```php
define( 'DOING_AJAX', true );
```

Automatically set by WordPress during AJAX requests. MilliCache skips caching.

### REST_REQUEST

```php
define( 'REST_REQUEST', true );
```

Automatically set by WordPress during REST API requests. MilliCache skips caching.

## Complete Configuration Example

```php
<?php
// wp-config.php

// Enable caching (REQUIRED)
define( 'WP_CACHE', true );

// Storage settings
define( 'MC_STORAGE_HOST', 'redis.example.com' );
define( 'MC_STORAGE_PORT', 6379 );
define( 'MC_STORAGE_USERNAME', 'your-username' );
define( 'MC_STORAGE_PASSWORD', 'secure-password' );
define( 'MC_STORAGE_DB', 0 );
define( 'MC_STORAGE_PERSISTENT', true );
define( 'MC_STORAGE_PREFIX', 'mll_prod' );

// Cache settings
define( 'MC_CACHE_TTL', 86400 );        // 1 day
define( 'MC_CACHE_GRACE', 2592000 );    // 30 days
define( 'MC_CACHE_DEBUG', false );
define( 'MC_CACHE_GZIP', true );

// Exclusions
define( 'MC_CACHE_NOCACHE_PATHS', [
    '/my-account/*',
    '/checkout/*',
    '/cart/*',
] );

define( 'MC_CACHE_NOCACHE_COOKIES', [
    'wp-*pass*',
    'comment_author_*',
] );

define( 'MC_CACHE_IGNORE_COOKIES', [
    '_*',
    '__utm*',
] );

define( 'MC_CACHE_IGNORE_REQUEST_KEYS', [
    '_*',
    'utm_*',
    'fbclid',
    'gclid',
] );

/* That's all, stop editing! */
```

## Next Steps

- [Configuration Overview](01-overview.md) — Quick configuration guide
- [WP-CLI Commands](../06-wp-cli/01-commands.md) — Manage settings via CLI
- [Storage Backends](../08-storage-backends/01-overview.md) — Redis/ValKey setup
