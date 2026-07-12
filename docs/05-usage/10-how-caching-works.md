---
title: 'How Full-Page Caching Works'
post_excerpt: 'MilliCache intercepts requests via the advanced-cache.php drop-in before WordPress loads. Learn the full request lifecycle, cache storage, and stale-while-revalidate flow.'
menu_order: 10
---

# How Caching Works

This guide explains the internals of MilliCache's caching mechanism, from request interception to cache serving.

## The Caching Lifecycle

### 1. Request Interception

When a request arrives, WordPress loads the `advanced-cache.php` drop-in before most of WordPress initializes. MilliCache's Engine starts here:

```
Request → wp-config.php → advanced-cache.php → MilliCache Engine
```

This early interception enables:
- Serving cached content without loading WordPress
- Evaluating rules before plugins load
- Minimal resource usage for cache hits

### 2. Bootstrap Rules Evaluation

Before WordPress loads, MilliCache evaluates **Bootstrap Rules** to determine if caching should proceed:

| Rule             | Condition                 | Result       |
|------------------|---------------------------|--------------|
| WP_CACHE check   | `WP_CACHE !== true`       | Skip caching |
| Request method   | Not GET or HEAD           | Skip caching |
| CLI check        | Running via WP-CLI        | Skip caching |
| REST API         | `REST_REQUEST === true`   | Skip caching |
| XMLRPC           | `XMLRPC_REQUEST === true` | Skip caching |
| File request     | URL matches file pattern  | Skip caching |
| No-cache cookies | Excluded cookie present   | Skip caching |
| No-cache paths   | URL matches excluded path | Skip caching |
| TTL check        | TTL ≤ 0                   | Skip caching |

If any rule triggers cache bypass, MilliCache lets WordPress handle the request normally.

> [!TIP]
> You can customize or add Bootstrap Rules via the [Rules](../04-rules/01-introduction.md) system.

### 3. Cache Lookup

If caching proceeds, MilliCache generates a **cache key** from:

- Request URL (path and query string)
- Cookies (excluding ignored ones)
- Custom unique variables (`MC_CACHE_UNIQUE`)
- Resolved request **buckets**: Short tokens for per-request signals (Authorization is built-in; others can be added via rules)

The key is hashed and used to look up cached content in Redis:

```
Cache Key = hash( URL + filtered_cookies + unique_vars + buckets )
```

