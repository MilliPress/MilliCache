# MilliRules

A flexible, framework-agnostic rule evaluation engine for PHP 7.4+.

## Overview

MilliRules is a powerful rule engine that allows you to define complex conditional logic using a fluent API. It's designed to be framework-agnostic while providing specialized support for HTTP and WordPress environments.

## Features

- **Fluent API**: Build complex rules with an intuitive, chainable syntax
- **Framework Agnostic**: Core engine works with any PHP application
- **Lazy-Loaded Context**: On-demand loading of context data for optimal performance
- **HTTP Support**: Built-in conditions for request handling
- **WordPress Integration**: Native support for WordPress queries and context
- **Extensible**: Easy to add custom conditions and actions
- **snake_case Convention**: Consistent naming throughout the API
- **PHP 7.4+ Compatible**: Works with modern PHP versions

## Installation

```bash
composer require millipress/millirules
```

## Quick Start

```php
use MilliRules\Rules;

// Simple HTTP rule
Rules::create('api_check')
    ->when()->request_url('/api/*')
    ->then()->custom( 'run_check', function($context) {
        // Your action here
    })
    ->register();
```

## Documentation

See the `/docs` directory for detailed documentation.

## License

GPL-2.0-or-later
