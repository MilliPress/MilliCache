---
title: 'Troubleshooting'
post_excerpt: 'Diagnose and fix common MilliCache issues: Redis connection failures, pages not caching, drop-in conflicts, stale content, and multisite problems with WP-CLI diagnostics.'
menu_order: 10
---

# Troubleshooting

This guide covers common issues and their solutions.

## Diagnostic Commands

Start troubleshooting with these commands:

```bash
# Check overall status
wp millicache status

# Test Redis connection
wp millicache test

# View cache statistics
wp millicache stats

# Check configuration sources
wp millicache config get --show-source
```

## Connection Issues

### Redis Connection Refused

**Symptoms:**
```
Error: Connection refused [tcp://127.0.0.1:6379]
```

**Solutions:**

1. **Check Redis is running:**
   ```bash
   redis-cli ping
   # Should return: PONG

   # Or check service status
   sudo systemctl status redis
   ```

2. **Verify Redis is listening:**
   ```bash
   netstat -tlnp | grep 6379
   # or
   ss -tlnp | grep 6379
   ```

3. **Check firewall rules:**
   ```bash
   sudo ufw status
   # Allow if needed
   sudo ufw allow 6379/tcp
   ```

4. **Verify configuration:**
   ```bash
   wp millicache config get storage
   ```

### Authentication Failed

**Symptoms:**
```
Error: NOAUTH Authentication required
```

**Solutions:**

1. **Verify credentials in MilliCache:**
   ```bash
   wp millicache config get storage.enc_username
   wp millicache config get storage.enc_password
   ```

2. **Re-set credentials:**
   ```bash
   wp millicache config set storage.enc_username "your-username"
   wp millicache config set storage.enc_password "your-password"
   ```

3. **Test Redis credentials directly:**
   ```bash
   # With a named user (Redis ACL)
   redis-cli -u "redis://your-username:your-password@127.0.0.1:6379" ping

   # With the default user (password only)
   redis-cli -a "your-password" ping
   ```

### Connection Timeout

**Symptoms:**
```
Error: Connection timed out
```

**Solutions:**

1. **Check network connectivity:**
   ```bash
   telnet redis-host 6379
   ```

2. **Verify DNS resolution:**
   ```bash
   host redis-host
   ```

3. **Check for network issues:**
   ```bash
   ping redis-host
   traceroute redis-host
   ```

4. **Increase PHP timeout (temporary):**
   ```php
   ini_set('default_socket_timeout', 10);
   ```

### Unix Socket Permission Denied

**Symptoms:**
```
Error: Permission denied [unix:///var/run/redis/redis.sock]
```

**Solutions:**

1. **Check socket exists:**
   ```bash
   ls -la /var/run/redis/redis.sock
   ```

2. **Add web server user to redis group:**
   ```bash
   sudo usermod -aG redis www-data
   sudo systemctl restart php-fpm
   ```

3. **Check Redis socket permissions:**
   ```conf
   # /etc/redis/redis.conf
   unixsocketperm 770
   ```

---

## Caching Issues

### Pages Not Being Cached

**Symptoms:**
- `X-MilliCache-Status: bypass`
- Cache entries count stays at 0
- Pages always show "miss"

**Diagnosis:**

1. **Enable debug mode:**
   ```php
   define( 'MC_CACHE_DEBUG', true );
   ```

