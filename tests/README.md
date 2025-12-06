# MilliCache Testing Guide

## Overview

MilliCache uses **Pest PHP** for elegant, expressive testing. This guide covers running tests, writing new tests, and understanding the test structure.

## Test Structure

```
tests/
├── Pest.php                    # Pest configuration
├── Unit/                       # Unit tests for individual components
│   ├── PatternMatcherTest.php
│   ├── ServerVarsTest.php
│   ├── CacheConfigTest.php
│   ├── CacheEntryTest.php
│   ├── CacheResultTest.php
│   └── FlagManagerTest.php
├── e2e/                        # End-to-end browser tests
└── wp-env/                     # WordPress test environment
```

## Running Tests

### Prerequisites

```bash
# Install dependencies (including Pest)
composer install

# Ensure PHP 7.4+ is installed
php --version
```

### Test Commands

```bash
# Run all unit tests
composer test:unit

# Run with coverage report
composer test:coverage

# Run specific test file
vendor/bin/pest tests/Unit/PatternMatcherTest.php

# Run with filter (test name contains "wildcard")
vendor/bin/pest --filter=wildcard

# Run in watch mode (re-run on file changes)
vendor/bin/pest --watch

# Verbose output
vendor/bin/pest -v
```

### Full Test Suite

```bash
# Run all tests (lint, PHPStan, unit tests)
composer test
```

## Writing Tests

### Pest Syntax

Pest uses a descriptive, nested syntax:

```php
<?php

use MilliCache\Engine\Utilities\PatternMatcher;

describe('PatternMatcher', function () {

    describe('wildcard matching', function () {
        it('matches prefix wildcard', function () {
            expect(PatternMatcher::match('test_cookie', 'test_*'))->toBeTrue();
        });

        it('does not match different pattern', function () {
            expect(PatternMatcher::match('other_cookie', 'test_*'))->toBeFalse();
        });
    });

    describe('regex matching', function () {
        it('matches regex patterns', function () {
            expect(PatternMatcher::match('test123', '/test\d+/'))->toBeTrue();
        });
    });
});
```

### Test Structure Best Practices

1. **Use `describe()` blocks** to group related tests
2. **Use `it()` for test cases** with clear descriptions
3. **Use `beforeEach()` for setup** that applies to multiple tests
4. **Use `afterEach()` for cleanup** to restore state

### Example: Testing with Setup/Teardown

```php
describe('ServerVars', function () {
    beforeEach(function () {
        // Save original state
        $this->original_server = $_SERVER;
    });

    afterEach(function () {
        // Restore original state
        $_SERVER = $this->original_server;
    });

    it('returns server variable value', function () {
        $_SERVER['REQUEST_URI'] = '/test/path';
        expect(ServerVars::get('REQUEST_URI'))->toBe('/test/path');
    });
});
```

### Expectations

Pest provides many readable expectations:

```php
// Equality
expect($value)->toBe(123);
expect($value)->toEqual($other);
expect($value)->toBeTruthy();
expect($value)->toBeFalsy();

// Types
expect($value)->toBeInt();
expect($value)->toBeString();
expect($value)->toBeArray();
expect($value)->toBeNull();

// Booleans
expect($value)->toBeTrue();
expect($value)->toBeFalse();

// Comparisons
expect($value)->toBeGreaterThan(10);
expect($value)->toBeLessThan(100);
expect($value)->toBeGreaterThanOrEqual(10);

// Arrays
expect($array)->toHaveKey('name');
expect($array)->toContain('value');
expect($array)->toBeEmpty();

// Strings
expect($string)->toStartWith('http');
expect($string)->toEndWith('.com');
expect($string)->toContain('test');

// Negation
expect($value)->not->toBe(123);
expect($array)->not->toContain('missing');
```

## Testing Components

### Utility Classes

**Location**: `tests/Unit/*Test.php`

Test utilities in isolation:

```php
use MilliCache\Engine\Utilities\PatternMatcher;

it('matches wildcard patterns', function () {
    expect(PatternMatcher::match('test_cookie', 'test_*'))->toBeTrue();
});
```

### Value Objects

Test immutability and conversions:

