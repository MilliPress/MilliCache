---
title: 'MilliCache Changelog'
post_excerpt: 'Version-by-version breakdown of new features, bug fixes, and improvements in MilliCache.'
menu_order: 30
---

# Changelog

## [1.7.0-beta.1](https://github.com/MilliPress/MilliCache/compare/v1.7.0-beta...v1.7.0-beta.1) (2026-05-13)


### Features

* **admin:** add millicache_admin_notice action and HTML allowlist ([c693a0a](https://github.com/MilliPress/MilliCache/commit/c693a0ac19f88dc066e08a093d381fa210f2333d))
* **migrations:** add Core/Migrations with storage→network move ([63875c4](https://github.com/MilliPress/MilliCache/commit/63875c4d4ac75c199413deb195f631687c10223c))
* **status:** rework cache size metrics and Status tab UI ([88991c4](https://github.com/MilliPress/MilliCache/commit/88991c46555d890f6cc1e8bb10704cb1d5df1d8b))


### Bug Fixes

* **cache status:** Correct key prefix for site flags in status retrieval ([0f419a2](https://github.com/MilliPress/MilliCache/commit/0f419a2bf51f73a7f8a84c0b8ed3fb39945f223d))
* **ui:** Correct month value from 'M' to 'mo' ([a0dbbf5](https://github.com/MilliPress/MilliCache/commit/a0dbbf5c3496fc117fe4521fc950935418590601))

## [1.7.0-beta](https://github.com/MilliPress/MilliCache/compare/v1.6.2...v1.7.0-beta) (2026-05-05)

MilliCache 1.7.0-beta brings a significant upgrade to how the cache handles response variants. The new bucket framework gives any part of your stack a clean way to tell the cache "these requests are different" — whether that's by auth token, content type, A/B test arm, or any other signal you can express as a rule condition. On top of that, identical response bodies are now stored only once and shared across variants automatically, so sites serving multiple representations of the same content use meaningfully less Redis memory without any extra configuration.



### Features

* **cache:** Add bucket framework with content-addressable body dedup ([#126](https://github.com/MilliPress/MilliCache/issues/126)) ([a279fd7](https://github.com/MilliPress/MilliCache/commit/a279fd704a05ce58165aed45038798f175f214cb))

## [1.6.2](https://github.com/MilliPress/MilliCache/compare/v1.6.1...v1.6.2) (2026-05-04)


### Features

* **flags:** Normalize flag identifiers (lowercase + trim) at boundaries ([2859cd2](https://github.com/MilliPress/MilliCache/commit/2859cd21b1ac0791ee0d0323fa27f46747360540))
* **rules:** Load user-defined rules from settings into the rule engine ([b3fc827](https://github.com/MilliPress/MilliCache/commit/b3fc82709356b7a1e8cabab071504f2f2c4b778c))


### Bug Fixes

* **deps:** Require millipress/millirules ^1.1.5 ([4edb44d](https://github.com/MilliPress/MilliCache/commit/4edb44d9cf6a4d956f91624f0f53824e9d151d07))
* **storage:** Drop stale flag fields and their set memberships on store ([50b8d54](https://github.com/MilliPress/MilliCache/commit/50b8d541c9f155db62417311f91926f4b4d8efd4))


### Miscellaneous

* release 1.6.2 ([509add9](https://github.com/MilliPress/MilliCache/commit/509add99f3025e7859833b08f6c58601603c5dc1))

## [1.6.1](https://github.com/MilliPress/MilliCache/compare/v1.6.0...v1.6.1) (2026-05-01)


### Bug Fixes

* **deps:** Require millipress/millibase ^2.4.0 ([3d1a0f9](https://github.com/MilliPress/MilliCache/commit/3d1a0f968bad8da0aee81c21aa4b1ec9180ec326))

## [1.6.0](https://github.com/MilliPress/MilliCache/compare/v1.5.2...v1.6.0) (2026-04-30)


### Features

* **rules:** Expose MilliRules registry/validation through Manager ([812cf36](https://github.com/MilliPress/MilliCache/commit/812cf36980071bbd18884e6112fdfd22ba2d5e53))


### Bug Fixes

* **rules:** Restrict default REST rule to GET/HEAD to avoid lock warning ([3144a48](https://github.com/MilliPress/MilliCache/commit/3144a48541edc17296a8e5902b1c35bf466daff7))

## [1.5.2](https://github.com/MilliPress/MilliCache/compare/v1.5.1...v1.5.2) (2026-04-26)


### Features

* **plugin:** Implement singleton pattern for MilliCache instance management ([8d92bcb](https://github.com/MilliPress/MilliCache/commit/8d92bcb3b93fac1c2f11d0c611bf60a926fc6f47))


### Miscellaneous

* release 1.5.2 ([b2d9de5](https://github.com/MilliPress/MilliCache/commit/b2d9de569b5ec523b3ef845b7beeee873960abad))

## [1.5.1](https://github.com/MilliPress/MilliCache/compare/v1.5.0...v1.5.1) (2026-04-24)


### Bug Fixes

* **engine:** Simplify autoloader initialization for better compatibility ([fe45266](https://github.com/MilliPress/MilliCache/commit/fe45266fe14fef13df84ad4b2cb408a32f205ade))

## [1.5.0](https://github.com/MilliPress/MilliCache/compare/v1.4.2...v1.5.0) (2026-04-23)


### Features

* **rules:** Add action metadata, scoped locking, and order-aware execution ([7196cd3](https://github.com/MilliPress/MilliCache/commit/7196cd3132757d22c22e7e072900c371d015cfe1))
* **rules:** Lock critical built-in rules and use order 0/1 convention ([c011d4c](https://github.com/MilliPress/MilliCache/commit/c011d4cff4060885873d892f323b07e8d6709f3b))
* **storage:** support username for authentication ([#108](https://github.com/MilliPress/MilliCache/issues/108)) ([19d62eb](https://github.com/MilliPress/MilliCache/commit/19d62eb06295b9e704b9701ec4bb6c92155f574a))


### Bug Fixes

* **activator:** Handle both old and new variable name in drop-in regex ([cf80b88](https://github.com/MilliPress/MilliCache/commit/cf80b8800915250ad8c43e3076fe19421c7a67d3))
* **engine:** Use PHP_INT_MAX - 10 for template_redirect priority ([8b7118f](https://github.com/MilliPress/MilliCache/commit/8b7118f19161248a20c20c10874858ca1098c736))
* **settings:** Remove inline padding from status tab wrapper ([7d919f3](https://github.com/MilliPress/MilliCache/commit/7d919f3249ed52428c5be2fb9c415232c4d9954b))
* **tests:** Update do_cache arguments to include string type with default value ([5afed64](https://github.com/MilliPress/MilliCache/commit/5afed643a5c72377988b7e5df848f19ac66620a6))

## [1.4.2](https://github.com/MilliPress/MilliCache/compare/v1.4.1...v1.4.2) (2026-04-22)


### Bug Fixes

* **activator:** Ensure symlink creation only if function exists ([f935526](https://github.com/MilliPress/MilliCache/commit/f9355262ed1c560caf7cb9d1772ea00f94158573))

## [1.4.1](https://github.com/MilliPress/MilliCache/compare/v1.4.0...v1.4.1) (2026-04-22)


### Bug Fixes

* **advanced-cache.php:** Set correct path to plugin on copy file operation ([35179c2](https://github.com/MilliPress/MilliCache/commit/35179c2e44105ca2cbce553b6db00f284b42ac7c))

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
