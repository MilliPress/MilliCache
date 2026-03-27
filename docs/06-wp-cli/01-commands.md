---
title: 'WP-CLI Commands'
post_excerpt: 'Full WP-CLI reference for MilliCache: wp millicache clear, stats, status, test, drop, config, and cli. Includes flags, usage examples, and output formats.'
menu_order: 10
---

# WP-CLI Commands

Complete reference for MilliCache command-line interface.

## Quick Reference

| Command                       | Description                       |
|-------------------------------|-----------------------------------|
| `wp millicache clear`         | Clear cache entries               |
| `wp millicache stats`         | View cache statistics             |
| `wp millicache status`        | Show plugin and connection status |
| `wp millicache test`          | Test Redis connection             |
| `wp millicache drop`          | Install/repair advanced-cache.php |
| `wp millicache cli`           | Open interactive Redis CLI        |
| `wp millicache config get`    | View configuration                |
| `wp millicache config set`    | Set configuration value           |
| `wp millicache config reset`  | Reset to defaults                 |
| `wp millicache config backup` | Create settings backup            |
| `wp millicache config restore`| Restore settings from backup      |
| `wp millicache config export` | Export settings as JSON           |
| `wp millicache config import` | Import settings from JSON         |

---

## Cache Commands

### wp millicache clear

Clear cached entries with various targeting options.

```bash
wp millicache clear [--id=<id>] [--url=<url>] [--flag=<flag>]
                    [--site=<site>] [--network=<network>] [--expire]
```

**Options:**

| Option                | Description                                                |
|-----------------------|------------------------------------------------------------|
| `--id=<id>`           | Comma-separated post IDs                                   |
| `--url=<url>`         | Comma-separated URLs                                       |
| `--flag=<flag>`       | Comma-separated cache flags (supports wildcards)           |
| `--site=<site>`       | Comma-separated site IDs (multisite)                       |
| `--network=<network>` | Comma-separated network IDs (multisite)                    |
| `--expire`            | Expire instead of delete (serve stale during regeneration) |

**Examples:**

```bash
# Clear all cache
wp millicache clear

# Clear specific posts
wp millicache clear --id=1,2,3,42

# Clear by URLs
wp millicache clear --url="https://example.com/,https://example.com/about/"

# Clear by flags
wp millicache clear --flag="home,archive:post"

# Clear with wildcard
wp millicache clear --flag="post:*"

# Expire instead of delete
wp millicache clear --expire

# Multisite: clear specific sites
wp millicache clear --site=1,2,3

# Multisite: clear entire network
wp millicache clear --network=1
```

### wp millicache stats

Display cache statistics.

```bash
wp millicache stats [--flag=<flag>] [--format=<format>]
```

**Options:**

| Option              | Description                                        |
|---------------------|----------------------------------------------------|
| `--flag=<flag>`     | Filter by flag pattern (supports wildcards)        |
| `--format=<format>` | Output: `table`, `json`, `yaml` (default: `table`) |

**Examples:**

```bash
# Basic statistics
wp millicache stats

# Filter by flag pattern
wp millicache stats --flag="post:*"

# JSON output
wp millicache stats --format=json
```

**Output:**

```
+----------+---------+
| property | value   |
+----------+---------+
| entries  | 142     |
| size     | 2856432 |
| size_h   | 2.7 MB  |
| avg_size | 20.1 KB |
+----------+---------+
```

---

## Configuration Commands

Configuration commands are provided by the MilliBase framework and manage settings using a priority hierarchy: constants > file > database > defaults.

### wp millicache config get

Display current configuration values.

```bash
wp millicache config get [<key>] [--show-source] [--format=<format>]
```

**Options:**

| Option              | Description                                                    |
|---------------------|----------------------------------------------------------------|
| `<key>`             | Module or setting key (e.g., `cache` or `cache.ttl`)           |
| `--show-source`     | Show where each value comes from                               |
| `--format=<format>` | Output: `table`, `json`, `yaml`, `csv` (default: `table`)      |

**Examples:**

```bash
# View all settings
wp millicache config get

# View specific module
wp millicache config get cache

# View specific setting
wp millicache config get cache.ttl

# View setting as JSON
wp millicache config get cache.ttl --format=json

# Show setting sources
wp millicache config get --show-source
```

**Source values:**
- `constant` — Defined in wp-config.php
- `file` — Set in config file
- `database` — Saved via admin or CLI
- `default` — Built-in default value

### wp millicache config set

Set a configuration value.

```bash
wp millicache config set <key> <value>
```

Values are automatically coerced: `"true"`/`"false"` become booleans, `"null"` becomes null, and numeric strings become numbers.

**Examples:**

```bash
# Set TTL
wp millicache config set cache.ttl 3600

# Set boolean
wp millicache config set cache.debug true

# Set array (JSON format)
wp millicache config set cache.nocache_paths '["/cart/*", "/checkout/*"]'

# Set password (automatically encrypted, masked in output)
wp millicache config set storage.enc_password "secret"
```

> [!NOTE]
> Settings defined via constants cannot be overridden via CLI.

### wp millicache config reset

Reset settings to default values. A backup is automatically created before resetting.

```bash
wp millicache config reset [--module=<module>] [--yes]
```

**Options:**

| Option              | Description                                        |
|---------------------|----------------------------------------------------|
| `--module=<module>` | Reset specific module: `storage`, `cache`, `rules` |
| `--yes`             | Skip confirmation                                  |

