# MilliCache: Redis Full Page Cache for WordPress

[![e2e-Tests](https://github.com/MilliPress/MilliCache/actions/workflows/playwright.yml/badge.svg)](https://github.com/MilliPress/MilliCache/actions/workflows/playwright.yml)

**MilliCache is a fast & flexible full-page caching solution for WordPress**. 
Unlike other caching plugins, it uses Redis as its backend to store cached pages. 
By leveraging Redis's in-memory storage capabilities, MilliCache is exceptionally fast, reliable, and scalable. 
Moreover, it allows for highly efficient and targeted cache invalidation, 
greatly increasing both efficiency and flexibility. 
This is especially important for the future of WordPress, 
as it enables more dynamic content and complex caching strategies — for example, with the use of the Block Editor.

Optimized for both WordPress Multisite and Single Site setups, 
MilliCache provides a versatile and robust caching solution for all types of WordPress environments.

> [!IMPORTANT]
>
> This plugin is currently under active development and not officially ready for production use.
> There is no settings UI yet; configuration is done via `wp-config.php`.
> Things may change without notice.
> Please [report](https://github.com/MilliPress/MilliCache/issues/new) any problems you encounter.

---

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Cache Flags](#cache-flags)
- [Clearing Cache](#clearing-cache)
- [WP-CLI Commands](#wp-cli-commands)
- [Debugging](#debugging)
- [Hooks & Filters](#hooks--filters)
- [Roadmap](#roadmap)
- [Testing](#testing)

---

## Features

- **Lightning Fast**: In-memory full-page caching using Redis.
- **[Cache Flagging](#cache-flags)**: High efficiency by supporting complex cache logic and selective clearing.
- **[Expired Cache Handling](#clearing-cache)**: Regenerates cache in the background.
- **Multisite Optimized**: Ideal for WordPress Multisite and Multi-Network.
- **Extensible**: Provides various [hooks & filters](#hooks--filters).
- **Gzip Compression**: Compresses cache to reduce memory usage.
- **[WP-CLI Commands](#wp-cli-commands)**: Manage cache via command line.
- **[Debugging](#debugging)**: Provides cache information in headers.
- **Scalable**: Works with Redis clusters.
- **Object Cache**: Works with Redis object cache plugins like [WP Redis](https://wordpress.org/plugins/wp-redis/) & [Redis Object Cache](https://wordpress.org/plugins/redis-cache/).
- **Compatible**: Work with [Redis Server](https://redis.io/), [KeyDB](https://keydb.dev) & [Dragonfly](https://www.dragonflydb.io/).

---

## Requirements

- PHP 7.4 or higher
- Redis server installed and running

### Installing Redis

For installation instructions,
refer to the [Redis Documentation](https://redis.io/docs/latest/operate/oss_and_stack/install/).

### Redis Configuration

Make sure your Redis server has enough memory allocated to store your cached pages.
MilliCache compresses cached pages using gzip to reduce memory usage.
However, you need to keep an eye on your cache size in the Dashboard to increase according to your hit rate.
We also recommend disabling flushing the Redis cache to disk and the `allkeys-lru` eviction policy
to ensure the server can make more room for new cached pages by evicting old ones.
Here is an example of the redis.conf file:

```
# Set maximum memory for Redis e.g. to 16 megabytes
maxmemory 16m

# Set eviction policy to remove less recently used keys first
maxmemory-policy allkeys-lru
```

Remember to restart the Redis server after making changes to the configuration file.

---

## Installation

1. **Install via**:

- **ZIP-File**:
   - Download the [latest release](https://github.com/millipress/millicache/releases/latest).
   - Upload the ZIP file in your WordPress admin area under `Plugins > Add New > Upload Plugin`.

- **Composer**:

   ```bash
   $ composer config repositories.millicache vcs https://github.com/millipress/millicache
   $ composer require millipress/millicache
   ```

2. **Activate the plugin** in your WordPress installation.

3. **[Configure the plugin](#configuration)** in your Dashboard `Settings -> MilliCache` or by defining constants in your `wp-config.php`.

4. **Enable WordPress caching** by adding to `wp-config.php`:

   ```php
   define('WP_CACHE', true);
   ```

---

## Configuration

Configure MilliCache either in your Dashboard `Settings -> MilliCache` 
or by setting constants in your `wp-config.php` file.
You can combine both methods, **the constants overwrite the settings** with higher priority.

```php
# Optional: Set Settings with Constants
define('MC_REDIS_HOST', '127.0.0.1');
define('MC_REDIS_PORT', 6379);
```

### General Configuration

| Constant                         | Description                                          | Default                             |
|----------------------------------|------------------------------------------------------|-------------------------------------|
| `MC_CACHE_DEBUG`                 | Enable Debugging                                     | `false`                             |
| `MC_CACHE_GZIP`                  | Enable Gzip Compression                              | `true`                              |
| `MC_CACHE_TTL`                   | Default Cache TTL                                    | `DAY_IN_SECONDS`                    |
| `MC_CACHE_MAX_TTL`               | Max TTL for Stale Cache Entries                      | `MONTH_IN_SECONDS`                  |
| `MC_CACHE_IGNORE_COOKIES`        | Cookies that are ignored/stripped from the request   | `[]`                                |
| `MC_CACHE_NOCACHE_COOKIES`       | Cookies which avoid caching                          | `['comment_author']`                |
| `MC_CACHE_IGNORE_REQUEST_KEYS`   | Request keys that are ignored                        | `['utm_source', 'utm_medium', ...]` |
| `MC_CACHE_SHOULD_CACHE_CALLBACK` | External callback to append custom cache conditions  | `''`                                |
| `MC_CACHE_UNIQUE`                | Variables that make the request & cache entry unique | `[]`                                |

### Redis Connection Configuration

| Constant              | Description                       | Default            |
|-----------------------|-----------------------------------|--------------------|
| `MC_REDIS_HOST`       | Redis Host                        | `127.0.0.1`        |
| `MC_REDIS_PORT`       | Redis Port                        | `6379`             |
| `MC_REDIS_PASSWORD`   | Redis Password                    | `''`               |
| `MC_REDIS_DB`         | Redis Database                    | `0`                |
| `MC_REDIS_PERSISTENT` | Redis Persistent Connection       | `true`             |
| `MC_REDIS_PREFIX`     | Redis Key Prefix                  | `mll`              |

---

## Cache Flags

MilliCache supports cache flags to group and clear caches based on flags, useful for complex cache handling.

### What are Cache Flags?

A single post or page can generate multiple cache entries because the cache keys change based on the request details,
such as different cookies or query parameters. 
For example, the following URLs, although they refer to the same content, will have separate cache entries:

- `https://example.org/?p=123`
- `https://example.org/post-slug/`
- `https://example.org/post-slug/page/2/`
- `https://example.org/post-slug/?show_comments=1`

These different cache entries are necessary because each URL might serve slightly different content. 
However, they are all related to the same post or page. 
To manage these related entries efficiently, MilliCache groups them using **cache flags**. 
In this case, all entries might share a flag like `post:123`.

**Cache flags allow you to:**

- **Group related cache entries** under a common identifier.
- **Assign multiple flags** to a cache entry for flexible grouping.
- **Clear all related cache entries** at once by targeting a specific flag.

For example, to clear all cache entries related to a specific post,
you can use the flag `post:123` with [WP-CLI commands](#wp-cli-commands) or MilliCache's [clearing functions](#clearing-cache).

### Built-in Flags

> **Note:** Flag prefixes are only added in multisite and multinetwork installations. In a standard single site installation, flags are used without prefixes.
>
> - **Single site:** `post:123`
> - **Multisite:** `1:post:123` (where `1` is the site ID)
> - **Multinetwork:** `1:2:post:123` (where `1` is the network ID and `2` is the site ID)

The basic built-in flags are:

| Flag          | Description                                                |
|---------------|------------------------------------------------------------|
| `home`        | Added to home & blog pages                                 |
| `post:?`      | Added to all posts, pages & CPT (Post ID)                  |
| `feed`        | Added to all feed pages                                    |
| `archive:?`   | Added to all post type archive pages (Post Type)           |
| `author:?`    | Added to all author pages (Author ID)                      |

### Adding Custom Cache Flags:

You can define your own cache flags to group entries based on specific conditions 
by using the `millicache_add_flags` filter.
For instance, if you want to group all posts containing a particular Gutenberg block:

1. **Add a Custom Flag:**
   ```php
   add_filter('millicache_add_flags', function($flags) {
       if (has_block('my-custom/block')) {
           $flags[] = 'block:my-custom/block';
       }
       return $flags;
   });
   ```

2. **Clear the Cache Using the Custom Flag:**
   Instead of clearing the entire cache, you can clear only the entries with the custom flag:
   ```bash
   # Single site
   wp millicache clear --flags="block:my-custom/block"
   
   # Multisite (with site ID 1)
   wp millicache clear --flags="1:block:my-custom/block"
   ```

This way, you efficiently manage your cache by only refreshing the parts that need updating, saving resources, 
and improving performance.

---

## Clearing Cache

By default, MilliCache expires (not deletes) cache entries when they reach their Time-To-Live (TTL).
When a cache entry expires, 
MilliCache serves the outdated copy to the visitor while regenerating the cache in the background. 
This ensures users experience no delay during cache regeneration.

Cache entries expire whenever necessary. 
For example, when a post is published or updated, the cache entry for the post and the front page is cleared. 
When a site option is updated, all cache entries of the site are cleared, and so on. 

MilliCache offers various methods to clear the cache by flags, URLs, or IDs.
You can also create custom hooks to clear or expire the cache whenever needed.

The `$expire` parameter in cache-clearing methods is optional:

- **Set to `true`**: Cache entries expire and regenerate in the background on the next request.
- **Default (`false`)**: Cache entries are deleted immediately.

Decide whether to expire or delete cache entries based on your requirements and how time-critical the content is.

- ### Clear Global Cache

  To clear all cache entries.
  
  ```php
  /*
   * @param bool $expire Expire cache if set to true, or delete by default. (optional)
   */
  \MilliCache\Engine::clear_cache($expire);
  ```

- ### Clear Cache by Flag

  To clear the cache by specific flags.
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
  
  - ##### `*`-Wildcards
  
    The `*` can be used to match any number of characters.
    For example, `site:1:*` would match `site:1:1`, `site:1:2`, `site:1:3`, ..., `site:1:123`, etc.
  
  - ##### `?`-Wildcards
  
    To match exactly one character, use `?`.
    For example, `site:?:*` would match `site:1:1`, `site:2:123`, 
  - `site:9:123`, etc., but **not** `site:10:1` or `site:11:123`.

- ### Clear Cache by URL

  To clear the cache by a specific URL.
  
  ```php
  /*
   * @param string|array $urls URL or array of URLs to clear.
   * @param bool $expire Expire cache if set to true, or delete by default. (optional)
   */
  \MilliCache\Engine::clear_cache_by_urls($urls, $expire);
  ```

- ### Clear Cache by Post IDs

  To clear the cache of specific posts, pages or CPTs.
  
  ```php
  /*
   * @param int|array $post_ids Post ID or array of Post IDs to clear.
   * @param bool $expire Expire cache if set to true, or delete by default. (optional)
   */
  \MilliCache\Engine::clear_cache_by_post_ids($post_ids, $expire);
  ```

- ### Clear Cache by Site IDs
  
  To clear the cache of specific sites in a WordPress Multisite network.
  
  ```php
    /*
     * @param int|array $site_ids Site ID or array of Site IDs to clear.
     * @param bool $expire Expire cache if set to true, or delete by default. (optional)
     */
    \MilliCache\Engine::clear_cache_by_site_ids($site_ids, $expire);
  ```


- ### Clear Cache by Network IDs

  To clear the cache of specific networks in a multi-network installation.
  
  ```php
  /*
   * @param int|array $network_ids Network ID or array of Network IDs to clear.
   * @param bool $expire Expire cache if set to true, or delete by default. (optional)
   */
  \MilliCache\Engine::clear_cache_by_network_ids($network_ids, $expire);
  ```

---

## WP-CLI Commands

### Get Stats

Get cache statistics such as the number of cache entries and cache size. 
The optional flag parameter can be used to filter the cache entries by a specific flag.

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

---

## Debugging

Enable debug mode by setting `MC_DEBUG` to `true` in your `wp-config.php`. This adds headers to the response:

```php
define('MC_DEBUG', true);
```

Inspect response headers using cURL or browser developer tools:

```bash
curl -I https://example.com
```

Headers include:

- `X-MilliCache-Flags`: The cache flags for the current request.
- `X-MilliCache-Status`: The cache status of the current request.
- `X-MilliCache-Expires`: The left TTL of the current request.
- `X-MilliCache-Time`: The cache time for the current request.
- `X-MilliCache-Key`: The cache key of the current request.
- `X-MilliCache-Gzip`: If Gzip compression is enabled.

---

## Hooks & Filters

MilliCache provides several hooks and filters to extend functionality. Some examples:

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

---

## Roadmap

Planned features:

- [ ] Settings UI
- [ ] Cache Preloading
- [ ] CDN Integration

---

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
> Please note that this instance is configured for testing, e.g., with 5-second Cache-TTL.
> So better to play with the first instance.

```bash
$ npm install
$ npm run env:start
```

You can log in with `admin` and `password`.

### Run the e2e-Tests

To run the Playwright tests under `http://localhost:8889`:

```bash
$ npm run env:e2e
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


---

## Credits

MilliCache is inspired by:

- [Page Cache Red by Pressjitsu](https://github.com/pressjitsu/pj-page-cache-red)
- [Cachify by PluginKollektive](https://github.com/pluginkollektiv/cachify/)
- [Cache Enabler by KeyCDN](https://github.com/keycdn/cache-enabler)
