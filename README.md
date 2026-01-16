# MilliCache

[![e2e-Tests](https://github.com/MilliPress/MilliCache/actions/workflows/playwright.yml/badge.svg)](https://github.com/MilliPress/MilliCache/actions/workflows/playwright.yml)

**High-performance full-page caching for WordPress** using Redis, ValKey, KeyDB, or Dragonfly.

MilliCache stores complete HTML pages in memory and serves them in under 10ms — without loading WordPress. Combined with intelligent cache flags and flexible rules, it's designed for sites that need both speed and control.

> [!IMPORTANT]
> This plugin is approaching a stable release. Please [report any issues](https://github.com/MilliPress/MilliCache/issues/new) you encounter.

## Key Features

- **Lightning Fast** — In-memory caching with sub-10ms response times
- **[Cache Flags](docs/03-cache-flags/01-introduction.md)** — Tag pages for precise, targeted cache invalidation
- **[Flexible Rules](docs/04-rules/01-introduction.md)** — Control caching behavior with condition-based rules
- **Multisite Ready** — Per-site isolation, network-wide management
- **Horizontal Scaling** — Multiple web servers can share a single Redis instance
- **WP-CLI Integration** — Full command-line cache management
- **[Multiple Backends](docs/08-storage-backends/01-overview.md)** — Redis, ValKey, KeyDB, or Dragonfly

## Quick Start

1. **Install** via [ZIP download](https://github.com/millipress/millicache/releases/latest) or Composer:
   ```bash
   composer require millipress/millicache
   ```

2. **Activate** the plugin in WordPress admin

3. **Enable caching** in `wp-config.php`:
   ```php
   define( 'WP_CACHE', true );

   // Optional: Configure Redis connection
   define( 'MC_STORAGE_HOST', '127.0.0.1' );
   define( 'MC_STORAGE_PORT', 6379 );
   ```

4. **Verify** in Settings UI, Browser Developer Tools, or with WP-CLI:
   ```bash
   wp millicache test
   wp millicache status
   ```

## Documentation

Full documentation is available in the [`docs/`](docs/) folder:

| Section                                                        | Description                            |
|----------------------------------------------------------------|----------------------------------------|
| [Getting Started](docs/01-getting-started/10-introduction.md)  | Introduction and installation          |
| [Configuration](docs/02-configuration/01-overview.md)          | Settings and constants reference       |
| [Cache Flags](docs/03-cache-flags/01-introduction.md)          | Targeted cache invalidation            |
| [Rules](docs/04-rules/01-introduction.md)                      | Condition-based caching control        |
| [Usage](docs/05-usage/10-how-caching-works.md)                 | How caching works, clearing, multisite |
| [WP-CLI](docs/06-wp-cli/01-commands.md)                        | Command-line reference                 |
| [Developers](docs/07-developers/10-architecture.md)            | Architecture, hooks, API               |
| [Storage Backends](docs/08-storage-backends/01-overview.md)    | Redis, ValKey, KeyDB, Dragonfly        |
| [Troubleshooting](docs/09-troubleshooting/01-common-issues.md) | Common issues and FAQ                  |

## Requirements

- PHP 7.4+ (8.x recommended)
- WordPress 6.6+
- Redis-compatible server ([Redis](https://redis.io/), [ValKey](https://valkey.io/), [KeyDB](https://keydb.dev/), or [Dragonfly](https://dragonflydb.io/))

## Testing

MilliCache uses PHPUnit, PHPStan, and Playwright for testing. To run tests locally:

```bash
# Start test environment (requires Docker + Node.js)
npm install
npm run env:start

# Run e2e tests
npm run env:e2e

# Stop environment
npm run env:stop
```

Test WordPress available at `http://localhost:8888` (login: `admin` / `password`)

### Useful Commands

```bash
npm run env:cli wp millicache stats    # Run WP-CLI commands
npm run env:redis-cli                  # Open Redis CLI
npm run env:reset                      # Reset environment
```

## Credits

MilliCache was initially inspired by:
- [Page Cache Red](https://github.com/pressjitsu/pj-page-cache-red) by Pressjitsu
- [Cachify](https://github.com/pluginkollektiv/cachify/) by PluginKollektive
- [Cache Enabler](https://github.com/keycdn/cache-enabler) by KeyCDN

## License

[GPLv2 or later](LICENSE)
