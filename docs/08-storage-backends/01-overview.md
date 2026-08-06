---
title: 'Storage Backends: Redis, ValKey, KeyDB & Dragonfly'
description: 'Compare MilliCache storage backends: Redis, ValKey as a drop-in Redis replacement, KeyDB, and Dragonfly, with connection setup and managed hosting tips.'
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
(sharding across multiple masters) is not supported; if your project requires
it, [reach out](https://www.millipress.com/contact/).

A misconfigured array (both `master` and `service`, Sentinel without
`sentinels`, or no recognized key) disables the cache and logs the reason rather
than connecting to the wrong server.

[MilliCache Pro](https://www.millipress.com/millicache-pro/) adds a
[visual connection editor](https://www.millipress.com/docs/millicache-pro/02-modules/10-storage-connections/)
for all three topologies, so replication and Sentinel can be set up from the
settings screen instead of a `MC_STORAGE_HOST` array in `wp-config.php`.

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

| Site Size               | Recommended Memory  |
|-------------------------|---------------------|
| Small (< 100 pages)     | 64 MB               |
| Medium (100-1000 pages) | 128-256 MB          |
| Large (1000+ pages)     | 512 MB+             |

The `allkeys-lru` eviction policy automatically removes least-recently-used entries when memory is full.

### Applying Settings at Runtime

You don't have to edit the Redis configuration file to change these. The memory
limit and eviction policy can both be applied live with `CONFIG SET`, with no
restart:

```bash
redis-cli CONFIG SET maxmemory 512mb
redis-cli CONFIG SET maxmemory-policy allkeys-lru
```

To target the exact server MilliCache is configured to use (host, port,
database, and password) without looking those details up, open a session with
`wp millicache cli` and run the commands there:

```bash
wp millicache cli
127.0.0.1:6379> CONFIG SET maxmemory 512mb
127.0.0.1:6379> CONFIG SET maxmemory-policy allkeys-lru
127.0.0.1:6379> quit
```

`CONFIG SET` takes effect immediately but is lost on the next Redis restart. To
make the change permanent, either add the same directives to your Redis
configuration file, or run `CONFIG REWRITE` to write the running configuration
back to that file (this requires Redis to already be started from a config
file):

```bash
redis-cli CONFIG REWRITE
```

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

MilliCache works with managed Redis and Valkey services out of the box. Point
`MC_STORAGE_HOST` at the endpoint your provider gives you; most managed
services require TLS (use the `tls://` prefix) and password authentication.

| Provider | Service |
|----------|---------|
| AWS | ElastiCache (Redis OSS / Valkey) |
| Google Cloud | Memorystore |
| Azure | Azure Cache for Redis |
| DigitalOcean | Managed Caching (Valkey) |
| Upstash | Serverless Redis |

> [!NOTE]
> Managed services must run in **non-cluster mode**: a single primary endpoint,
> optionally with replicas. Cluster mode (sharding across multiple masters) is
> not supported. If your project requires cluster mode,
> [reach out](https://www.millipress.com/contact/) and tell us about your setup.

Because a managed endpoint is reached over the network rather than localhost,
also review the [timeout settings](#timeouts) if the service runs in a
different region than your web servers.

### AWS ElastiCache

ElastiCache offers both Redis OSS and Valkey engines; both work with
MilliCache. Create the cache with **cluster mode disabled** and connect to the
primary endpoint. With in-transit encryption enabled, prefix the host with
`tls://`:

```php
define( 'MC_STORAGE_HOST', 'tls://master.example.abc123.euc1.cache.amazonaws.com' );
define( 'MC_STORAGE_PORT', 6379 );
define( 'MC_STORAGE_PASSWORD', 'your-auth-token' );  // if AUTH is enabled
```

### Google Cloud Memorystore

Memorystore instances are reachable over private IP from within the same VPC,
so your WordPress servers must run in that network (Compute Engine, GKE, or
Cloud Run with a VPC connector):

```php
define( 'MC_STORAGE_HOST', '10.0.0.3' );  // instance IP
define( 'MC_STORAGE_PORT', 6379 );
define( 'MC_STORAGE_PASSWORD', 'your-auth-string' );  // if AUTH is enabled
```

### Azure Cache for Redis

Azure exposes caches at `*.redis.cache.windows.net` with TLS on port 6380. Use
an access key as the password:

```php
define( 'MC_STORAGE_HOST', 'tls://example.redis.cache.windows.net' );
define( 'MC_STORAGE_PORT', 6380 );
define( 'MC_STORAGE_PASSWORD', 'your-access-key' );
```

### DigitalOcean Managed Caching

DigitalOcean's managed caching service (formerly Managed Redis, now backed by
Valkey) requires TLS and provides host, port, username, and password in the
control panel's connection details:

```php
define( 'MC_STORAGE_HOST', 'tls://db-example-do-user-123456-0.db.ondigitalocean.com' );
define( 'MC_STORAGE_PORT', 25061 );
define( 'MC_STORAGE_USERNAME', 'default' );
define( 'MC_STORAGE_PASSWORD', 'your-password' );
```

### Upstash

Upstash offers serverless Redis with per-request pricing. Connections require
TLS:

```php
define( 'MC_STORAGE_HOST', 'tls://example-12345.upstash.io' );
define( 'MC_STORAGE_PORT', 6379 );
define( 'MC_STORAGE_PASSWORD', 'your-password' );
```

## Performance Tips

1. **Run locally when possible**: Same-machine Redis has lowest latency
2. **Use persistent connections**: Reduces connection overhead
3. **Enable compression**: `MC_CACHE_GZIP` reduces network transfer
4. **Size memory appropriately**: Avoid frequent evictions
5. **Monitor with `wp millicache stats`**: Track cache efficiency

Your storage server can do more than page caching: the [Object Cache module](https://www.millipress.com/docs/millicache-pro/02-modules/09-object-cache/) in [MilliCache Pro](https://www.millipress.com/millicache-pro/) ships a persistent object cache drop-in that reuses the same connection, speeding up wp-admin and uncached pages with zero extra setup.

## Next Steps

- [Configuration Reference](../02-configuration/02-reference.md): All settings
- [WP-CLI Commands](../06-wp-cli/01-commands.md): Command reference
- [Troubleshooting](../09-troubleshooting/01-common-issues.md): Common issues
