---
title: 'MilliCache Changelog'
description: 'Release notes for every MilliCache version: new features, bug fixes, and improvements to the Redis full-page cache plugin for WordPress, with commit links.'
menu_order: 30
---

# Changelog

## [1.8.1-beta](https://github.com/MilliPress/MilliCache/compare/v1.8.0...v1.8.1-beta) (2026-08-29)


### Bug Fixes

* **engine:** keep ignored query keys in the request until rendering ([f6f7d33](https://github.com/MilliPress/MilliCache/commit/f6f7d3350f1f66216462292f9390a160ff42f3b8))

## [1.8.0](https://github.com/MilliPress/MilliCache/compare/v1.7.7...v1.8.0) (2026-08-22)

<!-- mc:auto sha=9ba0bffb378a -->
1.8.0 brings a redesigned cache management experience centered on a command palette, new abilities for AI assistants, and several correctness fixes to the caching engine and rule system.

The admin bar's one-click flush button is replaced by a command palette that lets you clear or expire specific targets — pages, post types, taxonomies, or the full cache — with descriptive labels. A snackbar confirms the action and reports how many entries were actually removed. Add-ons can register their own palette commands and control their position in the list. The palette is wider to accommodate longer target names and no longer intrudes on every admin search field.

**AI assistants can now check cache status and clear the cache.** Two new abilities — available over REST and from MCP clients — answer the questions site owners most often ask through an assistant: "why is my page not cached" and "clear the cache for this post or URL". The status ability returns a curated health summary (connectivity, entry count, and the checks that need attention) without leaking the full plugin and theme inventory that the settings UI needs. [MilliCache Pro](https://www.millipress.com/millicache-pro/) extends this to every aspect of the plugin: caching, cache entries, rules, preloading, the edge cache, and the object cache can all be managed through abilities as well.

Cache responses now include the remaining lifetime of a served page. The engine has been corrected to never store redirect responses and to capture output from the outermost buffer, which prevents partial content from being cached when other plugins open buffers early. URL hashes now preserve non-default ports, so sites on non-standard ports get correct per-URL cache isolation. The invalidation queue runs even when the drop-in fails to load, so scheduled purges are not silently lost.

On the rules side, `wp-cron.php` is now excluded by a dedicated locked rule that cannot be overridden by site rules. Previously it was only covered by the generic dot-file rule, which a higher-priority site rule could outrank, potentially causing a stale cron lock response to be replayed and stalling scheduled events. The REST API exclusion now also matches the `?rest_route=` query-string form used when pretty permalinks are off.

WP-CLI's clear flags are now scoped to the current site context on multisite. Redundant entries have been removed from the admin search results. Portuguese (Brazil), Italian, Spanish, and French translations are complete.
<!-- /mc:auto -->

### Features

* **abilities:** let assistants read cache status and clear the cache ([f80b057](https://github.com/MilliPress/MilliCache/commit/f80b057e2d88a2d6ab68a88d63495b2bad36a149))
* **abilities:** report network-wide problems in a site's cache status ([4e2b9f1](https://github.com/MilliPress/MilliCache/commit/4e2b9f1be5fc0e3a6841d7463a555fd57002b544))
* **abilities:** say whether the install is a multisite ([4573485](https://github.com/MilliPress/MilliCache/commit/457348597a48a8cd7e7feb2dcdf306d73d392078))
* **adminbar:** replace one-click flush with command palette integration ([382b10b](https://github.com/MilliPress/MilliCache/commit/382b10beec75a421834632919875090c65d21443))
* **adminbar:** snackbar clear feedback and a wider palette ([2c77206](https://github.com/MilliPress/MilliCache/commit/2c77206eb6b1635730c2047168b942b88bb03b03))
* **cache:** state the lifetime a replayed page has left ([8f699fd](https://github.com/MilliPress/MilliCache/commit/8f699fdad02c3d797f7923cb26b09c99cd5596d2))
* **clear:** report removed-entry counts instead of processed inputs ([23ec58f](https://github.com/MilliPress/MilliCache/commit/23ec58f9b5b2d09a23fae07137aafb171c912837))
* **cli:** scope bare clear flags to the WP-CLI site context ([d30d31e](https://github.com/MilliPress/MilliCache/commit/d30d31e386408c3a072c513a29108eb7528b9a6e))
* **commands:** let add-ons ride the palette's promote/demote cycle ([1db3d3f](https://github.com/MilliPress/MilliCache/commit/1db3d3f1a7475c2fe5221e82ae9fed2c5645c609))
* **commands:** offer expire alongside clear with descriptive target labels ([880c9eb](https://github.com/MilliPress/MilliCache/commit/880c9ebc4bf0e57edff930cb4f31c55578c58d01))
* **engine:** capture the page in the outermost output buffer ([6c4d393](https://github.com/MilliPress/MilliCache/commit/6c4d393ba419e569ab4b8e5aca0f989ecf95d240))
* **engine:** capture the page in the outermost output buffer ([7606f04](https://github.com/MilliPress/MilliCache/commit/7606f04b2ea9749f4ffd41f4a52e203bc477b2b6))
* **engine:** capture the page in the outermost output buffer ([6cee142](https://github.com/MilliPress/MilliCache/commit/6cee14202bdaf15594dfd7b9ed659533ef025d4b))
* **engine:** expose the request's effective TTL override ([31e2555](https://github.com/MilliPress/MilliCache/commit/31e2555437c91f94271313df3a5a6be37dd8984e))
* **rules:** build the rule registry when the drop-in has not ([b6ef71b](https://github.com/MilliPress/MilliCache/commit/b6ef71b032d5f37f748ddc6ae7a75d209ea2a341))


### Bug Fixes

* **adminbar:** keep the admin bar button size stable on page load ([2c77206](https://github.com/MilliPress/MilliCache/commit/2c77206eb6b1635730c2047168b942b88bb03b03))
* **cache:** report targets that belong to another site ([6a7947a](https://github.com/MilliPress/MilliCache/commit/6a7947ae23b67a4a6636befed36827171851427e))
* **clear:** anchor path-only URL targets onto the home URL ([27bd7dc](https://github.com/MilliPress/MilliCache/commit/27bd7dc6c42f3d2210bd02ae148d31fb461a08e5))
* **clear:** skip non-viewable taxonomies in post-related flags ([40cd884](https://github.com/MilliPress/MilliCache/commit/40cd88414019eda097f910bce72fb6acd40a01e8))
* **commands:** drop the stray focus ring after palette clears ([880c9eb](https://github.com/MilliPress/MilliCache/commit/880c9ebc4bf0e57edff930cb4f31c55578c58d01))
* **commands:** stop crowding every admin search, and drop the settings entry ([87b52d1](https://github.com/MilliPress/MilliCache/commit/87b52d1c19b0c499ea2200306a3f4a9631021bf2))
* **engine:** execute the invalidation queue when the drop-in never loads ([746bd0e](https://github.com/MilliPress/MilliCache/commit/746bd0eb9cfafe57df36c39a45ac8074d0708819))
* **engine:** keep non-default ports in URL-based cache hashes ([ed6f3d6](https://github.com/MilliPress/MilliCache/commit/ed6f3d670305dcf7750ed49bf8425e1149d80efa))
* **engine:** never store redirect responses ([6c4d393](https://github.com/MilliPress/MilliCache/commit/6c4d393ba419e569ab4b8e5aca0f989ecf95d240))
* **engine:** never store redirect responses ([7606f04](https://github.com/MilliPress/MilliCache/commit/7606f04b2ea9749f4ffd41f4a52e203bc477b2b6))
* **engine:** never store redirect responses ([6cee142](https://github.com/MilliPress/MilliCache/commit/6cee14202bdaf15594dfd7b9ed659533ef025d4b))
* **rules:** lock wp-cron.php out of the cache and cover the rest_route form ([908ce04](https://github.com/MilliPress/MilliCache/commit/908ce04ec9a3f0e4b64813253eebb69135b7c36c))
* **rules:** skip an action whose placeholder resolved to nothing ([a5e4894](https://github.com/MilliPress/MilliCache/commit/a5e4894ad0a10ede4b91cce7f67463cea2c9ebc9))
* **updates:** keep update checks off every admin page load ([1661d07](https://github.com/MilliPress/MilliCache/commit/1661d079641cccc28003d304659cbdbe769070df))

## [1.8.0-beta.2](https://github.com/MilliPress/MilliCache/compare/v1.8.0-beta.1...v1.8.0-beta.2) (2026-08-16)

<!-- mc:auto sha=5f67c5ceb2e0 -->
This release tightens how MilliCache interacts with CDNs and external caches, extends what AI assistants and WP-CLI can see, and closes a handful of gaps that caused silent failures.

**Replayed pages now tell downstream caches their true remaining lifetime.** Previously a cached response replayed its original `Cache-Control` headers unchanged, so a shared cache or CDN that ignored the `Age` header would treat a near-expired entry as freshly filled and hold it for the full `s-maxage`. Replayed responses now carry only the share of `s-maxage` that actually remains, so caches that ignore `Age` expire in step with the stored entry rather than well after it.

**AI assistants can now check cache status and clear the cache.** Two new abilities — available over REST and from MCP clients — answer the questions site owners most often ask through an assistant: "why is my page not cached" and "clear the cache for this post or URL". The status ability returns a curated health summary (connectivity, entry count, and the checks that need attention) without leaking the full plugin and theme inventory that the settings UI needs.

**WP-CLI now sees the full rule registry.** Because WP-CLI skips the drop-in, `wp millicache rules list` previously showed an empty engine — no built-in rules and nothing registered through `millicache()->rules()`. The manager now registers the rule set itself when it finds it missing, so the same question gets the same answer regardless of how it is asked.

**Placeholder-driven rule actions no longer silently write garbage.** When a bucket or flag was set from a placeholder that could not be filled — a missing cookie, for example — the literal placeholder text `{cookie.geo_country}` was used as the bucket name, pooling every visitor without that cookie together. Those actions now do nothing when the placeholder is empty or unresolved.
<!-- /mc:auto -->

### Features

* **abilities:** let assistants read cache status and clear the cache ([f80b057](https://github.com/MilliPress/MilliCache/commit/f80b057e2d88a2d6ab68a88d63495b2bad36a149))
* **abilities:** report network-wide problems in a site's cache status ([4e2b9f1](https://github.com/MilliPress/MilliCache/commit/4e2b9f1be5fc0e3a6841d7463a555fd57002b544))
* **abilities:** say whether the install is a multisite ([4573485](https://github.com/MilliPress/MilliCache/commit/457348597a48a8cd7e7feb2dcdf306d73d392078))
* **cache:** state the lifetime a replayed page has left ([8f699fd](https://github.com/MilliPress/MilliCache/commit/8f699fdad02c3d797f7923cb26b09c99cd5596d2))
* **commands:** let add-ons ride the palette's promote/demote cycle ([1db3d3f](https://github.com/MilliPress/MilliCache/commit/1db3d3f1a7475c2fe5221e82ae9fed2c5645c609))
* **engine:** expose the request's effective TTL override ([31e2555](https://github.com/MilliPress/MilliCache/commit/31e2555437c91f94271313df3a5a6be37dd8984e))
* **rules:** build the rule registry when the drop-in has not ([b6ef71b](https://github.com/MilliPress/MilliCache/commit/b6ef71b032d5f37f748ddc6ae7a75d209ea2a341))


### Bug Fixes

* **cache:** report targets that belong to another site ([6a7947a](https://github.com/MilliPress/MilliCache/commit/6a7947ae23b67a4a6636befed36827171851427e))
* **commands:** stop crowding every admin search, and drop the settings entry ([87b52d1](https://github.com/MilliPress/MilliCache/commit/87b52d1c19b0c499ea2200306a3f4a9631021bf2))
* **rules:** skip an action whose placeholder resolved to nothing ([a5e4894](https://github.com/MilliPress/MilliCache/commit/a5e4894ad0a10ede4b91cce7f67463cea2c9ebc9))

## [1.8.0-beta.1](https://github.com/MilliPress/MilliCache/compare/v1.8.0-beta...v1.8.0-beta.1) (2026-08-10)

<!-- mc:auto sha=98337a07d375 -->
Plugin and language-pack update checks previously ran on every admin page load, so a slow or unresponsive millipress.com could stall every admin request waiting for a timeout. Both checks now run only during WordPress's scheduled update refresh cycles, with a 3-second timeout and a 15-minute back-off after any failure. "Check again" still triggers an immediate fresh check.
<!-- /mc:auto -->

### Bug Fixes

* **updates:** keep update checks off every admin page load ([1661d07](https://github.com/MilliPress/MilliCache/commit/1661d079641cccc28003d304659cbdbe769070df))

## [1.8.0-beta](https://github.com/MilliPress/MilliCache/compare/v1.7.7...v1.8.0-beta) (2026-08-07)

<!-- mc:auto sha=df2f4a4a2b26 -->
1.8.0-beta is a significant release centered on two themes: a smarter cache-clearing experience and a more reliable caching engine.

**Command palette integration.** The admin bar Cache button now opens the WordPress command palette (wp-admin, WordPress 7.0+) instead of immediately flushing. From there you can clear or expire the website or network cache, jump to settings, or type a post ID, URL, or flag to target exactly what needs clearing. Expire is new — it marks entries stale for stale-while-revalidate regeneration rather than deleting them outright. Clear and expire results surface as snackbar toasts with the actual entry count removed, so you know whether anything was matched. On the front end and older WordPress the submenu behaves as before, with a two-step confirmation added for the network-wide clear.

**Accurate cache invalidation.** Several bugs that caused clears to silently match nothing are fixed: path-only URL targets (e.g. `/blog/`) are now anchored to the home URL before hashing, so they find what was actually stored. Sites running on non-standard ports no longer miss URL-based clears due to a port mismatch in the hash. WP-CLI flag clears on multisite now respect the `--url` site context instead of looking for a bare flag that was never stored. Internal taxonomies (Polylang language terms, nav menus) are excluded from post-related invalidation batches, which reduces unnecessary purge payloads. And queue execution is now guaranteed even in WP-CLI processes where the drop-in never loads, so publishes and imports triggered from the CLI actually clear the cache.

**Outermost output buffer capture.** The engine now opens its capture buffer before any plugin loads, meaning HTML post-processors (TranslatePress, HTML optimizers) run inside it. Their final output is what gets stored, eliminating a class of caching issues where transformed HTML was bypassed. Redirect responses (3xx) are never stored. The new `millicache()->response()->is_storable()` method is available for extensions.
<!-- /mc:auto -->

### Features

* **adminbar:** replace one-click flush with command palette integration ([382b10b](https://github.com/MilliPress/MilliCache/commit/382b10beec75a421834632919875090c65d21443))
* **adminbar:** snackbar clear feedback and a wider palette ([2c77206](https://github.com/MilliPress/MilliCache/commit/2c77206eb6b1635730c2047168b942b88bb03b03))
* **clear:** report removed-entry counts instead of processed inputs ([23ec58f](https://github.com/MilliPress/MilliCache/commit/23ec58f9b5b2d09a23fae07137aafb171c912837))
* **cli:** scope bare clear flags to the WP-CLI site context ([d30d31e](https://github.com/MilliPress/MilliCache/commit/d30d31e386408c3a072c513a29108eb7528b9a6e))
* **commands:** offer expire alongside clear with descriptive target labels ([880c9eb](https://github.com/MilliPress/MilliCache/commit/880c9ebc4bf0e57edff930cb4f31c55578c58d01))
* **engine:** capture the page in the outermost output buffer ([7606f04](https://github.com/MilliPress/MilliCache/commit/7606f04b2ea9749f4ffd41f4a52e203bc477b2b6))


### Bug Fixes

* **adminbar:** keep the admin bar button size stable on page load ([2c77206](https://github.com/MilliPress/MilliCache/commit/2c77206eb6b1635730c2047168b942b88bb03b03))
* **clear:** anchor path-only URL targets onto the home URL ([27bd7dc](https://github.com/MilliPress/MilliCache/commit/27bd7dc6c42f3d2210bd02ae148d31fb461a08e5))
* **clear:** skip non-viewable taxonomies in post-related flags ([40cd884](https://github.com/MilliPress/MilliCache/commit/40cd88414019eda097f910bce72fb6acd40a01e8))
* **commands:** drop the stray focus ring after palette clears ([880c9eb](https://github.com/MilliPress/MilliCache/commit/880c9ebc4bf0e57edff930cb4f31c55578c58d01))
* **engine:** execute the invalidation queue when the drop-in never loads ([746bd0e](https://github.com/MilliPress/MilliCache/commit/746bd0eb9cfafe57df36c39a45ac8074d0708819))
* **engine:** keep non-default ports in URL-based cache hashes ([ed6f3d6](https://github.com/MilliPress/MilliCache/commit/ed6f3d670305dcf7750ed49bf8425e1149d80efa))
* **engine:** never store redirect responses ([6cee142](https://github.com/MilliPress/MilliCache/commit/6cee14202bdaf15594dfd7b9ed659533ef025d4b))

## [1.7.7](https://github.com/MilliPress/MilliCache/compare/v1.7.6...v1.7.7) (2026-07-29)

<!-- mc:auto sha=36dc5e53f3fb -->
Multisite metrics are the headlining fix here: response times, bandwidth, and stale-serve counts were silently dropped on network installs, leaving Insights charts flat. That's resolved.

On the status side, a new dashboard check surfaces when the settings config file can't be written — meaning components that load before WordPress would otherwise run on stale settings without any warning. MilliCache heals the file automatically once the directory is writable again. Relatedly, constants now behave more predictably: defining one sets and locks the value, and removing it unlocks the setting while preserving whatever was last saved.
<!-- /mc:auto -->

### Features

* **dropins:** share install reporting and heal extension drop-ins ([800111c](https://github.com/MilliPress/MilliCache/commit/800111c9cf4b4666669ca075ac606b4deb2d24aa))
* **engine:** expose readiness for exception-free drop-in probes ([24b8d37](https://github.com/MilliPress/MilliCache/commit/24b8d37146f2dd8e2a0b83d5a0214c2527af9c64))
* **status:** report when the config file cannot be written ([54f2e82](https://github.com/MilliPress/MilliCache/commit/54f2e821843c0d165db4fc463b9346af2b88822e))


### Bug Fixes

* **deps:** require millipress/millibase ^2.8.0 ([dd906d9](https://github.com/MilliPress/MilliCache/commit/dd906d9f241b121b865a9f8ba7acd883e95e489f))
* **metrics:** record response times and honor retention on multisite ([087e764](https://github.com/MilliPress/MilliCache/commit/087e7645a2c168d2eca40001b81dc6b44986c09f))
* Reinstall an already-correct drop-in symlink when --force is passed ([3b2ed2f](https://github.com/MilliPress/MilliCache/commit/3b2ed2fc2540f0987e6623b8091401ccb50fa181))


### Miscellaneous

* pin the next release to 1.7.7 ([dd67e0c](https://github.com/MilliPress/MilliCache/commit/dd67e0c8fe214974e0c965d1e1503e0fe2277222))

## [1.7.6](https://github.com/MilliPress/MilliCache/compare/v1.7.5...v1.7.6) (2026-07-28)


### Features

* **engine:** announce every cache clear as one merged flag batch ([e2f1513](https://github.com/MilliPress/MilliCache/commit/e2f15137798c35e975f3c7d1a5da4bbca786da4a))
* **status:** warn before the storage server runs out of memory ([a4fc9e6](https://github.com/MilliPress/MilliCache/commit/a4fc9e69116e4419bc1fd4f8b9fe793e322f2552))


### Bug Fixes

* **cli:** give the interactive redis-cli session the real terminal ([14e2128](https://github.com/MilliPress/MilliCache/commit/14e21281b09e5a009b3617c56d7cbcfa5343737f))


### Miscellaneous

* **deps:** update dev dependencies ([2aa340a](https://github.com/MilliPress/MilliCache/commit/2aa340a8656abc5fb5ecac0ee5fd571d86424c22))

## [1.7.5](https://github.com/MilliPress/MilliCache/compare/v1.7.4...v1.7.5) (2026-07-24)


### Bug Fixes

* **cache:** clear a post's cache when it is unpublished ([43a1a88](https://github.com/MilliPress/MilliCache/commit/43a1a883c1e28eb633ac7b427e32bcb5842da656))
* **cache:** clear feed caches when a post is published or updated ([b04ede8](https://github.com/MilliPress/MilliCache/commit/b04ede8a015f8d2a34ded93236e76be825538c57))
* **cache:** fire millicache_cache_cleared_by_posts on automatic post invalidation ([6615c7d](https://github.com/MilliPress/MilliCache/commit/6615c7d44eae208ccc5730bd23b386c347930917))
* **engine:** accept Vary tokens covered by request keying or inert on GET ([53ad7b7](https://github.com/MilliPress/MilliCache/commit/53ad7b74b7f2d3cd9118a64bca28aaee3b994427)), closes [#172](https://github.com/MilliPress/MilliCache/issues/172)
* **engine:** resolve Authorization bucket from redirect and basic-auth channels ([7e7ed1b](https://github.com/MilliPress/MilliCache/commit/7e7ed1b78d17323ab77afe5fbc71b8c6ceb53591))
* **storage:** prevent a fatal error when toggling MilliCache alongside MilliCache Pro ([1ad4949](https://github.com/MilliPress/MilliCache/commit/1ad4949e9a2f667d5942bf0cb7f5eb3f8c513fc3))

## [1.7.4](https://github.com/MilliPress/MilliCache/compare/v1.7.3...v1.7.4) (2026-07-22)


### Features

* **i18n:** install language packs from the millipress.com languages API ([dd2d73d](https://github.com/MilliPress/MilliCache/commit/dd2d73dc3f8d7a1f1d4f5dd432bab3693e38e891))
* **release:** post a single Discord notification on stable release ([57b0def](https://github.com/MilliPress/MilliCache/commit/57b0defddc9ffac48c948eae8681179f5e268d84))


### Bug Fixes

* **i18n:** serve JS translations as handle-named full catalogs ([747594a](https://github.com/MilliPress/MilliCache/commit/747594a108fcf46408d53dd75f6a99b0dc1ca6cb))
* **updater:** Correct endpoint URL for plugin update information ([46611ad](https://github.com/MilliPress/MilliCache/commit/46611ad197ff3c59ee0205bb8526139578dbdfbb))


### Miscellaneous

* **release:** ship the language-pack injector as 1.7.4 ([5bd8ff6](https://github.com/MilliPress/MilliCache/commit/5bd8ff6f730e63d90c14683e106105ed1b4be14f))

## [1.7.3](https://github.com/MilliPress/MilliCache/compare/v1.7.2...v1.7.3) (2026-07-19)


### Features

* **cli:** let `wp millicache drop` target and reinstall any drop-in ([8937287](https://github.com/MilliPress/MilliCache/commit/8937287d2d7ff505f4d91a53eb76a1d26c1e925e))
* **i18n:** make translation-ready for language-pack delivery ([4757aa8](https://github.com/MilliPress/MilliCache/commit/4757aa81ab833b2927f6d0d453253128d23e3cd5))
* **logging:** adopt the shared MilliBase Logger ([7eccbaa](https://github.com/MilliPress/MilliCache/commit/7eccbaae4a7010719e4a9330fda3fca5d5b415c8))


### Bug Fixes

* **pcp:** satisfy Plugin Check across shipped sources ([41f918d](https://github.com/MilliPress/MilliCache/commit/41f918de3912bde84f05708fc3ebea51f73cc6bb))
* **ui:** share Status card styles between MilliCache and MilliCache Pro ([6aac122](https://github.com/MilliPress/MilliCache/commit/6aac12210d438dec29cadfd54285e69e7bb9dd28))

## [1.7.2](https://github.com/MilliPress/MilliCache/compare/v1.7.1...v1.7.2) (2026-07-16)


### Bug Fixes

* **dropin:** invalidate OPcache when installing or removing drop-ins ([841707d](https://github.com/MilliPress/MilliCache/commit/841707dde73f15ac743d72bacff603bd86bb62d7))
* **rules:** share the rules engine with other MilliRules integrations ([ba02b55](https://github.com/MilliPress/MilliCache/commit/ba02b55a922b82426e3406a9b37ebc7ff8be8b31))
* **settings:** recognize the cache buckets key so MC_CACHE_BUCKETS works ([3255794](https://github.com/MilliPress/MilliCache/commit/32557945d9c48aae5f25dc11dbe6e87b466e1116))
* **updater:** only self-update when this copy owns the plugin basename ([2f6d584](https://github.com/MilliPress/MilliCache/commit/2f6d5849e83d1027b026948ba40221a516326944))

## [1.7.1](https://github.com/MilliPress/MilliCache/compare/v1.7.0...v1.7.1) (2026-07-14)


### Bug Fixes

* **engine:** publish singleton before re-entrant bootstrap work ([cd6aa21](https://github.com/MilliPress/MilliCache/commit/cd6aa21c480de4cb4b2de5ff9b1bdad956bff783))
* **status:** stop KPI tiles stretching in Safari ([c8d2945](https://github.com/MilliPress/MilliCache/commit/c8d2945b00ca287e8583f43ac20e3738a7bc1f5c))

## [1.7.0](https://github.com/MilliPress/MilliCache/compare/v1.6.2...v1.7.0) (2026-07-12)

MilliCache 1.7.0 is a big step forward. The full feature list is long, so here are the highlights:

**Redis Replication & Sentinel**
MilliCache now supports high-availability setups. For most sites we still recommend simply running Redis or Valkey on the host server, but larger projects that need a more resilient topology are now covered.

**Status Graphs**
You can finally see how your cache is actually performing. A rebuilt Status dashboard charts your hit and miss ratio over time, so you can tell at a glance whether the cache is doing its job. On Multisite you also get network-wide stats.

**Cache Buckets**
Store different versions of the same request, for example based on a request header. This is really handy with plugins like roots/post-content-to-markdown, where you want to cache and serve different responses for the same URL: HTML for humans, Markdown for AI. In MilliCache that's a single rule.

**Deduplication**
Byte-identical responses are stored only once. MilliCache content-addresses each response body, so any requests that produce the exact same HTML (whether it's /?foo and /?foo=bar, or two entirely different URLs) share a single stored copy in Redis. Less memory, same speed.

**Cache Health**
Beyond the graphs, MilliCache now integrates with WordPress Site Health, so issues surface where you'd expect them: you won't miss it if, for example, your drop-in file is not in place.


### Features

* **admin:** add millicache_admin_notice action and HTML allowlist ([c693a0a](https://github.com/MilliPress/MilliCache/commit/c693a0ac19f88dc066e08a093d381fa210f2333d))
* **adminbar:** animate + 500ms delay before post-clear recount ([00c7dc1](https://github.com/MilliPress/MilliCache/commit/00c7dc1199e1768c21f6432be3c436f44190d3db))
* **adminbar:** live cache size + bounded current-view clear ([3877451](https://github.com/MilliPress/MilliCache/commit/38774516beb6e970597a37512d0c9d0b81fdd0fb))
* **admin:** centered loading indicator on the Status tab ([534d7b0](https://github.com/MilliPress/MilliCache/commit/534d7b0923dc9832732f6cb2db2f6e4bd1385d49))
* **admin:** count user-defined custom rules in the snapshot ([ab0d686](https://github.com/MilliPress/MilliCache/commit/ab0d686e954866dfb2fdf4016b79ce99d7cbfe0f))
* **admin:** docs links per check, "warnings" pill label, rules in snapshot ([38307a4](https://github.com/MilliPress/MilliCache/commit/38307a4e60a2fea6e389ac8db6a30244327210bf))
* **admin:** expose three extension filters on the status snapshot ([28c8cf1](https://github.com/MilliPress/MilliCache/commit/28c8cf1b59de5c0b90efea6bd902ff72bbb31cc8))
* **admin:** footer Status indicator with unified status payload ([dd72515](https://github.com/MilliPress/MilliCache/commit/dd72515259044f3654f75e91614697e05e96028a))
* **admin:** integrate with the WordPress Site Health screens ([2470fb8](https://github.com/MilliPress/MilliCache/commit/2470fb87100c16adabc171b163f158376294d361))
* **admin:** make the status extension filters scope-aware ([9498b55](https://github.com/MilliPress/MilliCache/commit/9498b55031c3f881c79fe2867f98d3ed35942089))
* **admin:** per-check breakdown in the footer Status modal ([52e58c9](https://github.com/MilliPress/MilliCache/commit/52e58c970d82df3e0de35323ff0b16dc99841b0b))
* **admin:** polish free Status chart to match Pro ([4841e79](https://github.com/MilliPress/MilliCache/commit/4841e794e5e0c78edbea182b31c933f5ce652ecb))
* **admin:** rebuild the Status tab — fixed panels, KPI/chart cards, lean Pro teaser ([1ef92ca](https://github.com/MilliPress/MilliCache/commit/1ef92ca5a3bbe78e0819a099d23a19520a6aad1d))
* **admin:** surface storage topology across CLI, status, and settings ([3f34067](https://github.com/MilliPress/MilliCache/commit/3f34067fc312b831ee26825f7727e53f3b9d56e2))
* **cache:** Add bucket framework with content-addressable body dedup ([#126](https://github.com/MilliPress/MilliCache/issues/126)) ([a279fd7](https://github.com/MilliPress/MilliCache/commit/a279fd704a05ce58165aed45038798f175f214cb))
* **cache:** cap entries at 5MB raw to protect Redis from oversized responses ([2e2511f](https://github.com/MilliPress/MilliCache/commit/2e2511fa8df6cf45a9d31379d82961a5afa9aad1))
* **engine:** expose install_mode() to report how MilliCache is loaded ([788e079](https://github.com/MilliPress/MilliCache/commit/788e0793fe65d35bd2d55131f50e7d485ab9ac31))
* hand the advanced-cache.php drop-in between co-resident MilliCache copies ([80d5ab9](https://github.com/MilliPress/MilliCache/commit/80d5ab9b56f496b1549afd6c621ed5c00ff42673))
* **hooks:** standardize cache-cleared action names ([1dedb37](https://github.com/MilliPress/MilliCache/commit/1dedb3796bb47aaa2605adb56242171f8d6e8827))
* **metrics:** make hit/miss retention windows configurable ([45257b5](https://github.com/MilliPress/MilliCache/commit/45257b5094ef9a187915bb2decbe718af6ef0f38))
* **metrics:** record hit/miss in the response path, excluding the preloader ([f42780f](https://github.com/MilliPress/MilliCache/commit/f42780ff1dbe442732f06f8e25bfd09ae591a656))
* **metrics:** time-bucketed per-blog hit/miss metrics engine ([e031f9c](https://github.com/MilliPress/MilliCache/commit/e031f9cde4164537eb5ca476aef2e0d4496a1f2e))
* **migrations:** add Core/Migrations with storage→network move ([63875c4](https://github.com/MilliPress/MilliCache/commit/63875c4d4ac75c199413deb195f631687c10223c))
* **plugin:** Add author information and plugin URI to advanced cache file ([ef97d53](https://github.com/MilliPress/MilliCache/commit/ef97d532432a62e883dffa575e467769db38801f))
* **response:** emit Age header on cache hits (RFC 9111) ([035bd24](https://github.com/MilliPress/MilliCache/commit/035bd24a13e7e86295b72c11e58ec97bc4445f2d))
* **rules:** skip caching search result pages by default ([4bf4be3](https://github.com/MilliPress/MilliCache/commit/4bf4be35f1f02ef6a1a1a13a255bbb0bc358f795))
* **settings:** Include metrics in network-scoped Settings instance for multisite ([59d2fa5](https://github.com/MilliPress/MilliCache/commit/59d2fa5ad339c9de43c9aef6699aa97e4cdf8b4b))
* **settings:** order the settings tabs by declared position ([d8f208a](https://github.com/MilliPress/MilliCache/commit/d8f208a97f3169a9f7df1a2b6c7f93d17f8000ec))
* **settings:** preserve storage connection settings across a full reset ([77772b1](https://github.com/MilliPress/MilliCache/commit/77772b1fd5c7c54acaeca85eebe04a8a1a374eda))
* **settings:** skeleton loading state for the Status dashboard ([d5b48a7](https://github.com/MilliPress/MilliCache/commit/d5b48a7a629e79770d5fe3d96325bcd14648bb4f))
* **site-health:** surface every status issue, not just the drop-in ([7a2d793](https://github.com/MilliPress/MilliCache/commit/7a2d7934d68019ed8d45329a3dab956cf07ef748))
* **status:** informational check tier, severity ordering, sticky modal tabs ([ec6a249](https://github.com/MilliPress/MilliCache/commit/ec6a2497d4c9b072441adf4b5582f25ff785d3a8))
* **status:** rework cache size metrics and Status tab UI ([88991c4](https://github.com/MilliPress/MilliCache/commit/88991c46555d890f6cc1e8bb10704cb1d5df1d8b))
* **status:** show each check as a subject and a verdict ([f863b98](https://github.com/MilliPress/MilliCache/commit/f863b98d0570c6d33dee25ddf7667fdaf55db787))
* **storage:** add generic key/value surface for reuse by drop-ins ([a1d195c](https://github.com/MilliPress/MilliCache/commit/a1d195c171a79f5f622dd50b562dbe6c8f00844e))
* **storage:** add ping() active reachability probe ([85068a2](https://github.com/MilliPress/MilliCache/commit/85068a2f099ee123e81de2d2e6ce23ab96a2fc82))
* **storage:** emit URL + canonical flags from entry deletion/expiry hooks ([d2445b1](https://github.com/MilliPress/MilliCache/commit/d2445b14a74d9eaf90cff3ee28dfd5cc010202be))
* **storage:** extract Connection class with shape-inferred topology ([afdfdce](https://github.com/MilliPress/MilliCache/commit/afdfdce1784b9505190e05b2d5ee2d150885e258))
* **ui:** Add footer with MilliCache version to Network and Site settings pages ([bfe5a1f](https://github.com/MilliPress/MilliCache/commit/bfe5a1f20db0a542c041f1e74a66bf3a17efbedf))
* **ui:** Add footer with MilliCache version to Network and Site settings pages ([130dcc7](https://github.com/MilliPress/MilliCache/commit/130dcc79f88e509535f98950ef33abe680bfdd0d))
* **updater:** honor millicache_updates at check time + add prerelease opt-in ([0153b35](https://github.com/MilliPress/MilliCache/commit/0153b357dd79796c7d5f50d8f3777f000498a311))


### Bug Fixes

* **admin:** color the Status modal check icons ([4ab80b3](https://github.com/MilliPress/MilliCache/commit/4ab80b3d487b6b8855bf30d02621cc1c1fdfa79a))
* **cache status:** Correct key prefix for site flags in status retrieval ([0f419a2](https://github.com/MilliPress/MilliCache/commit/0f419a2bf51f73a7f8a84c0b8ed3fb39945f223d))
* **cache:** stop SWR regeneration from storing serve-time headers ([2447389](https://github.com/MilliPress/MilliCache/commit/2447389c4184d79bb169cc8444a2334c6ac2cf75))
* **cron:** self-heal the nightly maintenance schedule on load ([32f8579](https://github.com/MilliPress/MilliCache/commit/32f857987539ded6daa798d15f6e9286fe0adc4c))
* **drop-in:** remove Plugin Name header so the drop-in is not listed as a plugin ([c532c15](https://github.com/MilliPress/MilliCache/commit/c532c15925856f0017206c1b10b64feaf84b1c3c))
* **network:** Update network settings URL for MilliCache management ([4861431](https://github.com/MilliPress/MilliCache/commit/486143110f438e4df99edb84d6a0bf6d72437227))
* **release:** isolate Strauss from setup-php's github-oauth token ([85b642e](https://github.com/MilliPress/MilliCache/commit/85b642eead3f8c4eec2e1eefbfef0ea3efb3a951))
* **settings:** register the metrics.active default so it survives resolution ([f079f91](https://github.com/MilliPress/MilliCache/commit/f079f9146e59598aaa6b99f890c9c17d11e72a6d))
* **status:** call the deduplication count unique responses, not pages ([52447f9](https://github.com/MilliPress/MilliCache/commit/52447f9dbdc27c0c60cc54e639c177c2a3d78485))
* **status:** show clean package versions in the debug info ([4deacaf](https://github.com/MilliPress/MilliCache/commit/4deacaf0de21a07fb3524a6b909fa18651f776e7))
* **storage:** preserve flag membership when expiring a cache entry ([7c83b33](https://github.com/MilliPress/MilliCache/commit/7c83b3388ae5efffa876487a4a674140e1046fda))
* **ui:** Correct month value from 'M' to 'mo' ([a0dbbf5](https://github.com/MilliPress/MilliCache/commit/a0dbbf5c3496fc117fe4521fc950935418590601))
* **workflow:** make polish-release-pr idempotent across reruns ([077c0c8](https://github.com/MilliPress/MilliCache/commit/077c0c8b4e3f9258522a3d950134db64be4a2fa3))


### Performance

* **storage:** batch flag-to-key resolution when clearing by sets ([1ee0b65](https://github.com/MilliPress/MilliCache/commit/1ee0b65e51e96db7efb41eb8a6db2825eee9f318))

## [1.7.0-beta.7](https://github.com/MilliPress/MilliCache/compare/v1.7.0-beta.6...v1.7.0-beta.7) (2026-07-10)

<!-- mc:auto sha=19d754483250 -->
Multi-copy installations — where MilliCache and a bundling plugin such as MilliCache Pro are active side-by-side — now hand off `advanced-cache.php` cleanly instead of leaving it in a broken state. Deactivating one copy re-points the drop-in to whichever sibling is still active, activation installs the drop-in belonging to the copy actually being activated, and a new self-heal step corrects a stale drop-in automatically after any plugin activation, deactivation, or update.
<!-- /mc:auto -->

### Features

* hand the advanced-cache.php drop-in between co-resident MilliCache copies ([80d5ab9](https://github.com/MilliPress/MilliCache/commit/80d5ab9b56f496b1549afd6c621ed5c00ff42673))

## [1.7.0-beta.6](https://github.com/MilliPress/MilliCache/compare/v1.7.0-beta.5...v1.7.0-beta.6) (2026-07-08)

<!-- mc:auto sha=e82d844d39a5 -->
Stale-while-revalidate regeneration was storing serve-time headers — including injected `Age` and `Cache-Control: no-cache` — causing regenerated entries to replay `no-cache` forever and remain edge-uncached. That is fixed: a new `millicache_entry_headers` filter runs at the single store chokepoint for both miss-capture and background regen, serve-time headers are scrubbed before storage, and regen now uses the original stored headers as its base rather than the frozen post-`fastcgi_finish_request()` header table.

Cache hits now emit an `Age` header per RFC 9111, so downstream CDN edges subtract elapsed time from the freshness window and expire their copy in sync with this entry rather than resetting to a full lifetime.

A new 5 MB entry size cap (`MAX_ENTRY_SIZE`) rejects oversized responses — such as PDF exports — before they reach Redis, preventing `maxmemory` exhaustion and legitimate-page eviction.

The Status panel gains an informational check tier (gray info icon, no health impact) for neutral facts and features that are off by choice, with checks now ordered by severity. The `millicache_updates` filter is evaluated at update-check time rather than constructor time, so filters registered in `functions.php` or mu-plugins are honored. Define `MC_UPDATE_PRERELEASE` to opt a site into prerelease builds.
<!-- /mc:auto -->

### Features

* **cache:** cap entries at 5MB raw to protect Redis from oversized responses ([2e2511f](https://github.com/MilliPress/MilliCache/commit/2e2511fa8df6cf45a9d31379d82961a5afa9aad1))
* **response:** emit Age header on cache hits (RFC 9111) ([035bd24](https://github.com/MilliPress/MilliCache/commit/035bd24a13e7e86295b72c11e58ec97bc4445f2d))
* **status:** informational check tier, severity ordering, sticky modal tabs ([ec6a249](https://github.com/MilliPress/MilliCache/commit/ec6a2497d4c9b072441adf4b5582f25ff785d3a8))
* **updater:** honor millicache_updates at check time + add prerelease opt-in ([0153b35](https://github.com/MilliPress/MilliCache/commit/0153b357dd79796c7d5f50d8f3777f000498a311))


### Bug Fixes

* **cache:** stop SWR regeneration from storing serve-time headers ([2447389](https://github.com/MilliPress/MilliCache/commit/2447389c4184d79bb169cc8444a2334c6ac2cf75))
* **drop-in:** remove Plugin Name header so the drop-in is not listed as a plugin ([c532c15](https://github.com/MilliPress/MilliCache/commit/c532c15925856f0017206c1b10b64feaf84b1c3c))
* **release:** isolate Strauss from setup-php's github-oauth token ([85b642e](https://github.com/MilliPress/MilliCache/commit/85b642eead3f8c4eec2e1eefbfef0ea3efb3a951))

## [1.7.0-beta.5](https://github.com/MilliPress/MilliCache/compare/v1.7.0-beta.4...v1.7.0-beta.5) (2026-06-30)

<!-- mc:auto sha=a3b5e42afc99 -->
This release tightens the storage and hook layers ahead of the 1.7.0 stable cut.

The most important fix addresses a regression introduced in v1.6.2: expiring a cache entry by flag was silently stripping its flag memberships, leaving it orphaned and unreachable by subsequent flag-based clears until its TTL naturally elapsed. Flag membership is now preserved correctly on expiry.

On the hooks side, all cache-invalidation actions are now named consistently under the `millicache_cache_cleared_by_<target>` pattern — `millicache_cache_cleared_by_urls` is new, and `millicache_cleared_by_networks` has been renamed to `millicache_cache_cleared_by_networks`. **This is a breaking change for any code listening on the old name.** Entry deletion and expiry hooks (`millicache_entry_deleting`, `millicache_entry_deleted`, and the new `millicache_entry_expired`) now carry the entry URL and canonical flags (e.g. `2:post:123`) as additional arguments, giving edge/CDN integrations a complete signal directly from the storage layer. Flag-to-key resolution when clearing by sets is also now batched into a single pipeline, reducing Redis round-trips proportionally to flag fan-out.

Finally, `Storage` gains a generic key/value surface (`get`, `get_multiple`, `set`, `delete`, `delete_by_pattern`) so Pro drop-ins such as an object-cache driver can reuse MilliCache's existing Redis connection and fail-fast logic without opening a second one.
<!-- /mc:auto -->

### Features

* **hooks:** standardize cache-cleared action names ([1dedb37](https://github.com/MilliPress/MilliCache/commit/1dedb3796bb47aaa2605adb56242171f8d6e8827))
* **plugin:** Add author information and plugin URI to advanced cache file ([ef97d53](https://github.com/MilliPress/MilliCache/commit/ef97d532432a62e883dffa575e467769db38801f))
* **storage:** add generic key/value surface for reuse by drop-ins ([a1d195c](https://github.com/MilliPress/MilliCache/commit/a1d195c171a79f5f622dd50b562dbe6c8f00844e))
* **storage:** emit URL + canonical flags from entry deletion/expiry hooks ([d2445b1](https://github.com/MilliPress/MilliCache/commit/d2445b14a74d9eaf90cff3ee28dfd5cc010202be))


### Bug Fixes

* **storage:** preserve flag membership when expiring a cache entry ([7c83b33](https://github.com/MilliPress/MilliCache/commit/7c83b3388ae5efffa876487a4a674140e1046fda))


### Performance

* **storage:** batch flag-to-key resolution when clearing by sets ([1ee0b65](https://github.com/MilliPress/MilliCache/commit/1ee0b65e51e96db7efb41eb8a6db2825eee9f318))

## [1.7.0-beta.4](https://github.com/MilliPress/MilliCache/compare/v1.7.0-beta.3...v1.7.0-beta.4) (2026-06-23)

A full settings reset now leaves your storage connection intact, so clearing your caching behavior no longer disconnects your cache server or forces you to re-enter the connection details.

### Features

* **settings:** Include metrics in network-scoped Settings instance for multisite ([59d2fa5](https://github.com/MilliPress/MilliCache/commit/59d2fa5ad339c9de43c9aef6699aa97e4cdf8b4b))
* **settings:** preserve storage connection settings across a full reset ([77772b1](https://github.com/MilliPress/MilliCache/commit/77772b1fd5c7c54acaeca85eebe04a8a1a374eda))
* **settings:** skeleton loading state for the Status dashboard ([d5b48a7](https://github.com/MilliPress/MilliCache/commit/d5b48a7a629e79770d5fe3d96325bcd14648bb4f))

## [1.7.0-beta.3](https://github.com/MilliPress/MilliCache/compare/v1.7.0-beta.2...v1.7.0-beta.3) (2026-06-20)

This beta brings high-availability storage to MilliCache. Alongside single-server setups, it adds first-class support for Redis Replication and Sentinel, inferred automatically from the shape of `MC_STORAGE_HOST` with no separate mode flag to manage: a host string is single-node, a `master` map enables master/replica replication, and a `service` map enables Sentinel-managed failover. The connection layer is also more resilient: a misconfigured connection now disables the cache and serves the site uncached instead of silently falling back to localhost, and a connection failure fails fast so a brief storage outage can no longer slow down every request. Cache analytics gain configurable hit/miss retention windows, letting you decide how much history to keep.

### Features

* **admin:** centered loading indicator on the Status tab ([534d7b0](https://github.com/MilliPress/MilliCache/commit/534d7b0923dc9832732f6cb2db2f6e4bd1385d49))
* **admin:** polish free Status chart to match Pro ([4841e79](https://github.com/MilliPress/MilliCache/commit/4841e794e5e0c78edbea182b31c933f5ce652ecb))
* **admin:** surface storage topology across CLI, status, and settings ([3f34067](https://github.com/MilliPress/MilliCache/commit/3f34067fc312b831ee26825f7727e53f3b9d56e2))
* **metrics:** make hit/miss retention windows configurable ([45257b5](https://github.com/MilliPress/MilliCache/commit/45257b5094ef9a187915bb2decbe718af6ef0f38))
* **storage:** extract Connection class with shape-inferred topology ([afdfdce](https://github.com/MilliPress/MilliCache/commit/afdfdce1784b9505190e05b2d5ee2d150885e258))


### Bug Fixes

* **network:** Update network settings URL for MilliCache management ([4861431](https://github.com/MilliPress/MilliCache/commit/486143110f438e4df99edb84d6a0bf6d72437227))

## [1.7.0-beta.2](https://github.com/MilliPress/MilliCache/compare/v1.7.0-beta.1...v1.7.0-beta.2) (2026-06-09)

Here's a friendly intro for the 1.7.0-beta.2 release:

---

This beta brings the biggest admin overhaul in a while. The Status tab has been rebuilt from the ground up with KPI cards, a 7-day hit-ratio sparkline, and a full-width requests chart — and the same diagnostic story now flows into WordPress's native Site Health screens, so admins troubleshooting from Tools → Site Health see MilliCache's drop-in state, storage connectivity, and WP_CACHE status right alongside core checks.

The footer gets a new Status pill that summarizes overall health at a glance and opens a modal with a structured per-check breakdown (good/warning/critical, with docs links) alongside the existing debug snapshot. That snapshot is also the source of truth for a revamped `wp millicache status` CLI command and a prefilled GitHub issue template, making bug reports a one-click affair.

On the metrics side, hit and miss counts are now recorded in the response path (hourly buckets, rolled up nightly, scoped per blog) with preloader requests sensibly excluded. The admin bar gains live cache-size fetching on menu open and a fix for "Clear Current View" that was accidentally wiping the entire cache when a shared flag existed.

Rounding things out: the nightly maintenance schedule now self-heals on load so it can't silently go missing after an update, a `ping()` probe gives Site Health an accurate storage-reachability signal, and the `install_mode()` helper reports whether MilliCache is running standalone or Composer-loaded.

### Features

* **adminbar:** animate + 500ms delay before post-clear recount ([00c7dc1](https://github.com/MilliPress/MilliCache/commit/00c7dc1199e1768c21f6432be3c436f44190d3db))
* **adminbar:** live cache size + bounded current-view clear ([3877451](https://github.com/MilliPress/MilliCache/commit/38774516beb6e970597a37512d0c9d0b81fdd0fb))
* **admin:** count user-defined custom rules in the snapshot ([ab0d686](https://github.com/MilliPress/MilliCache/commit/ab0d686e954866dfb2fdf4016b79ce99d7cbfe0f))
* **admin:** docs links per check, "warnings" pill label, rules in snapshot ([38307a4](https://github.com/MilliPress/MilliCache/commit/38307a4e60a2fea6e389ac8db6a30244327210bf))
* **admin:** expose three extension filters on the status snapshot ([28c8cf1](https://github.com/MilliPress/MilliCache/commit/28c8cf1b59de5c0b90efea6bd902ff72bbb31cc8))
* **admin:** footer Status indicator with unified status payload ([dd72515](https://github.com/MilliPress/MilliCache/commit/dd72515259044f3654f75e91614697e05e96028a))
* **admin:** integrate with the WordPress Site Health screens ([2470fb8](https://github.com/MilliPress/MilliCache/commit/2470fb87100c16adabc171b163f158376294d361))
* **admin:** make the status extension filters scope-aware ([9498b55](https://github.com/MilliPress/MilliCache/commit/9498b55031c3f881c79fe2867f98d3ed35942089))
* **admin:** per-check breakdown in the footer Status modal ([52e58c9](https://github.com/MilliPress/MilliCache/commit/52e58c970d82df3e0de35323ff0b16dc99841b0b))
* **admin:** rebuild the Status tab — fixed panels, KPI/chart cards, lean Pro teaser ([1ef92ca](https://github.com/MilliPress/MilliCache/commit/1ef92ca5a3bbe78e0819a099d23a19520a6aad1d))
* **engine:** expose install_mode() to report how MilliCache is loaded ([788e079](https://github.com/MilliPress/MilliCache/commit/788e0793fe65d35bd2d55131f50e7d485ab9ac31))
* **metrics:** record hit/miss in the response path, excluding the preloader ([f42780f](https://github.com/MilliPress/MilliCache/commit/f42780ff1dbe442732f06f8e25bfd09ae591a656))
* **metrics:** time-bucketed per-blog hit/miss metrics engine ([e031f9c](https://github.com/MilliPress/MilliCache/commit/e031f9cde4164537eb5ca476aef2e0d4496a1f2e))
* **storage:** add ping() active reachability probe ([85068a2](https://github.com/MilliPress/MilliCache/commit/85068a2f099ee123e81de2d2e6ce23ab96a2fc82))
* **ui:** Add footer with MilliCache version to Network and Site settings pages ([bfe5a1f](https://github.com/MilliPress/MilliCache/commit/bfe5a1f20db0a542c041f1e74a66bf3a17efbedf))
* **ui:** Add footer with MilliCache version to Network and Site settings pages ([130dcc7](https://github.com/MilliPress/MilliCache/commit/130dcc79f88e509535f98950ef33abe680bfdd0d))


### Bug Fixes

* **admin:** color the Status modal check icons ([4ab80b3](https://github.com/MilliPress/MilliCache/commit/4ab80b3d487b6b8855bf30d02621cc1c1fdfa79a))
* **cron:** self-heal the nightly maintenance schedule on load ([32f8579](https://github.com/MilliPress/MilliCache/commit/32f857987539ded6daa798d15f6e9286fe0adc4c))
* **settings:** register the metrics.active default so it survives resolution ([f079f91](https://github.com/MilliPress/MilliCache/commit/f079f9146e59598aaa6b99f890c9c17d11e72a6d))
* **workflow:** make polish-release-pr idempotent across reruns ([077c0c8](https://github.com/MilliPress/MilliCache/commit/077c0c8b4e3f9258522a3d950134db64be4a2fa3))

## [1.7.0-beta.1](https://github.com/MilliPress/MilliCache/compare/v1.6.2...v1.7.0-beta.1) (2026-05-13)

This release introduces a new **bucket framework** for response variants. Before, the cache treated every request producing the same URL as one entry — which meant sites returning different content based on a cookie, header, or user state had to either disable caching for those pages or fight invalidation manually. Now any rule condition can split requests into separate buckets — cookie values (consent state, A/B test arm, currency), auth tokens, or the `Accept` header. That last one is especially useful for sites optimizing for AI agents: pair MilliCache with something like [`roots/post-content-to-markdown`](https://github.com/roots/post-content-to-markdown), and the Markdown variant your AI crawlers fetch and the HTML variant your browsers fetch live under the same page context — publish or update a post, and both invalidate together instead of drifting out of sync. Paired with content-addressable body deduplication, identical response bodies are stored once and shared across every bucket that produces them, so variants that happen to land on the same output don't pay for duplicate storage.

**Multisite networks** get a proper Network Admin UI for storage settings. Until now, sharing Redis connection details across subsites meant setting them via `define()` constants in `wp-config.php` — that route still works (and still takes precedence if you set both), but you can now configure storage from Network Admin like any other network-wide setting. Existing per-site UI configurations are migrated there automatically the first time you upgrade, so nothing breaks. The **Status tab** has also been reworked with breakdowns that tell you something useful at a glance: total bytes, deduplicated bytes, average per entry — so you can see whether the body dedup is paying off for your site.

The upgraded MilliBase foundation also brings the new **Abilities API**. This exposes your cache settings through standardized REST endpoints that AI assistants and automation tools speak natively — so a tool like Claude can read your current TTL or update your ignore list directly, and CI pipelines can configure MilliCache the same way they'd configure any other service.


### Features

* **cache:** Add bucket framework with content-addressable body dedup ([#126](https://github.com/MilliPress/MilliCache/issues/126)) ([a279fd7](https://github.com/MilliPress/MilliCache/commit/a279fd704a05ce58165aed45038798f175f214cb))
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