```php
use MilliCache\Engine\ValueObjects\CacheConfig;

it('creates new instance with with_ttl', function () {
    $original = CacheConfig::from_settings(['ttl' => 3600]);
    $modified = $original->with_ttl(7200);

    expect($original->ttl)->toBe(3600);  // Unchanged
    expect($modified->ttl)->toBe(7200);   // New instance
});
```

### Processor Classes

Test state management:

```php
use MilliCache\Engine\Flags;

it('manages flags correctly', function () {
    $manager = new Flags();
    $manager->add('post:123');
    $manager->add('home');

    expect($manager->get_all())->toContain('post:123');
    expect($manager->get_all())->toContain('home');
});
```

## Test Coverage

### Generate Coverage Report

```bash
# HTML coverage report
vendor/bin/pest --coverage --coverage-html=coverage-report

# Open report in browser
open coverage-report/index.html

# Terminal coverage summary
vendor/bin/pest --coverage
```

### Coverage Goals

- **Utilities**: 100% coverage
- **Value Objects**: 100% coverage
- **Managers**: 100% coverage
- **Overall Project**: Minimum 80%

## Continuous Integration

### GitHub Actions (Example)

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          coverage: xdebug

      - name: Install dependencies
        run: composer install

      - name: Run tests
        run: composer test:unit

      - name: Coverage
        run: composer test:coverage
```

## Debugging Tests

### Enable Verbose Output

```bash
vendor/bin/pest -v
```

### Use `dump()` for Debugging

```php
it('debugs values', function () {
    $value = [1, 2, 3];
    dump($value);  // Outputs array to console
    expect($value)->toBeArray();
});
```

### Run Single Test

```bash
vendor/bin/pest --filter="matches wildcard patterns"
```

## Common Patterns

### Testing Exceptions

```php
it('throws exception for invalid input', function () {
    expect(fn() => PatternMatcher::match(null, null))
        ->toThrow(TypeError::class);
});
```

### Data Providers

```php
it('matches various patterns', function ($string, $pattern, $expected) {
    expect(PatternMatcher::match($string, $pattern))->toBe($expected);
})->with([
    ['test', 'test', true],
    ['test', 'other', false],
    ['test123', '/test\d+/', true],
]);
```

### Testing Private Methods

Use reflection for private methods (sparingly):

```php
it('calls private method', function () {
    $reflection = new ReflectionClass(PatternMatcher::class);
    $method = $reflection->getMethod('privateMethod');
    $method->setAccessible(true);

    $result = $method->invoke(null, 'arg');
    expect($result)->toBeTrue();
});
```

## WordPress Integration Tests

### WordPress Test Environment

For tests requiring WordPress functions:

```php
use function Tests\create_post;

it('integrates with WordPress', function () {
    $post_id = create_post(['post_title' => 'Test']);
    expect($post_id)->toBeInt();
});
```

### Mocking WordPress Functions

```php
use Mockery;

beforeEach(function () {
    // Mock is_multisite
    function is_multisite() {
        return true;
    }
});
```

## Performance Testing

### Benchmark Tests

```php
it('performs efficiently', function () {
    $start = microtime(true);

    for ($i = 0; $i < 10000; $i++) {
        PatternMatcher::match('test', 'test*');
    }

    $duration = microtime(true) - $start;
    expect($duration)->toBeLessThan(0.1);  // 100ms for 10k ops
});
```

## Troubleshooting

### Pest Not Found

```bash
# Ensure Pest is installed
composer require --dev pestphp/pest

# Initialize Pest
vendor/bin/pest --init
```

### Autoload Issues

```bash
# Regenerate autoloader
composer dump-autoload
```

### Test Discovery Issues

Ensure:
1. Test files end with `Test.php`
2. Test files are in `tests/Unit/` directory
3. Tests use `describe()` and `it()` syntax

## Resources

- **Pest Docs**: https://pestphp.com/docs
- **Expectations**: https://pestphp.com/docs/expectations
- **Plugins**: https://pestphp.com/docs/plugins
- **MilliCache Refactoring Guide**: See `../REFACTORING.md`

## Contributing

When adding new features:

1. Write tests first (TDD)
2. Ensure 100% coverage for new code
3. Run full test suite before committing
4. Document complex test scenarios

```bash
# Before committing
composer test
```

---

Happy Testing! 🧪
