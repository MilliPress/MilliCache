---
title: 'Config Commands'
post_excerpt: 'Manage MilliCache configuration via WP-CLI including get, set, reset, export, and import operations.'
menu_order: 30
---

# Config Commands

The `wp millicache config` command manages MilliCache configuration.

## Synopsis

```bash
wp millicache config <subcommand> [options]
```

## Subcommands

| Subcommand | Description |
|------------|-------------|
| `get` | Display configuration values |
| `set` | Set a configuration value |
| `reset` | Reset settings to defaults |
| `restore` | Restore settings from backup |
| `export` | Export settings to file or stdout |
| `import` | Import settings from file |

---

## wp millicache config get

Display current configuration values.

### Synopsis

```bash
wp millicache config get [<key>] [--module=<module>] [--show-source] [--format=<format>]
```

### Options

| Option | Description |
|--------|-------------|
| `<key>` | Specific setting key (e.g., `cache.ttl`) |
| `--module=<module>` | Filter by module: `storage`, `cache`, `rules` |
| `--show-source` | Show where each value comes from |
| `--format=<format>` | Output format: `table`, `json`, `yaml` |

### Examples

#### View All Settings

```bash
wp millicache config get
```

Output:
```
+---------------------------+----------------------------+
| key                       | value                      |
+---------------------------+----------------------------+
| storage.host              | 127.0.0.1                  |
| storage.port              | 6379                       |
| storage.db                | 0                          |
| storage.prefix            | mll                        |
| storage.persistent        | true                       |
| cache.ttl                 | 86400                      |
| cache.grace               | 2592000                    |
| cache.debug               | false                      |
| cache.gzip                | true                       |
| cache.nocache_paths       | []                         |
| cache.nocache_cookies     | ["wp-*pass*", ...]         |
+---------------------------+----------------------------+
```

#### View Specific Module

```bash
wp millicache config get --module=cache
```

#### View Specific Setting

```bash
wp millicache config get cache.ttl
```

Output:
```
86400
```

#### Show Setting Sources

```bash
wp millicache config get --show-source
```

Output:
```
+---------------------------+----------+----------+
| key                       | value    | source   |
+---------------------------+----------+----------+
| storage.host              | redis    | constant |
| storage.port              | 6379     | default  |
| cache.ttl                 | 3600     | database |
| cache.debug               | true     | file     |
+---------------------------+----------+----------+
```

Sources:
- `constant` - Defined in `wp-config.php`
- `file` - Set in config file
- `database` - Saved via admin or CLI
- `default` - Built-in default value

#### JSON Output

```bash
wp millicache config get --format=json
```

---

## wp millicache config set

Set a configuration value.

### Synopsis

```bash
wp millicache config set <key> <value>
```

### Options

| Option | Description |
|--------|-------------|
| `<key>` | Setting key (e.g., `cache.ttl`) |
| `<value>` | Value to set |

### Examples

#### Set TTL

```bash
wp millicache config set cache.ttl 3600
```

Output:
```
Success: Updated cache.ttl to 3600
```

#### Set Password

```bash
wp millicache config set storage.enc_password "mysecret"
```

> [!NOTE]
> Fields with the `enc_` prefix (like `enc_password`) are **automatically encrypted** when saved. No additional flags are needed.

#### Set Array Values

```bash
# Set as JSON array
wp millicache config set cache.nocache_paths '["/cart/*", "/checkout/*"]'
```

#### Set Boolean Values

```bash
wp millicache config set cache.debug true
wp millicache config set cache.gzip false
```

### Limitations

Settings defined via constants cannot be overridden:

```bash
wp millicache config set storage.host "newhost"
```

Output:
```
Error: storage.host is defined by a constant and cannot be modified.
```

---

## wp millicache config reset

Reset settings to default values.

### Synopsis

```bash
wp millicache config reset [--module=<module>] [--yes]
```

### Options

| Option | Description |
|--------|-------------|
| `--module=<module>` | Reset only specific module: `storage`, `cache`, `rules` |
| `--yes` | Skip confirmation prompt |

### Examples

#### Reset All Settings

```bash
wp millicache config reset
```