2. **Check debug headers:**
   ```bash
   curl -I https://example.com/
   # Look for X-MilliCache-* headers
   ```

   > [!TIP]
   > The [MilliCache Browser Extension](https://github.com/MilliPress/millicache-browser-ext/) adds a dedicated panel to your browser's developer tools for easier debugging.

**Common causes:**

| Header Status  | Cause                 | Solution             |
|----------------|-----------------------|----------------------|
| No headers     | Drop-in not installed | `wp millicache drop` |
| `bypass`       | Rule triggered        | Check below          |
| `miss`         | First request         | Normal, will cache   |

3. **Check for bypass reasons:**

   - **Logged in?** Log out and test
   - **POST request?** Only GET/HEAD cached
   - **Excluded cookie?** Check `MC_CACHE_NOCACHE_COOKIES`
   - **Excluded path?** Check `MC_CACHE_NOCACHE_PATHS`
   - **TTL = 0?** Check `MC_CACHE_TTL`

4. **Verify WP_CACHE:**
   ```bash
   wp config get WP_CACHE
   # Should be: true
   ```

### Cache Not Clearing

**Symptoms:**
- Old content still showing
- Updates not reflected

**Solutions:**

1. **Force clear all cache:**
   ```bash
   wp millicache clear
   ```

2. **Clear Redis directly:**
   ```bash
   redis-cli FLUSHDB
   ```

3. **Check for external cache (CDN, Varnish):**
   - Clear CDN cache separately
   - Check for upstream caching

4. **Verify clearing hooks working:**
   ```php
   add_action( 'millicache_cache_cleared', function() {
       error_log( 'Cache cleared successfully' );
   } );
   ```

### Stale Content After Updates

**Symptoms:**
- Updated content shows old version
- Takes time to refresh

**Diagnosis:**

1. **Check grace period behavior:**
   - Is content within grace period?
   - Grace serves stale during regen

2. **Verify post update triggers clearing:**
   ```php
   add_action( 'millicache_cache_cleared_by_posts', function( $ids ) {
       error_log( 'Cleared posts: ' . implode( ', ', $ids ) );
   } );
   ```

**Solutions:**

1. **Clear specific post:**
   ```bash
   wp millicache clear --id=123
   ```

2. **Delete instead of expire:**
   ```bash
   wp millicache clear --id=123
   # Without --expire flag = immediate delete
   ```

---

## Drop-in Issues

### advanced-cache.php Missing

**Symptoms:**
```
wp millicache status
# advanced_cache: missing
```

**Solution:**

```bash
wp millicache drop
```

### advanced-cache.php Outdated

**Symptoms:**
```
wp millicache status
# advanced_cache: outdated
```

**Solution:**

```bash
wp millicache drop --force
```

### Another Plugin's Drop-in

**Symptoms:**
```
Warning: Existing advanced-cache.php from another plugin detected.
```

**Solution:**

1. Deactivate other caching plugins
2. Delete old drop-in:
   ```bash
   rm wp-content/advanced-cache.php
   ```
3. Install MilliCache drop-in:
   ```bash
   wp millicache drop
   ```

### Symlink Not Working

**Symptoms:**
```
Notice: Symlinks not supported, using file copy.
```

**Cause:** File system or hosting doesn't support symlinks

**Impact:** None—file copy works identically

**If you want symlinks:**

1. Check file system supports symlinks
2. Verify permissions on `wp-content/`
3. Some hosts disable symlinks for security

---

## Performance Issues

### Slow Cache Hits

**Symptoms:**
- Cache hits taking >50ms
- Expected: 5-15ms

**Diagnosis:**

1. **Check network latency:**
   ```bash
   redis-cli --latency
   ```

2. **Check Redis performance:**
   ```bash
   redis-cli info stats | grep instantaneous_ops_per_sec
   ```

**Solutions:**

1. **Use Unix sockets:**
   ```php
   define( 'MC_STORAGE_HOST', '/var/run/redis/redis.sock' );
   ```

2. **Enable persistent connections:**
   ```php
   define( 'MC_STORAGE_PERSISTENT', true );
   ```

3. **Run Redis locally** instead of remote

### High Memory Usage

**Symptoms:**
- Redis using too much memory
- Keys being evicted

**Diagnosis:**

```bash
redis-cli info memory
wp millicache stats
```

**Solutions:**

1. **Increase Redis memory:**
   ```conf
   maxmemory 512mb
   ```

2. **Reduce TTL:**
   ```php
   define( 'MC_CACHE_TTL', 3600 );  // 1 hour
   ```

3. **Enable compression:**
   ```php
   define( 'MC_CACHE_GZIP', true );
   ```

4. **Reduce cache variations:**
   - Review `MC_CACHE_UNIQUE` settings
   - Add more items to ignore lists

### Cache Fragmentation

**Symptoms:**
- High used_memory_rss vs used_memory ratio
- Slow Redis operations

**Diagnosis:**

```bash
redis-cli info memory | grep fragmentation
```

**Solutions:**

1. **Restart Redis** (clears fragmentation)
2. **Use jemalloc** (better memory allocator)
3. **Schedule periodic restarts** (for high-write workloads)

---

## Multisite Issues

### Cache Not Isolated

**Symptoms:**
- Site A content appears on Site B
- Clearing Site A affects Site B

**Diagnosis:**

```bash
wp millicache stats --flag="*" --format=json
# Check flag prefixes
```

**Solutions:**

1. **Verify network activation:**
   - Plugin must be network-activated
   - Not per-site activated

2. **Check flag prefixes:**
   ```bash
   wp millicache stats --flag="1:*"  # Site 1
   wp millicache stats --flag="2:*"  # Site 2
   ```

### Network Clear Not Working

**Solutions:**

1. **Run from network admin context:**
   ```bash
   wp millicache clear --network=1
   ```

2. **Check capabilities:**
   - User needs `manage_network`

---

## Debug Techniques

### Enable All Logging

```php
// wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'MC_CACHE_DEBUG', true );
```

### Check PHP Error Log

```bash
tail -f /var/log/php/error.log
```

### Monitor Redis Commands

```bash
redis-cli monitor
```

### Test Request Flow

```bash
# With headers
curl -v https://example.com/ 2>&1 | grep -i millicache

# Multiple requests to test caching
for i in {1..3}; do
  curl -s -o /dev/null -w "Request $i: %{http_code} in %{time_total}s\n" https://example.com/
done
```

### Check Cache Entry

```bash
# Open Redis CLI
wp millicache cli

# Find cache keys
KEYS mll:*

# Get specific entry
GET mll:cache:abc123
```

## Getting Help

If issues persist:

1. **Gather diagnostics:**
   ```bash
   wp millicache status --format=json > status.json
   wp millicache config get --format=json > config.json
   wp millicache stats --format=json > stats.json
   ```

2. **Check PHP/WordPress versions**

3. **Report issues:** [GitHub Issues](https://github.com/millipress/millicache/issues)

## Next Steps

- [FAQ](02-faq.md) - Common questions
- [Storage Backends](../08-storage-backends/01-overview.md) - Configuration and optimization
- [WP-CLI Commands](../06-wp-cli/01-commands.md) - Command reference
