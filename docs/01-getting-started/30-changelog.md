---
title: 'MilliCache Changelog'
post_excerpt: 'Version-by-version breakdown of new features, bug fixes, and improvements in MilliCache.'
menu_order: 30
---

# Changelog

## [1.4.0](https://github.com/MilliPress/MilliCache/compare/v1.3.2...v1.4.0) (2026-04-01)

### Features

* **cache:** Store request URL and variant dimensions in cache entries ([d334421](https://github.com/MilliPress/MilliCache/commit/d334421d6dab06eaa759eb8189a5fd60e302eb7d))
* **settings:** Make schema defaults available to add-ons at plugin load ([c899d3b](https://github.com/MilliPress/MilliCache/commit/c899d3b5f4ad852465a038ea84dfdf7cb83b871b))


### Bug Fixes

* **e2e:** Checkout into lowercase directory for consistent plugin slug ([9a554a0](https://github.com/MilliPress/MilliCache/commit/9a554a029db904c47b8314656686c58e90221ea2))
* **release:** Reset manifest to last published version ([015ab7a](https://github.com/MilliPress/MilliCache/commit/015ab7a88006e4a9d4c5e05cc17cc007282e25e3))
* **storage:** Exclude expired keys from cache index count ([dcc68ef](https://github.com/MilliPress/MilliCache/commit/dcc68efa050b41374b9dbc8625b8001b729efad0))
* **storage:** Filter Redis hash fields to correctly identify flag fields ([ee18c24](https://github.com/MilliPress/MilliCache/commit/ee18c2471589032da8f6ce5d8980bbcf6b54d5cd))
* **storage:** Remove backward compat for pre-1.4.0 cache entries ([6ab8e0a](https://github.com/MilliPress/MilliCache/commit/6ab8e0a1588552119ddbe4c8c437f6fcfc2296b0))
* **storage:** Respect per-entry custom TTL/grace in Redis EXPIRE ([fd8ec58](https://github.com/MilliPress/MilliCache/commit/fd8ec58888242ce641cb0d7586ddbacdef0cbeb4))
* **tests:** Eliminate connection warnings from Storage scheme tests ([306f31d](https://github.com/MilliPress/MilliCache/commit/306f31d4a2eab4c2a111434a7ec4d3dd1f256a3b))


### Build

* **e2e:** Migrate to wp-env 11 with lifecycle scripts ([0220f95](https://github.com/MilliPress/MilliCache/commit/0220f954698a738f54d9a99534115816ea3e5264))

## [1.3.2](https://github.com/MilliPress/MilliCache/compare/v1.3.1...v1.3.2) (2026-03-23)


### Bug Fixes

* **ui:** Prevent asset enqueueing when admin bar is not showing ([b9be14a](https://github.com/MilliPress/MilliCache/commit/b9be14a4b244cae231afa812be2182a376c9b675))

## [1.3.1](https://github.com/MilliPress/MilliCache/compare/v1.3.0...v1.3.1) (2026-03-16)


### Bug Fixes

* **i18n:** Defer UI config to init hook to prevent early textdomain loading ([df51634](https://github.com/MilliPress/MilliCache/commit/df5163495f77256d0cf116b16a672452c6f49044))
* **ui:** Register hooks for UI initialization to ensure proper textdomain loading ([208621d](https://github.com/MilliPress/MilliCache/commit/208621ddec33dcabe1117f6bef25ef32613c635f))

## [1.3.0](https://github.com/MilliPress/MilliCache/compare/v1.2.0...v1.3.0) (2026-03-15)


### Features

* **admin:** Rebuild settings UI with MilliBase components ([574145e](https://github.com/MilliPress/MilliCache/commit/574145e38ebd9828a5a0b5ee5cde433bc528006d))
* **settings:** Integrate MilliBase as the settings framework ([83ba3fb](https://github.com/MilliPress/MilliCache/commit/83ba3fbd3eb51b0c6056b80cd90a5ca47254e793))
* **storage:** Add TLS support via scheme prefix in MC_STORAGE_HOST ([ac77698](https://github.com/MilliPress/MilliCache/commit/ac77698223331fc9383fa6ea0c7b5ddf4eb8691e))


### Bug Fixes

* **e2e:** Use dynamic slug in post deletion invalidation test ([1dcc057](https://github.com/MilliPress/MilliCache/commit/1dcc0573fc4c4090d45d7b7b3dc23638093a03b7))
* **manager:** Clearing by targets processes double prefixed flags in Multisite. ([17971f3](https://github.com/MilliPress/MilliCache/commit/17971f31b2e1c29dad7efdf12fbf92a610091e88))

## [1.2.0](https://github.com/MilliPress/MilliCache/compare/v1.1.0...v1.2.0) (2026-03-02)


### Features

* **storage:** Support Unix socket paths for Redis connections ([78254d7](https://github.com/MilliPress/MilliCache/commit/78254d77db584a6cacda52fadfd2e07e7552165f))


### Bug Fixes

* **storage:** Handle PredisException when retrieving Redis/Valkey config ([098b67c](https://github.com/MilliPress/MilliCache/commit/098b67ca6ae23901b28f8a594c8d2c3b4f7ebf2e))

## [1.1.0](https://github.com/MilliPress/MilliCache/compare/v1.0.2...v1.1.0) (2026-02-21)


### Features

* **deps:** Upgrade predis/predis from ^2.2 to ^3.0 ([b84a8bd](https://github.com/MilliPress/MilliCache/commit/b84a8bd5b3604308aea514c6559b88d1220a724d))


### Bug Fixes

* **release:** Remove draft config so Release Please creates git tags ([6d11112](https://github.com/MilliPress/MilliCache/commit/6d111121d23d4374d35f0facd08420dde3c22193))
* **ui:** Replace removed `warning` icon with `caution` ([3b8f686](https://github.com/MilliPress/MilliCache/commit/3b8f686e6540b2e688aaf9319e3a2b0b523d52cd))

## [1.0.2](https://github.com/MilliPress/MilliCache/compare/v1.0.1...v1.0.2) (2026-02-16)


### Bug Fixes

* Make check_cache_decision() public and remove Options::is_caching_allowed() ([b713aed](https://github.com/MilliPress/MilliCache/commit/b713aed77aa44708227596d7a66466d6057708bd))

## [1.0.1](https://github.com/MilliPress/MilliCache/compare/v1.0.0...v1.0.1) (2026-02-15)


### Bug Fixes

* **ci:** Use RELEASE_TOKEN for release-please to trigger PR workflows ([2f8322e](https://github.com/MilliPress/MilliCache/commit/2f8322efeb407fe34511ed6f5c28d1e279fa68a8))
* Register action namespaces in Engine constructor ([4a1ffb3](https://github.com/MilliPress/MilliCache/commit/4a1ffb3dd537536996acf3c137c5431f10ee348b))


### Refactoring

* **ci:** Move E2E from PR trigger to release workflow gate ([3c9de93](https://github.com/MilliPress/MilliCache/commit/3c9de93ecc5a7085bce86d906368ff44f92456a2))
* **ci:** Remove post-merge CI/E2E gates from release workflow ([810055a](https://github.com/MilliPress/MilliCache/commit/810055a38dfe84c43bc817472b426babebc50376))

## 1.0.0 (2026-02-13)

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