Output:
```
Are you sure you want to reset all settings? [y/n] y
Success: Settings reset to defaults.
```

#### Reset Specific Module

```bash
wp millicache config reset --module=cache
```

#### Skip Confirmation

```bash
wp millicache config reset --module=storage --yes
```

> [!NOTE]
> Reset creates a backup before clearing. Use `restore` to undo.

---

## wp millicache config restore

Restore settings from automatic backup.

### Synopsis

```bash
wp millicache config restore
```

### Description

MilliCache automatically backs up settings before:
- Reset operations
- Import operations
- Major setting changes

Backups are stored as transients and expire after 12 hours.

### Example

```bash
wp millicache config restore
```

Output:
```
Success: Settings restored from backup.
```

> [!WARNING]
> Backups expire after 12 hours. Restore promptly if needed.

---

## wp millicache config export

Export settings to file or stdout.

### Synopsis

```bash
wp millicache config export [--file=<path>] [--format=<format>]
```

### Options

| Option | Description |
|--------|-------------|
| `--file=<path>` | Export to file path |
| `--format=<format>` | Output format: `json`, `yaml` (default: `json`) |

### Examples

#### Export to Stdout

```bash
wp millicache config export
```

Output:
```json
{
  "storage": {
    "host": "127.0.0.1",
    "port": 6379
  },
  "cache": {
    "ttl": 86400,
    "grace": 2592000
  }
}
```

#### Export to File

```bash
wp millicache config export --file=millicache-settings.json
```

Output:
```
Success: Settings exported to millicache-settings.json
```

#### Export as YAML

```bash
wp millicache config export --format=yaml
```

### Use Cases

```bash
# Backup before changes
wp millicache config export --file=backup-$(date +%Y%m%d).json

# Copy settings between environments
wp millicache config export --file=production-settings.json
# Transfer file to staging
wp millicache config import --file=production-settings.json
```

---

## wp millicache config import

Import settings from file.

### Synopsis

```bash
wp millicache config import --file=<path> [--yes]
```

### Options

| Option | Description |
|--------|-------------|
| `--file=<path>` | Path to settings file (JSON or YAML) |
| `--yes` | Skip confirmation prompt |

### Examples

#### Import from File

```bash
wp millicache config import --file=settings.json
```

Output:
```
The following settings will be imported:
- cache.ttl: 86400 → 3600
- cache.debug: false → true

Are you sure? [y/n] y
Success: Settings imported from settings.json
```

#### Skip Confirmation

```bash
wp millicache config import --file=settings.json --yes
```

### File Format

JSON:
```json
{
  "storage": {
    "host": "redis.example.com",
    "port": 6379
  },
  "cache": {
    "ttl": 3600,
    "debug": true
  }
}
```

YAML:
```yaml
storage:
  host: redis.example.com
  port: 6379
cache:
  ttl: 3600
  debug: true
```

> [!TIP]
> Imported settings are merged with existing settings. Only specified keys are updated.

## Automation Examples

### Deployment Script

```bash
#!/bin/bash
# Deploy settings to production

# Backup existing settings
wp millicache config export --file=backup.json

# Import production settings
if wp millicache config import --file=production.json --yes; then
    echo "Settings deployed successfully"
else
    echo "Failed to deploy settings, restoring backup"
    wp millicache config import --file=backup.json --yes
fi
```

### Environment-Specific Setup

```bash
#!/bin/bash
# Set environment-specific configuration

ENVIRONMENT=${WP_ENV:-production}

case $ENVIRONMENT in
    development)
        wp millicache config set cache.ttl 60
        wp millicache config set cache.debug true
        ;;
    staging)
        wp millicache config set cache.ttl 3600
        wp millicache config set cache.debug true
        ;;
    production)
        wp millicache config set cache.ttl 86400
        wp millicache config set cache.debug false
        ;;
esac
```

## Next Steps

- [Diagnostic Commands](40-diagnostic-commands.md) - Status and testing
- [Constants Reference](../02-configuration/40-constants-reference.md) - All constants
- [Basic Settings](../02-configuration/10-basic-settings.md) - Configuration guide
