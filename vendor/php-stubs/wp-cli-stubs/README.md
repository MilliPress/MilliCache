# WP-CLI Stubs

[![Build Status](https://app.travis-ci.com/php-stubs/wp-cli-stubs.svg?branch=master)](https://app.travis-ci.com/php-stubs/wp-cli-stubs)

This package provides stub declarations for [WP-CLI](https://wp-cli.org/) functions, classes and interfaces.
These stubs can help plugin and theme developers leverage static analysis tools like [PHPStan](https://github.com/phpstan/phpstan), which are unable to parse WP-CLI as it is not clean OOP code.

Stubs are generated directly from the [source](https://wp-cli.org/) using [giacocorsiglia/stubs-generator](https://github.com/GiacoCorsiglia/php-stubs-generator).

### Requirements

- PHP >=7.1

### Installation

Require this package as a development dependency with [Composer](https://getcomposer.org).

```bash
composer require --dev php-stubs/wp-cli-stubs
```

Alternatively you may download `wp-cli-stubs.php` directly.

### Usage in PHPStan

Include all stubs in PHPStan configuration file.

```neon
parameters:
    scanFiles:
        - %rootDir%/../../php-stubs/wordpress-stubs/wordpress-stubs.php
        - %rootDir%/../../php-stubs/wp-cli-stubs/wp-cli-stubs.php
        - %rootDir%/../../php-stubs/wp-cli-stubs/wp-cli-commands-stubs.php
        - %rootDir%/../../php-stubs/wp-cli-stubs/wp-cli-i18n-stubs.php
        - %rootDir%/../../php-stubs/wp-cli-stubs/wp-cli-tools-stubs.php
```
