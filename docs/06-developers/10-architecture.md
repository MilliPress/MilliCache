---
title: 'Architecture'
post_excerpt: 'Understand the MilliCache architecture including component structure, request flow, and key classes.'
menu_order: 10
---

# Architecture

This guide explains MilliCache's internal architecture for developers who want to extend or integrate with the plugin.

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           WordPress Request                              │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                        advanced-cache.php                                │
│                     (Drop-in / Engine Entry)                            │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                              Engine                                      │
│                         (Singleton Core)                                │
│  ┌──────────────┐ ┌───────────────┐ ┌───────────────┐ ┌─────────────┐  │
│  │   Storage    │ │ FlagManager   │ │    Config     │ │   Options   │  │
│  │   (Redis)    │ │   (Flags)     │ │   (Settings)  │ │   (TTL)     │  │
│  └──────────────┘ └───────────────┘ └───────────────┘ └─────────────┘  │
│  ┌──────────────────────────────┐ ┌─────────────────────────────────┐  │
│  │     Cache Manager            │ │     Invalidation Manager        │  │
│  │  ┌────────┐ ┌────────┐      │ │  ┌────────┐ ┌────────────┐      │  │
│  │  │ Reader │ │ Writer │      │ │  │ Queue  │ │  Resolver  │      │  │
│  │  └────────┘ └────────┘      │ │  └────────┘ └────────────┘      │  │
│  └──────────────────────────────┘ └─────────────────────────────────┘  │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │                   Request/Response Processing                    │   │
│  │  ┌───────────────────────┐    ┌──────────────────────────────┐  │   │
│  │  │  Request Processor    │    │    Response Processor        │  │   │
│  │  │  ┌────────┐ ┌───────┐ │    │    ┌────────┐ ┌────────┐    │  │   │
│  │  │  │ Parser │ │Hasher │ │    │    │ State  │ │Headers │    │  │   │
│  │  │  └────────┘ └───────┘ │    │    └────────┘ └────────┘    │  │   │
│  │  └───────────────────────┘    └──────────────────────────────┘  │   │
│  └─────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                           MilliRules Engine                              │
│  ┌───────────────────────┐        ┌──────────────────────────────────┐ │
│  │   Bootstrap Rules     │        │     WordPress Rules              │ │
│  │   (Pre-WordPress)     │        │     (Post-WordPress)             │ │
│  └───────────────────────┘        └──────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────┘
```

## Directory Structure

```
src/
├── MilliCache.php              # Main plugin class
├── Engine.php                  # Cache engine (singleton)
├── Core/
│   ├── Loader.php              # WordPress hook orchestrator
│   ├── Settings.php            # Configuration management
│   └── Storage.php             # Redis connection & operations
├── Admin/
│   ├── Admin.php               # Admin UI controller
│   ├── CLI.php                 # WP-CLI commands
│   ├── RestAPI.php             # REST endpoints
│   ├── Adminbar.php            # Admin bar integration
│   ├── Activator.php           # Plugin activation
│   └── Deactivator.php         # Plugin deactivation
├── Engine/
│   ├── Cache/
│   │   ├── Config.php          # Cache configuration
│   │   ├── Manager.php         # Cache operations orchestrator
│   │   ├── Reader.php          # Read from cache
│   │   ├── Writer.php          # Write to cache
│   │   ├── Entry.php           # Cache entry model
│   │   ├── Validator.php       # Cache validation
│   │   ├── Result.php          # Cache operation result
│   │   └── Invalidation/
│   │       ├── Manager.php     # Invalidation orchestrator
│   │       ├── Queue.php       # Invalidation queue
│   │       └── Resolver.php    # Target resolution
│   ├── Request/
│   │   ├── Processor.php       # Request handling
│   │   ├── Parser.php          # Parse request data
│   │   ├── Cleaner.php         # Clean/normalize request
│   │   └── Hasher.php          # Generate cache key
│   ├── Response/
│   │   ├── Processor.php       # Response handling
│   │   ├── State.php           # Response state machine
│   │   └── Headers.php         # HTTP header management
│   ├── Flags.php               # Flag manager
│   ├── Options.php             # Runtime option overrides
│   └── Utilities/
│       ├── Multisite.php       # Multisite helpers
│       ├── PatternMatcher.php  # Wildcard matching
│       └── ServerVars.php      # Server variable access
└── Rules/
    ├── Bootstrap.php           # Pre-WordPress rules
    ├── WordPress.php           # Post-WordPress rules
    ├── RequestFlags.php        # Flag assignment
    └── Actions/
        ├── PHP/                # Bootstrap phase actions
        │   ├── DoCache.php
        │   ├── SetTtl.php
        │   └── SetGrace.php
        └── WP/                 # WordPress phase actions
            ├── AddFlag.php
            ├── RemoveFlag.php
            ├── ClearCache.php
            └── ClearSiteCache.php
```

## Key Classes

### Engine (Singleton)

The central coordinator. Manages all subsystems and orchestrates the caching flow.

**File:** `src/Engine.php`

```php
// Recommended: Use the millicache() helper
$engine = millicache();

// Or access Engine directly
$engine = \MilliCache\Engine::instance();

