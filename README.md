# MilliCache: High-Performance Full Page Cache for WordPress

[![e2e-Tests](https://github.com/MilliPress/MilliCache/actions/workflows/playwright.yml/badge.svg)](https://github.com/MilliPress/MilliCache/actions/workflows/playwright.yml)

**MilliCache is a fast & flexible full-page caching solution for WordPress**.
It uses in-memory key-value stores like Redis, Valkey, KeyDB, or Dragonfly as its backend to store cached pages.
By leveraging in-memory storage capabilities, MilliCache is exceptionally fast, reliable, and scalable.
Moreover, it allows for highly efficient and targeted cache invalidation,
greatly increasing both efficiency and flexibility.
This is especially important for the future of WordPress,
as it enables more dynamic content and complex caching strategies — for example, with the use of the Block Editor.

Optimized for both WordPress Multisite and Single Site setups, 
MilliCache provides a versatile and robust caching solution for all types of WordPress environments.

> [!IMPORTANT]
>
> This plugin is currently in Release Candidate stage and approaching stable release.
> While suitable for testing in production-like environments, please exercise caution and
> [report](https://github.com/MilliPress/MilliCache/issues/new) any problems you encounter.

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

- **Lightning Fast**: In-memory full-page caching using Redis or an alternative.
- **Settings UI**: Easy configuration and cache management through the WordPress admin.
- **[Cache Flagging](#cache-flags)**: High efficiency by supporting complex cache logic and selective clearing.
- **[Expired Cache Handling](#clearing-cache)**: Regenerates cache in the background.
- **Multisite Optimized**: Ideal for WordPress Multisite and Multi-Network.
- **Extensible**: Provides various [hooks & filters](#hooks--filters).
- **Gzip Compression**: Compresses cache to reduce memory usage.
- **[WP-CLI Commands](#wp-cli-commands)**: Manage cache via command line.
- **[Debugging](#debugging)**: Provides cache information in headers.
- **Scalable**: Works with server clusters.
- **Object Cache**: Works with Redis object cache plugins like [WP Redis](https://wordpress.org/plugins/wp-redis/) & [Redis Object Cache](https://wordpress.org/plugins/redis-cache/).
- **Flexible Storage**: Compatible with [Redis Server](https://redis.io/), [Valkey](https://valkey.io/), [KeyDB](https://keydb.dev) & [Dragonfly](https://www.dragonflydb.io/).

---

## Requirements

- PHP 7.4 or higher (PHP 8.x recommended for performance)
- A compatible in-memory storage server, either installed locally or accessible via network:
    - [Redis](https://redis.io/) (traditional option)
    - [Valkey](https://valkey.io) (Redis fork with enhanced features)
    - [KeyDB](https://keydb.dev) (Redis-compatible with multi-threading)
    - [Dragonfly](https://www.dragonflydb.io/) (modern Redis alternative)

### Storage Server Configuration

Make sure your storage server has enough memory allocated to store your cached pages.
MilliCache compresses cached pages using gzip to reduce memory usage.
However, you need to keep an eye on your cache size in the Dashboard to increase according to your hit rate.

For Redis, Valkey, and KeyDB, we recommend disabling persistence to disk and using the `allkeys-lru` eviction policy
to ensure the server can make more room for new cached pages by evicting old ones.
Here is an example configuration:


```
# Set maximum memory e.g. to 16 megabytes
maxmemory 16m

# Set eviction policy to remove less recently used keys first
maxmemory-policy allkeys-lru
```

Remember to restart your storage server after making changes to the configuration file.

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
define('MC_STORAGE_HOST', '127.0.0.1');
define('MC_STORAGE_PORT', 6379);
```

### General Configuration

| Constant                         | Description                                          | Default                |
|----------------------------------|------------------------------------------------------|------------------------|
| `MC_CACHE_DEBUG`                 | Enable Debugging                                     | `false`                |
| `MC_CACHE_GZIP`                  | Enable Gzip Compression                              | `true`                 |
| `MC_CACHE_TTL`                   | Default Cache TTL (Time To Live)                     | `DAY_IN_SECONDS`       |
| `MC_CACHE_GRACE`                 | Grace Period of stale cache for regenerating content | `MONTH_IN_SECONDS`     |
| `MC_CACHE_IGNORE_COOKIES`        | Cookies that are ignored/stripped from the request   | `[]`                   |
| `MC_CACHE_NOCACHE_COOKIES`       | Cookies which avoid caching                          | `['comment_author']`   |
| `MC_CACHE_IGNORE_REQUEST_KEYS`   | Request keys that are ignored                        | `['_*', 'utm_*', ...]` |
| `MC_CACHE_SHOULD_CACHE_CALLBACK` | External callback to append custom cache conditions  | `''`                   |
| `MC_CACHE_UNIQUE`                | Variables that make the request & cache entry unique | `[]`                   |

> [!NOTE]
> MilliCache supports wildcard patterns for cookie and request key configurations, allowing for more flexible cache control: `['cookie_*', 'coo*_*', 'request_*']`.

### Storage Server Connection Configuration

| Constant              | Description                          | Default            |
|-----------------------|--------------------------------------|--------------------|
| `MC_STORAGE_HOST`       | Storage Server Host                  | `127.0.0.1`        |
| `MC_STORAGE_PORT`       | Storage Server Port                  | `6379`             |
| `MC_STORAGE_PASSWORD`   | Storage Server Password              | `''`               |
| `MC_STORAGE_DB`         | Storage Server Database              | `0`                |
| `MC_STORAGE_PERSISTENT` | Storage Server Persistent Connection | `true`             |
| `MC_STORAGE_PREFIX`     | Storage Server Key Prefix            | `mll`              |

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

> **Important:** Flag prefixes are only added in multisite and multinetwork installations. Here's how the same flag appears in different WordPress setups:
>
> | Installation Type | Example Flag Format             | Example                                |
> |-------------------|---------------------------------|----------------------------------------|
> | Single site       | `flag:value`                    | `post:123`, `home`, `site`             |
> | Multisite         | `site_id:flag:value`            | `1:post:123`, `2:home`, `3:site`       |
> | Multinetwork      | `network_id:site_id:flag:value` | `1:2:post:123`, `1:3:home`, `2:1:site` |
>
> When using WP-CLI or API functions, you must include these prefixes appropriately for multisite/multinetwork environments.

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
by using the `millicache_custom_flags` filter.
For instance, if you want to group all posts containing a particular Gutenberg block:

1. **Add a Custom Flag:**
   ```php
   add_filter('millicache_custom_flags', function($flags) {
       if (has_block('my-custom/block')) {
           $flags[] = 'block:my-custom/block';
       }
       return $flags;
   });
   ```

2. **Clear the Cache Using the Custom Flag:**
   Instead of clearing the entire cache, you can clear only the entries with the custom flag:
   ```bash
   # Single site - clear all pages with the custom block
   wp millicache clear --flags="block:my-custom/block"
   
   # Single site - clear all posts and home page
   wp millicache clear --flags="post:123,home"
   
   # Multisite - clear all posts on site 1
   wp millicache clear --flags="1:post:*"
   
   # Multisite - clear home pages across all sites
   wp millicache clear --flags="*:home"
   
   # Multinetwork - clear all content on network 2, site 3
   wp millicache clear --flags="2:3:*"
   ```

This way, you efficiently manage your cache by only refreshing the parts that need updating, saving resources, 
and improving performance.

---

## Clearing Cache

By default, MilliCache uses two key time settings to control cache behavior:

1. **TTL (Time-To-Live)**: Period when cache is considered fresh
2. **Grace Period**: Additional time after TTL expires when stale cache can still be served

When a cache entry reaches its TTL, it becomes "stale" but isn't immediately deleted. Instead, MilliCache serves the stale copy to visitors while regenerating a fresh cache in the background. This ensures users experience no delay during cache regeneration.

Cache entries expire whenever necessary.
For example, when a post is published or updated, the cache entry for the post and the front page is cleared.
When a site option is updated, all cache entries of the site are cleared, and so on.

### Using the Settings UI

The MilliCache Settings UI provides a user-friendly way to clear cache:

1. Navigate to `Settings -> MilliCache` in your WordPress admin
2. In the "Cache Management" section:
   - Use "Clear All Cache" to remove all cached entries
   - Use "Clear By Flag" to selectively clear cache by specific flags
   - View the current cache statistics (size, entries count)

This interface makes it easy to manage cache without writing code or running CLI commands.

### Using the Adminbar

MilliCache integrates with the WordPress admin bar for quick cache management:

1. While logged in as an administrator, the admin bar displays a "MilliCache" menu item
2. Click on the menu to reveal options:
   - **Clear All Cache**: Immediately clear all cached content 
   - **Clear by Flag**: Expand to see common flag options (Home, Current Page, etc.)
   - **Settings**: Quick access to the MilliCache settings page

This provides convenient access to cache management functions from any page of your site without having to navigate to the settings page.

### Using PHP Functions

MilliCache offers various methods to clear the cache by flags, URLs, or IDs programmatically.
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

  MilliCache supports wildcards when clearing cache by flags.
  The way these wildcards are used depends on your WordPress installation type.
  
  - ##### `*`-Wildcards
  
    The `*` can be used to match any number of characters. Here are examples of different WordPress installations:
    
    | Installation Type | Example Pattern | What It Matches                                     |
    |-------------------|-----------------|-----------------------------------------------------|
    | Single site       | `post:*`        | All posts (matches `post:1`, `post:123`, etc.)      |
    | Single site       | `archive:*`     | All archive pages (matches `archive:post`, etc.)    |
    | Multisite         | `1:post:*`      | All posts on site 1                                 |
    | Multisite         | `*:home`        | Home page on all sites (matches `1:home`, `2:home`) |
    | Multinetwork      | `1:*:post:*`    | All posts on all sites in network 1                 |
    | Multinetwork      | `*:*:home`      | Home pages across all sites in all networks         |
  
  - ##### `?`-Wildcards
  
    The `?` matches exactly one character:
    
    | Installation Type | Example Pattern | What It Matches                                                              |
    |-------------------|-----------------|------------------------------------------------------------------------------|
    | Single site       | `post:?`        | Posts with single-digit IDs (1-9)                                            |
    | Multisite         | `?:home`        | Home pages on sites with single-digit IDs                                    |
    | Multinetwork      | `?:?:*`         | All content on sites with single-digit IDs in networks with single-digit IDs |

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

  To clear the cache of specific posts, pages, or CPTs.

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

- ### Clear Cache by Targets

  A convenient method to clear the cache based on different target types in a single call.
  This method automatically determines the target type by its format (URL, numeric Post ID, or cache flag).

  ```php
  /*
   * @param string|array $targets String or array of targets to clear:
   *   - URLs (any valid URL that starts with your site URL)
   *   - Post IDs (any numeric value will be treated as a post-ID)
   *   - Cache flags (any non-numeric, non-URL string will be treated as a flag)
   * @param bool $expire Expire cache if set to true, or delete by default. (optional)
   * @return void
   */
  \MilliCache\Engine::clear_cache_by_targets($targets, $expire);
  ```
  
  Example usage:

  ```php
  // Clear cache for multiple types of targets at once
  \MilliCache\Engine::clear_cache_by_targets([
      'home',                             // Treated as a flag
      'post:123',                         // Treated as a flag
      123,                                // Treated as a post-ID
      'https://example.com/special-page/' // Treated as a URL
  ]);
  
  // If no targets are provided, the entire site cache will be cleared
  \MilliCache\Engine::clear_cache_by_targets([]);
  ```
  
  In multisite installations,
  the method automatically limits flag-based clearing to the current site
  when called from a non-network admin context.

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

Enable debug mode by setting `MC_CACHE_DEBUG` to `true` in your `wp-config.php`. This adds headers to the response:

```php
define('MC_CACHE_DEBUG', true);
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

### `millicache_should_cache_request`

If the current request should be cached.

```php
add_filter(`millicache_should_cache_request`, function( $should_cache ) {
    // E.g. do cache 404 pages
    if ( is_404() ) {
        return true;
    }

    return $should_cache;
});
```

### `millicache_custom_flags`

Add custom flags to the cache entries.

```php
add_filter('millicache_custom_flags', function( $flags ) {
    $flags[] = 'my-custom-tag';
    return $flags;
});
```

### `millicache_clear_site_hooks`

Filter the hooks that clear the full cache.

```php
add_filter('millicache_clear_site_hooks', function( $hooks ) {
    
    // Add a custom hook with priority that clears the cache
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

## Testing

Testing is automated with tools such as PHPUnit, PHPStan, and PHP CodeSniffer.
For e2e-tests,
we use Playwright running on [@wordpress/env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/).
That makes it basic for you to test the plugin in a real WordPress environment.
To play with MilliCache or to run the tests, you need to have Docker and Node.js installed.

### Start the Test Environment

The following commands will start the WordPress environment with MilliCache Plugin installed under `http://localhost:8888`.

> [!NOTE]
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
- `npm run env:reset` - Reset the WordPress environments and start from scratch


---

## Credits

MilliCache is inspired by:

- [Page Cache Red by Pressjitsu](https://github.com/pressjitsu/pj-page-cache-red)
- [Cachify by PluginKollektive](https://github.com/pluginkollektiv/cachify/)
- [Cache Enabler by KeyCDN](https://github.com/keycdn/cache-enabler)
