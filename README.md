# MilliCache: Redis Full Page Cache for WordPress

[![Playwright Tests](https://github.com/MilliPress/MilliCache/actions/workflows/playwright.yml/badge.svg)](https://github.com/MilliPress/MilliCache/actions/workflows/playwright.yml)

Redis is a very well-known and widely used caching solution for object caching in WordPress, 
known for its in-memory storage and rapid performance. 
However, until now, there have been no full-page cache solutions available using Redis for WordPress.

Introducing MilliCache,
a groundbreaking full-page caching solution that uses Redis as its backend to store cached pages. 
Leveraging Redis's in-memory storage capabilities,
MilliCache is designed to be exceptionally fast, reliable, and scalable. 
While it is specifically optimized for WordPress Multisite and Multi-Network installations, 
MilliCache is equally effective in single-site setups, 
providing a versatile and robust caching solution for all types of WordPress environments.

> [!IMPORTANT]
> 
> This plugin is currently under active development and not officially ready for production use.
> There is no settings UI yet, configuration is done via `wp-config.php`.
> Things are subject to change without notice.
> Please report any problems you encounter.

---

## Table of Contents

- [Current Features](#current-features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Cache Flags](#cache-flags)
  - [What are cache flags?](#what-are-cache-flags)
  - [Default Cache Flags](#default-cache-flags)
  - [Adding Cache Flags](#adding-cache-flags)
- [Clear Cache](#clear-cache)
  - [Clear Global Cache](#clear-global-cache)
  - [Clear Cache by Flag](#clear-cache-by-flag)
  - [Clear Cache by URL](#clear-cache-by-url)
  - [Clear Cache by Post IDs](#clear-cache-by-post-ids)
  - [Clear Cache by Site IDs](#clear-cache-by-site-ids)
  - [Clear Cache by Network IDs](#clear-cache-by-network-ids)
- [WP CLI](#wp-cli)
- [Debugging](#debugging)
- [Hooks & Filters](#hooks--filters)
- [Roadmap](#roadmap)
- [Testing](#testing)

## Current Features

- Fast in-memory full page caching
- Gzip cache compression
- [Cache Flagging](#cache-flags) for complex cache logic & [Clearing](#clear-cache)
- [Expired cache handling](#clear-cache) to regenerate the cache in the background
- Optimised for WordPress Multisite & Multi-Network
- Extensible with [Hooks & Filters](#hooks--filters)
- [WP CLI commands](#wp-cli) for cache clearing & stats
- [Debugging](#debugging) headers for cache information
- Scalable: From local Redis instances to clusters
- Compatible with Redis object cache plugins like [WP Redis](https://wordpress.org/plugins/wp-redis/) & [Redis Object Cache](https://wordpress.org/plugins/redis-cache/)
- Compatible with [Redis Server](https://redis.io/), [KeyDB](https://keydb.dev) & [Dragonfly](https://www.dragonflydb.io/)

---

## Requirements

- PHP 7.4 or higher
- Redis Server

Please make sure you have a Redis server installed and running.
If you’re using Ubuntu, you can install Redis using the following commands

### Installing Redis on Ubuntu

```bash
$ sudo apt update
$ sudo apt install redis-server
```

After installing Redis, you can check the status with:

```bash
$ sudo systemctl status redis-server
```

If you are using a different operating system, please refer to the [Redis Documentation](https://redis.io/documentation).

### Redis Configuration

Make sure your Redis server has enough memory allocated to store your cached pages. 
MilliCache compresses cached pages using gzip to reduce memory usage. 
However, you need to keep an eye on your cache size in the Dashboard to increase according to your hit rate. 
We also recommend disabling flushing the Redis cache to disk and the `allkeys-lru` eviction policy 
to ensure the server can make more room for new cached pages by evicting old ones. 
Here is an example excerpt from the redis.conf file:

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

Make sure you have caching enabled in your WordPress installation
by setting the `WP_CACHE` constant to `true` in your `wp-config.php` file:

```php
define('WP_CACHE', true); # Enable WordPress Cache
```

Activate the plugin in your WordPress installation. That's it!

---
## Configuration

MilliCache can be configured by setting constants in your `wp-config.php` file. The following constants are available:

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

MilliCache supports cache flags to group and clear caches based on flags. This is useful for complex cache handling and clearing logics.

### What are cache flags?

A single post or page can have multiple cache entries because the cache keys vary depending on the request. Different cache entries are stored for different requests, such as a request with a different cookie or query string.

```
https://example.org/?p=123
https://example.org/post-slug/
https://example.org/post-slug/page/2/
https://example.org/post-slug/?show_comments=1
```

The cache entries for the above URLs are different because the cache keys are unique based on the request.
However,
they’re related to the same post or page and MilliCache groups these cached entries with a common flag `post:1:123`.
Entries can have multiple flags and can therefore be grouped in different ways.
This makes it possible to delete all cache entries of a specific group by using the flag.
For the example above,
you could use [WP-CLI](#wp-cli) or one of the [clearing functions](#clear-cache) to clear the `post:1:123` group.

Another built-in example is the flag `home:?`, which groups all cached entries of the home & blog pages.
The `?` is a wildcard placeholder for the site ID in multisite installations,
in single site installations it is always `1`.
Since in a multisite network each front & blog page cache is stored with the flag `home:?`,
you could clear all home page caches by clearing the cache with the flag `home:?` or a specific site by using `home:1`.

Now take this example to a more complex level.
Create your own cache flags to group cache entries by specific conditions:
Imagine that you [add a custom flag](#adding-cache-flags) to all posts
that contain a specific Gutenberg block in the content.
Instead of clearing the global cache,
you can just [clear the cache with this flag](#clear-cache-by-flag)
to clear all caches of posts that contain that block.

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

Note that the `?` in the flags are placeholders for the actual values that will be determined at runtime.
[Go to the Debugging section](#debugging) to learn how to see the actual flags in the response headers

### Adding Cache Flags

You can add flags to your cached pages by using the `millicache_add_flags` filter. Add the flags wisely, as they will increase the number of cache entries and the cache size.

```php
add_filter('millicache_add_flags', function( $flags ) {
    $flags[] = 'my-custom-tag';
    return $flags;
});
```

---

## Clear Cache

Please note that by default, MilliCache will expire (not delete) cache entries that’ve reached their lifetime (TTL).
When a cache entry has expired, it will be regenerated in the background on the next request and an outdated copy will be served to the visitor.
This way, the user will not notice any delay while the cache is regenerated.

Cache entries expire whenever necessary.
For example, when a post is published or updated, the cache entry for the post and the front page is cleared.
When a site option is updated, all cache entries of the site are cleared, and so on.
MilliCache provides different methods to clear the cache by flags, URLs, or IDs.
You can write your own custom hooks to clear or expire the cache whenever you need it.

The `$expire` parameter is optional.
If set to `true`, the cache entries will be regenerated in the background on the next request.
By default, the cache entries are deleted using the following methods.
It is up to you to decide whether to expire
or delete cache entries based on your requirements and how time-critical the cache entries are.

---
### Clear Global Cache

To clear all cache entries, use the following function. This will clear the cache completely. Note the `$expire` parameter to expire the cache entries instead of deleting them.

```php
/*
 * @param bool $expire Expire cache if set to true, or delete by default. (optional)
 */
\MilliCache\Engine::clear_cache($expire);
```

---
### Clear Cache by Flag

To clear the cache by specific flags, use the following.
This will clear all cache entries marked with the given flags.
Please note the [wildcard support](#wildcards) below.

```php
/*
 * @param string|array $flags Flag or array of flags to clear.
 * @param bool $expire Expire cache if set to true, or delete by default. (optional)
 */
\MilliCache\Engine::clear_cache_by_flags($flags, $expire);
```

#### Wildcards

Wildcards are supported, so you could use `site:1:*` to clear the cache of all sites in a particular network.

##### `*` Wildcards

The `*` can be used to match any number of characters.
For example, `site:1:*` would match `site:1:1`, `site:1:2`, `site:1:3`, ..., `site:1:123`, etc.

##### `?` Wildcards

To match exactly one character, use `?`.
For example, `site:?:*` would match `site:1:1`, `site:2:123`, `site:9:123`, etc., but not `site:10:1` or `site:11:123`.

---
### Clear Cache by URL

To clear the cache by a specific URL, use the following function. This will clear the cache entry for the URL.

```php
/*
 * @param string|array $urls URL or array of URLs to clear.
 * @param bool $expire Expire cache if set to true, or delete by default. (optional)
 */
\MilliCache\Engine::clear_cache_by_urls($urls, $expire);
```
---

### Clear Cache by Post IDs

To clear the cache of specific posts, use the following function. This will clear all cache entries for the post-IDs.

```php
/*
 * @param int|array $post_ids Post ID or array of Post IDs to clear.
 * @param bool $expire Expire cache if set to true, or delete by default. (optional)
 */
\MilliCache\Engine::clear_cache_by_post_ids($post_ids, $expire);
```
---

### Clear Cache by Site IDs

To clear the cache of specific sites in a WordPress multisite network, use the following function. This will clear all cache entries for the site IDs.

```php
/*
 * @param int|array $site_ids Site ID or array of Site IDs to clear.
 * @param bool $expire Expire cache if set to true, or delete by default. (optional)
 */
\MilliCache\Engine::clear_cache_by_site_ids($site_ids, $expire);
```
---

### Clear Cache by Network IDs

To clear the cache of specific networks in a multi-network installation, use the following function. This will clear all cache entries for the network IDs.

```php
/*
 * @param int|array $network_ids Network ID or array of Network IDs to clear.
 * @param bool $expire Expire cache if set to true, or delete by default. (optional)
 */
\MilliCache\Engine::clear_cache_by_network_ids($network_ids, $expire);
```

## WP CLI

MilliCache provides WP CLI commands to clear the cache and get cache information.

### Get Stats

Get cache statistics such as the number of cache entries and cache size. The optional flag parameter can be used to filter the cache entries by a specific flag.

```bash
$ wp millicache stats [--flag="<flag>"]
```

### Clear Cache

Clear the cache for specific flags, post-IDs, URLs, site IDs, or network IDs.
The optional `--expire` flag can be used to regenerate the cache entries in the background on the next request.

```bash
$ wp millicache clear [--flags="<flags>"] [--ids="<post_ids>"] [--urls="<urls>"] [--sites="<site_ids>"] [--networks="<network_ids>"] [--expire] 
```
`--flags` [supports wildcards](#wildcards) and can be a single flag or an array of flags separated by a comma as for `--ids`, `--urls`, `--sites` and `--networks`.


## Debugging

To enable debug mode, set `MC_DEBUG` to `true` in your `wp-config.php`. This will add several headers to the response.

```php
define('MC_DEBUG', true);
```

Visit your site in incognito mode or use cURL to inspect the response headers.

```bash
curl -I https://example.com
```

You will see the following headers:

- `X-MilliCache-Flags`: The cache flags for the current request.
- `X-MilliCache-Status`: The cache status of the current request.
- `X-MilliCache-Expires`: The left TTL of the current request.
- `X-MilliCache-Time`: The cache time for the current request.
- `X-MilliCache-Key`: The cache key of the current request.
- `X-MilliCache-Gzip`: If Gzip compression is enabled.

## Hooks & Filters

MilliCache provides several hooks and filters to extend its functionality. Here are some examples:

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

We plan to implement the following features soon:

- [ ] Settings UI
- [ ] Cache Preloading
- [ ] CDN integration

## Testing

Testing is automated with tools such as PHPUnit, PHPStan, and PHP CodeSniffer.
For e2e-tests,
we use Playwright running on [@wordpress/env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/).
That makes it basic for you to test the plugin in a real WordPress environment.
To play with MilliCache or to run the tests, you need to have Docker and Node.js installed.

### Start the Test Environment

The following commands will start the WordPress environment with MilliCache Plugin installed under `http://localhost:8888`.

> [!INFO]
>
> Another WordPress environment will be available under `http://localhost:8889` for the Playwright tests. 
> Please note that this instance is configured for testing, e.g. with 5-second Cache-TTL.
> So better to play with the first instance.

```bash
$ npm install
$ npm run env:start
```

You can log in with `admin` and `password`. 

### Run the e2e-Tests

To run the Playwright tests under `http://localhost:8889`:

```bash
$ npm run env:test
```
### Stop the Test Environment

To stop the environments:

```bash
$ npm run env:stop
```

### Destroy the Test Environment

To destroy and remove the environments:

```bash
$ npm run env:destroy
```

### Further useful commands

- `npm run env:cli wp ...` - Run WP-CLI command at the WordPress environment. E.g. `npm run env:cli wp millicache stats`
- `npm run env:redis-cli` - Open the Redis CLI
- `npm run env:reset` - Reset the WordPress environments & start from scratch

## Credits

MilliCache is inspired by [Page Cache Red by Pressjitsu](https://github.com/pressjitsu/pj-page-cache-red), [Cachify by PluginKollektive](https://github.com/pluginkollektiv/cachify/) and [Cache Enabler by KeyCDN](https://github.com/keycdn/cache-enabler).