// Access subsystems
$engine->storage();    // Redis connection
$engine->flags();      // Flag manager
$engine->config();     // Configuration
$engine->options();    // Runtime overrides
$engine->cache();      // Cache manager
$engine->clear();      // Invalidation manager
$engine->rules();      // Rules manager
```

### Storage

Handles all Redis operations.

**File:** `src/Core/Storage.php`

```php
$storage = $engine->storage();

// Low-level operations
$storage->set_cache( $key, $data, $flags );
$storage->get_cache( $key );
$storage->delete_cache( $key );
$storage->is_connected();
$storage->get_status();
```

### Settings

Configuration management with multi-source resolution.

**File:** `src/Core/Settings.php`

```php
$settings = new \MilliCache\Core\Settings();

// Get settings
$ttl = $settings->get( 'cache.ttl' );
$all = $settings->get_settings( 'cache' );

// Set settings
$settings->set( 'cache.ttl', 3600 );

// Import/Export
$settings->export( 'cache' );
$settings->import( $data );
```

### Cache Manager

Coordinates cache read/write operations.

**File:** `src/Engine/Cache/Manager.php`

```php
$cache = $engine->cache();

// Check for cached content
$result = $cache->get( $request_hash );

// Store content
$cache->set( $request_hash, $content, $headers, $flags );
```

### Invalidation Manager

Handles cache clearing with queuing and resolution.

**File:** `src/Engine/Cache/Invalidation/Manager.php`

```php
$invalidation = $engine->clear();

// Clear by various targets
$invalidation->posts( [ 1, 2, 3 ] );
$invalidation->flags( [ 'home', 'archive:post' ] );
$invalidation->urls( [ 'https://example.com/' ] );
$invalidation->sites( [ 1, 2 ], $network_id );
$invalidation->networks( 1 );
$invalidation->all();
```

### Flag Manager

Manages cache flags for the current request.

**File:** `src/Engine/Flags.php`

```php
$flags = $engine->flags();

// Manage flags
$flags->add( 'custom:my-flag' );
$flags->remove( 'home' );
$flags->get_all();
$flags->has( 'post:123' );
```

### Request Processor

Parses and normalizes incoming requests.

**File:** `src/Engine/Request/Processor.php`

```php
$request = new \MilliCache\Engine\Request\Processor( $config );

// Get cache key
$hash = $request->get_hash();

// Check if cacheable
$cacheable = $request->is_cacheable();
```

## Request Flow

### Cache Hit Flow

```
1. advanced-cache.php
   └─► Engine::start()
       └─► Bootstrap rules evaluated
           └─► Request parsed and hashed
               └─► Cache lookup (Redis GET)
                   └─► HIT: Decompress + send headers + output + exit
```

### Cache Miss Flow

```
1. advanced-cache.php
   └─► Engine::start()
       └─► Bootstrap rules evaluated
           └─► Request parsed and hashed
               └─► Cache lookup (Redis GET)
                   └─► MISS: Continue to WordPress

2. plugins_loaded hook
   └─► WordPress rules registered

3. template_redirect hook (priority 200)
   └─► WordPress rules evaluated
       └─► Output buffering starts

4. shutdown hook
   └─► Output buffer captured
       └─► Response validated
           └─► Flags collected
               └─► Cache stored (Redis SET)
                   └─► Output sent to browser
```

## Extension Points

### Hooks for Integration

```php
// Before cache storage
add_action( 'millicache_entry_storing', function( $hash, $key, $flags, $data ) {
    // Modify or log before storage
}, 10, 4 );

// After cache storage
add_action( 'millicache_entry_stored', function( $hash, $key, $flags, $data ) {
    // Notify external systems
}, 10, 4 );

// After cache clearing
add_action( 'millicache_cache_cleared', function( $expire ) {
    // Clear CDN, notify systems
} );

// Custom flags
add_filter( 'millicache_flags_for_request', function( $flags ) {
    // Add custom flags
    return $flags;
} );
```

### Custom Rule Actions

```php
// Create custom action
class MyCustomAction implements \MilliRules\Contracts\Action {
    public function execute( $context ) {
        // Custom logic
    }
}

// Register with MilliRules
use MilliRules\Rules;

Rules::add( [
    'id'      => 'mysite:custom',
    'condition' => [ 'request:path', 'matches', '/special/*' ],
    'actions' => [ new MyCustomAction() ],
    'order'   => 10,
    'phase'   => 'wp',
] );
```

## Dependencies

### External

| Package | Purpose |
|---------|---------|
| `predis/predis` | Redis client |
| `millipress/millirules` | Rules engine |

### WordPress

| Component | Usage |
|-----------|-------|
| Drop-in API | `advanced-cache.php` |
| Options API | Settings storage |
| Transients API | Backup storage |
| REST API | Remote management |
| WP-CLI | Command line |
| Hooks API | Integration points |

## Performance Considerations

### Early Exit

Cache hits exit before WordPress loads:
- No database queries
- No plugin/theme code
- Minimal memory usage

### Lazy Loading

Subsystems initialize on first use:
```php
// Storage not connected until first cache operation
$engine->storage()->get_cache( $key );
```

### Efficient Clearing

Invalidation uses Redis patterns:
```php
// Single Redis command clears matching keys
KEYS mll:flags:post:123:*
DEL [matching keys]
```

## Next Steps

- [Custom Rules](20-custom-rules.md) - Extend the rules engine
- [Hooks & Filters](30-hooks-filters.md) - All available hooks
- [API Reference](40-api-reference.md) - Function documentation
