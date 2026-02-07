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
define( 'MC_STORAGE_PASSWORD', 'your-password' );
```

### Using Unix Socket

```php
define( 'MC_STORAGE_HOST', '/var/run/redis/redis.sock' );
define( 'MC_STORAGE_PORT', 0 );  // Ignored for sockets
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

| Constant | Default | Description |
|----------|---------|-------------|
| `MC_STORAGE_HOST` | `127.0.0.1` | Hostname, IP, or socket path |
| `MC_STORAGE_PORT` | `6379` | TCP port (0 for sockets) |
| `MC_STORAGE_PASSWORD` | `''` | AUTH password |
| `MC_STORAGE_DB` | `0` | Database number (0-15) |
| `MC_STORAGE_PERSISTENT` | `true` | Use persistent connections |
| `MC_STORAGE_PREFIX` | `mll` | Key prefix |

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

**Solution:** Set the password constant:
```php
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

1. **Run locally when possible** — Same-machine Redis has lowest latency
2. **Use persistent connections** — Reduces connection overhead
3. **Enable compression** — `MC_CACHE_GZIP` reduces network transfer
4. **Size memory appropriately** — Avoid frequent evictions
5. **Monitor with `wp millicache stats`** — Track cache efficiency

## Next Steps

- [Configuration Reference](../02-configuration/02-reference.md) — All settings
- [WP-CLI Commands](../06-wp-cli/01-commands.md) — Command reference
- [Troubleshooting](../09-troubleshooting/01-common-issues.md) — Common issues
