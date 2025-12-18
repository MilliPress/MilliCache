# Changelog

## [1.0.0-rc.6](https://github.com/MilliPress/MilliCache/compare/v1.0.0-rc.5...v1.0.0-rc.6) (2025-12-18)


### Features

* **ci:** Add manual trigger for E2E workflow ([0cb13a8](https://github.com/MilliPress/MilliCache/commit/0cb13a83ec75c683082827698f40b87b959dd43c))
* CLI improvements, millicache() helper, and Rules Manager API ([#21](https://github.com/MilliPress/MilliCache/issues/21)) ([458409c](https://github.com/MilliPress/MilliCache/commit/458409cba3b5ca3ffae5ae0169d387c07d638186))
* **cli:** Add config, status, test, fix, and stats commands ([30ec2ca](https://github.com/MilliPress/MilliCache/commit/30ec2ca5afe4b5fb73a30a4243e2bd9d8feaec54))
* **core:** Add utility functions for plugin integration ([2ad43ae](https://github.com/MilliPress/MilliCache/commit/2ad43ae015120e8ee735eede772e5e9485578d1c))
* **core:** Update main plugin and drop-in files ([4fa4fef](https://github.com/MilliPress/MilliCache/commit/4fa4fefda4a53b71d84b87b8b95803237b2f04ac))
* **dependencies:** Upgrade MilliRules to version 0.6.0 and adjust related requirements ([3b83578](https://github.com/MilliPress/MilliCache/commit/3b835789ddf6dab732743ad4d2a8985a32451928))
* **docs:** Add first batch of comprehensive documentation for MilliCache configuration and usage ([ec7fa11](https://github.com/MilliPress/MilliCache/commit/ec7fa11319b1a9286f475be7a76183966fc28c3b))
* **engine:** Add cache management components ([797f714](https://github.com/MilliPress/MilliCache/commit/797f7144c8a787c23a6c9f46d1eb462da984f07b))
* **release:** Enable prerelease configuration for release-please with rc type ([3da8ac8](https://github.com/MilliPress/MilliCache/commit/3da8ac8a6ed46c2dbe86e23bf37ba52a55b80b60))
* **settings:** Add config management, backup/restore, and encryption ([be2e2a6](https://github.com/MilliPress/MilliCache/commit/be2e2a6b0289e031b7bb53d510fc33d0a750b463))
* **ui:** Add Settings UI with React components ([2aae440](https://github.com/MilliPress/MilliCache/commit/2aae4404a5bf8b6431317078e4e288eb78f77447))


### Bug Fixes

* **ci:** Add .htaccess for wp-env multisite tests ([#16](https://github.com/MilliPress/MilliCache/issues/16)) ([ba4b14f](https://github.com/MilliPress/MilliCache/commit/ba4b14f3f1cbfd9cdbda5831486301247a900a5b))
* **ci:** Find previous release commit using GitHub API timestamp ([#23](https://github.com/MilliPress/MilliCache/issues/23)) ([9fa458d](https://github.com/MilliPress/MilliCache/commit/9fa458d0cfad518e4cf13994ce7e631c7ad955f5))
* **ci:** Remove version bump push to avoid branch protection conflict ([16f34ec](https://github.com/MilliPress/MilliCache/commit/16f34ec42be4f51797f3ba5b9b01d31e665626f1))
* **ci:** Use semver sorting to find previous release tag ([#22](https://github.com/MilliPress/MilliCache/issues/22)) ([53fa65b](https://github.com/MilliPress/MilliCache/commit/53fa65b9db8b2b2510d8d5c5a3788d52c8135047))
* **cli:** Add connection timeout check before launching Redis CLI ([#19](https://github.com/MilliPress/MilliCache/issues/19)) ([d4d6a09](https://github.com/MilliPress/MilliCache/commit/d4d6a09021a2a0d38bd72926236cb1a6887998db))
* **cli:** Rename 'fix' command to 'drop' and update CLI command registration ([#25](https://github.com/MilliPress/MilliCache/issues/25)) ([04e1843](https://github.com/MilliPress/MilliCache/commit/04e184397927ad282c60dcb53265693ab2d7d178))
* **cli:** Restore wp millicache cli command ([#24](https://github.com/MilliPress/MilliCache/issues/24)) ([92046a5](https://github.com/MilliPress/MilliCache/commit/92046a5d3382d6625a3f8d171555de97bd2c238a))
* Move predis and millirules to require (not require-dev) ([a869e6b](https://github.com/MilliPress/MilliCache/commit/a869e6bafe2193f4ae628699c3bfc1a47da1fba3))
* **release:** Bump release candidate version to 1.0.0-rc.5 ([56ca17f](https://github.com/MilliPress/MilliCache/commit/56ca17fa647e8284e4eb0143d43f9204c7864293))
* **release:** Enable prerelease configuration for release-please with rc type ([1e027b2](https://github.com/MilliPress/MilliCache/commit/1e027b2d736c8f3b925abf252610a65bf759bd1d))
* **release:** Remove skip-github-release to let Release Please create tag and release ([53f6aa6](https://github.com/MilliPress/MilliCache/commit/53f6aa6971b844f37cc6efa757676a324cec3a3a))
* **release:** Reset manifest to 1.0.0-rc.4 to trigger proper release workflow ([4887138](https://github.com/MilliPress/MilliCache/commit/48871385c0406bd9e4d7746d6a665db4d5622723))
* **release:** Reset manifest to rc.4 to test full release workflow ([fc37e53](https://github.com/MilliPress/MilliCache/commit/fc37e5383548abd65d288e82cfa8c79430717c1a))
* Remove obsolete encrypt flag and introduce Rules Manager API ([#20](https://github.com/MilliPress/MilliCache/issues/20)) ([3a27dcc](https://github.com/MilliPress/MilliCache/commit/3a27dccb8e04b4e860581873b191ad0164136ac9))
* Remove vendor/ and deps/ from git tracking ([d31791f](https://github.com/MilliPress/MilliCache/commit/d31791f46beb174a2656c581a94e01353821ff4a))


### Refactoring

* **admin:** Replace AJAX with REST API and improve cache management ([837f13c](https://github.com/MilliPress/MilliCache/commit/837f13c56b927b51d4987aa6ec42cf20b40ab972))
* **core:** Update Loader and lifecycle classes for new architecture ([4d21e4f](https://github.com/MilliPress/MilliCache/commit/4d21e4f4bbf3d4c4bbf075b56a06b99d2c466dba))
* **deps:** Migrate from Mozart to Strauss for dependency namespacing ([a523341](https://github.com/MilliPress/MilliCache/commit/a523341a339f49c00c8a96befc9cdeec623a171d))
* **engine:** Restructure Engine with Manager pattern and MilliRules integration ([1b6aae5](https://github.com/MilliPress/MilliCache/commit/1b6aae56d563b7f43892fc8a3587026d4a9f1752))
* **storage:** Rename Redis to Storage and add multi-server support ([7f6a646](https://github.com/MilliPress/MilliCache/commit/7f6a646069e2f9563d09ed50cf0c1c2ae88c2334))


### Documentation

* **introduction:** Update link format for installation guide ([4d182f3](https://github.com/MilliPress/MilliCache/commit/4d182f382decfa5c67d3c89a3d1a729c46a306d2))
* **rules:** Unregister built-in rule on template redirect to prevent conflicts ([6c0bb48](https://github.com/MilliPress/MilliCache/commit/6c0bb486004445f466496f99336f3cf828e5b394))
* Update README with new architecture and features ([7a61242](https://github.com/MilliPress/MilliCache/commit/7a61242cd387e1618e2e97b9387d5dae544a3e69))

## [1.0.0-rc.5](https://github.com/MilliPress/MilliCache/compare/v1.0.0-rc.4...v1.0.0-rc.5) (2025-12-18)


### Features

* **ci:** Add manual trigger for E2E workflow ([0cb13a8](https://github.com/MilliPress/MilliCache/commit/0cb13a83ec75c683082827698f40b87b959dd43c))
* CLI improvements, millicache() helper, and Rules Manager API ([#21](https://github.com/MilliPress/MilliCache/issues/21)) ([458409c](https://github.com/MilliPress/MilliCache/commit/458409cba3b5ca3ffae5ae0169d387c07d638186))
* **cli:** Add config, status, test, fix, and stats commands ([30ec2ca](https://github.com/MilliPress/MilliCache/commit/30ec2ca5afe4b5fb73a30a4243e2bd9d8feaec54))
* **core:** Add utility functions for plugin integration ([2ad43ae](https://github.com/MilliPress/MilliCache/commit/2ad43ae015120e8ee735eede772e5e9485578d1c))
* **core:** Update main plugin and drop-in files ([4fa4fef](https://github.com/MilliPress/MilliCache/commit/4fa4fefda4a53b71d84b87b8b95803237b2f04ac))
* **dependencies:** Upgrade MilliRules to version 0.6.0 and adjust related requirements ([3b83578](https://github.com/MilliPress/MilliCache/commit/3b835789ddf6dab732743ad4d2a8985a32451928))
* **docs:** Add first batch of comprehensive documentation for MilliCache configuration and usage ([ec7fa11](https://github.com/MilliPress/MilliCache/commit/ec7fa11319b1a9286f475be7a76183966fc28c3b))
* **engine:** Add cache management components ([797f714](https://github.com/MilliPress/MilliCache/commit/797f7144c8a787c23a6c9f46d1eb462da984f07b))
* **release:** Enable prerelease configuration for release-please with rc type ([3da8ac8](https://github.com/MilliPress/MilliCache/commit/3da8ac8a6ed46c2dbe86e23bf37ba52a55b80b60))
* **settings:** Add config management, backup/restore, and encryption ([be2e2a6](https://github.com/MilliPress/MilliCache/commit/be2e2a6b0289e031b7bb53d510fc33d0a750b463))
* **ui:** Add Settings UI with React components ([2aae440](https://github.com/MilliPress/MilliCache/commit/2aae4404a5bf8b6431317078e4e288eb78f77447))


### Bug Fixes

* **ci:** Add .htaccess for wp-env multisite tests ([#16](https://github.com/MilliPress/MilliCache/issues/16)) ([ba4b14f](https://github.com/MilliPress/MilliCache/commit/ba4b14f3f1cbfd9cdbda5831486301247a900a5b))
* **ci:** Find previous release commit using GitHub API timestamp ([#23](https://github.com/MilliPress/MilliCache/issues/23)) ([9fa458d](https://github.com/MilliPress/MilliCache/commit/9fa458d0cfad518e4cf13994ce7e631c7ad955f5))
* **ci:** Remove version bump push to avoid branch protection conflict ([16f34ec](https://github.com/MilliPress/MilliCache/commit/16f34ec42be4f51797f3ba5b9b01d31e665626f1))
* **ci:** Use semver sorting to find previous release tag ([#22](https://github.com/MilliPress/MilliCache/issues/22)) ([53fa65b](https://github.com/MilliPress/MilliCache/commit/53fa65b9db8b2b2510d8d5c5a3788d52c8135047))
* **cli:** Add connection timeout check before launching Redis CLI ([#19](https://github.com/MilliPress/MilliCache/issues/19)) ([d4d6a09](https://github.com/MilliPress/MilliCache/commit/d4d6a09021a2a0d38bd72926236cb1a6887998db))
* **cli:** Rename 'fix' command to 'drop' and update CLI command registration ([#25](https://github.com/MilliPress/MilliCache/issues/25)) ([04e1843](https://github.com/MilliPress/MilliCache/commit/04e184397927ad282c60dcb53265693ab2d7d178))
* **cli:** Restore wp millicache cli command ([#24](https://github.com/MilliPress/MilliCache/issues/24)) ([92046a5](https://github.com/MilliPress/MilliCache/commit/92046a5d3382d6625a3f8d171555de97bd2c238a))
* Move predis and millirules to require (not require-dev) ([a869e6b](https://github.com/MilliPress/MilliCache/commit/a869e6bafe2193f4ae628699c3bfc1a47da1fba3))
* **release:** Bump release candidate version to 1.0.0-rc.5 ([56ca17f](https://github.com/MilliPress/MilliCache/commit/56ca17fa647e8284e4eb0143d43f9204c7864293))
* **release:** Enable prerelease configuration for release-please with rc type ([1e027b2](https://github.com/MilliPress/MilliCache/commit/1e027b2d736c8f3b925abf252610a65bf759bd1d))
* **release:** Reset manifest to 1.0.0-rc.4 to trigger proper release workflow ([4887138](https://github.com/MilliPress/MilliCache/commit/48871385c0406bd9e4d7746d6a665db4d5622723))
* **release:** Reset manifest to rc.4 to test full release workflow ([fc37e53](https://github.com/MilliPress/MilliCache/commit/fc37e5383548abd65d288e82cfa8c79430717c1a))
* Remove obsolete encrypt flag and introduce Rules Manager API ([#20](https://github.com/MilliPress/MilliCache/issues/20)) ([3a27dcc](https://github.com/MilliPress/MilliCache/commit/3a27dccb8e04b4e860581873b191ad0164136ac9))
* Remove vendor/ and deps/ from git tracking ([d31791f](https://github.com/MilliPress/MilliCache/commit/d31791f46beb174a2656c581a94e01353821ff4a))


### Refactoring

* **admin:** Replace AJAX with REST API and improve cache management ([837f13c](https://github.com/MilliPress/MilliCache/commit/837f13c56b927b51d4987aa6ec42cf20b40ab972))
* **core:** Update Loader and lifecycle classes for new architecture ([4d21e4f](https://github.com/MilliPress/MilliCache/commit/4d21e4f4bbf3d4c4bbf075b56a06b99d2c466dba))
* **deps:** Migrate from Mozart to Strauss for dependency namespacing ([a523341](https://github.com/MilliPress/MilliCache/commit/a523341a339f49c00c8a96befc9cdeec623a171d))
* **engine:** Restructure Engine with Manager pattern and MilliRules integration ([1b6aae5](https://github.com/MilliPress/MilliCache/commit/1b6aae56d563b7f43892fc8a3587026d4a9f1752))
* **storage:** Rename Redis to Storage and add multi-server support ([7f6a646](https://github.com/MilliPress/MilliCache/commit/7f6a646069e2f9563d09ed50cf0c1c2ae88c2334))


### Documentation

* **introduction:** Update link format for installation guide ([4d182f3](https://github.com/MilliPress/MilliCache/commit/4d182f382decfa5c67d3c89a3d1a729c46a306d2))
* **rules:** Unregister built-in rule on template redirect to prevent conflicts ([6c0bb48](https://github.com/MilliPress/MilliCache/commit/6c0bb486004445f466496f99336f3cf828e5b394))
* Update README with new architecture and features ([7a61242](https://github.com/MilliPress/MilliCache/commit/7a61242cd387e1618e2e97b9387d5dae544a3e69))

## [1.0.0-rc.5](https://github.com/MilliPress/MilliCache/compare/v1.0.0-rc.4...v1.0.0-rc.5) (2025-12-18)


### Features

* **ci:** Add manual trigger for E2E workflow ([0cb13a8](https://github.com/MilliPress/MilliCache/commit/0cb13a83ec75c683082827698f40b87b959dd43c))
* CLI improvements, millicache() helper, and Rules Manager API ([#21](https://github.com/MilliPress/MilliCache/issues/21)) ([458409c](https://github.com/MilliPress/MilliCache/commit/458409cba3b5ca3ffae5ae0169d387c07d638186))
* **cli:** Add config, status, test, fix, and stats commands ([30ec2ca](https://github.com/MilliPress/MilliCache/commit/30ec2ca5afe4b5fb73a30a4243e2bd9d8feaec54))
* **core:** Add utility functions for plugin integration ([2ad43ae](https://github.com/MilliPress/MilliCache/commit/2ad43ae015120e8ee735eede772e5e9485578d1c))
* **core:** Update main plugin and drop-in files ([4fa4fef](https://github.com/MilliPress/MilliCache/commit/4fa4fefda4a53b71d84b87b8b95803237b2f04ac))
* **dependencies:** Upgrade MilliRules to version 0.6.0 and adjust related requirements ([3b83578](https://github.com/MilliPress/MilliCache/commit/3b835789ddf6dab732743ad4d2a8985a32451928))
* **docs:** Add first batch of comprehensive documentation for MilliCache configuration and usage ([ec7fa11](https://github.com/MilliPress/MilliCache/commit/ec7fa11319b1a9286f475be7a76183966fc28c3b))
* **engine:** Add cache management components ([797f714](https://github.com/MilliPress/MilliCache/commit/797f7144c8a787c23a6c9f46d1eb462da984f07b))
* **release:** Enable prerelease configuration for release-please with rc type ([3da8ac8](https://github.com/MilliPress/MilliCache/commit/3da8ac8a6ed46c2dbe86e23bf37ba52a55b80b60))
* **settings:** Add config management, backup/restore, and encryption ([be2e2a6](https://github.com/MilliPress/MilliCache/commit/be2e2a6b0289e031b7bb53d510fc33d0a750b463))
* **ui:** Add Settings UI with React components ([2aae440](https://github.com/MilliPress/MilliCache/commit/2aae4404a5bf8b6431317078e4e288eb78f77447))


### Bug Fixes

* **ci:** Add .htaccess for wp-env multisite tests ([#16](https://github.com/MilliPress/MilliCache/issues/16)) ([ba4b14f](https://github.com/MilliPress/MilliCache/commit/ba4b14f3f1cbfd9cdbda5831486301247a900a5b))
* **ci:** Find previous release commit using GitHub API timestamp ([#23](https://github.com/MilliPress/MilliCache/issues/23)) ([9fa458d](https://github.com/MilliPress/MilliCache/commit/9fa458d0cfad518e4cf13994ce7e631c7ad955f5))
* **ci:** Remove version bump push to avoid branch protection conflict ([16f34ec](https://github.com/MilliPress/MilliCache/commit/16f34ec42be4f51797f3ba5b9b01d31e665626f1))
* **ci:** Use semver sorting to find previous release tag ([#22](https://github.com/MilliPress/MilliCache/issues/22)) ([53fa65b](https://github.com/MilliPress/MilliCache/commit/53fa65b9db8b2b2510d8d5c5a3788d52c8135047))
* **cli:** Add connection timeout check before launching Redis CLI ([#19](https://github.com/MilliPress/MilliCache/issues/19)) ([d4d6a09](https://github.com/MilliPress/MilliCache/commit/d4d6a09021a2a0d38bd72926236cb1a6887998db))
* **cli:** Rename 'fix' command to 'drop' and update CLI command registration ([#25](https://github.com/MilliPress/MilliCache/issues/25)) ([04e1843](https://github.com/MilliPress/MilliCache/commit/04e184397927ad282c60dcb53265693ab2d7d178))
* **cli:** Restore wp millicache cli command ([#24](https://github.com/MilliPress/MilliCache/issues/24)) ([92046a5](https://github.com/MilliPress/MilliCache/commit/92046a5d3382d6625a3f8d171555de97bd2c238a))
* Move predis and millirules to require (not require-dev) ([a869e6b](https://github.com/MilliPress/MilliCache/commit/a869e6bafe2193f4ae628699c3bfc1a47da1fba3))
* **release:** Bump release candidate version to 1.0.0-rc.5 ([56ca17f](https://github.com/MilliPress/MilliCache/commit/56ca17fa647e8284e4eb0143d43f9204c7864293))
* **release:** Enable prerelease configuration for release-please with rc type ([1e027b2](https://github.com/MilliPress/MilliCache/commit/1e027b2d736c8f3b925abf252610a65bf759bd1d))
* **release:** Reset manifest to 1.0.0-rc.4 to trigger proper release workflow ([4887138](https://github.com/MilliPress/MilliCache/commit/48871385c0406bd9e4d7746d6a665db4d5622723))
* **release:** Reset manifest to rc.4 to test full release workflow ([fc37e53](https://github.com/MilliPress/MilliCache/commit/fc37e5383548abd65d288e82cfa8c79430717c1a))
* Remove obsolete encrypt flag and introduce Rules Manager API ([#20](https://github.com/MilliPress/MilliCache/issues/20)) ([3a27dcc](https://github.com/MilliPress/MilliCache/commit/3a27dccb8e04b4e860581873b191ad0164136ac9))
* Remove vendor/ and deps/ from git tracking ([d31791f](https://github.com/MilliPress/MilliCache/commit/d31791f46beb174a2656c581a94e01353821ff4a))


### Refactoring

* **admin:** Replace AJAX with REST API and improve cache management ([837f13c](https://github.com/MilliPress/MilliCache/commit/837f13c56b927b51d4987aa6ec42cf20b40ab972))
* **core:** Update Loader and lifecycle classes for new architecture ([4d21e4f](https://github.com/MilliPress/MilliCache/commit/4d21e4f4bbf3d4c4bbf075b56a06b99d2c466dba))
* **deps:** Migrate from Mozart to Strauss for dependency namespacing ([a523341](https://github.com/MilliPress/MilliCache/commit/a523341a339f49c00c8a96befc9cdeec623a171d))
* **engine:** Restructure Engine with Manager pattern and MilliRules integration ([1b6aae5](https://github.com/MilliPress/MilliCache/commit/1b6aae56d563b7f43892fc8a3587026d4a9f1752))
* **storage:** Rename Redis to Storage and add multi-server support ([7f6a646](https://github.com/MilliPress/MilliCache/commit/7f6a646069e2f9563d09ed50cf0c1c2ae88c2334))


### Documentation

* **introduction:** Update link format for installation guide ([4d182f3](https://github.com/MilliPress/MilliCache/commit/4d182f382decfa5c67d3c89a3d1a729c46a306d2))
* **rules:** Unregister built-in rule on template redirect to prevent conflicts ([6c0bb48](https://github.com/MilliPress/MilliCache/commit/6c0bb486004445f466496f99336f3cf828e5b394))
* Update README with new architecture and features ([7a61242](https://github.com/MilliPress/MilliCache/commit/7a61242cd387e1618e2e97b9387d5dae544a3e69))

## [1.0.0-rc.5](https://github.com/MilliPress/MilliCache/compare/millicache-v1.0.0-rc.4...millicache-v1.0.0-rc.5) (2025-12-18)


### Features

* **ci:** Add manual trigger for E2E workflow ([0cb13a8](https://github.com/MilliPress/MilliCache/commit/0cb13a83ec75c683082827698f40b87b959dd43c))
* CLI improvements, millicache() helper, and Rules Manager API ([#21](https://github.com/MilliPress/MilliCache/issues/21)) ([458409c](https://github.com/MilliPress/MilliCache/commit/458409cba3b5ca3ffae5ae0169d387c07d638186))
* **cli:** Add config, status, test, fix, and stats commands ([30ec2ca](https://github.com/MilliPress/MilliCache/commit/30ec2ca5afe4b5fb73a30a4243e2bd9d8feaec54))
* **core:** Add utility functions for plugin integration ([2ad43ae](https://github.com/MilliPress/MilliCache/commit/2ad43ae015120e8ee735eede772e5e9485578d1c))
* **core:** Update main plugin and drop-in files ([4fa4fef](https://github.com/MilliPress/MilliCache/commit/4fa4fefda4a53b71d84b87b8b95803237b2f04ac))
* **dependencies:** Upgrade MilliRules to version 0.6.0 and adjust related requirements ([3b83578](https://github.com/MilliPress/MilliCache/commit/3b835789ddf6dab732743ad4d2a8985a32451928))
* **docs:** Add first batch of comprehensive documentation for MilliCache configuration and usage ([ec7fa11](https://github.com/MilliPress/MilliCache/commit/ec7fa11319b1a9286f475be7a76183966fc28c3b))
* **engine:** Add cache management components ([797f714](https://github.com/MilliPress/MilliCache/commit/797f7144c8a787c23a6c9f46d1eb462da984f07b))
* **release:** Enable prerelease configuration for release-please with rc type ([3da8ac8](https://github.com/MilliPress/MilliCache/commit/3da8ac8a6ed46c2dbe86e23bf37ba52a55b80b60))
* **settings:** Add config management, backup/restore, and encryption ([be2e2a6](https://github.com/MilliPress/MilliCache/commit/be2e2a6b0289e031b7bb53d510fc33d0a750b463))
* **ui:** Add Settings UI with React components ([2aae440](https://github.com/MilliPress/MilliCache/commit/2aae4404a5bf8b6431317078e4e288eb78f77447))


### Bug Fixes

* **ci:** Add .htaccess for wp-env multisite tests ([#16](https://github.com/MilliPress/MilliCache/issues/16)) ([ba4b14f](https://github.com/MilliPress/MilliCache/commit/ba4b14f3f1cbfd9cdbda5831486301247a900a5b))
* **ci:** Find previous release commit using GitHub API timestamp ([#23](https://github.com/MilliPress/MilliCache/issues/23)) ([9fa458d](https://github.com/MilliPress/MilliCache/commit/9fa458d0cfad518e4cf13994ce7e631c7ad955f5))
* **ci:** Remove version bump push to avoid branch protection conflict ([16f34ec](https://github.com/MilliPress/MilliCache/commit/16f34ec42be4f51797f3ba5b9b01d31e665626f1))
* **ci:** Use semver sorting to find previous release tag ([#22](https://github.com/MilliPress/MilliCache/issues/22)) ([53fa65b](https://github.com/MilliPress/MilliCache/commit/53fa65b9db8b2b2510d8d5c5a3788d52c8135047))
* **cli:** Add connection timeout check before launching Redis CLI ([#19](https://github.com/MilliPress/MilliCache/issues/19)) ([d4d6a09](https://github.com/MilliPress/MilliCache/commit/d4d6a09021a2a0d38bd72926236cb1a6887998db))
* **cli:** Rename 'fix' command to 'drop' and update CLI command registration ([#25](https://github.com/MilliPress/MilliCache/issues/25)) ([04e1843](https://github.com/MilliPress/MilliCache/commit/04e184397927ad282c60dcb53265693ab2d7d178))
* **cli:** Restore wp millicache cli command ([#24](https://github.com/MilliPress/MilliCache/issues/24)) ([92046a5](https://github.com/MilliPress/MilliCache/commit/92046a5d3382d6625a3f8d171555de97bd2c238a))
* Move predis and millirules to require (not require-dev) ([a869e6b](https://github.com/MilliPress/MilliCache/commit/a869e6bafe2193f4ae628699c3bfc1a47da1fba3))
* **release:** Enable prerelease configuration for release-please with rc type ([1e027b2](https://github.com/MilliPress/MilliCache/commit/1e027b2d736c8f3b925abf252610a65bf759bd1d))
* Remove obsolete encrypt flag and introduce Rules Manager API ([#20](https://github.com/MilliPress/MilliCache/issues/20)) ([3a27dcc](https://github.com/MilliPress/MilliCache/commit/3a27dccb8e04b4e860581873b191ad0164136ac9))
* Remove vendor/ and deps/ from git tracking ([d31791f](https://github.com/MilliPress/MilliCache/commit/d31791f46beb174a2656c581a94e01353821ff4a))


### Refactoring

* **admin:** Replace AJAX with REST API and improve cache management ([837f13c](https://github.com/MilliPress/MilliCache/commit/837f13c56b927b51d4987aa6ec42cf20b40ab972))
* **core:** Update Loader and lifecycle classes for new architecture ([4d21e4f](https://github.com/MilliPress/MilliCache/commit/4d21e4f4bbf3d4c4bbf075b56a06b99d2c466dba))
* **deps:** Migrate from Mozart to Strauss for dependency namespacing ([a523341](https://github.com/MilliPress/MilliCache/commit/a523341a339f49c00c8a96befc9cdeec623a171d))
* **engine:** Restructure Engine with Manager pattern and MilliRules integration ([1b6aae5](https://github.com/MilliPress/MilliCache/commit/1b6aae56d563b7f43892fc8a3587026d4a9f1752))
* **storage:** Rename Redis to Storage and add multi-server support ([7f6a646](https://github.com/MilliPress/MilliCache/commit/7f6a646069e2f9563d09ed50cf0c1c2ae88c2334))


### Documentation

* **rules:** Unregister built-in rule on template redirect to prevent conflicts ([6c0bb48](https://github.com/MilliPress/MilliCache/commit/6c0bb486004445f466496f99336f3cf828e5b394))
* Update README with new architecture and features ([7a61242](https://github.com/MilliPress/MilliCache/commit/7a61242cd387e1618e2e97b9387d5dae544a3e69))

---
title: 'MilliCache Changelog'
post_excerpt: 'Version-by-version breakdown of new features, bug fixes, refactoring, and API changes in MilliCache.'
menu_order: 30
---

## Changelog
