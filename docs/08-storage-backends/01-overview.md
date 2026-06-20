---
title: 'Storage Backends: Redis, ValKey, KeyDB & Dragonfly'
post_excerpt: 'MilliCache works with Redis, ValKey, KeyDB, and Dragonfly. Compare backends, configure connections, set memory limits, and choose the right server for your WordPress site.'
menu_order: 10
---

# Storage Backends

MilliCache works with any Redis-compatible server. This guide covers supported backends, configuration, and recommendations.

## Supported Backends

| Backend | License | Key Feature | Best For |
|---------|---------|-------------|----------|
| **Redis** | RSALv2 | Most popular, proven | Most deployments |
| **ValKey** | BSD-3 | Open-source Redis fork | License-concerned users |
| **KeyDB** | BSD-3 | Multithreaded | High-throughput needs |
| **Dragonfly** | BSL | Memory efficient | Large cache requirements |

All backends use the same protocol and configuration. Your choice depends on licensing preferences, performance needs, and hosting availability.

## Quick Comparison

### Redis

The original in-memory data store. Most documentation, hosting support, and community resources.

- **Pros:** Battle-tested, extensive documentation, widest hosting support
- **Cons:** RSALv2 license may concern some users
- **Install:** [redis.io/docs/install](https://redis.io/docs/install/)

### ValKey

Linux Foundation fork of Redis, fully open-source under BSD-3 license.

- **Pros:** Fully open-source, active development, drop-in Redis replacement
- **Cons:** Newer project, less hosting support currently
- **Install:** [valkey.io/docs](https://valkey.io/docs/)

### KeyDB

Multithreaded Redis fork with improved performance on multi-core systems.

- **Pros:** Higher throughput, MVCC for better concurrency
- **Cons:** Less widespread than Redis
- **Install:** [docs.keydb.dev](https://docs.keydb.dev/)

### Dragonfly

Modern Redis alternative with significantly better memory efficiency.

- **Pros:** Uses less memory for same data, faster on large datasets
- **Cons:** Newer, Business Source License
- **Install:** [dragonflydb.io/docs](https://dragonflydb.io/docs/)

## MilliCache Configuration

Connection settings are the same for all backends.

### Basic Connection

```php
// wp-config.php
define( 'WP_CACHE', true );

define( 'MC_STORAGE_HOST', '127.0.0.1' );
define( 'MC_STORAGE_PORT', 6379 );
```

### With Authentication

```php
define( 'MC_STORAGE_HOST', 'redis.example.com' );
define( 'MC_STORAGE_PORT', 6379 );
define( 'MC_STORAGE_USERNAME', 'your-username' );
define( 'MC_STORAGE_PASSWORD', 'your-password' );
```

### Using Unix Socket

```php
define( 'MC_STORAGE_HOST', '/var/run/redis/redis.sock' );
define( 'MC_STORAGE_PORT', 0 );  // Ignored for sockets
```

### With TLS (e.g. AWS ElastiCache)

```php
define( 'MC_STORAGE_HOST', 'tls://master.example.cache.amazonaws.com' );
define( 'MC_STORAGE_PORT', 6379 );
```

### Multiple Sites on Same Server

Use different databases or prefixes:

```php
// Site A
define( 'MC_STORAGE_DB', 0 );
define( 'MC_STORAGE_PREFIX', 'sitea' );

// Site B
define( 'MC_STORAGE_DB', 1 );
define( 'MC_STORAGE_PREFIX', 'siteb' );
```

### All Connection Constants

| Constant                  | Default     | Description                                               |
|---------------------------|-------------|-----------------------------------------------------------|
| `MC_STORAGE_HOST`         | `127.0.0.1` | Hostname, IP, socket, `tls://host`, or a node array (see below) |
| `MC_STORAGE_PORT`         | `6379`      | TCP port (0 for sockets)                                  |
| `MC_STORAGE_USERNAME`     | `''`        | ACL username (default user if empty)                      |
| `MC_STORAGE_PASSWORD`     | `''`        | AUTH password                                             |
| `MC_STORAGE_DB`           | `0`         | Database number (0-15)                                    |
| `MC_STORAGE_PERSISTENT`   | `true`      | Use persistent connections                                |
| `MC_STORAGE_PREFIX`       | `mll`       | Key prefix                                                |
| `MC_STORAGE_TIMEOUT`      | `1.0`       | Connection timeout in seconds                             |
| `MC_STORAGE_READ_TIMEOUT` | `2.0`       | Read/write timeout in seconds                             |

### Timeouts

`MC_STORAGE_TIMEOUT` (default `1.0`) bounds connecting to the server, so an
unreachable backend falls back to uncached WordPress quickly instead of stalling
the request. `MC_STORAGE_READ_TIMEOUT` (default `2.0`) bounds waiting for a
command response; raise it only if you serve very large cached responses over a
slow link.

## High Availability: Replication & Sentinel

For read-scaling, a node-local replica, or automatic failover, set
`MC_STORAGE_HOST` to an array. A `master` key selects replication; a `service`
key selects Sentinel. These modes are configured in `wp-config.php` only; the
Settings screen shows a read-only notice when an array is active.

`MC_STORAGE_USERNAME`, `MC_STORAGE_PASSWORD`, and `MC_STORAGE_DB` apply to every
node. Each address is `host`, `host:port`, or `tls://host[:port]`.

### Replication (master + replicas)

```php
define( 'MC_STORAGE_HOST', array(
    'master'   => 'master.example.com',                     // writes
    'replicas' => array( '127.0.0.1', 'replica.tld:6380' ), // reads
) );

define( 'MC_STORAGE_PASSWORD', 'shared-secret' );  // applied to all nodes
```

`replicas` is optional and accepts a single address or a list. Replication is
asynchronous, so a just-written entry may briefly lag on a replica.

### Sentinel (managed failover)

```php
define( 'MC_STORAGE_HOST', array(
    'service'   => 'mymaster',
    'sentinels' => array( '10.0.0.1:26379', '10.0.0.2:26379' ),
) );
```

Sentinel discovers the master and re-resolves it on failover. Cluster mode
(sharding across multiple masters) is not supported.

A misconfigured array (both `master` and `service`, Sentinel without
`sentinels`, or no recognized key) disables the cache and logs the reason rather
than connecting to the wrong server.

## Recommended Server Configuration

These settings are recommended for WordPress caching workloads:

```conf
# Memory allocation (adjust based on your needs)
maxmemory 256mb
maxmemory-policy allkeys-lru

# Persistence (optional - cache can be regenerated)
save ""
appendonly no

# Performance
tcp-keepalive 300
timeout 0

# Security (if exposed to network)
bind 127.0.0.1
requirepass your-strong-password
```

### Memory Sizing

| Site Size | Recommended Memory |
|-----------|-------------------|
| Small (< 100 pages) | 64 MB |
| Medium (100-1000 pages) | 128-256 MB |
| Large (1000+ pages) | 512 MB+ |

The `allkeys-lru` eviction policy automatically removes least-recently-used entries when memory is full.

## Testing Your Connection

After configuration, verify the connection:

```bash
# Quick test
wp millicache test

# Check status
wp millicache status

# View stats
wp millicache stats
```

## Troubleshooting Connection Issues

### Connection Refused

```
Error: Connection refused
```

**Causes:**
- Server not running
- Wrong host/port
- Firewall blocking connection

**Solutions:**
```bash
# Check if Redis is running
redis-cli ping

# Check listening port
netstat -tlnp | grep 6379

# Test connection manually
redis-cli -h 127.0.0.1 -p 6379 ping
```

### Authentication Failed

```
Error: NOAUTH Authentication required
```

**Solution:** Set the authentication constants:
```php
define( 'MC_STORAGE_USERNAME', 'your-username' );     // omit if using default user
define( 'MC_STORAGE_PASSWORD', 'your-password' );
```

### Timeout

```
Error: Connection timed out
```

**Causes:**
- Server overloaded
- Network issues
- Firewall timeout

**Solutions:**
- Check server resources
- Verify network connectivity
- Check firewall rules
- If the backend is healthy but distant, raise `MC_STORAGE_TIMEOUT` (connection)
  or `MC_STORAGE_READ_TIMEOUT` (command response) above their `1.0`/`2.0` second defaults

## Cloud Provider Options

Most cloud providers offer managed Redis:

| Provider | Service |
|----------|---------|
| AWS | ElastiCache for Redis |
| Google Cloud | Memorystore |
| Azure | Azure Cache for Redis |
| DigitalOcean | Managed Redis |
| Upstash | Serverless Redis |

For managed services, use the provided connection details in your MilliCache configuration.

## Performance Tips

1. **Run locally when possible**: Same-machine Redis has lowest latency
2. **Use persistent connections**: Reduces connection overhead
3. **Enable compression**: `MC_CACHE_GZIP` reduces network transfer
4. **Size memory appropriately**: Avoid frequent evictions
5. **Monitor with `wp millicache stats`**: Track cache efficiency

## Next Steps

- [Configuration Reference](../02-configuration/02-reference.md): All settings
- [WP-CLI Commands](../06-wp-cli/01-commands.md): Command reference
- [Troubleshooting](../09-troubleshooting/01-common-issues.md): Common issues