Buckets handle dimensions that vary *per request*; `unique_vars` provides static deployment-level isolation. See [`MC_CACHE_BUCKETS`](../02-configuration/02-reference.md#mc_cache_buckets) and [Bucket Extension](../07-developers/02-hooks-filters.md#bucket-extension).

### 4. Cache Hit

If cached content exists and is fresh:

1. Decompress content (if gzip enabled)
2. Send stored HTTP headers
3. Output HTML to browser
4. **Exit immediately** (WordPress never loads)

Response time: typically **5-15ms**.

### 5. Cache Miss

If no cache exists or content is expired:

1. Let WordPress load normally
2. Register WordPress Rules on `plugins_loaded`
3. Start output buffering on `template_redirect` (priority 200)
4. Evaluate WordPress Rules to either cache or bypass
5. Capture the complete response
6. Store in Redis with flags
7. Send response to browser

### 6. Grace Period Serving

If cached content is expired but within the grace period:

1. Serve stale content immediately
2. Mark cache for regeneration
3. Next request triggers actual regeneration
4. Fresh content stored for future requests

This ensures visitors never wait for page generation.

## Cache Storage Structure

Each cache entry is split across two Redis keyspaces: the **request entry** holds per-request metadata; the **output entry** holds the response body and is content-addressable, so identical bodies across variants share storage.

| Keyspace | Key shape                       | Holds                                                                  |
|----------|---------------------------------|------------------------------------------------------------------------|
| Request  | `<prefix>:c:<request_hash>`     | Headers, status, flags, variant meta, `output_ref` (sha1 pointer)      |
| Output   | `<prefix>:o:<output_hash>`      | Response body bytes (compressed if gzip is enabled)                    |
| Refs     | `<prefix>:o:<output_hash>:refs` | Redis SET of request keys referencing this body                        |

The reference SET tracks who's pointing at each body so it can be garbage-collected when the last referrer is removed. Variants that genuinely differ get their own body; identical bodies across variants share one.

### Per-entry fields

| Component    | Description                                            |
|--------------|--------------------------------------------------------|
| `output_ref` | SHA-1 pointer to the body in the output keyspace       |
| `headers`    | HTTP response headers                                  |
| `status`     | HTTP status code                                       |
| `flags`      | Tags for invalidation                                  |
| `variant`    | Differentiating dimensions (cookies, buckets, method)  |
| `updated`    | Timestamp when cached                                  |
| `gzip`       | Whether the body bytes are compressed                  |

### Flags (Tags)

Every cached page is tagged with flags for targeted invalidation:

```
Homepage:     [home, archive:post]
Single post:  [post:123]
Archives:     [archive:category:5, archive:post]
Author:       [archive:author:1]
```

When a post is updated, MilliCache clears all entries matching its flags.

## Cache Flow Diagram

```
┌──────────────────────────────────────────────────────────┐
│                           REQUEST                        │
└──────────────────────────────────────────────────────────┘
                               │
                               ▼
┌──────────────────────────────────────────────────────────┐
│                   advanced-cache.php                     │
│                   (MilliCache Engine)                    │
└──────────────────────────────────────────────────────────┘
                               │
                               ▼
┌──────────────────────────────────────────────────────────┐
│                    Bootstrap Rules                       │
│    [WP_CACHE] [Method] [Cookies] [Paths] [CLI] [REST]    │
└──────────────────────────────────────────────────────────┘
                               │
          ┌────────────────────┴────────────────┐
          │                                     │
      Skip Cache                             Continue
          │                                     │
          ▼                                     ▼
┌───────────────────┐           ┌─────────────────────────────┐
│   Load WordPress  │           │      Generate Cache Key     │
│    (no caching)   │           │      URL + Cookies + ...    │
└───────────────────┘           └─────────────────────────────┘
                                                │
                                                ▼
                                  ┌───────────────────────────┐
                                  │       Redis Lookup        │
                                  └───────────────────────────┘
                                                │
                    ┌───────────────────────────┼───────────────────────────┐
                    │                           │                           │
               Cache HIT                   Cache MISS                  Grace HIT
                    │                           │                           │
                    ▼                           ▼                           ▼
┌─────────────────────────┐    ┌─────────────────────────┐    ┌─────────────────────────┐
│   Decompress (if gzip)  │    │    Load WordPress       │    │   Serve Stale Content   │
│   Send Headers          │    │    Register WP Rules    │    │  Mark for Regeneration  │
│   Output HTML           │    │    Buffer Output        │    └─────────────────────────┘
│   EXIT (~5-15ms)        │    │    Store with Flags     │
└─────────────────────────┘    │    Send Response        │
                               └─────────────────────────┘
```

## Cache Status Values

| Status   | Meaning                                    |
|----------|--------------------------------------------|
| `hit`    | Fresh cached content served                |
| `miss`   | No cache exists, content generated         |
| `bypass` | Caching skipped (rule matched)             |
| `grace`  | Stale content served, regeneration pending |

View status via debug headers:

```
X-MilliCache-Status: hit
```

## What Gets Cached

**Cached:**
- GET and HEAD requests
- 200 OK responses
- Anonymous (logged-out) visitors
- Pages, posts, archives, taxonomies
- Custom post types and taxonomies
- Static front page
- RSS/Atom feeds (unless excluded)

**Not Cached:**
- POST, PUT, DELETE requests
- Logged-in users
- Non-200 responses (404, 500, etc.)
- AJAX requests (`DOING_AJAX`)
- Cron requests (`DOING_CRON`)
- REST API requests (`REST_REQUEST`)
- WP-CLI commands
- Pages with `DONOTCACHEPAGE` constant
- Requests matching excluded cookies/paths
- Responses larger than 5MB (raw size, before compression)

## Cache Key Components

The cache key ensures unique caching per variation:

| Component        | Example              | Effect                            |
|------------------|----------------------|-----------------------------------|
| URL path         | `/blog/hello-world/` | Each URL cached separately        |
| Query string     | `?page=2`            | Pagination cached separately      |
| Filtered cookies | `currency=USD`       | Custom variations (if configured) |
| Unique variables | `device=mobile`      | Custom variations (if configured) |

### Ignored Components

By default, these don't affect the cache key:

- Cookies starting with `_` (analytics)
- Query params starting with `_` or `utm_`
- Fragment identifiers (`#section`)

## Performance Characteristics

| Metric           | Cache Hit  | Cache Miss        |
|------------------|------------|-------------------|
| Response Time    | 5-15ms     | 200-2000ms        |
| PHP Execution    | Minimal    | Full WordPress    |
| Database Queries | 0          | Typically 50-300+ |
| Memory Usage     | Minimal    | Full application  |

## Next Steps

- [Cache Clearing](20-cache-clearing.md) - Understand invalidation
- [Rules](../04-rules/01-introduction.md) - Condition-based caching control
- [Cache Flags](../03-cache-flags/01-introduction.md) - Targeted invalidation
