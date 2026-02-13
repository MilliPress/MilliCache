# Changelog

## 1.0.0 (2026-02-13)


### Features

* **cache:** Make cache pipeline accept explicit status and headers ([c309054](https://github.com/MilliPress/MilliCache/commit/c30905436708261eff048a1fad3e8b7bf7346a27))
* **ci:** Add manual trigger for E2E workflow ([0cb13a8](https://github.com/MilliPress/MilliCache/commit/0cb13a83ec75c683082827698f40b87b959dd43c))
* CLI improvements, millicache() helper, and Rules Manager API ([#21](https://github.com/MilliPress/MilliCache/issues/21)) ([458409c](https://github.com/MilliPress/MilliCache/commit/458409cba3b5ca3ffae5ae0169d387c07d638186))
* **cli:** Add config, status, test, fix, and stats commands ([30ec2ca](https://github.com/MilliPress/MilliCache/commit/30ec2ca5afe4b5fb73a30a4243e2bd9d8feaec54))
* **core:** Add utility functions for plugin integration ([2ad43ae](https://github.com/MilliPress/MilliCache/commit/2ad43ae015120e8ee735eede772e5e9485578d1c))
* **core:** Update main plugin and drop-in files ([4fa4fef](https://github.com/MilliPress/MilliCache/commit/4fa4fefda4a53b71d84b87b8b95803237b2f04ac))
* **dependencies:** Upgrade MilliRules to version 0.6.0 and adjust related requirements ([3b83578](https://github.com/MilliPress/MilliCache/commit/3b835789ddf6dab732743ad4d2a8985a32451928))
* **deps:** Enable shared namespaces for Composer installs ([7e96a5d](https://github.com/MilliPress/MilliCache/commit/7e96a5d32772079a21041fbd3afc8bee643a0c29))
* **docs:** Add first batch of comprehensive documentation for MilliCache configuration and usage ([ec7fa11](https://github.com/MilliPress/MilliCache/commit/ec7fa11319b1a9286f475be7a76183966fc28c3b))
* **engine:** Add cache management components ([797f714](https://github.com/MilliPress/MilliCache/commit/797f7144c8a787c23a6c9f46d1eb462da984f07b))
* **engine:** Move middleware methods to their natural classes ([c6f47d3](https://github.com/MilliPress/MilliCache/commit/c6f47d3dd4277b3943e950cd444f0d1427269a73))
* **release:** Enable prerelease configuration for release-please with rc type ([3da8ac8](https://github.com/MilliPress/MilliCache/commit/3da8ac8a6ed46c2dbe86e23bf37ba52a55b80b60))
* **settings:** Add config management, backup/restore, and encryption ([be2e2a6](https://github.com/MilliPress/MilliCache/commit/be2e2a6b0289e031b7bb53d510fc33d0a750b463))
* **ui:** Add Settings UI with React components ([2aae440](https://github.com/MilliPress/MilliCache/commit/2aae4404a5bf8b6431317078e4e288eb78f77447))
* **updater:** Add plugin update checker for seamless updates with WordPress update system ([a9fc17a](https://github.com/MilliPress/MilliCache/commit/a9fc17ad9a5fdbcf77f27ad198ce74851d5bf145))


### Bug Fixes

* **admin:** Change cache size calculations to use bytes instead of kilobytes ([eb6a46c](https://github.com/MilliPress/MilliCache/commit/eb6a46c60e90bde15ea21699bc38f77f62be011b))
* **ci:** Add .htaccess for wp-env multisite tests ([#16](https://github.com/MilliPress/MilliCache/issues/16)) ([ba4b14f](https://github.com/MilliPress/MilliCache/commit/ba4b14f3f1cbfd9cdbda5831486301247a900a5b))
* **ci:** Always run CI on pull requests ([94eff02](https://github.com/MilliPress/MilliCache/commit/94eff022b928a1089c699da04c2a2a23942ecb34))
* **ci:** Find previous release commit using GitHub API timestamp ([#23](https://github.com/MilliPress/MilliCache/issues/23)) ([9fa458d](https://github.com/MilliPress/MilliCache/commit/9fa458d0cfad518e4cf13994ce7e631c7ad955f5))
* **ci:** Gate tag/release creation behind CI and E2E tests ([ff6968a](https://github.com/MilliPress/MilliCache/commit/ff6968a1e3f5e8055d59892ee38dbb311cb72bfb))
* **ci:** Remove version bump push to avoid branch protection conflict ([16f34ec](https://github.com/MilliPress/MilliCache/commit/16f34ec42be4f51797f3ba5b9b01d31e665626f1))
* **ci:** Use semver sorting to find previous release tag ([#22](https://github.com/MilliPress/MilliCache/issues/22)) ([53fa65b](https://github.com/MilliPress/MilliCache/commit/53fa65b9db8b2b2510d8d5c5a3788d52c8135047))
* **cli:** Add connection timeout check before launching Redis CLI ([#19](https://github.com/MilliPress/MilliCache/issues/19)) ([d4d6a09](https://github.com/MilliPress/MilliCache/commit/d4d6a09021a2a0d38bd72926236cb1a6887998db))
* **cli:** Rename 'fix' command to 'drop' and update CLI command registration ([#25](https://github.com/MilliPress/MilliCache/issues/25)) ([04e1843](https://github.com/MilliPress/MilliCache/commit/04e184397927ad282c60dcb53265693ab2d7d178))
* **cli:** Restore wp millicache cli command ([#24](https://github.com/MilliPress/MilliCache/issues/24)) ([92046a5](https://github.com/MilliPress/MilliCache/commit/92046a5d3382d6625a3f8d171555de97bd2c238a))
* correct path to Predis autoloader in deps directory ([#37](https://github.com/MilliPress/MilliCache/issues/37)) ([bb5a6dc](https://github.com/MilliPress/MilliCache/commit/bb5a6dc9419bb6e25a4d4f4568505354f555d34a))
* **dependencies:** Update millirules and gitignore composer.lock ([7f59eb3](https://github.com/MilliPress/MilliCache/commit/7f59eb3b0c4d16e2dd756daa0e96dbaa627ed03a))
* **deps:** Downgrade @types/node to v20 for WordPress compatibility ([ebbd454](https://github.com/MilliPress/MilliCache/commit/ebbd45468ed7399af12e4db6f288ab88ea1b637e))
* Include stubs in release archives for IDE support ([634ae3c](https://github.com/MilliPress/MilliCache/commit/634ae3cd0d1cfa2ffefe979b139fd1687db106f7))
* **lint:** Add period to inline comment for PHPCS compliance ([ecc1b29](https://github.com/MilliPress/MilliCache/commit/ecc1b29e3b6b0f7fc8e056818f4299910f767494))
* Move predis and millirules to require (not require-dev) ([a869e6b](https://github.com/MilliPress/MilliCache/commit/a869e6bafe2193f4ae628699c3bfc1a47da1fba3))
* **release:** Build prefixed deps, fix changelog, and reset for rc.5 ([1f05b53](https://github.com/MilliPress/MilliCache/commit/1f05b530ccfd6668d86bfbe3ed26e072c96ee0f6))
* **release:** Build prefixed deps, fix changelog, and reset for rc.5 ([#42](https://github.com/MilliPress/MilliCache/issues/42)) ([2617f78](https://github.com/MilliPress/MilliCache/commit/2617f78b4da89818f7c73827b904e9e1e44a4e8f))
* **release:** Bump release candidate version to 1.0.0-rc.5 ([56ca17f](https://github.com/MilliPress/MilliCache/commit/56ca17fa647e8284e4eb0143d43f9204c7864293))
* **release:** Enable prerelease configuration for release-please with rc type ([1e027b2](https://github.com/MilliPress/MilliCache/commit/1e027b2d736c8f3b925abf252610a65bf759bd1d))
* **release:** Preserve build artifacts during tag update and reset for clean release ([#40](https://github.com/MilliPress/MilliCache/issues/40)) ([9c8f2f3](https://github.com/MilliPress/MilliCache/commit/9c8f2f399855fb14d3b23002e7b569d8eeab1b85))
* **release:** Preserve dist directory and reset for clean rc.5 release ([#38](https://github.com/MilliPress/MilliCache/issues/38)) ([3f24840](https://github.com/MilliPress/MilliCache/commit/3f24840e51fd45c5dbd7c2071d78bd1dc84bdd2f))
* **release:** Remove skip-github-release to let Release Please create tag and release ([53f6aa6](https://github.com/MilliPress/MilliCache/commit/53f6aa6971b844f37cc6efa757676a324cec3a3a))
* **release:** Remove x-release-please-version annotations from source ([e80ada4](https://github.com/MilliPress/MilliCache/commit/e80ada43b357029b33f8e913cc285d565fedfa08))
* **release:** Reset manifest to 1.0.0-rc.4 to trigger proper release workflow ([4887138](https://github.com/MilliPress/MilliCache/commit/48871385c0406bd9e4d7746d6a665db4d5622723))
* **release:** Reset manifest to rc.4 to test full release workflow ([fc37e53](https://github.com/MilliPress/MilliCache/commit/fc37e5383548abd65d288e82cfa8c79430717c1a))
* **release:** Update release notes with performance results ([0424924](https://github.com/MilliPress/MilliCache/commit/0424924ad6390e5162b82e197c8a3c5f60a48a20))
* **release:** Update tag to include built dependencies for Composer ([3e6de16](https://github.com/MilliPress/MilliCache/commit/3e6de16473a9e10d3017ebfc62529dd5f17ef274))
* **release:** Use PAT for last-release-sha update and revert v1.0.1 ([60d29fd](https://github.com/MilliPress/MilliCache/commit/60d29fded68adafb1c21070f6415f14f252d1b70))
* **release:** Use stable zip filename for WordPress compatibility ([3ce7cdc](https://github.com/MilliPress/MilliCache/commit/3ce7cdca7a111c504fd3ffdbe3417acf54be581b))
* Remove obsolete encrypt flag and introduce Rules Manager API ([#20](https://github.com/MilliPress/MilliCache/issues/20)) ([3a27dcc](https://github.com/MilliPress/MilliCache/commit/3a27dccb8e04b4e860581873b191ad0164136ac9))
* Remove vendor/ and deps/ from git tracking ([d31791f](https://github.com/MilliPress/MilliCache/commit/d31791f46beb174a2656c581a94e01353821ff4a))
* **rules:** Combine REST request rule checks to lookup 'wp-json' in URL ([2f1e732](https://github.com/MilliPress/MilliCache/commit/2f1e73278ebfd1b6b1116f42c2621a5c34a5212d))
* **tests:** Resolve flaky E2E tests due to TTL timing and flag order ([d81c162](https://github.com/MilliPress/MilliCache/commit/d81c162733ecb74d3f62147fd4f8335c765f32b9))


### Refactoring

* **admin:** Replace AJAX with REST API and improve cache management ([837f13c](https://github.com/MilliPress/MilliCache/commit/837f13c56b927b51d4987aa6ec42cf20b40ab972))
* **api:** Rename `millipress_` functions and variables to `millicache_` for consistency ([87a68a6](https://github.com/MilliPress/MilliCache/commit/87a68a65d12687935664743bbb2012a0f74daaf2))
* **api:** Rename Options::should_cache() to is_caching_allowed() ([60f8a47](https://github.com/MilliPress/MilliCache/commit/60f8a47404e5487a0fb48b82b7f149c0f202ee82))
* **cache:** Make cache pipeline accept explicit status and headers ([f6dc332](https://github.com/MilliPress/MilliCache/commit/f6dc332b9e72c3969be81e86850972a866988a8f))
* **ci:** Remove orphan tag repointing from release workflows ([12cca26](https://github.com/MilliPress/MilliCache/commit/12cca26151b8d4c64133e471ad5fa6e9e0ae64ee))
* **cli:** Introduce OutputTrait for consistent CLI output formatting ([422b675](https://github.com/MilliPress/MilliCache/commit/422b6751cc66ac920345f77a94f5c402fee246f3))
* **core:** Update Loader and lifecycle classes for new architecture ([4d21e4f](https://github.com/MilliPress/MilliCache/commit/4d21e4f4bbf3d4c4bbf075b56a06b99d2c466dba))
* **deps:** Migrate from Mozart to Strauss for dependency namespacing ([a523341](https://github.com/MilliPress/MilliCache/commit/a523341a339f49c00c8a96befc9cdeec623a171d))
* **deps:** Move runtime dependencies to dev-deps ([191f3d1](https://github.com/MilliPress/MilliCache/commit/191f3d1aa8f4a026d78a7618afdd68ecd88a40c3))
* **deps:** Move Strauss scoping from source-level to build-time only ([#71](https://github.com/MilliPress/MilliCache/issues/71)) ([2953654](https://github.com/MilliPress/MilliCache/commit/2953654d2846fdf87f6cb861a46a31f5c22e0d71))
* **engine:** Move middleware methods to their natural classes ([7c3090e](https://github.com/MilliPress/MilliCache/commit/7c3090e4472a3d6d7a2bacb2de6950310254e508))
* **engine:** Restructure Engine with Manager pattern and MilliRules integration ([1b6aae5](https://github.com/MilliPress/MilliCache/commit/1b6aae56d563b7f43892fc8a3587026d4a9f1752))
* **millicache:** Remove unused millicache_loaded action hook ([d230e5d](https://github.com/MilliPress/MilliCache/commit/d230e5d337328425ace8055013f785676f3ad59a))
* **results:** Change Markdown header for performance results to improve visibility ([b02b4f6](https://github.com/MilliPress/MilliCache/commit/b02b4f633da8ff423ffb457caecf1ca4654049be))
* **storage:** Rename Redis to Storage and add multi-server support ([7f6a646](https://github.com/MilliPress/MilliCache/commit/7f6a646069e2f9563d09ed50cf0c1c2ae88c2334))
* **ui:** Remove deprecated __nextHasNoMarginBottom props ([4919375](https://github.com/MilliPress/MilliCache/commit/491937547e918a003add743e70251b5cb35245f5))


### Miscellaneous

* release 1.0.0 ([bf3c4da](https://github.com/MilliPress/MilliCache/commit/bf3c4daa6feb5d793b587ef87e91663b518a963f))
* **release:** Reset for rc.5 release test ([1e988ac](https://github.com/MilliPress/MilliCache/commit/1e988ac696930ea70b28832843d6618d73b50c2f))
* **release:** Set next version ([369d647](https://github.com/MilliPress/MilliCache/commit/369d647fde4d07b348afe9fd5f73c9e0ce9be09c))

---
title: 'MilliCache Changelog'
post_excerpt: 'Version-by-version breakdown of new features, bug fixes, and improvements in MilliCache.'
menu_order: 30
---

## Changelog
