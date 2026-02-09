---
title: 'MilliCache Changelog'
post_excerpt: 'Version-by-version breakdown of new features, bug fixes, and improvements in MilliCache.'
menu_order: 30
---

# Changelog

## [1.2.0](https://github.com/MilliPress/MilliCache/compare/v1.1.0...v1.2.0) (2026-02-09)


### Features

* **cache:** Make cache pipeline accept explicit status and headers ([c309054](https://github.com/MilliPress/MilliCache/commit/c30905436708261eff048a1fad3e8b7bf7346a27))
* **engine:** Move middleware methods to their natural classes ([c6f47d3](https://github.com/MilliPress/MilliCache/commit/c6f47d3dd4277b3943e950cd444f0d1427269a73))


### Bug Fixes

* **release:** Use stable zip filename for WordPress compatibility ([3ce7cdc](https://github.com/MilliPress/MilliCache/commit/3ce7cdca7a111c504fd3ffdbe3417acf54be581b))

## [1.1.0](https://github.com/MilliPress/MilliCache/compare/v1.0.2...v1.1.0) (2026-02-07)


### Features

* **updater:** Add plugin update checker for seamless updates with WordPress update system ([a9fc17a](https://github.com/MilliPress/MilliCache/commit/a9fc17ad9a5fdbcf77f27ad198ce74851d5bf145))

## [1.0.2](https://github.com/MilliPress/MilliCache/compare/v1.0.1...v1.0.2) (2026-01-31)

Maintenance release with internal CI and build improvements.

## [1.0.1](https://github.com/MilliPress/MilliCache/compare/v1.0.0...v1.0.1) (2026-01-19)

### Bug Fixes

* **admin:** Fix cache size calculations to use bytes instead of kilobytes ([eb6a46c](https://github.com/MilliPress/MilliCache/commit/eb6a46c60e90bde15ea21699bc38f77f62be011b))

## [1.0.0](https://github.com/MilliPress/MilliCache/releases/tag/v1.0.0) (2026-01-16)

Initial stable release of MilliCache — a full-page cache plugin for WordPress powered by Redis compatible servers.

### Highlights

* **In-Memory Full-Page Cache** — Pages are served directly from memory before WordPress even initializes. No database queries.
* **Flag-Based Invalidation** — Tag cached pages with flags like `post:123` or `archive:category:5`, then clear related entries with a single command. Built-in flags are assigned automatically; custom flags give you full control.
* **Stale-While-Revalidate** — Serve expired content while fresh content regenerates in the background. Prevents cache stampedes on high-traffic pages.
* **Rules Engine** — Define caching behavior with a fluent, chainable PHP API. Set TTL, grace periods, and exclusions per condition — all version-controllable.
* **Multisite Native** — Per-site cache isolation with network-wide management. Clear one site, a subset, or an entire network.
* **Multiple Backends** — Redis, ValKey, KeyDB, or Dragonfly. Any Redis-compatible server works out of the box.
* **WP-CLI Integration** — Commands for cache testing, status checks, diagnostics, and bulk operations. AI-agent friendly.
* **Debug Headers & Browser Extension** — `X-MilliCache-*` headers show cache status, flags, and keys. The companion browser extension makes debugging effortless.
* **REST API** — `/millicache/v1/*` endpoints for cache control, status checks, and settings — ideal for CI/CD pipelines and monitoring.
* **Action & Filter Hooks** — Full customization of caching behavior, flag assignment, and invalidation events.
* **Smart Auto-Invalidation** — Cache clears automatically when posts, menus, widgets, or theme settings change.
* **Open Source** — GPL-2.0+ licensed. No vendor lock-in.