**Examples:**

```bash
# Reset all settings
wp millicache config reset

# Reset specific module
wp millicache config reset --module=cache

# Skip confirmation
wp millicache config reset --yes
```

### wp millicache config backup

Create a manual backup of current settings.

```bash
wp millicache config backup
```

Backups expire after 12 hours and are also created automatically before reset and import operations.

### wp millicache config restore

Restore settings from the most recent backup.

```bash
wp millicache config restore
```

### wp millicache config export

Export settings as JSON to stdout or file.

```bash
wp millicache config export [--file=<path>] [--module=<module>] [--include-encrypted]
```

**Options:**

| Option                | Description                                    |
|-----------------------|------------------------------------------------|
| `--file=<path>`       | Write to file instead of stdout                |
| `--module=<module>`   | Export only a specific module                   |
| `--include-encrypted` | Include decrypted values of encrypted fields    |

**Examples:**

```bash
# Export to stdout
wp millicache config export

# Export to file
wp millicache config export --file=settings.json

# Export only cache module
wp millicache config export --module=cache

# Include encrypted fields
wp millicache config export --include-encrypted --file=full-backup.json
```

### wp millicache config import

Import settings from a JSON file. A backup is automatically created before importing.

```bash
wp millicache config import --file=<path> [--merge] [--no-merge] [--yes]
```

**Options:**

| Option        | Description                                              |
|---------------|----------------------------------------------------------|
| `--file=<path>` | Path to JSON file (required)                          |
| `--merge`     | Merge with existing settings (default)                   |
| `--no-merge`  | Replace all settings with imported values                |
| `--yes`       | Skip confirmation                                        |

**Examples:**

```bash
# Import from file (merges by default)
wp millicache config import --file=settings.json

# Replace all settings
wp millicache config import --file=settings.json --no-merge

# Skip confirmation
wp millicache config import --file=settings.json --yes
```


---

## Diagnostic Commands

### wp millicache status

Display comprehensive plugin and cache status.

```bash
wp millicache status [--format=<format>]
```

**Output:**

```
+-------------------+------------------+
| property          | status           |
+-------------------+------------------+
| plugin_version    | 1.0.0            |
| wp_cache          | enabled          |
| advanced_cache    | symlink          |
| storage_connected | yes              |
| storage_version   | 7.2.4            |
| storage_memory    | 12.5 MB / 256 MB |
| cache_entries     | 142              |
| cache_size        | 2.7 MB           |
+-------------------+------------------+
```

### wp millicache test

Run comprehensive Redis connection tests.

```bash
wp millicache test
```

**Output:**

```
+-------------+--------+------------------+
| test        | status | info             |
+-------------+--------+------------------+
| Connection  | PASS   | Connected        |
| Ping        | PASS   | 0.23ms           |
| Write       | PASS   | Stored test data |
| Read        | PASS   | Data verified    |
| Delete      | PASS   | Cleaned up       |
+-------------+--------+------------------+
```

### wp millicache drop

Install or repair the advanced-cache.php drop-in file.

```bash
wp millicache drop [--force]
```

**Options:**

| Option    | Description                     |
|-----------|---------------------------------|
| `--force` | Force reinstall even if current |

**Examples:**

```bash
# Standard install/fix
wp millicache drop

# Force reinstall
wp millicache drop --force
```

### wp millicache cli

Open an interactive Redis CLI session.

```bash
wp millicache cli
```

Requires `redis-cli` installed on the system. Launches with configured connection settings.

**Common Redis commands once connected:**

```redis
PING                    # Check connection
KEYS mll:*              # List MilliCache keys
DBSIZE                  # Get key count
INFO memory             # Check memory usage
QUIT                    # Exit CLI
```

---

## Multisite Usage

In multisite installations:

```bash
# Run on specific site
wp millicache clear --url=site1.example.com

# Clear specific sites
wp millicache clear --site=1,2,3

# Clear entire network
wp millicache clear --network=1

# Stats for specific site
wp millicache stats --flag="2:*"
```

---

## Output Formats

Most commands support multiple formats:

```bash
# Table (default)
wp millicache status

# JSON
wp millicache status --format=json

# YAML
wp millicache status --format=yaml
```

---

## Common Workflows

### After Deployment

```bash
wp millicache drop --force
wp millicache test
wp millicache clear
```

### Debugging

```bash
wp millicache status
wp millicache test
wp millicache config set cache.debug true
wp millicache stats
```

### Backup and Restore Settings

```bash
# Create a manual backup
wp millicache config backup

# Restore from backup
wp millicache config restore

# Export to file for external backup
wp millicache config export --file=backup.json

# Import from file
wp millicache config import --file=backup.json
```

### Health Check Script

```bash
#!/bin/bash
if wp millicache test; then
    echo "Redis connection OK"
else
    echo "Redis connection failed"
    exit 1
fi
```

---

## Exit Codes

| Code  | Meaning  |
|-------|----------|
| `0`   | Success  |
| `1`   | Error    |

---

## Help

```bash
# General help
wp help millicache

# Command-specific help
wp help millicache clear
wp help millicache config
```

## Next Steps

- [Configuration Reference](../02-configuration/02-reference.md) — All constants
- [Cache Clearing](../05-usage/20-cache-clearing.md) — Clearing strategies
- [Troubleshooting](../09-troubleshooting/01-common-issues.md) — Common issues
