---
title: 'WordPress Multisite Caching'
post_excerpt: 'MilliCache supports WordPress Multisite with per-site cache isolation, network-wide clearing, and subdomain or subdirectory handling. Network-activate for full control.'
menu_order: 30
---

# Multisite

MilliCache fully supports WordPress Multisite with per-site cache isolation, network-wide management, and multi-network compatibility.

## Installation

### Network Activation

1. [Install MilliCache](../01-getting-started/20-installation.md) as a regular plugin
2. Network-activate from **Network Admin → Plugins**
3. Add `WP_CACHE` to `wp-config.php`:

```php
define( 'WP_CACHE', true );
```

> [!IMPORTANT]
> MilliCache must be network-activated. Per-site activation is not supported in multisite.

## Cache Isolation

Each site's cache is automatically isolated using flag prefixes:

| Environment   | Flag Format                     | Example        |
|---------------|---------------------------------|----------------|
| Single site   | `{flag}`                        | `post:123`     |
| Multisite     | `{site_id}:{flag}`              | `2:post:123`   |
| Multi-network | `{network_id}:{site_id}:{flag}` | `1:2:post:123` |

This ensures:
- Site A's cache doesn't affect Site B
- Clearing Site A doesn't clear Site B
- Each site can have different content for the same path

## Network-Wide Settings

### Via Constants

Settings in `wp-config.php` apply to all sites:

```php
define( 'MC_CACHE_TTL', 86400 );
define( 'MC_STORAGE_HOST', 'redis.example.com' );
```

### Via Database

Network-level settings are stored in the main site's options. Per-site settings can be configured via the admin UI on each site.

The settings are synced to config files for each site:

```
/wp-content/settings/millicache/
├── example_com.php
├── site1_example_com.php
└── site2_example_com.php
```

Each file returns settings for that domain:

```php
<?php
// example_com.php
return [
    // ...
    'cache' => [
        // ...
        'ttl' => 3600,  // 1 hour for this site
    ],
];
```

## Cache Clearing

### Clear Single Site

From a specific site's context:

```bash
# Clear current site
wp millicache clear --url=site1.example.com

# Clear by site ID
wp millicache clear --site=2
```

Via admin bar on each site: **MilliCache → Clear Site Cache**

### Clear Multiple Sites

```bash
# Clear specific sites
wp millicache clear --site=1,2,3

# Clear sites with specific network
wp millicache clear --site=1,2,3 --network=1
```

### Clear Entire Network

```bash
# Clear all sites in network
wp millicache clear --network=1

# Clear all networks
wp millicache clear --network=1,2
```

Via Network Admin bar: **MilliCache → Clear Network Cache**

### Clear All Sites

```bash
wp millicache clear
```

Without arguments from the main site context, clears all cache.

## PHP Functions in Multisite

### Clear by Site IDs

```php
// Clear specific sites
millipress_clear_cache_by_site_ids( [ 1, 2, 3 ] );

// Clear sites in a specific network
millipress_clear_cache_by_site_ids( [ 1, 2 ], 1 );

// Expire instead of delete
millipress_clear_cache_by_site_ids( [ 1, 2 ], null, true );
```

### Clear by Network

```php
// Clear the entire network
millipress_clear_cache_by_network_id( 1 );

// Expire instead of delete
millipress_clear_cache_by_network_id( 1, true );
```

### Prefix Flags

```php
$flags = [ 'post:123', 'home' ];

// Prefix for current site
$prefixed = millipress_prefix_flags( $flags );
// Result: [ '2:post:123', '2:home' ]

// Prefix for specific site
$prefixed = millipress_prefix_flags( $flags, 3 );
// Result: [ '3:post:123', '3:home' ]
```

## Statistics

### Per-Site Stats

```bash
# Stats for specific site
wp millicache stats --url=site1.example.com

# Filter by site flag prefix
wp millicache stats --flag="2:*"
```

### Network Stats

From network admin context:

```bash
wp millicache stats
```

## Subdirectory vs. Subdomain

MilliCache works with both multisite configurations:

### Subdomain Multisite

```
site1.example.com → Cache key includes full domain
site2.example.com → Separate cache namespace
```

### Subdirectory Multisite

```
example.com/site1/ → Cache key includes path
example.com/site2/ → Separate cache namespace
```

No special configuration required — MilliCache automatically handles both.

## Domain Mapping

If using domain mapping (third-party domains pointing to subsites):

1. Each mapped domain gets its own cache entries
2. Flags are still prefixed by site ID, not domain
3. Clearing by site ID clears all entries for that site

```php
// Clear site 3, regardless of which domains point to it
millipress_clear_cache_by_site_ids( [ 3 ] );
```

## Best Practices

### 1. Use Network-Wide Constants

Keep storage configuration consistent:

```php
// wp-config.php
define( 'MC_STORAGE_HOST', 'redis.internal' );
define( 'MC_STORAGE_PREFIX', 'mll_prod_' );
```

### 2. Monitor Per-Site Usage

Check which sites consume the most cache:

```bash
# Stats filtered by site prefix
for i in 1 2 3 4 5; do
  echo "Site $i:"
  wp millicache stats --flag="$i:*" --format=json
done
```

## Troubleshooting

### Cache Isn’t Isolated

If sites seem to share cache:

1. Verify network activation (not per-site)
2. Check flag prefixes: `wp millicache stats --flag="*" --format=json`
3. Verify `MC_STORAGE_PREFIX` is consistent across all sites

### Network Clear Not Working

1. Verify you're running from network admin context
2. Check user has `manage_network` capability
3. Use explicit network ID: `wp millicache clear --network=1`

### Inconsistent Settings

1. Check setting sources: `wp millicache config get --show-source`
2. Verify config file naming matches domain exactly
3. Constants override all other sources

## Next Steps

- [WP-CLI Commands](../06-wp-cli/01-commands.md) - Command line management
- [Hooks & Filters](../07-developers/02-hooks-filters.md) - Multisite-specific hooks
- [Configuration](../02-configuration/01-overview.md) - Settings overview
