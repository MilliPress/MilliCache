# MilliCache: Redis Full Page Cache for WordPress

As a full page caching solution, MilliCache uses Redis as a backend to store the cached pages. It is designed to be fast, reliable and scalable. MilliCache is developed for WordPress Multisite and Multi-Network installations, but can also be used in single site installations.

> [!IMPORTANT]
> 
> This plugin is currently under active development and not officially ready for production use.
> There is no Settings UI so far, configuration is done via `wp-config.php`. 
> It lacks proper documentation and tests. Furthermore, things might change without any prior notice.
> Please report any issues you encounter.

## Current Features

- Rapid In-Memory Full Page Caching
- [Cache Flagging](#cache-flags) for complex cache handling & [clearing](#clear-cache)
- [Expired Cache Handling](#clear--expire-cache) regenerates the cache in the background
- Developed for WordPress Multisite & Multi-Network
- Extensible by [Hooks & Filters](#hooks--filters)
- [WP CLI Commands](#wp-cli) for Cache Clearing & Stats
- [Debugging](#debugging) Headers for Cache Information
- Gzip Compression for Cache Entries
- Scalable: Even works with Redis Cluster

---

## Requirements

- PHP 7.4 or higher
- PHP Redis or Predis extension
- Redis Server

Please go sure that you have a Redis Server and the PECL PHP Redis extension installed and running. If you are using Ubuntu, you can install Redis with the following commands:

### Install Redis on Ubuntu

```bash
$ sudo apt update
$ sudo apt install redis-server php8-redis
```

After installing the Redis PECL extension, make sure you restart your PHP server.
After installing Redis, you can check the status with:

```bash
$ sudo systemctl status redis-server
```

If you are using a different operating system, please refer to the [Redis Documentation](https://redis.io/documentation).

### Redis Configuration

Make sure your Redis server has enough memory allocated to store your cached pages. MilliCache compresses cached pages using gzip to reduce memory usage. However, you need to keep an eye on your cache size in the Dashboard to increase according to your hit rate. We also recommend disabling flushing the Redis cache to disk and the `allkeys-lru` eviction policy to ensure the server can make more room for new cached pages by evicting old ones. Here is an example excerpt from the redis.conf file:

```
# Set maximum memory for Redis to 16 megabytes
maxmemory 16m

# Set eviction policy to remove less recently used keys first
maxmemory-policy allkeys-lru
```

Remember to restart the Redis server after making changes to the configuration file.


---
## Installation

To install MilliCache, download the latest version from GitHub or install via Composer:

```bash
$ composer require millipress/millicache
```

[Configure the plugin](#configuration) in your `wp-config.php`:

```php
define('MC_REDIS_HOST', '127.0.0.1'); # Redis Host
define('MC_REDIS_PORT', 6379); # Redis Port
...
```

Go sure that you have caching enabled in your WordPress installation by setting the `WP_CACHE` constant to `true` in your `wp-config.php`:

```php
define('WP_CACHE', true); # Enable WordPress Cache
```

Activate the plugin in your WordPress installation. That's it!

---
## Configuration

MilliCache can be configured by setting constants in your `wp-config.php`. The following constants are available:

### General Configuration

| Constant                   | Description                                           | Default                             |
|----------------------------|-------------------------------------------------------|-------------------------------------|
| `MC_DEBUG`                 | Enable Debugging                                      | `false`                             |
| `MC_GZIP`                  | Enable Gzip Compression                               | `true`                              |
| `MC_TTL`                   | Default Cache TTL                                     | `DAY_IN_SECONDS`                    |
| `MC_IGNORE_COOKIES`        | Cookies that are ignored/striped from the request     | `[]`                                |
| `MC_NOCACHE_COOKIES`       | Cookies which avoid caching                           | `['comment_author']`                |
| `MC_IGNORE_REQUEST_KEYS`   | Request Keys that are ignored                         | `['utm_source', 'utm_medium', ...]` |
| `MC_SHOULD_CACHE_CALLBACK` | External callback to append custom cache conditions   | `''`                                |
| `MC_UNIQUE`                | Variables that make the request & cache entry unique. | `[]`                                |

### Redis Connection Configuration

| Constant              | Description                       | Default            |
|-----------------------|-----------------------------------|--------------------|
| `MC_REDIS_HOST`       | Redis Host                        | `127.0.0.1`        |
| `MC_REDIS_PORT`       | Redis Port                        | `6379`             |
| `MC_REDIS_Password`   | Redis Password                    | `''`               |
| `MC_REDIS_DB`         | Redis Database                    | `0`                |
| `MC_REDIS_PERSISTENT` | Redis Persistent Connection       | `true`             |
| `MC_REDIS_PREFIX`     | Redis Key Prefix                  | `mll`              |
| `MC_REDIS_MAX_TTL`    | Max TTL for Expired Cache Entries | `MONTH_IN_SECONDS` |

---
## Cache Flags

MilliCache supports cache flags to group and clear caches based on flags. This is beneficial for complex cache handling and clearing logics.

### What are Cache Flags?

A single post or page can have multiple cache entries as the cache keys vary based on the request. Different cache entries are stored for different requests, for example, a request with different cookies or query string.

```
https://example.org/?p=123
https://example.org/post-slug/
https://example.org/post-slug/page/2/
https://example.org/post-slug/?show_comments=1
```

The cache entries for the above URLs are different as the cache keys are unique based on the request. But still they are related to the same post or page and MilliCache groups these cache entries with a common flag `post:1:123`.
Entries can have multiple flags and therefore can be grouped in different ways. That makes it possible to clear all cache entries of a specific group by using the flag. For the example above, you could use [WP-CLI](#wp-cli) or one of the [clearing functions](#clear-cache) to clear the group `post:1:123`.

Another built-in example is the flag `home:?` which groups all cache entries of the home & blog pages. The `?` is a wildcard placeholder for the site ID in Multisite installations, for single site installations it is always `1`.
As in a Multisite Network every front- & blog-page cache is stored with the flag `home:?`, you could clear all caches of home pages by clearing the cache with the flag `home:?` or of a specific site by using `home:1`.

Now take this example to a more complex level. Build your own cache flags to group cache entries by specific conditions: 
Imagine you [add a custom flag](#add-cache-flags) to all posts that contain a specific Gutenberg block in the content. Instead of clearing global caches, you can only clear all caches of posts that contain this block by [clearing the cache by this flag](#clear-cache-by-flag).

### Default Cache Flags

The Following cache flags are built-in and used by MilliCache for default cache handling:

| Flag          | WordPress Condition              | Description                                                |
|---------------|----------------------------------|------------------------------------------------------------|
| `site:?:?`    | none                             | Added to all cache entries (Network ID & Site ID)          |
| `home:?`      | `is_front_page()` or `is_home()` | Added to home & blog pages (Site ID)                       |
| `post:?:?`    | `is_singular()`                  | Added to all posts, pages & CPT (Site ID & Post ID)        |
| `feed:?`      | `is_feed()`                      | Added to all feed pages (Site ID)                          |
| `archive:?:?` | `is_post_type_archive()`         | Added to all post type archive pages (Site ID & Post Type) |
| `author:?:?`  | `is_author()`                    | Added to all author pages (Site ID & Author ID)            |

Please note that the `?` in the flags are placeholders for the actual values that will be determined at runtime.
[Go to Debugging Section](#debugging) to learn how you can see the actual flags in the response headers.

### Add Cache Flags

You can add flags to your cached pages by using the `millicache_add_flags` filter. Add the flags wisely as they will increase the number of cache entries and the cache size.

```php
add_filter('millicache_add_flags', function( $flags ) {
    $flags[] = 'my-custom-tag';
    return $flags;
});
```

---
## Clear & Expire Cache

Please note that MilliCache by default expires (not deletes) cache entries that have reached their lifetime (TTL). When a cache entry is expired, it will regenerate in the background at the next request and a stale copy is served to the visitor.
That way, the user will not notice any delay while the cache is regenerating.

Cache entries will expire whenever necessary. For example, when a post is published or updated, the cache entry of the post and the front-page will be cleared. When a site option is updated, all cache entries of the site will be cleared and so on.
MilliCache provides the various methods to clear the cache by flags, URLs, or IDs. You can write your own custom hooks to clear or expire cache whenever you need it.

The parameter `$expire` is optional. If set to `true`, the cache entries will regenerate in the background at the next request. By default, the cache entries will be deleted by using the following methods.
It is up to you to decide whether you want to expire or delete cache entries based on your requirements and how time-critical the cache entries are.

---
### Clear Global Cache

To clear all cache entries, use the following function. This will completely clear the cache. Please note the `$expire` parameter to expire the cache entries instead of deleting them.

```php
/*
 * @param bool $expire Expire cache if set to true, or delete by default. (optional)
 */
Millicache_Engine::clear_cache($expire);
```

---
### Clear Cache by Flag

To clear the cache by specific flags, use the following. This will clear all cache entries flagged with the given flags. Please note the [wildcard support](#wildcards) below.

```php
/*
 * @param string|array $flags Flag or array of flags to clear.
 * @param bool $expire Expire cache if set to true, or delete by default. (optional)
 */
Millicache_Engine::clear_cache_by_flags($flags, $expire);
```

#### Wildcards

Wildcards are supported, so you could clear all cache entries of all sites in a specific network with `site:1:*`.

##### `*`-Wildcards

The `*` can be used to match any number of characters. For example, `site:1:*` would match `site:1:1`, `site:1:2`, `site:1:3`, ..., `site:1:123`, etc.

##### `?`-Wildcards

To match exactly one character use `?`. For example, `site:?:*` would match e.g. `site:1:1`, `site:2:123`, `site:9:123`, etc., but not `site:10:1` or `site:11:123`.

---
### Clear Cache by URL

To clear the cache by a specific URL, use the following function. This will clear the cache entry of the URL.

```php
/*
 * @param string|array $urls URL or array of URLs to clear.
 * @param bool $expire Expire cache if set to true, or delete by default. (optional)
 */
Millicache_Engine::clear_cache_by_urls($urls, $expire);
```
---

### Clear Cache by Post IDs

To clear the cache of specific posts, use the following function. This will clear all cache entries of the post-IDs.

```php
/*
 * @param string|array $post_ids Post ID or array of Post IDs to clear.
 * @param bool $expire Expire cache if set to true, or delete by default. (optional)
 */
Millicache_Engine::clear_cache_by_post_ids($post_ids, $expire);
```
---

### Clear Cache by Site IDs

To clear the cache of specific sites in a WordPress Multisite Network, use the following function. This will clear all cache entries of the site IDs.

```php
/*
 * @param string|array $site_ids Site ID or array of Site IDs to clear.
 * @param bool $expire Expire cache if set to true, or delete by default. (optional)
 */
Millicache_Engine::clear_cache_by_site_ids($site_ids, $expire);
```
---

### Clear Cache by Network IDs

To clear the cache of specific networks in a Multi-Network installation, use the following function. This will clear all cache entries of the network IDs.

```php
/*
 * @param string|array $network_ids Network ID or array of Network IDs to clear.
 * @param bool $expire Expire cache if set to true, or delete by default. (optional)
 */
Millicache_Engine::clear_cache_by_network_ids($network_ids, $expire);
```

## WP CLI

MilliCache provides WP CLI commands to clear the cache and get cache information.

### Get Stats

Get cache statistics such as the number of cache entries and cache size. The optional flag parameter can be used to filter the cache entries by a specific flag.

```bash
$ wp millicache stats [--flag=<flag>]
```

### Clear Cache

Clear the cache by specific flags, post-IDs, URLs, site IDs or network IDs. The optional `--expire` flag can be used to regenerate the cache entries in the background at the next request.

```bash
$ wp millicache clear [--flags=<flags>] [--ids=<post_ids>] [--urls=<urls>] [--sites=<site_ids>] [--networks=<network_ids>] [--expire] 
```
`--flags` [supports wildcards](#wildcards) and can be a single flag or an array of flags separated by a comma as for `--ids`, `--urls`, `--sites` and `--networks`.


## Debugging

To enable debug mode, set `MC_DEBUG` to `true` in your `wp-config.php`. This will add various headers to the response.

```php
define('MC_DEBUG', true);
```

Visit your website in incognito mode or use cURL to inspect the response headers.

```bash
curl -I https://example.com
```

You will see the following headers:

- `X-MilliCache-Flags`: The cache flags of the current request.
- `X-MilliCache-Status`: The cache status of the current request.
- `X-MilliCache-Expires`: The left TTL of the current request.
- `X-MilliCache-Time`: The cache time of the current request.
- `X-MilliCache-Key`: The cache key of the current request.
- `X-MilliCache-Gzip`: If Gzip compression is enabled.

## Hooks & Filters

MilliCache provides several hooks and filters to extend the functionality. Here are some examples:

### `millicache_add_flags`

Add custom flags to the cache entries.

```php
add_filter('millicache_add_flags', function( $flags ) {
    $flags[] = 'my-custom-tag';
    return $flags;
});
```

### `millicache_clear_site_hooks`

Filter the hooks that clear the full cache.

```php
add_filter('millicache_clear_site_hooks', function( $hooks ) {
    
    // Add additional hook with priority that clears the cache
    $hooks['my_custom_hook'] = 10;
    
    return $hooks;
});
```

### `millicache_clear_site_options`

Filter the site options that will clear the full cache whenever changed.

```php
add_filter('millicache_clear_site_options', function( $options ) {
    
    // Add a site option that clears the cache
    $options[] = 'my_custom_option';
    
    return $options;
});
```

## Roadmap

We are planning to implement the following features soon:

- [ ] Settings UI
- [ ] Clear Network Options
- [ ] Cache Preloading
- [ ] CDN Integration

## Credits

MilliCache is inspired by [Page Cache Red by Pressjitsu](https://github.com/pressjitsu/pj-page-cache-red), [Cachify by PluginKollektive](https://github.com/pluginkollektiv/cachify/) and [Cache Enabler by KeyCDN](https://github.com/keycdn/cache-enabler).
