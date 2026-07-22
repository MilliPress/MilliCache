# MilliCache

[![CI](https://github.com/MilliPress/MilliCache/actions/workflows/ci.yml/badge.svg)](https://github.com/MilliPress/MilliCache/actions/workflows/ci.yml)
[![e2e-Tests](https://github.com/MilliPress/MilliCache/actions/workflows/e2e.yml/badge.svg)](https://github.com/MilliPress/MilliCache/actions/workflows/e2e.yml)
[![Discord](https://img.shields.io/badge/Discord-Join%20the%20community-5865F2?logo=discord&logoColor=white)](https://discord.gg/mx8HAXaKGY)
[![GitHub Discussions](https://img.shields.io/github/discussions/MilliPress/MilliCache?logo=github&label=Discussions)](https://github.com/MilliPress/MilliCache/discussions)

**High-performance full-page caching for WordPress** using Redis, Valkey, or any compatible backend.

MilliCache stores complete HTML pages in memory and serves them in milliseconds — without loading WordPress. 
Combined with intelligent cache flags and flexible rules, it's designed for scaling sites that need both speed and control.

## Key Features

- **Lightning Fast** — In-memory caching with blazing fast response times
- **[Cache Flags](docs/03-cache-flags/01-introduction.md)** — Tag pages for precise, targeted cache invalidation
- **[Flexible Rules](docs/04-rules/01-introduction.md)** — Control caching behavior with condition-based rules
- **Multisite Native** — Per-site isolation, network-wide management
- **Built to Scale** — Multiple web servers can share a single Redis instance
- **WP-CLI Integration** — Full command-line cache management
- **[Multiple Backends](docs/08-storage-backends/01-overview.md)** — Redis, Valkey, Dragonfly, or any Redis-compatible server

Learn more on [millipress.com](https://www.millipress.com/millicache), where you also find the [full documentation](https://www.millipress.com/docs/millicache).

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

Full documentation is available on [millipress.com/docs/millicache](https://www.millipress.com/docs/millicache):

| Section                                                        | Description                            |
|----------------------------------------------------------------|----------------------------------------|
| [Getting Started](https://www.millipress.com/docs/millicache/01-getting-started/10-introduction/)  | Introduction and installation          |
| [Configuration](https://www.millipress.com/docs/millicache/02-configuration/01-overview/)          | Settings and constants reference       |
| [Cache Flags](https://www.millipress.com/docs/millicache/03-cache-flags/01-introduction/)          | Targeted cache invalidation            |
| [Rules](https://www.millipress.com/docs/millicache/04-rules/01-introduction/)                      | Condition-based caching control        |
| [Usage](https://www.millipress.com/docs/millicache/05-usage/10-how-caching-works/)                 | How caching works, clearing, multisite |
| [WP-CLI](https://www.millipress.com/docs/millicache/06-wp-cli/01-commands/)                        | Command-line reference                 |
| [Developers](https://www.millipress.com/docs/millicache/07-developers/01-architecture/)            | Architecture, hooks, API               |
| [Storage Backends](https://www.millipress.com/docs/millicache/08-storage-backends/01-overview/)    | Redis, Valkey, or any compatible server |
| [Troubleshooting](https://www.millipress.com/docs/millicache/09-troubleshooting/01-common-issues/) | Common issues and FAQ                  |

## MilliCache Pro

[MilliCache Pro](https://www.millipress.com/millicache-pro/) extends MilliCache with premium modules: a cache entries browser, a visual rules builder, block editor cache intelligence, cache preloading, detailed metrics, edge caching (bunny.net, Cloudflare), an asset CDN, and a persistent object cache. See the [Pro documentation](https://www.millipress.com/docs/millicache-pro/) for details.

## Requirements

- PHP 7.4+ (8.x recommended)
- WordPress 6.6+
- A Redis-compatible server: [Redis](https://redis.io/), [Valkey](https://valkey.io/), [Dragonfly](https://dragonflydb.io/), or any other RESP-compatible backend

## Community

The [Discord](https://discord.gg/mx8HAXaKGY) server and [GitHub Discussions](https://github.com/MilliPress/MilliCache/discussions) are places to swap setups, trade tips, and share your ideas for where MilliCache should head next. We're listening.

For bug reports, open a [GitHub issue](https://github.com/MilliPress/MilliCache/issues). [MilliCache Pro](https://www.millipress.com/millicache-pro) customers also get direct support by email and a private Discord channel.

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

[GPLv2 or later](LICENSE.txt)
